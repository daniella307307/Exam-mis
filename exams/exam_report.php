<?php
include('../db.php');

// ==============================
// FETCH EXAM (optionally by ?exam_id=X, else latest)
// ==============================
$exam_id = isset($_GET['exam_id']) ? (int)$_GET['exam_id'] : 0;

if ($exam_id) {
    $stmt = $conn->prepare("SELECT * FROM exams WHERE exam_id = ? LIMIT 1");
    $stmt->bind_param("i", $exam_id);
} else {
    $stmt = $conn->prepare("SELECT * FROM exams ORDER BY created_at DESC LIMIT 1");
}
$stmt->execute();
$exam = $stmt->get_result()->fetch_assoc();

if (!$exam) { echo "No exam found."; exit(); }

$exam_id     = $exam['exam_id'];
$exam_title  = htmlspecialchars($exam['title']);
$exam_topic  = htmlspecialchars($exam['topic']);
$exam_grade  = htmlspecialchars($exam['grade']);
$exam_status = $exam['status'];
$exam_code   = $exam['exam_code'] ?? '';

// ==============================
// FILTERS
// ==============================
$filter_grade  = $_GET['grade']  ?? '';
$filter_school = $_GET['school'] ?? '';
$filter_result = $_GET['result'] ?? '';
$filter_search = $_GET['search'] ?? '';

// ==============================
// TOTAL MARKS
// ==============================
$stmt3 = $conn->prepare("SELECT SUM(marks) AS total_marks FROM questions WHERE exam_id = ?");
$stmt3->bind_param("i", $exam_id);
$stmt3->execute();
$total_marks = $stmt3->get_result()->fetch_assoc()['total_marks'] ?? 0;

// ==============================
// FETCH ALL EXAMS for dropdown
// ==============================
$all_exams = $conn->query("SELECT exam_id, title FROM exams ORDER BY created_at DESC");

// ==============================
// FETCH PLAYERS (with filters)
// ==============================
$sql = "SELECT * FROM players WHERE exam_id = ?";
$params = [$exam_id];
$types  = "i";

if ($filter_grade)  { $sql .= " AND grade = ?";   $types .= "s"; $params[] = $filter_grade; }
if ($filter_school) { $sql .= " AND school = ?";  $types .= "s"; $params[] = $filter_school; }
if ($filter_search) { $sql .= " AND nickname LIKE ?"; $types .= "s"; $params[] = "%$filter_search%"; }

$sql .= " ORDER BY score DESC";
$pstmt = $conn->prepare($sql);
$pstmt->bind_param($types, ...$params);
$pstmt->execute();
$all_players = $pstmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Apply pass/fail filter in PHP (needs total_marks)
if ($filter_result === 'pass') {
    $all_players = array_filter($all_players, fn($p) => $total_marks > 0 && ($p['score'] / $total_marks) >= 0.5);
} elseif ($filter_result === 'fail') {
    $all_players = array_filter($all_players, fn($p) => $total_marks == 0 || ($p['score'] / $total_marks) < 0.5);
}

// Unique grades and schools for filter dropdowns
$grades_stmt  = $conn->prepare("SELECT DISTINCT grade FROM players WHERE exam_id = ? ORDER BY grade");
$grades_stmt->bind_param("i", $exam_id);
$grades_stmt->execute();
$distinct_grades = $grades_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$schools_stmt = $conn->prepare("SELECT DISTINCT school FROM players WHERE exam_id = ? ORDER BY school");
$schools_stmt->bind_param("i", $exam_id);
$schools_stmt->execute();
$distinct_schools = $schools_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Exam Reports</title>
<link rel="stylesheet" href="../dist/styles.css">
</head>
<body class="bg-gray-100 min-h-screen">
<?php include('../Auth/SF/header.php'); ?>

<div class="flex flex-1 overflow-hidden">

    <!-- SIDEBAR -->
    <div class="bg-white border-r border-gray-200 min-h-screen w-64 hidden md:block">
        <?php include('./dynamic_sidebar.php'); ?>
    </div>

    <!-- MAIN CONTENT -->
    <div class="flex-1 p-8 overflow-y-auto">

        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">Exam Reports</h1>
                <p class="text-sm text-gray-500">Scores and performance overview</p>
            </div>
            <div class="flex gap-3">
                <a href="examscreator.php"
                   class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm rounded-lg shadow">
                    + Create Exam
                </a>
                <a href="exam_reports.php?exam_id=<?= $exam_id ?>"
                   class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm rounded-lg shadow">
                    Download Excel
                </a>
            </div>
        </div>

        <!-- Exam Switcher -->
        <div class="mb-6">
            <form method="GET" class="flex items-center gap-3">
                <label class="text-sm text-gray-600">Viewing exam:</label>
                <select name="exam_id" onchange="this.form.submit()"
                        class="border border-gray-300 rounded px-3 py-1.5 text-sm bg-white">
                    <?php while ($e = $all_exams->fetch_assoc()): ?>
                        <option value="<?= $e['exam_id'] ?>"
                            <?= $e['exam_id'] == $exam_id ? 'selected' : '' ?>>
                            <?= htmlspecialchars($e['title']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </form>
        </div>

        <!-- Info Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-4 rounded-xl border">
                <p class="text-xs text-gray-500 mb-1">Exam title</p>
                <p class="font-semibold text-gray-800"><?= $exam_title ?></p>
            </div>
            <div class="bg-white p-4 rounded-xl border">
                <p class="text-xs text-gray-500 mb-1">Topic</p>
                <p class="font-semibold text-gray-800"><?= $exam_topic ?></p>
            </div>
            <div class="bg-white p-4 rounded-xl border">
                <p class="text-xs text-gray-500 mb-1">Grade</p>
                <p class="font-semibold text-gray-800"><?= $exam_grade ?></p>
            </div>
            <div class="bg-white p-4 rounded-xl border">
                <p class="text-xs text-gray-500 mb-1">Total marks</p>
                <p class="font-semibold text-blue-600"><?= $total_marks ?></p>
            </div>
        </div>

        <!-- Status + Exam Code -->
        <div class="flex items-center gap-4 mb-6">
            <span class="px-3 py-1 text-sm rounded-full font-medium
                <?= $exam_status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600' ?>">
                <?= ucfirst($exam_status) ?>
            </span>
            <?php if ($exam_code): ?>
                <span class="text-sm text-gray-500">
                    Exam code: <strong class="text-gray-800"><?= htmlspecialchars($exam_code) ?></strong>
                </span>
            <?php endif; ?>
        </div>

        <!-- Filters -->
        <form method="GET" class="flex flex-wrap gap-3 items-center mb-5">
            <input type="hidden" name="exam_id" value="<?= $exam_id ?>">

            <select name="grade" class="border border-gray-300 rounded px-3 py-1.5 text-sm bg-white">
                <option value="">All grades</option>
                <?php foreach ($distinct_grades as $g): ?>
                    <option value="<?= htmlspecialchars($g['grade']) ?>"
                        <?= $filter_grade === $g['grade'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($g['grade']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="school" class="border border-gray-300 rounded px-3 py-1.5 text-sm bg-white">
                <option value="">All schools</option>
                <?php foreach ($distinct_schools as $s): ?>
                    <option value="<?= htmlspecialchars($s['school']) ?>"
                        <?= $filter_school === $s['school'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s['school']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="result" class="border border-gray-300 rounded px-3 py-1.5 text-sm bg-white">
                <option value="">All results</option>
                <option value="pass" <?= $filter_result === 'pass' ? 'selected' : '' ?>>Pass only</option>
                <option value="fail" <?= $filter_result === 'fail' ? 'selected' : '' ?>>Fail only</option>
            </select>

            <input type="text" name="search" value="<?= htmlspecialchars($filter_search) ?>"
                   placeholder="Search player..."
                   class="border border-gray-300 rounded px-3 py-1.5 text-sm bg-white">

            <button type="submit"
                    class="px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded">
                Filter
            </button>

            <?php if ($filter_grade || $filter_school || $filter_result || $filter_search): ?>
                <a href="?exam_id=<?= $exam_id ?>"
                   class="text-sm text-gray-500 hover:text-red-500">Clear filters</a>
            <?php endif; ?>
        </form>

        <!-- Leaderboard Table -->
        <div class="bg-white rounded-xl border">
            <div class="flex justify-between items-center px-6 py-4 border-b">
                <h2 class="font-semibold text-gray-800">Leaderboard &amp; reports</h2>
                <span class="text-sm text-gray-500"><?= count($all_players) ?> player(s)</span>
            </div>

            <?php if (count($all_players) > 0): ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b text-xs uppercase tracking-wide">
                            <th class="px-6 py-3">#</th>
                            <th class="px-6 py-3">Player</th>
                            <th class="px-6 py-3">Score</th>
                            <th class="px-6 py-3">%</th>
                            <th class="px-6 py-3">Progress</th>
                            <th class="px-6 py-3">Grade</th>
                            <th class="px-6 py-3">School</th>
                            <th class="px-6 py-3">Result</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $rank = 1;
                    foreach ($all_players as $row):
                        $score      = $row['score'];
                        $pct        = ($total_marks > 0) ? round(($score / $total_marks) * 100) : 0;
                        $pass       = $pct >= 50;
                        $bar_color  = $pct >= 50 ? 'bg-blue-500' : 'bg-red-400';
                    ?>
                        <tr class="border-b hover:bg-gray-50 transition">
                            <td class="px-6 py-3 font-semibold text-gray-500"><?= $rank++ ?></td>
                            <td class="px-6 py-3"><?= htmlspecialchars($row['nickname']) ?></td>
                            <td class="px-6 py-3 font-semibold text-blue-600"><?= $score ?> / <?= $total_marks ?></td>
                            <td class="px-6 py-3"><?= $pct ?>%</td>
                            <td class="px-6 py-3">
                                <div class="w-20 bg-gray-100 rounded-full h-1.5">
                                    <div class="<?= $bar_color ?> h-1.5 rounded-full"
                                         style="width:<?= $pct ?>%"></div>
                                </div>
                            </td>
                            <td class="px-6 py-3"><?= htmlspecialchars($row['grade']) ?></td>
                            <td class="px-6 py-3"><?= htmlspecialchars($row['school']) ?></td>
                            <td class="px-6 py-3">
                                <span class="px-2 py-1 rounded text-xs font-medium
                                    <?= $pass ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' ?>">
                                    <?= $pass ? 'Pass' : 'Fail' ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
                <div class="text-center py-12">
                    <p class="text-gray-400">No players match your filters.</p>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<?php include('../Auth/SF/footer.php'); ?>
</body>
</html>