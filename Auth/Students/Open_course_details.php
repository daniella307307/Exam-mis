<?php
include('header.php');
include('Access.php');

// Validate and sanitize input parameters
$CERTIFICATE = isset($_GET['CERTIFICATE']) ? intval($_GET['CERTIFICATE']) : 0;
$COURSE = isset($_GET['COURSE']) ? intval($_GET['COURSE']) : 0;
$ID = isset($_GET['ID']) ? intval($_GET['ID']) : 0;

// Check if required parameters are provided
if ($COURSE <= 0) {
    die("Invalid course parameter");
}

// Use prepared statements to prevent SQL injection
$stmt = mysqli_prepare($conn, "SELECT cc.*, c.certification_name 
                               FROM certification_courses cc 
                               LEFT JOIN certifications c ON cc.course_certificate = c.certification_id 
                               WHERE cc.course_id = ?");
mysqli_stmt_bind_param($stmt, "i", $COURSE);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$selct_cert = mysqli_fetch_array($result);

$course_name = htmlspecialchars($selct_cert['course_name'] ?? 'Unknown Course');
$certification_name = htmlspecialchars($selct_cert['certification_name'] ?? 'Unknown Certification');

// Fetch file information
$filePath = "";
if ($ID > 0) {
    $stmt = mysqli_prepare($conn, "SELECT topic_document FROM learning_topics WHERE topic_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $ID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($File_found = mysqli_fetch_array($result)) {
        $filePath = $File_found['topic_document'] ?? '';
        
        if (!empty($filePath)) {
            $filePath = preg_replace('/^\.\.\//', '', $filePath);
            $filePath = str_replace(' ', '%20', $filePath);
            $filePath = "https://bluelackesadigital.com/Auth/" . ltrim($filePath, '/');
        }
    }
}

// Handle status filter
$status = "Active";
if (isset($_GET['STATUS']) && $_GET['STATUS'] === "Inactive") {
    $status = "Inactive";
}

// Fetch modules with prepared statement
$stmt = mysqli_prepare($conn, "SELECT * FROM learning_topics 
                               LEFT JOIN learning_weeks ON learning_topics.topic_week = learning_weeks.week_id 
                               WHERE topic_course = ? AND topic_status = ?");
mysqli_stmt_bind_param($stmt, "is", $COURSE, $status);
mysqli_stmt_execute($stmt);
$select_modules = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $course_name; ?> - Topics</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.7.570/pdf.min.js"></script>
    <style>
        #pdfContainer {
            width: 100%;
            max-width: 900px;
            background-color: #f4f4f4;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            overflow-x: auto;
        }

        .pdf-page {
            width: 100%;
            margin-bottom: 20px;
            padding: 10px;
            background-color: white;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
        }

        .page-separator {
            height: 1px;
            background-color: #ccc;
            margin: 10px 0;
        }
        
        .btn {
            background-color: #3b82f6;
            color: white;
            font-weight: bold;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            border: none;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background-color: #2563eb;
        }
        
        .document-info {
            margin: 15px 0;
            padding: 10px;
            background-color: #f0f9ff;
            border-left: 4px solid #3b82f6;
        }
    </style>
</head>
<body>
<div class="flex flex-1">
    <?php include('side_bar_courses.php'); ?>
    <main class="bg-white-500 flex-1 p-3 overflow-hidden">
        <div class="flex flex-col">
            <div class="flex flex-1 flex-col md:flex-row lg:flex-row mx-2"></div>
            <div class="flex flex-1 flex-col md:flex-row lg:flex-row mx-2">
                <div class="mb-2 border-solid border-gray-300 rounded border shadow-sm w-full">
                    <div class="bg-gray-200 px-2 py-3 border-solid border-gray-200 border-b">
                        <a href="Modules_per_Certification?CERTIFICATE=<?php echo urlencode($CERTIFICATE); ?>">
                            <button class='btn'>Back</button>
                        </a>
                    </div>
                    <div class="p-3">
                        <h1 class="text-xl font-bold">Main Topics of the Course</h1>
                        <div class="document-info">
                            <p><strong>Course Name:</strong> <?php echo $course_name; ?></p>
                            <p><strong>Certificate Name:</strong> <?php echo $certification_name; ?></p>
                            <p><strong>Status:</strong> <?php echo htmlspecialchars($status); ?></p>
                        </div>
                        
                        <?php if (!empty($filePath)): ?>
                            <div class="document-info">
                                <p>Document Path: <a href="<?php echo htmlspecialchars($filePath); ?>" target="_blank" class="text-blue-600 hover:underline">View Document</a></p>
                            </div>
                            <div id="pdfContainer"></div>
                        <?php else: ?>
                            <div class="document-info">
                                <p>No document available for this topic.</p>
                            </div>
                        <?php endif; ?>
                        
                        <script>
                            // Only attempt to load PDF if filePath exists
                            <?php if (!empty($filePath)): ?>
                            var url = '<?php echo $filePath; ?>';
                            var pdfjsLib = window['pdfjs-dist/build/pdf'];
                            pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.7.570/pdf.worker.min.js';
                            
                            var pdfContainer = document.getElementById('pdfContainer');
                            
                            // Show loading message
                            pdfContainer.innerHTML = '<p>Loading document...</p>';
                            
                            if (url) {
                                var loadingTask = pdfjsLib.getDocument(url);
                                loadingTask.promise.then(function(pdf) {
                                    pdfContainer.innerHTML = ''; // Clear loading message
                                    
                                    for (var pageNumber = 1; pageNumber <= pdf.numPages; pageNumber++) {
                                        (function(pageNum) {
                                            pdf.getPage(pageNum).then(function(page) {
                                                var scale = 1.5;
                                                var viewport = page.getViewport({scale: scale});
                                                var canvas = document.createElement('canvas');
                                                canvas.className = 'pdf-page';
                                                var context = canvas.getContext('2d');
                                                canvas.height = viewport.height;
                                                canvas.width = viewport.width;
                                                
                                                pdfContainer.appendChild(canvas);
                                                
                                                var renderContext = {
                                                    canvasContext: context,
                                                    viewport: viewport
                                                };
                                                
                                                page.render(renderContext);
                                                
                                                if (pageNum < pdf.numPages) {
                                                    var separator = document.createElement('div');
                                                    separator.className = 'page-separator';
                                                    pdfContainer.appendChild(separator);
                                                }
                                            });
                                        })(pageNumber);
                                    }
                                }).catch(function(error) {
                                    pdfContainer.innerHTML = '<p>Error loading PDF: ' + error.message + '</p>';
                                    console.error('PDF loading error:', error);
                                });
                            }
                            <?php endif; ?>
                            
                            // Prevent right-click and certain keyboard shortcuts
                            document.addEventListener("contextmenu", function(e) {
                                e.preventDefault();
                            }, false);
                            
                            document.addEventListener("keydown", function(e) {
                                if (e.ctrlKey && (e.key === "s" || e.key === "p" || e.key === "c")) {
                                    e.preventDefault();
                                }
                            });
                        </script>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
<?php include('footer.php'); ?>
</body>
</html>