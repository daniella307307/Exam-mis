<?php
/**
 * Cambridge-style curriculum browser — Level 2 of 4: Months.
 *
 * URL: curriculum_months.php?CERT=<id>&TERM=<1|2|3>
 *
 * Four month cards for the chosen term. Apr / Aug / Dec are subtly badged
 * as "Projects month" (lighter teaching, more PBL) per the teacher's note.
 * Each card shows how many weeks already have Bunny content.
 */
include('header.php');
require_once(__DIR__ . '/curriculum_helpers.php');

curriculum_ensure_table($conn);

$CERT = isset($_GET['CERT']) ? (int)$_GET['CERT'] : 0;
$TERM = isset($_GET['TERM']) ? (int)$_GET['TERM'] : 0;

$terms = curriculum_terms();
if ($CERT <= 0 || !isset($terms[$TERM])) {
    echo '<p class="p-4">Bad term/cert. <a href="Current_Courses.php" class="text-blue-600 underline">Back to Current Courses</a>.</p>';
    include('footer.php'); exit;
}

$cstmt = $conn->prepare("SELECT certification_id, certification_name FROM certifications WHERE certification_id = ? LIMIT 1");
$cstmt->bind_param('i', $CERT);
$cstmt->execute();
$cert = $cstmt->get_result()->fetch_assoc();
$cstmt->close();

if (!$cert) {
    echo '<p class="p-4">Certification not found. <a href="Current_Courses.php" class="text-blue-600 underline">Back to Current Courses</a>.</p>';
    include('footer.php'); exit;
}

// Weeks-with-content per month for this cert+term.
$per_month = array_fill_keys($terms[$TERM]['months'], 0);
$mstmt = $conn->prepare(
    "SELECT month_number, COUNT(*) AS cnt
       FROM curriculum_weeks
      WHERE certification_id = ? AND term_number = ?
        AND (COALESCE(bunny_pdf_url,'') <> '' OR COALESCE(bunny_video_url,'') <> '')
      GROUP BY month_number"
);
$mstmt->bind_param('ii', $CERT, $TERM);
$mstmt->execute();
$mres = $mstmt->get_result();
while ($r = $mres->fetch_assoc()) {
    $per_month[(int)$r['month_number']] = (int)$r['cnt'];
}
$mstmt->close();

$names = curriculum_month_names();
?>

<div class="flex flex-1">
    <?php include('side_bar_courses.php'); ?>

    <main class="bg-gray-50 flex-1 p-6 overflow-y-auto">
        <div class="text-xs text-gray-500 mb-2 uppercase tracking-wide font-semibold">
            <a href="Current_Courses.php" class="hover:text-blue-600">Current Courses</a>
            <span class="mx-1">›</span>
            <a href="curriculum_terms.php?CERT=<?= (int)$CERT ?>" class="hover:text-blue-600"><?= htmlspecialchars($cert['certification_name']) ?></a>
            <span class="mx-1">›</span>
            <span class="text-gray-800"><?= htmlspecialchars($terms[$TERM]['label']) ?></span>
        </div>

        <div class="flex justify-between items-end mb-5">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">
                    <?= htmlspecialchars($terms[$TERM]['label']) ?>
                    <span class="text-base font-medium text-gray-500">(<?= htmlspecialchars($terms[$TERM]['range']) ?>)</span>
                </h1>
                <p class="text-sm text-gray-500">Pick a month to manage its weekly content.</p>
            </div>
            <a href="curriculum_terms.php?CERT=<?= (int)$CERT ?>"
               class="text-xs text-gray-500 hover:text-blue-600 underline">← All terms</a>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <?php foreach ($terms[$TERM]['months'] as $m):
                $is_proj = curriculum_is_projects_month($m);
                $count   = (int)($per_month[$m] ?? 0);
            ?>
                <a href="curriculum_weeks.php?CERT=<?= (int)$CERT ?>&TERM=<?= (int)$TERM ?>&MONTH=<?= (int)$m ?>"
                   class="month-card">
                    <div class="month-card-inner">
                        <div class="month-name"><?= htmlspecialchars($names[$m]) ?></div>
                        <?php if ($is_proj): ?>
                            <span class="proj-badge">🎯 Projects month</span>
                        <?php endif; ?>
                        <div class="month-foot">
                            <?= $count > 0
                                ? '<span class="text-emerald-600 text-xs font-bold">📚 ' . $count . ' week' . ($count == 1 ? '' : 's') . ' ready</span>'
                                : '<span class="text-gray-400 text-xs">Empty</span>' ?>
                            <span class="arrow">›</span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </main>
</div>

<style>
    .month-card { display:block; text-decoration:none; color:inherit; }
    .month-card-inner {
        background:#fff;
        border:1px solid #e5e7eb;
        border-radius:12px;
        padding:18px 20px;
        box-shadow:0 4px 12px rgba(0,0,0,.04);
        transition:transform .15s ease, box-shadow .15s ease, border-color .15s ease;
        min-height:120px;
        display:flex; flex-direction:column; justify-content:space-between;
    }
    .month-card:hover .month-card-inner {
        transform:translateY(-3px);
        box-shadow:0 12px 26px rgba(59,130,246,.18);
        border-color:#3b82f6;
    }
    .month-name { font-size:18px; font-weight:800; color:#111827; }
    .proj-badge {
        display:inline-block; margin-top:6px;
        font-size:10px; font-weight:800; letter-spacing:.4px;
        padding:3px 8px; border-radius:99px;
        background:#fef3c7; color:#92400e; border:1px solid #fde68a;
    }
    .month-foot { display:flex; justify-content:space-between; align-items:center; margin-top:8px; }
    .month-foot .arrow { color:#3b82f6; font-weight:900; font-size:18px; transition:transform .15s ease; }
    .month-card:hover .arrow { transform:translateX(3px); }
</style>

<?php include('footer.php'); ?>
