<?php
/**
 * Online Simulators — curriculum-matched virtual labs & coding tools students
 * use in the robotics / electronics programme (circuit sims, Arduino sims,
 * block coding, robotics sims, CAD, AI/ML playgrounds, web dev).
 *
 * Rows live in the `simulators` table (sim_name, sim_description = FIELD/group,
 * sim_link, sim_status). Cards are grouped by field. Each active tool has:
 *   • Launch  → opens the real site in a NEW TAB (always works, even when a
 *               site blocks iframe embedding via X-Frame-Options).
 *   • Preview → tries to embed it inside Open_Simulator (nice when allowed).
 */
include('header.php');

$res = mysqli_query($conn, "SELECT * FROM simulators WHERE sim_status = 'Active'
                            ORDER BY sim_description ASC, sim_name ASC");

/* Group active simulators by their field (sim_description). */
$grouped = [];
$total = 0;
while ($row = mysqli_fetch_assoc($res)) {
    $field = trim($row['sim_description']) !== '' ? trim($row['sim_description']) : 'General';
    $grouped[$field][] = $row;
    $total++;
}

/* Icon per field. */
function sim_icon($field) {
    $map = [
        'ELECTRONICS & CIRCUITS'     => 'fa-bolt',
        'ARDUINO & MICROCONTROLLERS' => 'fa-microchip',
        'BLOCK CODING & PROGRAMMING' => 'fa-code',
        'ROBOTICS SIMULATION'        => 'fa-robot',
        'PHYSICS & MECHANICS'        => 'fa-atom',
        'CAD & 3D DESIGN'            => 'fa-cube',
        'AI & MACHINE LEARNING'      => 'fa-brain',
        'WEB DEVELOPMENT'            => 'fa-globe',
    ];
    return isset($map[$field]) ? $map[$field] : 'fa-flask';
}
?>

<div class="flex flex-1">
    <?php include('Laboratory_side_bar.php'); ?>

    <main class="bg-gray-50 flex-1 p-6 overflow-y-auto">

        <!-- Heading -->
        <div class="mb-5">
            <h1 class="text-2xl font-bold text-gray-800">🔬 Online Simulators &amp; Virtual Labs</h1>
            <p class="text-sm text-gray-500 mt-1">
                Virtual tools used across the curriculum — circuits, Arduino, coding, robotics, CAD, AI and web.
                <span class="font-semibold text-gray-700"><?= (int)$total ?> tools available.</span>
            </p>
        </div>

        <?php if (empty($grouped)): ?>
            <div class="empty-card">
                <h2 class="text-lg font-bold mb-2">No simulators available</h2>
                <p class="text-sm text-gray-600">No active simulators are configured yet.</p>
            </div>
        <?php else: ?>
            <?php foreach ($grouped as $field => $sims): ?>
                <div class="sim-section">
                    <div class="sim-section-head">
                        <i class="fas <?= sim_icon($field) ?>"></i>
                        <h2><?= htmlspecialchars($field) ?></h2>
                        <span class="sim-section-count"><?= count($sims) ?></span>
                    </div>
                    <div class="sim-grid">
                        <?php foreach ($sims as $s):
                            $link = trim($s['sim_link']);
                        ?>
                            <div class="sim-card">
                                <div class="sim-card-top">
                                    <div class="sim-card-icon"><i class="fas <?= sim_icon($field) ?>"></i></div>
                                    <div class="sim-card-name"><?= htmlspecialchars($s['sim_name']) ?></div>
                                </div>
                                <div class="sim-card-host">
                                    <i class="fas fa-link"></i>
                                    <?= htmlspecialchars(parse_url($link, PHP_URL_HOST) ?: $link) ?>
                                </div>
                                <div class="sim-card-actions">
                                    <a href="<?= htmlspecialchars($link) ?>" target="_blank" rel="noopener noreferrer"
                                       class="sim-btn sim-btn-primary">
                                        <i class="fas fa-external-link-alt"></i> Launch
                                    </a>
                                    <a href="Open_Simulator?SIM=<?= (int)$s['sim_id'] ?>&STATUS=Active"
                                       class="sim-btn sim-btn-ghost">
                                        <i class="fas fa-eye"></i> Preview
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
    .sim-section { margin-bottom:26px; }
    .sim-section-head {
        display:flex; align-items:center; gap:10px; margin-bottom:12px;
        padding-bottom:8px; border-bottom:2px solid #e5e7eb;
    }
    .sim-section-head i { color:#1d4ed8; font-size:18px; }
    .sim-section-head h2 { font-size:16px; font-weight:800; color:#1e3a8a; }
    .sim-section-count {
        font-size:11px; font-weight:700; background:#eff6ff; color:#1d4ed8;
        padding:2px 9px; border-radius:99px; border:1px solid #bfdbfe;
    }

    .sim-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:14px; }
    .sim-card {
        display:flex; flex-direction:column; background:#fff; border:1px solid #e5e7eb;
        border-radius:12px; padding:16px; box-shadow:0 1px 3px rgba(0,0,0,.04);
        transition:transform .12s ease, box-shadow .12s ease, border-color .12s ease;
    }
    .sim-card:hover { transform:translateY(-3px); box-shadow:0 8px 20px rgba(0,0,0,.09); border-color:#bfdbfe; }
    .sim-card-top { display:flex; align-items:center; gap:12px; }
    .sim-card-icon {
        flex:none; width:46px; height:46px; border-radius:11px;
        background:linear-gradient(135deg,#eff6ff,#dbeafe); color:#1d4ed8;
        display:flex; align-items:center; justify-content:center; font-size:20px;
    }
    .sim-card-name { font-size:15px; font-weight:800; color:#111827; line-height:1.25; }
    .sim-card-host {
        font-size:11px; color:#9ca3af; margin:12px 0 14px; display:flex; align-items:center; gap:6px;
        overflow:hidden; text-overflow:ellipsis; white-space:nowrap;
    }
    .sim-card-actions { display:flex; gap:8px; margin-top:auto; }
    .sim-btn {
        flex:1; text-align:center; padding:9px 10px; border-radius:8px;
        font-size:13px; font-weight:700; text-decoration:none;
        display:inline-flex; align-items:center; justify-content:center; gap:6px;
        transition:all .12s ease;
    }
    .sim-btn-primary { background:#16a34a; color:#fff; }
    .sim-btn-primary:hover { background:#15803d; }
    .sim-btn-ghost { background:#fff; color:#1d4ed8; border:1px solid #bfdbfe; }
    .sim-btn-ghost:hover { background:#eff6ff; }

    .empty-card {
        background:#fff; border:1px dashed #d1d5db; border-radius:10px;
        padding:32px 24px; text-align:center; color:#4b5563;
    }
</style>

<?php include('footer.php'); ?>
<script src="../../main.js"></script>
</body>
</html>
