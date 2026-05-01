<?php
require_once('../db_connection.php');
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$exam_id = isset($data['exam_id']) ? (int)$data['exam_id'] : 0;

if (!$exam_id) {
    echo json_encode(['success' => false, 'error' => 'No exam ID provided']);
    exit;
}

try {
    // Start transaction
    $conn->begin_transaction();

    // Delete options for all questions in this exam
    $stmt = $conn->prepare("DELETE FROM options WHERE question_id IN 
                            (SELECT question_id FROM questions WHERE exam_id = ?)");
    $stmt->bind_param('i', $exam_id);
    $stmt->execute();
    $stmt->close();

    // Delete all questions for this exam
    $stmt = $conn->prepare("DELETE FROM questions WHERE exam_id = ?");
    $stmt->bind_param('i', $exam_id);
    $stmt->execute();
    $stmt->close();

    // Delete the exam itself
    $stmt = $conn->prepare("DELETE FROM exams WHERE exam_id = ?");
    $stmt->bind_param('i', $exam_id);
    if (!$stmt->execute()) {
        throw new Exception('Failed to delete exam: ' . $stmt->error);
    }
    $stmt->close();

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Exam deleted successfully']);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
