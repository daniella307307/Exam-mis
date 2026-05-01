<?php
session_start();
include('../db.php');

$exam_id = $_GET['exam_id'] ?? 0;
if (!$exam_id) {
    echo "Invalid Exam ID.";
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question_text = $_POST['question_text'];
    $question_type = $_POST['question_type'];
    $marks = $_POST['marks'] ?? 1;

    $stmt = $conn->prepare("INSERT INTO questions (exam_id, question_text, question_type, marks) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("issi", $exam_id, $question_text, $question_type, $marks);
    $stmt->execute();
    $question_id = $stmt->insert_id;

    // Handle MCQ options
    if ($question_type == 'mcq') {
        $options = $_POST['option_text'] ?? [];
        $correct_index = $_POST['correct_option'] ?? -1;
        foreach ($options as $i => $opt) {
            $is_correct = ($i == $correct_index) ? 1 : 0;
            $stmt2 = $conn->prepare("INSERT INTO options (question_id, option_text, is_correct) VALUES (?, ?, ?)");
            $stmt2->bind_param("isi", $question_id, $opt, $is_correct);
            $stmt2->execute();
        }
    }

    header("Location: edit_exam.php?exam_id=$exam_id&success=1");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Question</title>
<link rel="stylesheet" href="../dist/styles.css">
<script>
function toggleOptions() {
    const type = document.querySelector('select[name="question_type"]').value;
    document.getElementById('mcq-options').style.display = (type === 'mcq') ? 'block' : 'none';
}
function addOptionField() {
    const container = document.getElementById('mcq-options-container');
    const index = container.children.length;
    const div = document.createElement('div');
    div.classList.add('mb-1');
    div.innerHTML = `
        <input type="radio" name="correct_option" value="${index}" required> 
        <input type="text" name="option_text[]" placeholder="Option text" class="border p-1 rounded w-3/4" required>
    `;
    container.appendChild(div);
}
</script>
    <link rel="stylesheet" href="/Exam-mis/exams/assets/exam-theme.css">
</head>
<body class="bg-gray-100 min-h-screen p-6 exam-dark">
<div class="max-w-2xl mx-auto bg-white p-6 rounded shadow-md">
    <h1 class="text-xl font-bold mb-4" style="color:#fff">Add Question</h1>

    <form method="POST">
        <div class="mb-2">
            <label>Question Text</label>
            <textarea name="question_text" class="w-full border p-2 rounded" required></textarea>
        </div>

        <div class="mb-2">
            <label>Question Type</label>
            <select name="question_type" onchange="toggleOptions()" class="w-full border p-2 rounded" required>
                <option value="mcq">Multiple Choice (MCQ)</option>
                <option value="true_false">True/False</option>
                <option value="essay">Essay</option>
            </select>
        </div>

        <div class="mb-2">
            <label>Marks</label>
            <input type="number" name="marks" value="1" class="w-full border p-2 rounded">
        </div>

        <div id="mcq-options" class="mb-2">
            <label>Options</label>
            <div id="mcq-options-container"></div>
            <button type="button" onclick="addOptionField()" class="bg-green-500 text-white px-2 py-1 rounded mt-1">Add Option</button>
        </div>

        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Add Question</button>
    </form>
</div>
</body>
</html>
<?php $conn->close(); ?>
