<?php
/**
 * Developer curriculum manager — Term cards (3 cards: Term 1 / 2 / 3).
 * URL: curriculum_terms.php?CERT=<certification_id>
 */
include('header.php');
require_once(__DIR__ . '/../curriculum_helpers.php');

curriculum_ensure_table($conn);

$CERT = isset($_GET['CERT']) ? (int)$_GET['CERT'] : 0;
if ($CERT <= 0) {
    echo '<p class="p-4">No certification specified. <a href="Curriculum.php" class="text-blue-600 underline">Back to Curriculum</a>.</p>';
    include('footer.php'); exit;
}

$cstmt = $conn->prepare("SELECT certification_id, certification_name, certification_duration FROM certifications WHERE certification_id = ? LIMIT 1");
$cstmt->bind_param('i', $CERT);
$cstmt->execute();
$cert = $cstmt->get_result()->fetch_assoc();
$cstmt->close();

if (!$cert) {
    echo '<p class="p-4">Certification not found.</p>';
    include('footer.php'); exit;
}

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
    <?php include('dynamic_side_bar.php'); ?>

    <main class="bg-gray-50 flex-1 p-6 overflow-y-auto">
        <div class="flex items-center gap-3 mb-3">
            <a href="Curriculum.php" class="back-btn">← Back to Certifications</a>
        </div>
        <div class="text-xs text-gray-500 mb-2 uppercase tracking-wide font-semibold">
            <a href="Curriculum.php" class="hover:text-purple-600">Curriculum</a>
            <span class="mx-1">›</span>
            <span class="text-gray-800"><?= htmlspecialchars($cert['certification_name']) ?></span>
        </div>

        <div class="flex justify-between items-end mb-5">
            <div>
                <h1 class="text-2xl font-bold text-gray-800"><?= htmlspecialchars($cert['certification_name']) ?></h1>
                <p class="text-sm text-gray-500">Cambridge-style 3-term layout · <?= htmlspecialchars($cert['certification_duration']) ?> months</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <?php foreach (curriculum_terms() as $tnum => $term):
                $count = (int)($counts[$tnum] ?? 0);
                $names = curriculum_month_names();
                $abbr  = array_map(fn($m) => substr($names[$m], 0, 3), $term['months']);
            ?>
                <a href="curriculum_months.php?CERT=<?= (int)$CERT ?>&TERM=<?= (int)$tnum ?>"
                   class="term-card">
                    <div class="term-card-inner">
                        <div class="term-num">Term <?= (int)$tnum ?></div>
                        <div class="term-range"><?= htmlspecialchars($term['range']) ?></div>
                        <div class="term-months"><?= htmlspecialchars(implode(' • ', $abbr)) ?></div>
                        <div class="term-foot">
                            <?= $count > 0
                                ? '<span class="text-emerald-600">📚 ' . $count . ' week' . ($count == 1 ? '' : 's') . ' published</span>'
                                : '<span class="text-gray-400">No content yet — click to start</span>' ?>
                            <span class="arrow">›</span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </main>
</div>

<style>
    .back-btn {
        display:inline-flex; align-items:center; gap:6px;
        padding:8px 14px; border-radius:8px;
        background:#fff; color:#1f2937;
        border:1px solid #d1d5db;
        font-size:13px; font-weight:700; text-decoration:none;
        transition:transform .12s ease, box-shadow .12s ease, border-color .12s ease;
    }
    .back-btn:hover { transform:translateX(-2px); border-color:#7c3aed; color:#7c3aed; box-shadow:0 4px 10px rgba(0,0,0,.06); }
    .term-card { display:block; text-decoration:none; color:inherit; }
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
        box-shadow:0 14px 30px rgba(124,58,237,.18);
        border-color:#7c3aed;
    }
    .term-num { font-size:11px; font-weight:800; letter-spacing:2px; text-transform:uppercase; color:#7c3aed; }
    .term-range { font-size:24px; font-weight:800; color:#111827; margin:4px 0 12px; }
    .term-months {
        font-size:13px; color:#6b7280; font-weight:600;
        padding:10px 12px; background:#f9fafb; border-radius:8px;
        border:1px dashed #e5e7eb;
    }
    .term-foot { display:flex; justify-content:space-between; align-items:center; margin-top:16px; font-size:13px; font-weight:600; }
    .term-foot .arrow { font-size:22px; color:#7c3aed; font-weight:900; transition:transform .15s ease; }
    .term-card:hover .arrow { transform:translateX(4px); }
</style>

<?php include('footer.php'); ?>
