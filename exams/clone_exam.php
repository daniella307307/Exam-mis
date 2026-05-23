<?php
/**
 * Republish a Public-Library exam into the current teacher's school.
 *
 * Deep-clones the source exam:
 *   - new exams row owned by the current user, scoped to their school,
 *     with a brand-new exam_code + pin and status='draft'
 *   - copies every question
 *   - copies every option (preserves is_correct so the new owner inherits
 *     answer keys — they're now the owner, so it's expected)
 *   - remembers the source via exams.cloned_from_exam_id so the dashboard
 *     can render "📝 Adapted from [Original Teacher]"
 *
 * Source eligibility: must be `is_public = 1`. We deliberately do NOT
 * allow cloning of same-school exams — those already behave as "mine"
 * under the new ACL, so cloning would just create confusing duplicates.
 *
 * POST: exam_id  (the source exam to clone)
 * Response: { success: bool, new_exam_id?: int, error?: string }
 */
require_once('../db_connection.php');
require_once(__DIR__ . '/lib/exam_acl.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'POST only']);
    exit;
}

$acl = exam_acl_context($conn);
if (!$acl['user_id']) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$src_exam_id   = (int)($_POST['exam_id'] ?? 0);
$user_id       = (int)$acl['user_id'];
$my_school_id  = (int)($acl['school_id'] ?? 0);

if ($src_exam_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'exam_id required']);
    exit;
}

// ---------------------------------------------------------------------
// Idempotent migration: ensure exams.cloned_from_exam_id exists. Lets the
// teacher run the feature without manually applying the SQL migration.
// SHOW COLUMNS doesn't throw, ALTER is wrapped in a column-exists guard.
// ---------------------------------------------------------------------
$col_check = $conn->query("SHOW COLUMNS FROM `exams` LIKE 'cloned_from_exam_id'");
if ($col_check && $col_check->num_rows === 0) {
    @ $conn->query("ALTER TABLE `exams` ADD COLUMN `cloned_from_exam_id` INT NULL DEFAULT NULL");
}
if ($col_check) { $col_check->close(); }

// ---------------------------------------------------------------------
// Load the source exam and verify it's republish-eligible.
// ---------------------------------------------------------------------
$sstmt = $conn->prepare(
    "SELECT exam_id, title, topic, grade, duration, school_id, created_by,
            is_public, passing_score, exam_certification
       FROM exams
      WHERE exam_id = ? LIMIT 1"
);
$sstmt->bind_param('i', $src_exam_id);
$sstmt->execute();
$src = $sstmt->get_result()->fetch_assoc();
$sstmt->close();

if (!$src) {
    echo json_encode(['success' => false, 'error' => 'Source exam not found']);
    exit;
}

if ((int)$src['is_public'] !== 1 && !$acl['is_developer']) {
    // The Public-Library button is the only legitimate entrance to this
    // endpoint. Refuse clones of private same-school or cross-school exams.
    echo json_encode([
        'success' => false,
        'error'   => 'Only exams marked Public can be republished. Ask the original teacher to mark it Public.',
    ]);
    exit;
}

// Picking the title: keep the original — students see the exam name as the
// original teacher wrote it. The "Adapted from" credit lives separately on
// the dashboard card via cloned_from_exam_id.
$new_title       = (string)$src['title'];
$new_topic       = (string)$src['topic'];
$new_grade       = (string)$src['grade'];
$new_duration    = (int)$src['duration'];
$passing_score   = isset($src['passing_score']) ? (int)$src['passing_score'] : 50;
$exam_cert       = isset($src['exam_certification']) && $src['exam_certification']
                     ? (int)$src['exam_certification']
                     : null;

$new_exam_code = (string)rand(10000, 99999);
$new_pin       = str_pad((string)rand(1, 9999), 4, '0', STR_PAD_LEFT);
$now           = date('Y-m-d H:i:s');
$end           = date('Y-m-d H:i:s', strtotime("+{$new_duration} minutes"));

// ---------------------------------------------------------------------
// Transactional deep-copy. If anything fails, no orphan rows remain.
// ---------------------------------------------------------------------
$conn->begin_transaction();
try {
    // Insert the new exam row, owned by the current teacher at their school.
    // status=draft so the teacher can review before activating; is_public=0
    // so the cloned copy doesn't immediately re-appear in someone else's
    // Public Library.
    $ins = $conn->prepare(
        "INSERT INTO exams
           (title, exam_code, topic, grade, duration, created_by, school_id,
            status, start_time, end_time, pin, is_active, is_public,
            passing_score, exam_certification, cloned_from_exam_id)
         VALUES (?, ?, ?, ?, ?, ?, ?, 'draft', ?, ?, ?, 0, 0, ?, ?, ?)"
    );
    $ins->bind_param(
        'ssssiiisssiii',
        $new_title, $new_exam_code, $new_topic, $new_grade, $new_duration,
        $user_id, $my_school_id, $now, $end, $new_pin,
        $passing_score, $exam_cert, $src_exam_id
    );
    if (!$ins->execute()) {
        throw new Exception('Exam insert failed: ' . $ins->error);
    }
    $new_exam_id = $ins->insert_id;
    $ins->close();

    // Pull all questions from the source.
    $qstmt = $conn->prepare(
        "SELECT question_id, question_text, question_type, marks
           FROM questions WHERE exam_id = ? ORDER BY question_id ASC"
    );
    $qstmt->bind_param('i', $src_exam_id);
    $qstmt->execute();
    $src_questions = $qstmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $qstmt->close();

    foreach ($src_questions as $sq) {
        $qins = $conn->prepare(
            "INSERT INTO questions (exam_id, question_text, question_type, marks)
             VALUES (?, ?, ?, ?)"
        );
        $qins->bind_param('issi', $new_exam_id, $sq['question_text'], $sq['question_type'], $sq['marks']);
        if (!$qins->execute()) {
            throw new Exception('Question copy failed: ' . $qins->error);
        }
        $new_qid = $qins->insert_id;
        $qins->close();

        // Copy options for this question. The new owner inherits the answer
        // keys (is_correct) — they're the owner now, so this is intentional.
        $ostmt = $conn->prepare(
            "SELECT option_text, is_correct
               FROM options WHERE question_id = ? ORDER BY option_id ASC"
        );
        $ostmt->bind_param('i', $sq['question_id']);
        $ostmt->execute();
        $opt_rows = $ostmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $ostmt->close();

        foreach ($opt_rows as $op) {
            $oins = $conn->prepare(
                "INSERT INTO options (question_id, option_text, is_correct)
                 VALUES (?, ?, ?)"
            );
            $iscorrect = (int)$op['is_correct'];
            $oins->bind_param('isi', $new_qid, $op['option_text'], $iscorrect);
            if (!$oins->execute()) {
                throw new Exception('Option copy failed: ' . $oins->error);
            }
            $oins->close();
        }
    }

    $conn->commit();
    echo json_encode([
        'success'      => true,
        'new_exam_id'  => $new_exam_id,
        'new_exam_code'=> $new_exam_code,
        'questions'    => count($src_questions),
    ]);
} catch (Throwable $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
