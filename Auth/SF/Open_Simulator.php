<?php
/**
 * Open_Simulator — embedded preview of a single simulator.
 *
 * Many simulator sites send X-Frame-Options / frame-ancestors headers that
 * block being embedded, in which case the iframe renders blank. So the toolbar
 * ALWAYS carries an "Open in new tab" button (the reliable path), plus a JS
 * watchdog that reveals a friendly notice if the frame never loads.
 */
include('session.php');

$SIM = isset($_GET['SIM']) ? (int)$_GET['SIM'] : 0;

$stmt = $conn->prepare("SELECT sim_name, sim_link, sim_status FROM simulators WHERE sim_id = ?");
$stmt->bind_param("i", $SIM);
$stmt->execute();
$sim = $stmt->get_result()->fetch_assoc();

if (!$sim || $sim['sim_status'] !== 'Active') {
    $back = 'Online_Simulators';
    echo '<!DOCTYPE html><meta charset="utf-8"><body style="font-family:sans-serif;padding:40px;text-align:center">'
       . '<h2>Simulator not available</h2>'
       . '<p>This simulator does not exist or is disabled.</p>'
       . '<p><a href="' . $back . '">← Back to Online Simulators</a></p></body>';
    exit;
}

$name = $sim['sim_name'];
$link = trim($sim['sim_link']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($name) ?> | Simulator</title>
    <style>
        * { box-sizing:border-box; }
        html,body { margin:0; height:100%; font-family:'Source Sans Pro',system-ui,sans-serif; }
        .sim-bar {
            display:flex; align-items:center; gap:12px; flex-wrap:wrap;
            background:#0f172a; color:#fff; padding:10px 16px;
        }
        .sim-bar .name { font-weight:800; font-size:15px; margin-right:auto; }
        .sim-bar a {
            display:inline-flex; align-items:center; gap:6px;
            padding:8px 14px; border-radius:8px; font-size:13px; font-weight:700;
            text-decoration:none; transition:all .12s ease;
        }
        .btn-open { background:#16a34a; color:#fff; }
        .btn-open:hover { background:#15803d; }
        .btn-back { background:rgba(255,255,255,.12); color:#fff; }
        .btn-back:hover { background:rgba(255,255,255,.22); }
        .frame-wrap { position:relative; height:calc(100vh - 52px); background:#f1f5f9; }
        iframe { width:100%; height:100%; border:0; }
        .blocked {
            display:none; position:absolute; inset:0; margin:auto;
            width:90%; max-width:520px; height:max-content;
            background:#fff; border:1px solid #e5e7eb; border-radius:14px;
            padding:34px 28px; text-align:center; box-shadow:0 12px 40px rgba(0,0,0,.15);
        }
        .blocked h3 { margin:0 0 8px; color:#0f172a; }
        .blocked p { color:#475569; font-size:14px; margin:0 0 18px; }
        .blocked a {
            display:inline-flex; align-items:center; gap:8px;
            background:#16a34a; color:#fff; padding:12px 22px;
            border-radius:10px; font-weight:800; text-decoration:none; font-size:15px;
        }
    </style>
</head>
<body>
    <div class="sim-bar">
        <span class="name">🔬 <?= htmlspecialchars($name) ?></span>
        <a class="btn-open" href="<?= htmlspecialchars($link) ?>" target="_blank" rel="noopener noreferrer">
            ↗ Open in new tab
        </a>
        <a class="btn-back" href="Online_Simulators">← Back</a>
    </div>

    <div class="frame-wrap">
        <iframe id="simframe" src="<?= htmlspecialchars($link) ?>"
                allow="camera; microphone; fullscreen; accelerometer; gyroscope"
                onload="frameLoaded()"></iframe>

        <div class="blocked" id="blocked">
            <h3>This simulator can't be embedded here</h3>
            <p><?= htmlspecialchars($name) ?> blocks being shown inside another page for security.
               Open it directly in a new tab — it works perfectly there.</p>
            <a href="<?= htmlspecialchars($link) ?>" target="_blank" rel="noopener noreferrer">
                ↗ Open <?= htmlspecialchars(parse_url($link, PHP_URL_HOST) ?: 'simulator') ?>
            </a>
        </div>
    </div>

    <script>
        // If the frame hasn't reported a successful load shortly after mount,
        // assume the site refused embedding and show the fallback launcher.
        var loaded = false;
        function frameLoaded() {
            try {
                // Cross-origin access throws, but reaching onload at all with a
                // real document means it rendered; a blocked frame stays blank.
                loaded = true;
                document.getElementById('blocked').style.display = 'none';
            } catch (e) { /* cross-origin, still counts as loaded */ loaded = true; }
        }
        setTimeout(function () {
            if (!loaded) { document.getElementById('blocked').style.display = 'block'; }
        }, 3500);
    </script>
</body>
</html>
