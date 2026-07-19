<?php
/**
 * Software_Resources.php — direct links to the software/tools used across the
 * robotics & electronics curriculum. Students & facilitators get one place to
 * download or open each tool (Arduino IDE, VS Code, CodeRobo, Scratch, …).
 *
 * Data source: `software_resources` (sw_name, sw_description, sw_category,
 * sw_download_url, sw_platform, sw_type = download|web, sw_icon, sw_status).
 * Rows are grouped by category. "download" tools show a Download button;
 * "web" tools (browser-based, e.g. CodeRobo) show an Open button.
 */
include('header.php');

$res = mysqli_query($conn, "SELECT * FROM software_resources WHERE sw_status = 'Active'
                            ORDER BY sw_category ASC, sw_name ASC");
$grouped = [];
$total = 0;
while ($row = mysqli_fetch_assoc($res)) {
    $cat = trim($row['sw_category']) !== '' ? trim($row['sw_category']) : 'Other Tools';
    $grouped[$cat][] = $row;
    $total++;
}

function sw_cat_icon($cat) {
    $map = [
        'IDEs & Code Editors'         => 'fa-code',
        'Robotics & Coding for Kids'  => 'fa-robot',
        'Electronics & Circuit Design'=> 'fa-bolt',
        'Design & 3D / CAD'           => 'fa-cube',
    ];
    return $map[$cat] ?? 'fa-download';
}
?>

<div class="flex flex-1">
    <?php include('Laboratory_side_bar.php'); ?>

    <main class="bg-gray-50 flex-1 p-6 overflow-y-auto">

        <!-- Heading -->
        <div class="mb-5">
            <span class="icrp-brand"><i class="fas fa-download"></i> ICRPplus</span>
            <h1 class="text-2xl font-bold text-gray-800 mt-2">💾 Software Resources</h1>
            <p class="text-sm text-gray-500 mt-1">
                Direct links to the software and tools we use in the lab — download and install, or open in your browser.
                <span class="font-semibold text-gray-700"><?= (int)$total ?> tools listed.</span>
            </p>
        </div>

        <div class="sw-howto">
            <i class="fas fa-info-circle"></i>
            <span><strong>How to use:</strong> click <strong>Download</strong> to get the installer for your computer (Windows / macOS / Linux), then run it and follow the on-screen steps. Tools marked <strong>Open</strong> run straight in your web browser — no installation needed.</span>
        </div>

        <?php if (empty($grouped)): ?>
            <div class="empty-card">
                <h2 class="text-lg font-bold mb-2">No software listed yet</h2>
                <p class="text-sm text-gray-600">No active software resources are configured.</p>
            </div>
        <?php else: ?>
            <?php foreach ($grouped as $cat => $tools): ?>
                <div class="sim-section">
                    <div class="sim-section-head">
                        <i class="fas <?= sw_cat_icon($cat) ?>"></i>
                        <h2><?= htmlspecialchars($cat) ?></h2>
                        <span class="sim-section-count"><?= count($tools) ?></span>
                    </div>
                    <div class="sim-grid">
                        <?php foreach ($tools as $t):
                            $is_web = ($t['sw_type'] === 'web');
                            $link   = trim($t['sw_download_url']);
                        ?>
                            <div class="sim-card">
                                <div class="sim-card-top">
                                    <div class="sim-card-icon"><i class="fas <?= htmlspecialchars($t['sw_icon'] ?: 'fa-download') ?>"></i></div>
                                    <div class="sim-card-name"><?= htmlspecialchars($t['sw_name']) ?></div>
                                </div>
                                <?php if (!empty($t['sw_description'])): ?>
                                    <p class="sw-desc"><?= htmlspecialchars($t['sw_description']) ?></p>
                                <?php endif; ?>
                                <?php if (!empty($t['sw_platform'])): ?>
                                    <div class="sw-platform"><i class="fas fa-desktop"></i> <?= htmlspecialchars($t['sw_platform']) ?></div>
                                <?php endif; ?>
                                <div class="sim-card-actions">
                                    <a href="<?= htmlspecialchars($link) ?>" target="_blank" rel="noopener noreferrer"
                                       class="sim-btn <?= $is_web ? 'sim-btn-ghost' : 'sim-btn-primary' ?>">
                                        <?php if ($is_web): ?>
                                            <i class="fas fa-external-link-alt"></i> Open
                                        <?php else: ?>
                                            <i class="fas fa-download"></i> Download
                                        <?php endif; ?>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

    </main>
</div>

<style>
    .icrp-brand { display:inline-flex; align-items:center; gap:7px; font-weight:800; font-size:12px; letter-spacing:.5px; color:#1d4ed8; background:linear-gradient(135deg,#eff6ff,#dbeafe); border:1px solid #bfdbfe; padding:4px 12px; border-radius:99px; text-transform:uppercase; }
    .sw-howto { display:flex; gap:10px; align-items:flex-start; background:#eff6ff; border:1px solid #bfdbfe; color:#1e40af; border-radius:10px; padding:12px 16px; font-size:13px; margin-bottom:20px; }
    .sw-howto i { margin-top:2px; }

    .sim-section { margin-bottom:26px; }
    .sim-section-head { display:flex; align-items:center; gap:10px; margin-bottom:12px; padding-bottom:8px; border-bottom:2px solid #e5e7eb; }
    .sim-section-head i { color:#1d4ed8; font-size:18px; }
    .sim-section-head h2 { font-size:16px; font-weight:800; color:#1e3a8a; }
    .sim-section-count { font-size:11px; font-weight:700; background:#eff6ff; color:#1d4ed8; padding:2px 9px; border-radius:99px; border:1px solid #bfdbfe; }

    .sim-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:14px; }
    .sim-card { display:flex; flex-direction:column; background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:16px; box-shadow:0 1px 3px rgba(0,0,0,.04); transition:transform .12s ease, box-shadow .12s ease, border-color .12s ease; }
    .sim-card:hover { transform:translateY(-3px); box-shadow:0 8px 20px rgba(0,0,0,.09); border-color:#bfdbfe; }
    .sim-card-top { display:flex; align-items:center; gap:12px; }
    .sim-card-icon { flex:none; width:46px; height:46px; border-radius:11px; background:linear-gradient(135deg,#eff6ff,#dbeafe); color:#1d4ed8; display:flex; align-items:center; justify-content:center; font-size:20px; }
    .sim-card-name { font-size:15px; font-weight:800; color:#111827; line-height:1.25; }
    .sw-desc { font-size:12px; color:#6b7280; margin:12px 0 8px; line-height:1.45; }
    .sw-platform { font-size:11px; color:#9ca3af; margin-bottom:14px; display:flex; align-items:center; gap:6px; }
    .sim-card-actions { display:flex; gap:8px; margin-top:auto; }
    .sim-btn { flex:1; text-align:center; padding:9px 10px; border-radius:8px; font-size:13px; font-weight:700; text-decoration:none; display:inline-flex; align-items:center; justify-content:center; gap:6px; transition:all .12s ease; }
    .sim-btn-primary { background:#16a34a; color:#fff; }
    .sim-btn-primary:hover { background:#15803d; }
    .sim-btn-ghost { background:#fff; color:#1d4ed8; border:1px solid #bfdbfe; }
    .sim-btn-ghost:hover { background:#eff6ff; }

    .empty-card { background:#fff; border:1px dashed #d1d5db; border-radius:10px; padding:32px 24px; text-align:center; color:#4b5563; }
</style>

<?php include('footer.php'); ?>
<script src="../../main.js"></script>
</body>
</html>
