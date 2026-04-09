<?php
include('../db.php');

$stmt = $conn->prepare("SELECT exam_id, title, topic, grade, status FROM exams ORDER BY created_at DESC LIMIT 1");
$stmt->execute();
$exam = $stmt->get_result()->fetch_assoc();

if (!$exam) {
    echo "<div class='p-6 text-center text-gray-600'>
            No exam found.<br>
            <a href='examscreator.php' class='text-blue-600 underline'>Create one now</a>
          </div>";
    exit();
}

$exam_id = $exam['exam_id'];
$exam_title = htmlspecialchars($exam['title'], ENT_QUOTES, 'UTF-8');
$exam_topic = htmlspecialchars($exam['topic'], ENT_QUOTES, 'UTF-8');
$exam_grade = htmlspecialchars($exam['grade'], ENT_QUOTES, 'UTF-8');
$exam_status = htmlspecialchars($exam['status'], ENT_QUOTES, 'UTF-8');
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Exam Details</title>
<link rel="stylesheet" href="../dist/styles.css">
</head>

<body class="bg-gray-100 min-h-screen">
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
                <h1 class="text-xl font-semibold text-gray-800">
                    Latest Exam
                </h1>

                <a href="examscreator.php"
                   class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm rounded-lg shadow-sm transition float-right">
                    +
                   Create Exam
                </a>
            </div>

            <!-- Exam Card -->
            <div class="bg-white rounded-2xl shadow-md border border-gray-200 p-8 hover:shadow-lg transition">

                <h2 class="text-3xl font-semibold text-gray-800 mb-6">
                    <?= $exam_title ?>
                </h2>

                <div class="space-y-4 text-gray-600 text-sm">
                    <p>
                        <span class="font-semibold text-gray-700">Topic:</span>
                        <?= $exam_topic ?>
                    </p>

                    <p>
                        <span class="font-semibold text-gray-700">Grade:</span>
                        <?= $exam_grade ?>
                    </p>

                    <p>
                        <span class="font-semibold text-gray-700">Status:</span>
                        <span class="ml-2 px-2 py-1 text-xs rounded 
                        <?= $exam_status === 'active'
                            ? 'bg-green-100 text-green-700'
                            : 'bg-gray-100 text-gray-600' ?>">
                            <?= ucfirst($exam_status) ?>
                        </span>
                    </p>
                </div>

                <!-- Actions -->
                <div class="mt-8 flex justify-end gap-4">
                    <a href="edit_exam.php?exam_id=<?= $exam_id ?>"
                       class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg shadow-sm transition">
                       Edit Exam
                    </a>

                    <a href="exam_report.php?exam_id=<?= $exam_id ?>"
                       class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm rounded-lg shadow-sm transition">
                       View Report
                    </a>
                </div>

            </div>

        </div>

    </div>
</div>

<?php include('../Auth/SF/footer.php'); ?>
</body>
</html>