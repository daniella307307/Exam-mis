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
elseif($LANG=="FR"){ $externalFile = $File_found['topic_french'] ?? '';}
elseif($LANG=="Bilingual"){$externalFile = $File_found['topic_document'] ?? '';}
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
<title>Course Document Viewer</title>
<style>
    body { font-family: Arial, sans-serif; margin: 0; padding: 0; overflow: auto; }
    #pdf-container { width: 100%; height: 80vh; position: relative; overflow: auto; }
    #pdf-iframe { width: 100%; height: 100%; border: none; }
    .course-info { margin-bottom: 20px; }
    .language-selector { margin: 10px 0; }
    .language-btn { 
        background-color: #f0f0f0; 
        border: 1px solid #ccc; 
        padding: 8px 15px; 
        margin-right: 5px; 
        cursor: pointer; 
        border-radius: 4px;
        transition: background-color 0.3s;
    }
    .language-btn:hover { background-color: #e0e0e0; }
    .language-btn.active { 
        background-color: #4a90e2; 
        color: white; 
        border-color: #4a90e2;
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
                  
                  <!-- Language Selector -->
                  <div class="language-selector" style="display: inline-block; margin-left: 20px;">
                      <span style="margin-right: 10px;">Language:</span>
                      <button class="language-btn <?php echo ($LANG == 'ENG' || $LANG == '') ? 'active' : ''; ?>" data-lang="ENG">English</button>
                      <button class="language-btn <?php echo $LANG == 'FR' ? 'active' : ''; ?>" data-lang="FR">French</button> 
                  </div>
              </div>
              <div class="p-3">
                  <div class="course-info">
                      <h1>Main Topics of the Course</h1>
                      <p><strong>Course Name:</strong> <?php echo htmlspecialchars($course_name); ?></p>
                      <p><strong>Certificate Name:</strong> <?php echo htmlspecialchars($certification_name); ?></p>
                      <p><strong>Status:</strong> <?php echo htmlspecialchars($status); ?></p>
                  </div>

                  <div id="pdf-container">
                  <?php if (!empty($externalFile)): ?>
                      <div style="position:relative; width:100%; height:100vh; overflow:auto;">
                          <iframe 
                              src="<?php echo $externalFile; ?>#toolbar=0&navpanes=0" 
                              style="width:100%; height:100%; border:none; display:block;">
                          </iframe>

                          <!-- Overlay -->
                          <div 
                              style="position:absolute; top:0; left:0; width:95%; height:100%; background:transparent; pointer-events:auto;"
                              oncontextmenu="return false;"    
                              onselectstart="return false;"   
                              ondragstart="return false;">
                          </div>
                      </div>
                  <?php else: ?>
                      <p style="color: red; font-weight: bold;">No document available for this topic</p>
                  <?php endif; ?>
                  </div>

                  <script>
                      // Language selector functionality
                      document.querySelectorAll('.language-btn').forEach(button => {
                          button.addEventListener('click', function() {
                              const lang = this.getAttribute('data-lang');
                              const urlParams = new URLSearchParams(window.location.search);
                              
                              // Update the LANG parameter
                              urlParams.set('LANG', lang);
                              
                              // Redirect to the same page with updated language
                              window.location.href = window.location.pathname + '?' + urlParams.toString();
                          });
                      });

                      // Disable right-click on container
                      document.getElementById('pdf-container').addEventListener('contextmenu', function(e){
                          e.preventDefault();
                          alert('Right-click disabled on this document.');
                      });

                      // Disable some keyboard shortcuts
                      document.addEventListener('keydown', function(e){
                          if (e.ctrlKey && (e.key === 's' || e.key === 'p' || e.key === 'u')) {
                              e.preventDefault();
                              alert('This action is disabled.');
                          }
                          if (e.key === 'F12') {
                              e.preventDefault();
                              alert('This action is disabled.');
                          }
                      });
                  </script>

              </div>
          </div>
      </div>
  </main>
</div>
</body>
</html>