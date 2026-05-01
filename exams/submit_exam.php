<?php
session_start();
include("../db.php");

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
    // ============ ESSAY ============
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