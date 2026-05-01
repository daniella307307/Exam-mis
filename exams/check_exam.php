<?php
header("Content-Type: application/json");
include('../db.php');

// Get exam code from query parameter
$exam_code = $_GET['code'] ?? '';

if (empty($exam_code)) {
    echo json_encode([
        "success" => false,
        "error" => "Please provide exam code parameter: ?code=123456"
    ]);
    exit;
}

// Query the exam
$stmt = $conn->prepare("SELECT * FROM exams WHERE exam_code = ? LIMIT 1");
$stmt->bind_param("s", $exam_code);
$stmt->execute();
$exam_result = $stmt->get_result();

if ($exam_result->num_rows === 0) {
    echo json_encode([
        "success" => false,
        "error" => "Exam not found with code: $exam_code",
        "tip" => "Check if the code is correct and was actually saved"
    ]);
    exit;
}

$exam = $exam_result->fetch_assoc();
$exam_id = $exam['exam_id'];

// Get questions for this exam
$q_stmt = $conn->prepare("SELECT question_id, question_text, question_type, marks FROM questions WHERE exam_id = ? ORDER BY question_id");
$q_stmt->bind_param("i", $exam_id);
$q_stmt->execute();
$questions_result = $q_stmt->get_result();

$questions = [];
while ($q = $questions_result->fetch_assoc()) {
    // Get options for this question
    $opt_stmt = $conn->prepare("SELECT option_text, is_correct FROM options WHERE question_id = ? ORDER BY option_id");
    $opt_stmt->bind_param("i", $q['question_id']);
    $opt_stmt->execute();
    $options_result = $opt_stmt->get_result();
    
    $options = [];
    while ($opt = $options_result->fetch_assoc()) {
        $options[] = $opt;
    }
    
    $q['options'] = $options;
    $questions[] = $q;
}

echo json_encode([
    "success" => true,
    "exam" => $exam,
    "question_count" => count($questions),
    "questions" => $questions
]);
?>

<?php $conn->close(); ?>
