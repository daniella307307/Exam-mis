<?php
session_start();
include('../db.php');

// Get the exam ID from GET parameter
$exam_id = $_GET['exam_id'] ?? 0;
if (!$exam_id) {
    echo "Invalid Exam ID.";
    exit();
}

// Fetch exam details
$stmt = $conn->prepare("SELECT * FROM exams WHERE exam_id=? LIMIT 1");
$stmt->bind_param("i", $exam_id);
$stmt->execute();
$exam = $stmt->get_result()->fetch_assoc();

if (!$exam) {
    echo "Exam not found.";
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $topic = $_POST['topic'];
    $grade = $_POST['grade'];
    $status = $_POST['status'];

    // If publishing and exam_code is empty, generate a 6-digit code
    if ($status === 'active' && empty($exam['exam_code'])) {
        $exam_code = mt_rand(100000, 999999);
    } else {
        $exam_code = $exam['exam_code'];
    }

    $update = $conn->prepare("UPDATE exams SET title=?, topic=?, grade=?, status=?, exam_code=? WHERE exam_id=?");
    $update->bind_param("sssssi", $title, $topic, $grade, $status, $exam_code, $exam_id);
    $update->execute();

    header("Location: edit_exam.php?exam_id=$exam_id&success=1");
    exit();
}

// Fetch questions for this exam
$qstmt = $conn->prepare("SELECT * FROM questions WHERE exam_id=? ORDER BY created_at ASC");
$qstmt->bind_param("i", $exam_id);
$qstmt->execute();
$questions = $qstmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Exam - <?= htmlspecialchars($exam['title']) ?></title>
<link rel="stylesheet" href="../dist/styles.css">
</head>
<body class="bg-gray-100 min-h-screen p-6">

<div class="max-w-4xl mx-auto bg-white p-6 rounded shadow-md">
    <h1 class="text-2xl font-bold mb-4">Edit Exam: <?= htmlspecialchars($exam['title']) ?></h1>

    <?php if (isset($_GET['success'])): ?>
        <div class="bg-green-200 text-green-800 p-2 mb-4 rounded">Exam updated successfully!</div>
    <?php endif; ?>

    <!-- Exam Details Form -->
    <form method="POST" class="mb-6">
        <div class="mb-2">
            <label>Title</label>
            <input type="text" name="title" value="<?= htmlspecialchars($exam['title']) ?>" class="w-full border p-2 rounded">
        </div>
        <div class="mb-2">
            <label>Topic</label>
            <input type="text" name="topic" value="<?= htmlspecialchars($exam['topic']) ?>" class="w-full border p-2 rounded">
        </div>
        <div class="mb-2">
            <label>Grade</label>
            <input type="text" name="grade" value="<?= htmlspecialchars($exam['grade']) ?>" class="w-full border p-2 rounded">
        </div>
        <div class="mb-2">
            <label>Status</label>
            <select name="status" class="w-full border p-2 rounded">
                <option value="draft" <?= $exam['status']=='draft' ? 'selected' : '' ?>>Draft</option>
                <option value="active" <?= $exam['status']=='active' ? 'selected' : '' ?>>Active</option>
                <option value="ended" <?= $exam['status']=='ended' ? 'selected' : '' ?>>Ended</option>
            </select>
        </div>
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Save Changes</button>
    </form>

    <hr class="my-4">

    <!-- Questions -->
    <h2 class="text-xl font-bold mb-2">Questions</h2>
    <a href="add_question.php?exam_id=<?= $exam_id ?>" class="bg-green-500 text-white px-3 py-1 rounded mb-2 inline-block">Add New Question</a>

    <?php while($q = $questions->fetch_assoc()): ?>
        <div class="border p-3 rounded mb-2">
            <p><strong>Question:</strong> <?= htmlspecialchars($q['question_text']) ?> (<?= $q['marks'] ?> marks)</p>
            <p><strong>Type:</strong> <?= htmlspecialchars($q['question_type']) ?></p>
            <a href="edit_question.php?question_id=<?= $q['question_id'] ?>" class="text-blue-600 mr-2">Edit</a>
            <a href="delete_question.php?question_id=<?= $q['question_id'] ?>&exam_id=<?= $exam_id ?>" class="text-red-600">Delete</a>
        </div>
    <?php endwhile; ?>
</div>

</body>
</html>