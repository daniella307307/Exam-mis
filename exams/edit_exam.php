<?php
include("../db.php");
$exam_id = $_GET['exam_id'] ?? 0;

if (!$exam_id) {
    echo "Invalid Exam ID.";
    exit();
}

$stmt = $conn->prepare("SELECT * FROM exams WHERE exam_id=? LIMIT 1");
$stmt->bind_param("i", $exam_id);
$stmt->execute();
$exam = $stmt->get_result()->fetch_assoc();

if (!$exam) {
    echo "Exam not found.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title  = $_POST['title'];
    $topic  = $_POST['topic'];
    $grade  = $_POST['grade'];
    $status = $_POST['status'];

    if ($status === 'active' && empty($exam['exam_code'])) {
        $exam_code = mt_rand(100000, 999999);
    } else {
        $exam_code = $exam['exam_code'];
    }

    $update = $conn->prepare("UPDATE exams SET title=?, topic=?, grade=?, status=?, exam_code=? WHERE exam_id=?");
    $update->bind_param("sssssi", $title, $topic, $grade, $status, $exam_code, $exam_id);
    $update->execute();

    // Re-fetch updated exam so the form shows fresh data
    $stmt2 = $conn->prepare("SELECT * FROM exams WHERE exam_id=? LIMIT 1");
    $stmt2->bind_param("i", $exam_id);
    $stmt2->execute();
    $exam = $stmt2->get_result()->fetch_assoc();

    $success = true;
}

$qstmt = $conn->prepare("SELECT * FROM questions WHERE exam_id=? ORDER BY created_at ASC");
$qstmt->bind_param("i", $exam_id);
$qstmt->execute();
$questions = $qstmt->get_result();

// Safe escaped values for the form
$exam_title  = htmlspecialchars($exam['title'],  ENT_QUOTES, 'UTF-8');
$exam_topic  = htmlspecialchars($exam['topic'],  ENT_QUOTES, 'UTF-8');
$exam_grade  = htmlspecialchars($exam['grade'],  ENT_QUOTES, 'UTF-8');
$exam_status = $exam['status'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Exam Details</title>
<link rel="stylesheet" href="../dist/styles.css">
    <link rel="stylesheet" href="<?= APP_BASE_URL ?>/exams/assets/exam-theme.css">
</head>

<body class="bg-gray-100 min-h-screen exam-dark">
<?php include('../Auth/SF/header.php'); ?>

<!-- LAYOUT WRAPPER: sidebar + content side by side -->
<div class="flex flex-1 overflow-hidden">

    <!-- SIDEBAR -->
    <div class="bg-white border-r border-gray-200 min-h-screen w-64 hidden md:block lg:block">
        <?php include('dynamic_sidebar.php'); ?>
    </div>

    <!-- MAIN CONTENT -->
    <div class="flex-1 p-8 overflow-y-auto">

        <div class="max-w-3xl">

            <!-- Header -->
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-xl font-semibold" style="color:#fff">
                    Edit Exam
                </h1>

                <a href="edit_question.php?exam_id=<?= $exam_id ?>"
                   class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg shadow-sm transition float-right">
                    + Add Question
                </a>
            </div>

            <?php if (isset($success)): ?>
                <div class="mb-6 p-4 bg-green-100 text-green-800 rounded">
                    Exam updated successfully!
                </div>
            <?php endif; ?>

            <!-- Exam Edit Form -->
            <form method="POST" class="bg-white p-6 rounded shadow-md mb-8">
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Title</label>
                    <input type="text" name="title" value="<?= $exam_title ?>" class="
w-full border p-2 rounded" required>
                </div>

                <div class="mb-4">
                    <label class="block text
-gray-700 font-bold mb-2">Topic</label>
                    <input type="text" name="topic" value="<?= $exam_topic ?>" class="
w-full border p-2 rounded" required>
                </div>

                <div class="mb-4">
                    <label class="block text
-gray-700 font-bold mb-2">Grade</label>
                    <input type="text" name="grade" value="<?= $exam_grade ?>" class="w-full border p-2 rounded" required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Status</label>
                    <select name="status" class="w-full border p-2 rounded" required>
                        <option value="draft" <?= $exam_status === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="active" <?= $exam_status === 'active' ? 'selected' : '' ?>>Active</option>
                    </select>

                </div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                    Save Changes
                </button>
            </form>
            <!-- Questions List -->
            <div class="bg-white p-6 rounded shadow-md">
                <h2 class="text-lg font-semibold mb-4" style="color:#fff">Questions</h2>
                <?php if ($questions->num_rows > 0): ?>
                    <ul class="space-y-4">
                        <?php while ($question = $questions->fetch_assoc()): ?>
                            <li class="border p-4 rounded hover:bg-gray-50 transition">
                                <div class="flex justify-between items-center">
                                    <span><?= htmlspecialchars($question['question_text'], ENT_QUOTES, 'UTF-8') ?></span>
                                    <a href="edit_question.php?exam_id=<?= $exam_id ?>&question_id=<?= $question['question_id'] ?>" class="text-blue-600 hover:underline text-sm">
                                        Edit
                                    </a>
                                    <a href="delete_questions.php?exam_id=<?= $exam_id ?>&question_id=<?= $question['question_id'] ?>" class="text-red-600 hover:underline text-sm" onclick="return confirm('Are you sure you want to delete this question?');">
                                        Delete
                                    </a>
                                </div>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-gray-600">No questions added yet.</p>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<?php include('../Auth/SF/footer.php'); ?>
</body>
</html>
<?php $conn->close(); ?>
