<?php
/**
 * Modules — flat list of every certification and its modules, grouped by
 * grade (Play Ground → PP1 → PP2 → GRADE 1 … GRADE 12). Reached from the
 * "Modules" link next to each promotion row on Current_Courses.php.
 *
 * Intentionally a read-only listing: no filters, no school scope, no actions.
 * To actually open a module/course, the user goes through the Certification
 * or Promotion side from Current Courses.
 */
include('header.php');

$LANG = isset($_GET['LANG']) ? $_GET['LANG'] : $school_language;

/* Pull every active module joined to its certification, in ID order so the
 * grade sequence (Play Ground → GRADE 12) is preserved as data was seeded. */
$sql = "SELECT c.certification_id, c.certification_name, c.certification_duration,
               cc.course_id, cc.course_code, cc.course_name, cc.course_french, cc.course_status
          FROM certifications c
          LEFT JOIN certification_courses cc
            ON cc.course_certificate = c.certification_id
           AND cc.course_status = 'Active'
         ORDER BY c.certification_id ASC, cc.course_id ASC";
$res = mysqli_query($conn, $sql);

/* Group rows in PHP so each certification renders as its own block. */
$grouped = [];
while ($r = mysqli_fetch_assoc($res)) {
    $cid = (int)$r['certification_id'];
    if (!isset($grouped[$cid])) {
        $grouped[$cid] = [
            'certification_name'     => $r['certification_name'],
            'certification_duration' => $r['certification_duration'],
            'modules' => [],
        ];
    }
    if (!empty($r['course_id'])) {
        $grouped[$cid]['modules'][] = [
            'course_id'     => $r['course_id'],
            'course_code'   => $r['course_code'],
            'course_name'   => $r['course_name'],
            'course_french' => $r['course_french'],
        ];
    }
}
?>

<div class="flex flex-1">
    <?php include('dynamic_side_bar.php'); ?>

    <main class="bg-gray-50 flex-1 p-6 overflow-y-auto">

        <!-- Back nav -->
        <div class="flex items-center gap-3 mb-3">
            <a href="Current_Courses.php" class="back-btn">← Back to Current Courses</a>
        </div>
        <div class="text-xs text-gray-500 mb-4 uppercase tracking-wide font-semibold">
            <a href="Current_Courses.php" class="hover:text-blue-600">Current Courses</a>
            <span class="mx-1">›</span>
            <span class="text-gray-800">Modules</span>
        </div>

        <div class="mb-5">
            <h1 class="text-2xl font-bold text-gray-800">📚 Modules</h1>
            <p class="text-sm text-gray-500 mt-1">All certifications and their modules.</p>
        </div>

        <?php if (empty($grouped)): ?>
            <div class="empty-card">
                <h2 class="text-lg font-bold mb-2">No modules found</h2>
                <p class="text-sm text-gray-600">No active certifications or modules are configured yet.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php foreach ($grouped as $cert): ?>
                    <div class="cert-card">
                        <div class="cert-head">
                            <h2 class="cert-title"><?= htmlspecialchars($cert['certification_name']) ?></h2>
                            <?php if (!empty($cert['certification_duration'])): ?>
                                <span class="cert-duration"><?= htmlspecialchars($cert['certification_duration']) ?> months</span>
                            <?php endif; ?>
                        </div>

                        <?php if (empty($cert['modules'])): ?>
                            <p class="text-sm text-gray-400 italic">No active modules.</p>
                        <?php else: ?>
                            <ul class="module-list">
                                <?php foreach ($cert['modules'] as $m):
                                    $name = ($LANG === 'French' || $LANG === 'FR') && !empty($m['course_french'])
                                        ? $m['course_french']
                                        : $m['course_name'];
                                ?>
                                    <li>
                                        <span class="module-code"><?= htmlspecialchars($m['course_code']) ?></span>
                                        <span class="module-name"><?= htmlspecialchars($name) ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
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
    .back-btn:hover { transform:translateX(-2px); border-color:#3b82f6; color:#3b82f6; box-shadow:0 4px 10px rgba(0,0,0,.06); }

    .cert-card {
        background:#fff; border:1px solid #e5e7eb; border-radius:10px;
        padding:18px 20px; box-shadow:0 1px 3px rgba(0,0,0,.04);
    }
    .cert-head {
        display:flex; justify-content:space-between; align-items:center;
        margin-bottom:10px; padding-bottom:8px; border-bottom:1px solid #f1f5f9;
    }
    .cert-title { font-size:16px; font-weight:800; color:#1e3a8a; }
    .cert-duration {
        font-size:11px; font-weight:700; letter-spacing:.4px; text-transform:uppercase;
        background:#eff6ff; color:#1d4ed8; padding:3px 8px; border-radius:99px;
        border:1px solid #bfdbfe;
    }
    .module-list { list-style:none; padding:0; margin:0; }
    .module-list li {
        display:flex; align-items:baseline; gap:8px;
        padding:6px 0; font-size:14px; color:#374151;
        border-bottom:1px dashed #f1f5f9;
    }
    .module-list li:last-child { border-bottom:none; }
    .module-code {
        font-family:'Courier New',monospace; font-size:11px; font-weight:700;
        color:#6b7280; background:#f3f4f6; padding:2px 6px; border-radius:4px;
        min-width:60px; text-align:center;
    }
    .module-name { color:#111827; font-weight:600; }

    .empty-card {
        background:#fff; border:1px dashed #d1d5db; border-radius:10px;
        padding:32px 24px; text-align:center; color:#4b5563;
    }
</style>

<?php include('footer.php'); ?>
<script src="../../main.js"></script>
</body>
</html>
