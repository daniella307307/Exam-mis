<?php
/**
 * Developer curriculum manager — Level 0: pick a certification.
 *
 * Whatever the Developer publishes here propagates to every school's SF
 * curriculum browser because curriculum_weeks is keyed by certification_id
 * alone (not by school).
 *
 * Layout convention matches the rest of the Developer area (header.php +
 * dynamic_side_bar.php). Tailwind classes mirror the existing Promotions /
 * Schools pages so the dev panel stays visually consistent.
 */
include('header.php');
require_once(__DIR__ . '/../curriculum_helpers.php');

curriculum_ensure_table($conn);

// Pull every certification, plus a quick "weeks ready" count so the
// dashboard tells the Developer where work has happened and where it
// hasn't.
$rows = mysqli_query(
    $conn,
    "SELECT c.certification_id, c.certification_name, c.certification_duration, c.certification_status,
            COALESCE((
                SELECT COUNT(*) FROM curriculum_weeks cw
                 WHERE cw.certification_id = c.certification_id
                   AND (COALESCE(cw.bunny_pdf_url,'') <> '' OR COALESCE(cw.bunny_video_url,'') <> '')
            ), 0) AS weeks_ready
       FROM certifications c
      ORDER BY c.certification_status DESC, c.certification_name ASC"
);
$certifications = [];
while ($r = mysqli_fetch_assoc($rows)) { $certifications[] = $r; }
?>

<div class="flex flex-1">
    <?php include('dynamic_side_bar.php'); ?>

    <main class="bg-gray-50 flex-1 p-6 overflow-y-auto">

        <div class="flex justify-between items-end mb-5">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">📘 Curriculum manager</h1>
                <p class="text-sm text-gray-500">
                    Pick a certification to manage its Cambridge-style term structure.
                    Anything you publish appears in every school&rsquo;s SF view.
                </p>
            </div>
            <a href="index.php" class="back-btn">← Back to Dashboard</a>
        </div>

        <?php if (empty($certifications)): ?>
            <div class="empty">No certifications found. Add one in the Promotions / Certifications area first.</div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                <?php foreach ($certifications as $c):
                    $ready = (int)$c['weeks_ready'];
                    $is_active = strcasecmp((string)$c['certification_status'], 'Active') === 0;
                ?>
                    <a href="curriculum_terms.php?CERT=<?= (int)$c['certification_id'] ?>"
                       class="cert-card">
                        <div class="cert-card-inner">
                            <div class="cert-row">
                                <span class="cert-tag">Cambridge-style</span>
                                <span class="cert-status <?= $is_active ? 'on' : 'off' ?>">
                                    <?= $is_active ? '● Active' : '○ Inactive' ?>
                                </span>
                            </div>
                            <div class="cert-name"><?= htmlspecialchars($c['certification_name']) ?></div>
                            <div class="cert-meta"><?= htmlspecialchars($c['certification_duration']) ?> months</div>
                            <div class="cert-foot">
                                <?= $ready > 0
                                    ? '<span class="text-emerald-600">📚 ' . $ready . ' week' . ($ready == 1 ? '' : 's') . ' published</span>'
                                    : '<span class="text-gray-400">No content uploaded yet</span>' ?>
                                <span class="arrow">›</span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <p class="mt-6 text-xs text-gray-500">
            💡 Apr / Aug / Dec are project-focused months. The week page lets you upload PDF + video
            straight to Bunny CDN; school facilitators see whatever you publish, read-only.
        </p>
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
    .empty {
        padding:40px; text-align:center; color:#6b7280;
        background:#fff; border:1px dashed #e5e7eb; border-radius:14px;
    }
    .cert-card { display:block; text-decoration:none; color:inherit; }
    .cert-card-inner {
        background:#fff;
        border:1px solid #e5e7eb;
        border-radius:14px;
        padding:22px 24px;
        box-shadow:0 6px 18px rgba(0,0,0,.05);
        transition:transform .15s ease, box-shadow .15s ease, border-color .15s ease;
        min-height:180px;
        display:flex; flex-direction:column; justify-content:space-between;
    }
    .cert-card:hover .cert-card-inner {
        transform:translateY(-3px);
        box-shadow:0 14px 30px rgba(124,58,237,.18);
        border-color:#7c3aed;
    }
    .cert-row { display:flex; justify-content:space-between; align-items:center; }
    .cert-tag {
        font-size:10px; font-weight:800; letter-spacing:1.5px; text-transform:uppercase;
        color:#7c3aed; background:#f3e8ff; padding:3px 8px; border-radius:99px;
    }
    .cert-status { font-size:11px; font-weight:800; }
    .cert-status.on  { color:#059669; }
    .cert-status.off { color:#9ca3af; }
    .cert-name { font-size:18px; font-weight:800; color:#111827; margin:12px 0 4px; }
    .cert-meta { font-size:12px; color:#6b7280; }
    .cert-foot {
        display:flex; justify-content:space-between; align-items:center;
        font-size:13px; font-weight:700; margin-top:12px;
    }
    .cert-foot .arrow {
        color:#7c3aed; font-weight:900; font-size:20px;
        transition:transform .15s ease;
    }
    .cert-card:hover .arrow { transform:translateX(4px); }
</style>

<?php include('footer.php'); ?>
