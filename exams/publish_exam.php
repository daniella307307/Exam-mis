<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log to file in exams directory
$logFile = __DIR__ . '/publish_log.txt';
ini_set('error_log', $logFile);

// Use the single-connection wrapper to reduce connection churn
require_once('../db_connection.php');

header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        throw new Exception('No data received');
    }

    $exam_id          = isset($data['exam_id']) && $data['exam_id'] ? (int)$data['exam_id'] : 0;
    $title            = $data['title'] ?? '';
    $topic            = $data['topic'] ?? 'General';
    $grade            = $data['grade'] ?? '10';
    $duration         = intval($data['duration'] ?? 60);
    $school_id        = isset($data['school_id']) ? intval($data['school_id']) : 0;
    $passing_score    = isset($data['passing_score']) ? intval($data['passing_score']) : 50;
    $exam_certification = isset($data['exam_certification']) && $data['exam_certification'] ? intval($data['exam_certification']) : null;
    $questions        = $data['questions'] ?? [];
    $user_id          = 1; // Default user

    if (!$title || empty($questions)) {
        throw new Exception('Title and questions required');
    }

    if (!$school_id) {
        throw new Exception('School is required');
    }

    // Start transaction
    $conn->begin_transaction();

    $now = date('Y-m-d H:i:s');
    $end = date('Y-m-d H:i:s', strtotime("+$duration minutes"));

    if ($exam_id) {
        // UPDATE existing exam
        error_log("[PUBLISH] Updating exam ID=$exam_id");
        $stmt = $conn->prepare("UPDATE exams SET title=?, topic=?, grade=?, duration=?, school_id=? WHERE exam_id=?");
        $stmt->bind_param("sssiiii", $title, $topic, $grade, $duration, $school_id, $exam_id);
        if (!$stmt->execute()) {
            throw new Exception('Exam update: ' . $stmt->error);
        }
        $stmt->close();

        // Delete old questions and options
        $ostmt = $conn->prepare("DELETE FROM options WHERE question_id IN (SELECT question_id FROM questions WHERE exam_id = ?)");
        $ostmt->bind_param("i", $exam_id);
        $ostmt->execute();
        $ostmt->close();

        $qdelstmt = $conn->prepare("DELETE FROM questions WHERE exam_id = ?");
        $qdelstmt->bind_param("i", $exam_id);
        $qdelstmt->execute();
        $qdelstmt->close();

        error_log("[PUBLISH] Deleted old questions for exam $exam_id");
    } else {
        // CREATE new exam
        $exam_code = rand(10000, 99999);
        $pin = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        $stmt = $conn->prepare("INSERT INTO exams (title, exam_code, topic, grade, duration, created_by, school_id, status, start_time, end_time, pin, is_active)
                                VALUES (?, ?, ?, ?, ?, ?, ?, 'active', ?, ?, ?, 1)");
        $stmt->bind_param("siisiiisss", $title, $exam_code, $topic, $grade, $duration, $user_id, $school_id, $now, $end, $pin);

        if (!$stmt->execute()) {
            throw new Exception('Exam insert: ' . $stmt->error);
        }

        $exam_id = $stmt->insert_id;
        $stmt->close();
        error_log("[PUBLISH] New exam created: ID=$exam_id, Title=$title");
    }

    // Debug: Log that exam was created/updated
    error_log("[PUBLISH] Questions array count: " . count($questions));
    error_log("[PUBLISH] Questions data: " . json_encode($questions));

    // Add questions
    foreach ($questions as $q) {
        $qtext = $q['text'] ?? '';
        $qtype = strtolower(str_replace('-', '_', $q['type'] ?? 'essay'));
        error_log("[DEBUG] Raw type from JS: " . ($q['type'] ?? 'MISSING') . " | Mapped to: $qtype");
        
        // Map types
        if ($qtype === 'multiple') $qtype = 'mcq';
        if ($qtype === 'short_answer') $qtype = 'essay';
        
        $marks = intval($q['points'] ?? 1);

        error_log("[PUBLISH] Processing question: type=$qtype, marks=$marks, text=" . substr($qtext, 0, 50));

        $stmt = $conn->prepare("INSERT INTO questions (exam_id, question_text, question_type, marks) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("issi", $exam_id, $qtext, $qtype, $marks);
        
        if (!$stmt->execute()) {
            error_log("[PUBLISH] Question insert FAILED: " . $stmt->error);
            throw new Exception('Question insert: ' . $stmt->error);
        }
        
        error_log("[PUBLISH] Question inserted: ID=" . $stmt->insert_id);
        
        $question_id = $stmt->insert_id;
        $stmt->close();

    // Add options (for MCQ, True/False, and Practical PDF)
        if ($qtype === 'mcq' && isset($q['options']) && is_array($q['options'])) {
            error_log("[PUBLISH] Adding MCQ options, count: " . count($q['options']));
            foreach ($q['options'] as $idx => $opt) {
                $is_correct = ($idx === $q['correctAnswer']) ? 1 : 0;
                error_log("[PUBLISH]   Option $idx: correct=$is_correct, text=" . substr($opt, 0, 30));
                $stmt = $conn->prepare("INSERT INTO options (question_id, option_text, is_correct) VALUES (?, ?, ?)");
                $stmt->bind_param("isi", $question_id, $opt, $is_correct);
                if (!$stmt->execute()) {
                    error_log("[PUBLISH] Option insert FAILED: " . $stmt->error);
                    throw new Exception('Option insert for MCQ: ' . $stmt->error);
                }
                $stmt->close();
            }
        } 
        else if ($qtype === 'true_false') {
            error_log("[PUBLISH] Adding True/False options");
            // True option
            $text = 'True';
            $correct = ($q['correctAnswer'] === true || $q['correctAnswer'] === 1) ? 1 : 0;
            error_log("[PUBLISH]   True option: correct=$correct");
            $stmt = $conn->prepare("INSERT INTO options (question_id, option_text, is_correct) VALUES (?, ?, ?)");
            $stmt->bind_param("isi", $question_id, $text, $correct);
            if (!$stmt->execute()) {
                error_log("[PUBLISH] True option insert FAILED: " . $stmt->error);
                throw new Exception('True option insert: ' . $stmt->error);
            }
            $stmt->close();
            
            // False option
            $text = 'False';
            $correct = ($q['correctAnswer'] === true || $q['correctAnswer'] === 1) ? 0 : 1;
            error_log("[PUBLISH]   False option: correct=$correct");
            $stmt = $conn->prepare("INSERT INTO options (question_id, option_text, is_correct) VALUES (?, ?, ?)");
            $stmt->bind_param("isi", $question_id, $text, $correct);
            if (!$stmt->execute()) {
                error_log("[PUBLISH] False option insert FAILED: " . $stmt->error);
                throw new Exception('False option insert: ' . $stmt->error);
            }
            $stmt->close();
        }
        else if ($qtype === 'practical') {
            // Practical question - persist the Bunny URL (if provided) as an option so the viewer can show the link
            if (!empty($q['bunny_url'])) {
                $bunnyUrl = $q['bunny_url'];
                error_log("[PUBLISH] Adding practical PDF option: " . substr($bunnyUrl, 0, 200));
                $pstmt = $conn->prepare("INSERT INTO options (question_id, option_text, is_correct) VALUES (?, ?, 0)");
                if ($pstmt === false) {
                    error_log("[PUBLISH] prepare failed for practical option: " . $conn->error);
                } else {
                    $pstmt->bind_param("is", $question_id, $bunnyUrl);
                    if (!$pstmt->execute()) {
                        error_log("[PUBLISH] Practical option insert FAILED: " . $pstmt->error);
                        throw new Exception('Practical option insert: ' . $pstmt->error);
                    }
                    $pstmt->close();
                }
            } else {
                error_log("[PUBLISH] Practical question missing bunny_url, skipping option insert");
            }
        } else {
            error_log("[PUBLISH] Question type $qtype has no options (essay/short-answer)");
        }
        // Essays do NOT have options; they are graded manually
    }

    $conn->commit();
    error_log("[PUBLISH] Transaction COMMITTED successfully for exam $exam_id");
    
    // Always fetch and return exam code
    $cstmt = $conn->prepare("SELECT exam_code FROM exams WHERE exam_id = ?");
    $cstmt->bind_param("i", $exam_id);
    $cstmt->execute();
    $code_result = $cstmt->get_result()->fetch_assoc();
    $exam_code = $code_result['exam_code'] ?? 'N/A';
    $cstmt->close();
    
    echo json_encode(['success' => true, 'exam_id' => $exam_id, 'exam_code' => $exam_code]);

} catch (Exception $e) {
    error_log("[PUBLISH] Exception caught: " . $e->getMessage());
    if (isset($conn)) {
        error_log("[PUBLISH] Rolling back transaction");
        $conn->rollback();
        // connection is managed by db_connection.php wrapper; do not explicitly close here
    }
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>