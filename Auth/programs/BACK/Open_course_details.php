<?php
include('header.php');
include('Access.php');

$CERTIFICATE = $_GET['CERTIFICATE'] ?? '';
$COURSE = $_GET['COURSE'] ?? '';
$ID = $_GET['ID'] ?? '';

$selct_cert = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM certification_courses LEFT JOIN certifications ON certification_courses.course_certificate = certifications.certification_id WHERE course_id ='$COURSE'"));

$course_name = $selct_cert['course_name'] ?? 'Unknown Course';
$certification_name = $selct_cert['certification_name'] ?? 'Unknown Certification';

$select_file = mysqli_query($conn, "SELECT * FROM learning_topics WHERE topic_id='$ID'");
$File_found = mysqli_fetch_array($select_file);
$filePath = $File_found['topic_document'] ?? '';

if (!empty($filePath)) {
    $filePath = preg_replace('/^\.\.\//', '', $filePath);
    $filePath = str_replace(' ', '%20', $filePath);
    $filePath = "https://bluelackesadigital.com/Auth/" . ltrim($filePath, '/');
    
} else {
    $filePath = "";
}

if (isset($_GET['STATUS'])) {
    $STATUS = $_GET['STATUS'];
    $status = ($STATUS == "Active") ? "Active" : "Inactive";
} else {
    $STATUS = "Active";
    $status = $STATUS;
}
$action = "topic_course='$COURSE' AND topic_status ='$status'";

$select_modules = mysqli_query($conn, "SELECT * FROM learning_topics LEFT JOIN learning_weeks ON learning_topics.topic_week = learning_weeks.week_id WHERE $action");
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.7.570/pdf.min.js"></script>
<style>
    #pdfContainer {
        width: 100%; /* Changed to 100% to fit within the container */
        max-width: 900px;
        background-color: #f4f4f4;
        padding: 20px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        border-radius: 5px;
        overflow-x: auto; /* Added horizontal scroll for small screens */
    }

    .pdf-page {
        width: 100%;
        margin-bottom: 20px; /* Reduced margin for better spacing */
        padding: 10px; /* Reduced padding */
        background-color: white;
        box-shadow: 0 0 5px rgba(0, 0, 0, 0.1); /* Reduced shadow */
        border-radius: 5px;
    }

    .page-separator {
        height: 1px; /* Reduced separator height */
        background-color: #ccc;
        margin: 10px 0; /* Reduced margin */
    }
</style>

<div class="flex flex-1">
    <?php include('side_bar_courses.php'); ?>
    <main class="bg-white-500 flex-1 p-3 overflow-hidden">
        <div class="flex flex-col">
            <div class="flex flex-1 flex-col md:flex-row lg:flex-row mx-2"></div>
            <div class="flex flex-1 flex-col md:flex-row lg:flex-row mx-2">
                <div class="mb-2 border-solid border-gray-300 rounded border shadow-sm w-full">
                    <div class="bg-gray-200 px-2 py-3 border-solid border-gray-200 border-b">
                        <a href="Modules_per_Certification?CERTIFICATE=<?php echo urlencode($CERTIFICATE); ?>">
                            <button class='bg-blue-400 hover:bg-green-300 text-white font-bold py-2 px-4 rounded'>Back</button>
                        </a>
                    </div>
                    <div class="p-3">
                        <p><strong><big>Main Topics of the Course</big></strong></p>
                        <p>Course Name:&nbsp;<strong><big><?php echo htmlspecialchars($course_name); ?></big></strong></p>
                        <p>Certificate Name:&nbsp;<strong><big><?php echo htmlspecialchars($certification_name); ?></big></strong></p>
                        <br>
                        <p>Status: &nbsp;<strong><big><?php echo htmlspecialchars($status); ?></big></strong></p>
                        <br>
                        <?php if (!empty($filePath)): ?>
                            <p>Document Path: <a href="<?php echo htmlspecialchars($filePath); ?>" target="_blank">View Document</a></p>
                            <div id="pdfContainer"></div>
                        <?php else: ?>
                            <p>Document Path: File not found.</p>
                        <?php endif; ?>
                        <script>
                            var url = '<?php echo urldecode($filePath); ?>';
                            var pdfjsLib = window['pdfjs-dist/build/pdf'];
                            pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.7.570/pdf.worker.min.js';
                            var pdfContainer = document.getElementById('pdfContainer');
                            if (url) {
                                var loadingTask = pdfjsLib.getDocument(url);
                                loadingTask.promise.then(function (pdf) {
                                    for (var pageNumber = 1; pageNumber <= pdf.numPages; pageNumber++) {
                                        pdf.getPage(pageNumber).then(function (page) {
                                            var scale = 1.5;
                                            var viewport = page.getViewport({scale: scale});
                                            var canvas = document.createElement('canvas');
                                            canvas.className = 'pdf-page';
                                            pdfContainer.appendChild(canvas);
                                            var context = canvas.getContext('2d');
                                            canvas.height = viewport.height;
                                            canvas.width = viewport.width;
                                            var renderContext = {
                                                canvasContext: context,
                                                viewport: viewport
                                            };
                                            page.render(renderContext);
                                            if (pageNumber < pdf.numPages) {
                                                var separator = document.createElement('div');
                                                separator.className = 'page-separator';
                                                pdfContainer.appendChild(separator);
                                            }
                                        });
                                    }
                                });
                            }
                            document.addEventListener("contextmenu", function (e) {
                                e.preventDefault();
                            }, false);
                            document.addEventListener("keydown", function (e) {
                                if (e.ctrlKey && (e.key === "s" || e.key === "p")) {
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