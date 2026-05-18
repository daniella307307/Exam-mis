<?php
session_start();
include("../db.php");

/**
 * Lenient short-answer match.
 *
 *   - Case insensitive ("HUB" == "hub")
 *   - Punctuation stripped, whitespace collapsed
 *   - Token order doesn't matter ("hub and motors" matches "motors and hub")
 *   - Per-token typo tolerance via similar_text — singular/plural pairs like
 *     hub/hubs or motor/motors score ~85–91%, comfortably above the 70% cutoff
 *   - A question is correct overall when ≥70% of the expected tokens have a
 *     ≥70%-similar counterpart in the student's answer
 *   - Teachers can supply alternate answers separated by '|' ; any match wins
 */
function fuzzy_short_answer_match(string $expected, string $actual, int $threshold_pct = 70): bool {
    $normalize_tokens = function (string $s): array {
        $s = mb_strtolower(trim($s));
        $s = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $s); // strip punctuation, keep unicode letters/digits
        $tokens = preg_split('/\s+/', $s, -1, PREG_SPLIT_NO_EMPTY);
        return $tokens ?: [];
    };

    foreach (preg_split('/\s*\|\s*/', $expected) as $variant) {
        $exp = $normalize_tokens($variant);
        $act = $normalize_tokens($actual);
        if (empty($exp) || empty($act)) continue;

        // Fast path: order-insensitive exact match
        $a = $exp; $b = $act; sort($a); sort($b);
        if (implode(' ', $a) === implode(' ', $b)) return true;

        // Greedy per-token best match (each student token claimed at most once)
        $remaining = $act;
        $matched   = 0;
        foreach ($exp as $et) {
            $best_i = -1;
            $best_p = 0;
            foreach ($remaining as $i => $at) {
                similar_text($et, $at, $p);
                if ($p > $best_p) { $best_p = $p; $best_i = $i; }
            }
            if ($best_p >= $threshold_pct) {
                $matched++;
                unset($remaining[$best_i]);
                $remaining = array_values($remaining);
            }
        }

        $coverage = ($matched / count($exp)) * 100;
        if ($coverage >= $threshold_pct) return true;
    }
    return false;
}

// Fall back to POST hidden fields if the session expired during a long exam.
$exam_id   = (int) ($_SESSION['exam_id']   ?? $_POST['eid'] ?? 0);
$player_id = (int) ($_SESSION['player_id'] ?? $_POST['pid'] ?? 0);

if (!$exam_id || !$player_id) {
    header("Location: join_exam.php");
    exit();
}

// Verify the player actually belongs to this exam before saving anything.
$vchk = $conn->prepare("SELECT 1 FROM players WHERE player_id = ? AND exam_id = ?");
$vchk->bind_param("ii", $player_id, $exam_id);
$vchk->execute();
if (!$vchk->get_result()->fetch_row()) {
    header("Location: join_exam.php");
    exit();
}

// Re-hydrate session so downstream pages (results / exam_submitted) see them.
$_SESSION['exam_id']   = $exam_id;
$_SESSION['player_id'] = $player_id;

// Fetch questions WITH type
$qstmt = $conn->prepare("
    SELECT q.question_id, q.marks, q.question_type
    FROM questions q
    WHERE q.exam_id = ?
");
$qstmt->bind_param("i", $exam_id);
$qstmt->execute();
$questions = $qstmt->get_result()->fetch_all(MYSQLI_ASSOC);

$total_score = 0;

foreach ($questions as $q) {
    $qid = $q['question_id'];
    $qtype = $q['question_type'];
    $marks = (int) $q['marks'];
    
    // Get submitted answer value
    $submitted_value = $_POST["q{$qid}"] ?? '';

    $chosen = '';
    $is_correct = 0;
    $points = 0;

    // ============ MCQ ============
    if ($qtype === 'mcq') {
        $selected_option_id = (int) $submitted_value;
        
        if ($selected_option_id > 0) {
            // Fetch option and check if correct
            $check = $conn->prepare("
                SELECT option_text, is_correct
                FROM options
                WHERE option_id = ? AND question_id = ?
            ");
            $check->bind_param("ii", $selected_option_id, $qid);
            $check->execute();
            $result = $check->get_result()->fetch_assoc();
            
            if ($result) {
                $chosen = $result['option_text'];
                $is_correct = (int) $result['is_correct'];
                $points = $is_correct ? $marks : 0;
            }
        }
    }
    // ============ TRUE/FALSE ============
    else if ($qtype === 'true_false') {
        $selected_option_id = (int) $submitted_value;
        
        if ($selected_option_id > 0) {
            // Fetch option and check if correct
            $check = $conn->prepare("
                SELECT option_text, is_correct
                FROM options
                WHERE option_id = ? AND question_id = ?
            ");
            $check->bind_param("ii", $selected_option_id, $qid);
            $check->execute();
            $result = $check->get_result()->fetch_assoc();
            
            if ($result) {
                $chosen = $result['option_text']; // "True" or "False"
                $is_correct = (int) $result['is_correct'];
                $points = $is_correct ? $marks : 0;
            }
        }
    }
    // ============ SHORT ANSWER (lenient auto-grade) ============
    else if ($qtype === 'short_answer') {
        $chosen = trim($submitted_value);
        if ($chosen !== '') {
            // Fetch the teacher's expected answer (stored as the is_correct=1
            // option by publish_exam.php). If absent, this short_answer was
            // published before the expected-answer feature existed — fall
            // through with 0 points so the teacher can grade it manually,
            // exactly like an essay.
            $ans_stmt = $conn->prepare("SELECT option_text FROM options WHERE question_id = ? AND is_correct = 1 LIMIT 1");
            $ans_stmt->bind_param("i", $qid);
            $ans_stmt->execute();
            $row = $ans_stmt->get_result()->fetch_assoc();
            $ans_stmt->close();

            $expected = trim((string)($row['option_text'] ?? ''));
            if ($expected !== '' && fuzzy_short_answer_match($expected, $chosen)) {
                $is_correct = 1;
                $points     = $marks;
            }
        }
    }
    // ============ ESSAY (manual grading) ============
    else if ($qtype === 'essay') {
        $chosen = trim($submitted_value);
        if (!empty($chosen)) {
            $is_correct = 0;
            $points = 0;
        }
    }
    // ============ PRACTICAL (PROJECT LINK) ============
    else if ($qtype === 'practical') {
        $chosen = trim($submitted_value);
        if (!empty($chosen)) {
            $is_correct = 0; // Teacher reviews manually
            $points = 0;
        }
    }

    $total_score += $points;

    // Save answer to database
    $ins = $conn->prepare("
        INSERT INTO answers 
        (player_id, exam_id, question_id, chosen_answer, is_correct, points_earned)
        VALUES (?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            chosen_answer = VALUES(chosen_answer),
            is_correct = VALUES(is_correct),
            points_earned = VALUES(points_earned)
    ");

    $ins->bind_param(
        "iiisii",
        $player_id,
        $exam_id,
        $qid,
        $chosen,
        $is_correct,
        $points
    );

    if (!$ins->execute()) {
        error_log("Answer save error for Q{$qid}: " . $ins->error);
    }
}

// Update player score
$upd = $conn->prepare("UPDATE players SET score = ? WHERE player_id = ?");
$upd->bind_param("ii", $total_score, $player_id);
$upd->execute();

// Group propagation: in group mode, only the placeholder (the row whose
// player_id sits in the session) actually answered questions; the other group
// members are sibling rows linked by group_nbr (set in waiting_room.php).
// Push the same score onto every sibling so the leaderboard and per-student
// reports show all named members with the team's score instead of 0.
$gn_stmt = $conn->prepare("SELECT group_nbr FROM players WHERE player_id = ? LIMIT 1");
$gn_stmt->bind_param("i", $player_id);
$gn_stmt->execute();
$gn_row = $gn_stmt->get_result()->fetch_assoc();
$gn_stmt->close();

$group_nbr = (int)($gn_row['group_nbr'] ?? 0);
if ($group_nbr > 0) {
    $g_upd = $conn->prepare(
        "UPDATE players SET score = ? WHERE exam_id = ? AND group_nbr = ?"
    );
    $g_upd->bind_param("iii", $total_score, $exam_id, $group_nbr);
    $g_upd->execute();
    $g_upd->close();
}

// ---- Certificate issuance ----
// Get total marks and exam passing threshold
$tm_stmt = $conn->prepare("SELECT SUM(marks) AS total FROM questions WHERE exam_id = ?");
$tm_stmt->bind_param("i", $exam_id);
$tm_stmt->execute();
$total_marks = (int)($tm_stmt->get_result()->fetch_assoc()['total'] ?? 0);

$col_chk = $conn->query("SHOW COLUMNS FROM exams LIKE 'passing_score'");
$cert_cols = ($col_chk && $col_chk->num_rows > 0);

if ($cert_cols) {
    $ex_stmt = $conn->prepare("
        SELECT e.title, e.passing_score, e.exam_certification, c.certification_name
        FROM exams e
        LEFT JOIN certifications c ON e.exam_certification = c.certification_id
        WHERE e.exam_id = ?
    ");
} else {
    $ex_stmt = $conn->prepare("SELECT title FROM exams WHERE exam_id = ?");
}
$ex_stmt->bind_param("i", $exam_id);
$ex_stmt->execute();
$exam_info = $ex_stmt->get_result()->fetch_assoc();

$passing_score = (int)($exam_info['passing_score'] ?? 50);
$percentage    = $total_marks > 0 ? (int)round(($total_score / $total_marks) * 100) : 0;

$tbl_chk = $conn->query("SHOW TABLES LIKE 'student_certificates'");
$cert_table_exists = ($tbl_chk && $tbl_chk->num_rows > 0);

if ($cert_table_exists && $percentage >= $passing_score) {
    // Get player nickname
    $pn_stmt = $conn->prepare("SELECT nickname FROM players WHERE player_id = ?");
    $pn_stmt->bind_param("i", $player_id);
    $pn_stmt->execute();
    $player_name = $pn_stmt->get_result()->fetch_assoc()['nickname'] ?? 'Student';

    // Check if cert already issued
    $chk_stmt = $conn->prepare("SELECT cert_id FROM student_certificates WHERE player_id = ? AND exam_id = ?");
    $chk_stmt->bind_param("ii", $player_id, $exam_id);
    $chk_stmt->execute();
    $already_issued = $chk_stmt->get_result()->num_rows > 0;

    if (!$already_issued) {
        $cert_code    = 'CERT-' . strtoupper(bin2hex(random_bytes(6)));
        $cert_name    = $exam_info['certification_name'] ?? null;
        $cert_id_fk   = $exam_info['exam_certification'] ? (int)$exam_info['exam_certification'] : null;
        $exam_title   = $exam_info['title'] ?? '';

        $ci_stmt = $conn->prepare("
            INSERT INTO student_certificates
                (player_id, exam_id, player_name, exam_title, certification_id, certification_name,
                 score, total_marks, percentage, cert_code)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $ci_stmt->bind_param(
            "iissisiiis",
            $player_id, $exam_id, $player_name, $exam_title,
            $cert_id_fk, $cert_name,
            $total_score, $total_marks, $percentage, $cert_code
        );
        $ci_stmt->execute();
    }
}
// ---- End certificate issuance ----

// Check if exam has practical questions
$has_practical = false;
foreach ($questions as $q) {
    if ($q['question_type'] === 'practical') {
        $has_practical = true;
        break;
    }
}

if (isset($conn) && $conn instanceof mysqli) { $conn->close(); }

$_SESSION['exam_id']   = $exam_id;
$_SESSION['player_id'] = $player_id;
session_write_close();

$q = "?eid=" . $exam_id . "&pid=" . $player_id;

if ($has_practical) {
    header("Location: exam_submitted.php" . $q);
} else {
    header("Location: leaderboard.php" . $q);
}
exit();
?>