<?php
// protected_viewer.php
//session_start();
include('header.php');   // make sure header.php defines $conn or require config before
//require_once 'config.php';
//require_once 'functions.php';

// --- Configuration / secret
$secretKey = "mySuperSecretKey";

// --- Read and sanitize inputs
$CERTIFICATE = isset($_GET['CERTIFICATE']) ? $_GET['CERTIFICATE'] : '';
$COURSE      = isset($_GET['COURSE']) ? $_GET['COURSE'] : '';
$ID          = isset($_GET['ID']) ? $_GET['ID'] : '';
$LANG        = isset($_GET['LANG']) ? $_GET['LANG'] : '';
$STATUS      = isset($_GET['STATUS']) ? $_GET['STATUS'] : "Active";

// Basic sanitization to reduce risk (prefer prepared statements in real-world)
$CERTIFICATE = mysqli_real_escape_string($conn, $CERTIFICATE);
$COURSE      = mysqli_real_escape_string($conn, $COURSE);
$ID          = mysqli_real_escape_string($conn, $ID);
$LANG        = mysqli_real_escape_string($conn, $LANG);
$STATUS      = ($STATUS === "Active") ? "Active" : "Inactive";

// --- Fetch course / certification names (left join)
$selct_cert_q = "
    SELECT certification_courses.*, certifications.certification_name
    FROM certification_courses
    LEFT JOIN certifications ON certification_courses.course_certificate = certifications.certification_id
    WHERE course_id = '$COURSE'
    LIMIT 1
";
$selct_cert_res = mysqli_query($conn, $selct_cert_q);
$selct_cert = mysqli_fetch_array($selct_cert_res);

$course_name = $selct_cert['course_name'] ?? 'Unknown Course';
$certification_name = $selct_cert['certification_name'] ?? 'Unknown Certification';

// --- Fetch the document file path
$select_file_q = "SELECT * FROM learning_topics WHERE topic_id='$ID' LIMIT 1";
$select_file = mysqli_query($conn, $select_file_q);
$File_found = mysqli_fetch_array($select_file);

// Determine which file to serve based on language
if ($LANG === "ENG" || $LANG === "") {
    $externalFile = $File_found['topic_document'] ?? '';
} elseif ($LANG === "FR") {
    $externalFile = $File_found['topic_french'] ?? '';
} elseif ($LANG === "Bilingual") {
    $externalFile = $File_found['topic_document'] ?? '';
} else {
    $externalFile = $File_found['topic_document'] ?? '';
}

// Token (example use)
$token = hash_hmac('sha256', $externalFile, $secretKey);

// Get module list for display (optional)
$select_modules = mysqli_query($conn, "
    SELECT learning_topics.*, learning_weeks.*
    FROM learning_topics
    LEFT JOIN learning_weeks ON learning_topics.topic_week = learning_weeks.week_id
    WHERE topic_course='$COURSE' AND topic_status='$STATUS'
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Protected PDF Viewer</title>

<!-- =========================
     STYLES (visual + protection)
     ========================= -->
<style>
/* Basic layout */
body { font-family: Arial, sans-serif; margin:0; padding:0; background:#f6f7fb; color:#222; }
main { padding: 12px; }

/* PDF container */
#pdf-container { width:100%; height:80vh; position:relative; overflow:auto; -webkit-font-smoothing:antialiased; }
#pdf-container-inner { position:relative; height:100%; overflow:auto; }

/* iframe */
#pdf-iframe { width:100%; height:100%; border:none; display:block; pointer-events:auto; }

/* watermark */
.watermark {
    position:absolute;
    top:35%;
    left:10%;
    font-size:40px;
    opacity:0.18;
    transform:rotate(-25deg);
    color:red;
    font-weight:bold;
    pointer-events:none;
    white-space: pre-line;
    text-align:left;
}

/* language selector */
.language-btn { border:1px solid #ccc; padding:6px 10px; margin-right:6px; cursor:pointer; background:#fff; }
.language-btn.active { background-color:#4a90e2; color:white; border-color:#4a90e2; }

/* overlay that sits above the iframe (transparent by default) */
.protection-overlay {
    position:absolute;
    top:0; left:0; right:0; bottom:0;
    background:transparent;
    pointer-events:none; /* allow scrolling */
}

/* block overlay (shown on restricted actions) */
#block-overlay {
    position: fixed;
    top: 0; left: 0;
    width: 100vw; height: 100vh;
    background: rgba(0,0,0,0.95);
    color: #ff4949;
    font-size: 32px;
    display: none;
    align-items: center;
    justify-content: center;
    font-weight: 900;
    z-index: 999999;
    text-align: center;
    padding: 20px;
    box-sizing: border-box;
}

/* Global disable select & drag to strengthen protection */
* {
    -webkit-touch-callout: none;
    -webkit-user-select: none;
    -khtml-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
}
img, iframe, div, body {
    -webkit-user-drag: none;
    user-drag: none;
}

/* small responsive tweaks */
@media (max-width:640px){
    .watermark { font-size:28px; left:5%; top:25%; }
}
</style>
</head>
<body>

<!-- Optional sidebar include -->
<div class="flex flex-1">
  <?php include('side_bar_courses.php'); ?>

  <main class="bg-white-500 flex-1 p-3 overflow-auto">
      <div class="flex flex-col">
          <div class="mb-2 border-solid border-gray-300 rounded border shadow-sm w-full">
              <div class="bg-gray-200 px-2 py-3 border-solid border-gray-200 border-b">
                  <a href="Modules_per_Certification?CERTIFICATE=<?php echo urlencode($CERTIFICATE); ?>">
                      <button class='bg-blue-400 hover:bg-green-300 text-white font-bold py-2 px-4 rounded'>Back</button>
                  </a>
                  <div class="language-selector" style="display:inline-block; margin-left:20px;">
                      <span style="margin-right:10px;">Language:</span>
                      <button class="language-btn <?php echo ($LANG=='ENG'||$LANG=='')?'active':''; ?>" data-lang="ENG">English</button>
                      <button class="language-btn <?php echo $LANG=='FR'?'active':''; ?>" data-lang="FR">French</button>
                  </div>
              </div>

              <div class="p-3">
                  <div class="course-info">
                      <h1>Main Topics of the Course</h1>
                      <p><strong>Course Name:</strong> <?php echo htmlspecialchars($course_name); ?></p>
                      <p><strong>Certificate Name:</strong> <?php echo htmlspecialchars($certification_name); ?></p>
                      <p><strong>Status:</strong> <?php echo htmlspecialchars($STATUS); ?></p>
                  </div>

                  <div id="pdf-container" aria-live="polite">
                      <?php if (!empty($externalFile)): ?>
                      <div id="pdf-container-inner" role="region" aria-label="Protected document viewer">
                          <!-- Iframe: toolbar disabled via URL fragment; you can replace src with a secure proxy if needed -->
                          <iframe
                              id="pdf-iframe"
                              src="<?php echo htmlspecialchars($externalFile); ?>#toolbar=0&navpanes=0&statusbar=0"
                              allow="clipboard-read; clipboard-write">
                          </iframe>

                          <!-- Watermark: you may replace with dynamic watermark for user info -->
                          <div class="watermark">CONFIDENTIAL DOCUMENT
BLIS copyright &#169; <?php echo DATE("Y");?></div>

                          <!-- Transparent overlay to help intercept events when needed -->
                          <div class="protection-overlay" aria-hidden="true"></div>
                      </div>
                      <?php else: ?>
                          <p style="color:red; font-weight:bold;">No document available for this topic</p>
                      <?php endif; ?>
                  </div>
              </div>
          </div>
      </div>
  </main>
</div>

<!-- Block overlay (shown when a forbidden action is detected) -->
<div id="block-overlay">This action is disabled! Refresh to continue.</div>

<!-- =========================
     PROTECTION SCRIPT
     ========================= -->
<script>
// --- Language selector buttons
document.querySelectorAll('.language-btn').forEach(button => {
    button.addEventListener('click', function() {
        const lang = this.getAttribute('data-lang');
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.set('LANG', lang);
        // keep other params intact
        window.location.href = window.location.pathname + '?' + urlParams.toString();
    });
});

// --- Disable right-click
document.addEventListener("contextmenu", e => {
    e.preventDefault();
    showBlockOverlay();
    return false;
}, true);

// --- Disable selection, copy, cut, dragstart globally
['selectstart','copy','cut','dragstart'].forEach(evt => {
    document.addEventListener(evt, e => {
        e.preventDefault();
        showBlockOverlay();
        return false;
    }, true);
});

// --- Disable keyboard shortcuts (Ctrl/Cmd + keys) and other keys
document.addEventListener('keydown', function(e) {
    const key = (e.key || '').toLowerCase();
    const isCtrl = e.ctrlKey || e.metaKey;

    // Keys to block when used with Ctrl/Cmd
    const blockedWithCtrl = ['p','s','u','c','a','x','i','j','k','o'];
    if (isCtrl && blockedWithCtrl.includes(key)) {
        e.preventDefault();
        e.stopPropagation();
        showBlockOverlay();
        return false;
    }

    // Block specific single keys (F12, PrintScreen)
    if (key === 'f12' || key === 'printscreen' || key === 'prtsc') {
        e.preventDefault();
        e.stopPropagation();
        try { navigator.clipboard && navigator.clipboard.writeText(''); } catch (err) {}
        showBlockOverlay();
        return false;
    }
}, true);

// --- Catch keyup for PrintScreen on some browsers
document.addEventListener('keyup', function(e) {
    const key = (e.key || '').toLowerCase();
    if (key.includes('printscreen') || key === 'prtsc') {
        try { navigator.clipboard && navigator.clipboard.writeText(''); } catch (err) {}
        showBlockOverlay();
    }
}, true);

// --- Detect switching tabs / window hidden (some screenshot tools switch focus)
document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        showBlockOverlay();
    }
});

// --- Developer tools detection using window size differences (heuristic)
let devtoolsWarningCount = 0;
setInterval(() => {
    const threshold = 170; // px difference heuristic
    if (window.outerWidth - window.innerWidth > threshold || window.outerHeight - window.innerHeight > threshold) {
        devtoolsWarningCount++;
        if (devtoolsWarningCount > 1) showBlockOverlay();
    } else {
        devtoolsWarningCount = 0;
    }
}, 800);

// --- Show blocking overlay function
function showBlockOverlay() {
    const overlay = document.getElementById('block-overlay');
    if (!overlay) return;
    overlay.style.display = 'flex';
    // Make it nearly impossible to interact below
    overlay.style.pointerEvents = 'auto';
}

// --- Try to harden the PDF iframe when same-origin
(function protectIframe() {
    const iframe = document.getElementById('pdf-iframe');
    if (!iframe) return;
    iframe.addEventListener('load', () => {
        try {
            const innerDoc = iframe.contentDocument || iframe.contentWindow.document;
            if (!innerDoc) return;
            // Block contextmenu/copy/select inside iframe
            ['contextmenu','copy','cut','selectstart','dragstart'].forEach(evt => {
                innerDoc.addEventListener(evt, e => {
                    e.preventDefault();
                    window.parent.showBlockOverlay && window.parent.showBlockOverlay();
                });
            });
            // Prevent text selection inside iframe
            if (innerDoc.body) innerDoc.body.style.userSelect = "none";
        } catch (err) {
            // cross-origin iframe => can't access; leave outer protections
        }
    });
})();

// --- Extra: try to clear clipboard on some suspicious events (best-effort)
function tryClearClipboard() {
    try {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText('');
        }
    } catch (e) {}
}
</script>

</body>
</html>
