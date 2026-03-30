<?php
ini_set('display_errors', 1); 
ini_set('display_startup_errors', 1); 
error_reporting(E_ALL); 

include('header.php');

// Input validation and sanitization
$CERTIFICATE = htmlspecialchars(filter_input(INPUT_GET, 'CERTIFICATE', FILTER_DEFAULT), ENT_QUOTES, 'UTF-8'); 
$COURSE = htmlspecialchars(filter_input(INPUT_GET, 'COURSE', FILTER_DEFAULT), ENT_QUOTES, 'UTF-8'); 
$ID = htmlspecialchars(filter_input(INPUT_GET, 'ID', FILTER_DEFAULT), ENT_QUOTES, 'UTF-8'); 

// Validate required parameters
if (!$CERTIFICATE || !$COURSE || !$ID) {
    die("Invalid request parameters.");
}

try {
    // Fetch course details
    $stmt = $conn->prepare("SELECT cc.*, c.certification_name 
                          FROM certification_courses cc
                          LEFT JOIN certifications c ON cc.course_certificate = c.certification_id 
                          WHERE cc.course_id = ?");
    $stmt->bind_param("s", $COURSE);
    $stmt->execute();
    $course_data = $stmt->get_result()->fetch_assoc();

    // Fetch PDF details
    $stmt = $conn->prepare("SELECT topic_document FROM learning_topics WHERE topic_id = ?");
    $stmt->bind_param("s", $ID);
    $stmt->execute();
    $pdf_data = $stmt->get_result()->fetch_assoc();

    // Validate PDF data
    if (!$pdf_data || !isset($pdf_data['topic_document'])) {
        throw new Exception("Requested document not found");
    }

    // Construct secure PDF URL
    $base_cdn = "https://bluelackesadigital.com/Auth/";
    $pdf_file = str_replace('../', '', $pdf_data['topic_document']); // Clean path
    $pdf_url =urldecode($base_cdn.urlencode($pdf_file))   ;

    // Verify URL format
    if (!filter_var($pdf_url, FILTER_VALIDATE_URL)) {
        throw new Exception("Invalid document URL format");
    }

} catch (Exception $e) {
    die("Error: " . htmlspecialchars($e->getMessage()));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure PDF Viewer</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
    <style>
        #pdfContainer {
            width: 100%;
            max-width: 900px;
            background-color: #f4f4f4;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            margin: auto;
        }
        .pdf-page {
            width: 100%;
            margin-bottom: 10px;
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var pdfUrl = "<?= $pdf_url ?>";
            var pdfContainer = document.getElementById("pdfContainer");
            var pdfjsLib = window["pdfjs-dist/build/pdf"];

            pdfjsLib.getDocument(pdfUrl).promise.then(function(pdf) {
                for (var pageNumber = 1; pageNumber <= pdf.numPages; pageNumber++) {
                    pdf.getPage(pageNumber).then(function(page) {
                        var scale = 1.5;
                        var viewport = page.getViewport({scale: scale});

                        var canvas = document.createElement("canvas");
                        canvas.className = "pdf-page";
                        pdfContainer.appendChild(canvas);

                        var context = canvas.getContext("2d");
                        canvas.height = viewport.height;
                        canvas.width = viewport.width;

                        var renderContext = {
                            canvasContext: context,
                            viewport: viewport
                        };
                        page.render(renderContext);
                    });
                }
            });

            // Disable right-click to prevent downloads
            document.addEventListener("contextmenu", function(e) {
                e.preventDefault();
            });

            // Disable print and save shortcuts
            document.addEventListener("keydown", function(e) {
                if (e.ctrlKey && (e.key === "s" || e.key === "p")) {
                    e.preventDefault();
                }
            });
        });
    </script>
</head>
<body>
    <div class="flex flex-1">
        <?php include('side_bar_courses.php'); ?>

        <main class="bg-white-500 flex-1 p-3 overflow-hidden">
            <div class="security-header">
                <a href="Modules_per_Certification?CERTIFICATE=<?= urlencode($CERTIFICATE) ?>">
                    <button class='bg-blue-400 hover:bg-green-300 text-white font-bold py-2 px-4 rounded'>
                        ← Back to Courses
                    </button>
                </a>
                <span class="text-sm text-gray-600 ml-4">Secure Document Viewer</span>
                <?php echo  $pdf_url;?>
            </div>

            <div id="pdfContainer"></div>
        </main>
    </div>

    <?php include('footer.php'); ?>
</body>
</html>
