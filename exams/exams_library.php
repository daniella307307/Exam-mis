<?php
include('../db.php');

$stmt = $conn->prepare("SELECT exam_id, title, topic, grade, status FROM exams ORDER BY created_at DESC LIMIT 1");
$stmt->execute();
$exam = $stmt->get_result()->fetch_assoc();

if (!$exam) {
    echo "<div class='p-6 text-center text-gray-600'>
            No exam found.<br>
            <a href='exam_creator_working.php' class='text-blue-600 underline'>Create one now</a>
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
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.13/css/all.css" integrity="sha384-DNOHZ68U8hZfKXOrtjWvjxusGo9WQnrNx2sqG0tfsghAvtVlRW3tvkXWZh58N9jp" crossorigin="anonymous">
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
                    Latest Exam
                </h1>

                <a href="exam_creator_working.php"
                   class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm rounded-lg shadow-sm transition float-right"
                   style="color:#fff;font-weight:700">
                    + Create Exam
                </a>
            </div>

            <!-- Exam Card -->
            <div class="bg-white rounded-2xl shadow-md border border-gray-200 p-8 hover:shadow-lg transition">

                <h2 class="text-3xl font-semibold mb-6" style="color:#fff">
                    <?= $exam_title ?>
                </h2>

                <div class="space-y-4 text-sm" style="color:#e2e8f0">
                    <p>
                        <span class="font-semibold" style="color:#fff">Topic:</span>
                        <span style="color:#f1f5f9"><?= $exam_topic ?></span>
                    </p>

                    <p>
                        <span class="font-semibold" style="color:#fff">Grade:</span>
                        <span style="color:#f1f5f9"><?= $exam_grade ?></span>
                    </p>

                    <p>
                        <span class="font-semibold" style="color:#fff">Status:</span>
                        <?php if ($exam_status === 'active'): ?>
                            <span class="ml-2 px-2 py-1 text-xs rounded font-bold"
                                  style="background:rgba(34,197,94,.18);color:#86efac;border:1px solid rgba(34,197,94,.4)">
                                <?= ucfirst($exam_status) ?>
                            </span>
                        <?php else: ?>
                            <span class="ml-2 px-2 py-1 text-xs rounded font-bold"
                                  style="background:rgba(148,163,184,.18);color:#cbd5e1;border:1px solid rgba(148,163,184,.3)">
                                <?= ucfirst($exam_status) ?>
                            </span>
                        <?php endif; ?>
                    </p>
                </div>

                <!-- Actions -->
                <div class="mt-8 flex justify-end gap-4">
                    <a href="edit_exam.php?exam_id=<?= $exam_id ?>"
                       class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg shadow-sm transition"
                       style="color:#fff;font-weight:700">
                       Edit Exam
                    </a>

                    <a href="exam_report.php?exam_id=<?= $exam_id ?>"
                       class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm rounded-lg shadow-sm transition"
                       style="color:#fff;font-weight:700">
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
<?php // Connection is closed by db_connection.php's shutdown handler. ?>
