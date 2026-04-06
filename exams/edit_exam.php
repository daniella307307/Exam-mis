<?php
session_start();
include('../db.php');

$exam_id = $_GET['exam_id'] ?? 0;

if (!$exam_id) {
    echo "Invalid Exam ID.";
    exit();
}

/* Fetch exam */
$stmt = $conn->prepare("SELECT * FROM exams WHERE exam_id=? LIMIT 1");
$stmt->bind_param("i", $exam_id);
$stmt->execute();
$exam = $stmt->get_result()->fetch_assoc();

if (!$exam) {
    echo "Exam not found.";
    exit();
}

/* Update exam */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title  = $_POST['title'];
    $topic  = $_POST['topic'];
    $grade  = $_POST['grade'];
    $status = $_POST['status'];

    if ($status === 'active' && empty($exam['exam_code'])) {
        $exam_code = mt_rand(100000,999999);
    } else {
        $exam_code = $exam['exam_code'];
    }

    $update = $conn->prepare("UPDATE exams SET title=?, topic=?, grade=?, status=?, exam_code=? WHERE exam_id=?");
    $update->bind_param("sssssi",$title,$topic,$grade,$status,$exam_code,$exam_id);
    $update->execute();

    header("Location: edit_exam.php?exam_id=$exam_id&success=1");
    exit();
}

/* Fetch questions */
$qstmt = $conn->prepare("SELECT * FROM questions WHERE exam_id=? ORDER BY created_at ASC");
$qstmt->bind_param("i",$exam_id);
$qstmt->execute();
$questions = $qstmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Edit Exam</title>

<link rel="stylesheet" href="../dist/styles.css">

</head>

<body class="bg-gray-50 min-h-screen flex flex-col items-center p-6">

<!-- Header -->

<div class="w-full max-w-3xl flex justify-between items-center mb-6">

<h1 class="text-xl font-bold text-gray-700">
Edit Exam
</h1>

<a href="exam_details.php"
class="text-sm text-purple-600 font-semibold">
← Back
</a>

</div>


<!-- Exam Card -->

<div class="w-full max-w-3xl bg-white border border-gray-200 rounded-xl shadow-sm p-6 mb-6">

<h2 class="text-2xl font-bold text-gray-800 mb-4">
<?= htmlspecialchars($exam['title']) ?>
</h2>

<?php if (isset($_GET['success'])): ?>
<div class="bg-green-100 text-green-700 px-3 py-2 rounded mb-4 text-sm">
Exam updated successfully
</div>
<?php endif; ?>


<form method="POST" class="space-y-4">

<div>
<label class="text-sm font-semibold text-gray-600">Title</label>
<input
type="text"
name="title"
value="<?= htmlspecialchars($exam['title']) ?>"
class="w-full border border-gray-200 rounded-lg p-2 mt-1">
</div>


<div>
<label class="text-sm font-semibold text-gray-600">Topic</label>
<input
type="text"
name="topic"
value="<?= htmlspecialchars($exam['topic']) ?>"
class="w-full border border-gray-200 rounded-lg p-2 mt-1">
</div>


<div>
<label class="text-sm font-semibold text-gray-600">Grade</label>
<input
type="text"
name="grade"
value="<?= htmlspecialchars($exam['grade']) ?>"
class="w-full border border-gray-200 rounded-lg p-2 mt-1">
</div>


<div>
<label class="text-sm font-semibold text-gray-600">Status</label>

<select
name="status"
class="w-full border border-gray-200 rounded-lg p-2 mt-1">

<option value="draft" <?= $exam['status']=='draft' ? 'selected':'' ?>>Draft</option>
<option value="active" <?= $exam['status']=='active' ? 'selected':'' ?>>Active</option>
<option value="ended" <?= $exam['status']=='ended' ? 'selected':'' ?>>Ended</option>

</select>

</div>


<button
class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm">
Save Changes
</button>

</form>

</div>


<!-- Questions Section -->

<div class="w-full max-w-3xl bg-white border border-gray-200 rounded-xl shadow-sm p-6">

<div class="flex justify-between items-center mb-4">

<h2 class="text-xl font-bold text-gray-800">
Questions
</h2>

<a
href="add_question.php?exam_id=<?= $exam_id ?>"
class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-lg text-sm">
Add Question
</a>

</div>


<?php if ($questions->num_rows == 0): ?>

<p class="text-gray-500 text-sm">
No questions added yet.
</p>

<?php endif; ?>


<?php while($q = $questions->fetch_assoc()): ?>

<div class="border border-gray-200 rounded-lg p-4 mb-3">

<p class="font-semibold text-gray-700">
<?= htmlspecialchars($q['question_text']) ?>
</p>

<p class="text-sm text-gray-500 mt-1">
<?= htmlspecialchars($q['question_type']) ?> • <?= $q['marks'] ?> marks
</p>

<div class="mt-2 text-sm">

<a
href="edit_question.php?question_id=<?= $q['question_id'] ?>"
class="text-blue-600 font-semibold mr-3">
Edit
</a>

<a
href="delete_question.php?question_id=<?= $q['question_id'] ?>&exam_id=<?= $exam_id ?>"
class="text-red-600 font-semibold">
Delete
</a>

</div>

</div>

<?php endwhile; ?>

</div>

</body>
</html>