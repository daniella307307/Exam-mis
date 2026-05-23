<?php
/**
 * Cambridge-style curriculum browser — Level 1 of 4: Terms.
 *
 * URL: curriculum_terms.php?CERT=<certification_id>
 *
 * Entry point from Current_Courses.php. Shows three big cards for the
 * Cambridge-style term layout (Term 1 / Term 2 / Term 3) and a count of
 * how many weeks already have Bunny content saved against each term.
 */
include('header.php');
require_once(__DIR__ . '/curriculum_helpers.php');

curriculum_ensure_table($conn);

$CERT = isset($_GET['CERT']) ? (int)$_GET['CERT'] : 0;
if ($CERT <= 0) {
    echo '<p class="p-4">No certification specified. <a href="Current_Courses.php" class="text-blue-600 underline">Back to Current Courses</a>.</p>';
    include('footer.php'); exit;
}

$cstmt = $conn->prepare("SELECT certification_id, certification_name, certification_duration FROM certifications WHERE certification_id = ? LIMIT 1");
$cstmt->bind_param('i', $CERT);
$cstmt->execute();
$cert = $cstmt->get_result()->fetch_assoc();
$cstmt->close();

if (!$cert) {
    echo '<p class="p-4">Certification not found. <a href="Current_Courses.php" class="text-blue-600 underline">Back to Current Courses</a>.</p>';
    include('footer.php'); exit;
}

// Tally weeks-with-content per term so each card can show "8 weeks ready" etc.
$counts = [1 => 0, 2 => 0, 3 => 0];
$tstmt = $conn->prepare(
    "SELECT term_number, COUNT(*) AS cnt
       FROM curriculum_weeks
      WHERE certification_id = ?
        AND (COALESCE(bunny_pdf_url,'') <> '' OR COALESCE(bunny_video_url,'') <> '')
      GROUP BY term_number"
);
$tstmt->bind_param('i', $CERT);
$tstmt->execute();
$tres = $tstmt->get_result();
while ($r = $tres->fetch_assoc()) {
    $counts[(int)$r['term_number']] = (int)$r['cnt'];
}
$tstmt->close();
?>

<div class="flex flex-1">
    <?php include('side_bar_courses.php'); ?>

    <main class="bg-gray-50 flex-1 p-6 overflow-y-auto">
        <!-- Crumbs -->
        <div class="text-xs text-gray-500 mb-2 uppercase tracking-wide font-semibold">
            <a href="Current_Courses.php" class="hover:text-blue-600">Current Courses</a>
            <span class="mx-1">›</span>
            <span class="text-gray-800"><?= htmlspecialchars($cert['certification_name']) ?></span>
        </div>

        <!-- Header -->
        <div class="flex justify-between items-end mb-5">
            <div>
                <h1 class="text-2xl font-bold text-gray-800"><?= htmlspecialchars($cert['certification_name']) ?></h1>
                <p class="text-sm text-gray-500">Cambridge-style 3-term layout · <?= htmlspecialchars($cert['certification_duration']) ?> months</p>
            </div>
            <a href="Modules_per_Certification.php?CERTIFICATE=<?= (int)$CERT ?>"
               class="text-xs text-gray-500 hover:text-blue-600 underline">
                ⚙️ Open the old Modules view
            </a>
        </div>

        <!-- Term cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <?php foreach (curriculum_terms() as $tnum => $term):
                $count = (int)($counts[$tnum] ?? 0);
            ?>
                <a href="curriculum_months.php?CERT=<?= (int)$CERT ?>&TERM=<?= (int)$tnum ?>"
                   class="term-card group">
                    <div class="term-card-inner">
                        <div class="term-num">Term <?= (int)$tnum ?></div>
                        <div class="term-range"><?= htmlspecialchars($term['range']) ?></div>
                        <div class="term-months">
                            <?php
                            $names = curriculum_month_names();
                            $abbr  = array_map(fn($m) => substr($names[$m], 0, 3), $term['months']);
                            echo htmlspecialchars(implode(' • ', $abbr));
                            ?>
                        </div>
                        <div class="term-foot">
                            <?= $count > 0
                                ? '<span class="text-emerald-600">📚 ' . $count . ' week' . ($count == 1 ? '' : 's') . ' ready</span>'
                                : '<span class="text-gray-400">No content yet — click to start adding</span>' ?>
                            <span class="arrow">›</span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="mt-6 text-xs text-gray-500">
            💡 Tip: Apr / Aug / Dec are project-focused months. Add lighter Bunny content there if you prefer.
        </div>
    </main>
</div>

<style>
    .term-card {
        display:block;
        text-decoration:none;
        color:inherit;
    }
    .term-card-inner {
        background:#fff;
        border:1px solid #e5e7eb;
        border-radius:14px;
        padding:24px 26px;
        box-shadow:0 6px 18px rgba(0,0,0,.05);
        transition:transform .15s ease, box-shadow .15s ease, border-color .15s ease;
    }
    .term-card:hover .term-card-inner {
        transform:translateY(-3px);
        box-shadow:0 14px 30px rgba(59,130,246,.18);
        border-color:#3b82f6;
    }
    .term-num {
        font-size:11px; font-weight:800; letter-spacing:2px; text-transform:uppercase;
        color:#3b82f6;
    }
    .term-range {
        font-size:24px; font-weight:800; color:#111827; margin:4px 0 12px;
    }
    .term-months {
        font-size:13px; color:#6b7280; font-weight:600;
        padding:10px 12px; background:#f9fafb; border-radius:8px;
        border:1px dashed #e5e7eb;
    }
    .term-foot {
        display:flex; justify-content:space-between; align-items:center;
        margin-top:16px;
        font-size:13px; font-weight:600;
    }
    .term-foot .arrow {
        font-size:22px; color:#3b82f6; font-weight:900;
        transition:transform .15s ease;
    }
    .term-card:hover .arrow { transform:translateX(4px); }
</style>

<?php include('footer.php'); ?>
