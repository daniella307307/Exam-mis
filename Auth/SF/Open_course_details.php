<?php
include('header.php');
$secretKey = "mySuperSecretKey";
$CERTIFICATE = $_GET['CERTIFICATE'] ?? '';
$COURSE = $_GET['COURSE'] ?? '';
$ID = $_GET['ID'] ?? '';
$LANG = $_GET['LANG'] ?? '';

// Fetch course and certification names
$selct_cert = mysqli_fetch_array(mysqli_query($conn, "
    SELECT * FROM certification_courses
    LEFT JOIN certifications ON certification_courses.course_certificate = certifications.certification_id
    WHERE course_id ='$COURSE'
"));
$course_name = $selct_cert['course_name'] ?? 'Unknown Course';
$certification_name = $selct_cert['certification_name'] ?? 'Unknown Certification';

// Fetch the document file path
$select_file = mysqli_query($conn, "SELECT * FROM learning_topics WHERE topic_id='$ID'");
$File_found = mysqli_fetch_array($select_file);

if($LANG=="ENG"){$externalFile = $File_found['topic_document'] ?? '';}
elseif($LANG=="FR"){ $externalFile = $File_found['topic_french'] ?? ''; }
elseif($LANG=="Bilingual"){$externalFile = $File_found['topic_document'] ?? ''; }
else{$externalFile = $File_found['topic_document'] ?? ''; }

$token = hash_hmac('sha256', $externalFile, $secretKey);

// Set status
$STATUS = $_GET['STATUS'] ?? "Active";
$status = ($STATUS == "Active") ? "Active" : "Inactive";

$select_modules = mysqli_query($conn, "
    SELECT * FROM learning_topics
    LEFT JOIN learning_weeks ON learning_topics.topic_week = learning_weeks.week_id
    WHERE topic_course='$COURSE' AND topic_status='$status'
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Protected PDF Viewer</title>

<style>
/*

body { font-family: Arial, sans-serif; margin:0; padding:0; overflow:hidden; }
#pdf-container { width:100%; height:80vh; position:relative; overflow:auto; user-select:none; -webkit-user-select:none; -moz-user-select:none; -ms-user-select:none; }
#pdf-iframe { width:100%; height:100%; border:none; display:block; pointer-events:auto; }
.watermark { position:absolute; top:35%; left:10%; font-size:40px; opacity:0.18; transform:rotate(-25deg); color:red; font-weight:bold; pointer-events:none; }
.language-btn.active { background-color:#4a90e2; color:white; border-color:#4a90e2; }

.protection-overlay {
    position:absolute;
    top:0; left:0;
    right:0; bottom:0;
    background:transparent;
    pointer-events:none;  
}
#pdf-container-inner {
    position:relative;
    height:100%;
    overflow:auto;
}

*/
</style>



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
              <!--
              
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
              -->
              
              
              

              <div class="p-3">
                  <div class="course-info">
                      <h1>Main Topics of the Course</h1>
                      <p><strong>Course Name:</strong> <?php echo htmlspecialchars($course_name); ?></p>
                      <p><strong>Certificate Name:</strong> <?php echo htmlspecialchars($certification_name); ?></p>
                      <p><strong>Status:</strong> <?php echo htmlspecialchars($status); ?></p>
                  </div>

                  <div id="pdf-container">
                      <?php if (!empty($externalFile)): ?>
                      <div id="pdf-container-inner">
                          <iframe 
                              id="pdf-iframe"
                              src="<?php echo $externalFile; ?>#toolbar=0&navpanes=0&statusbar=0"
                              allow="clipboard-read; clipboard-write">
                          </iframe>

                          <div class="watermark">CONFIDENTIAL DOCUMENT<br>BLIS copyright &#169; <?php echo DATE("Y");?></div>

                          <div class="protection-overlay"></div>
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

<script>
// Language selector
document.querySelectorAll('.language-btn').forEach(button => {
    button.addEventListener('click', function() {
        const lang = this.getAttribute('data-lang');
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.set('LANG', lang);
        window.location.href = window.location.pathname + '?' + urlParams.toString();
    });
});

// Disable right-click
document.addEventListener("contextmenu", e => e.preventDefault());

// Block text selection & copy
['selectstart','copy','cut','dragstart'].forEach(evt => {
    document.addEventListener(evt, e => e.preventDefault());
});

// Keyboard shortcuts blocking
document.addEventListener('keydown', function(e) {
    const key = e.key.toLowerCase();
    const isCtrl = e.ctrlKey || e.metaKey;

    if (
        (isCtrl && ['p','s','u','c','a'].includes(key)) || // Ctrl/Cmd + P/S/U/C/A
        key === 'f12' ||                                    // F12
        key === 'printscreen'                                // PrintScreen
    ) {
        e.preventDefault();
        e.stopPropagation();
        showBlockOverlay();
        return false;
    }
}, true);

// Blocking overlay
function showBlockOverlay() {
    if(document.getElementById('block-overlay')) return;
    const block = document.createElement('div');
    block.id = 'block-overlay';
    block.style.position='fixed';
    block.style.top='0';
    block.style.left='0';
    block.style.width='100vw';
    block.style.height='100vh';
    block.style.background='rgba(0,0,0,0.9)';
    block.style.color='red';
    block.style.display='flex';
    block.style.alignItems='center';
    block.style.justifyContent='center';
    block.style.fontSize='40px';
    block.style.fontWeight='bold';
    block.style.zIndex='999999';
    block.innerText='This action is disabled! Refresh to continue';
    document.body.appendChild(block);
}

// Harden iframe if same domain
document.getElementById("pdf-iframe").addEventListener("load", () => {
    try {
        const innerDoc = document.getElementById("pdf-iframe").contentWindow.document;
        ['contextmenu','copy','selectstart','cut','dragstart'].forEach(evt => {
            innerDoc.addEventListener(evt, e => e.preventDefault());
        });
        innerDoc.body.style.userSelect="none";
    } catch(e) {
        // Cross-domain PDF: still protected by overlay + JS
    }
});

// DevTools detection
let warningCount = 0;
setInterval(() => {
    if(window.outerWidth - window.innerWidth > 200 || window.outerHeight - window.innerHeight > 200){
        warningCount++;
        if(warningCount > 1) showBlockOverlay();
    } else {
        warningCount = 0;
    }
}, 2000);
</script>

</body>
</html>
