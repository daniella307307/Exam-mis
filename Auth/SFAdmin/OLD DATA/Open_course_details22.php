<?php 
include('header.php');
include('Access.php');

$CERTIFICATE = $_GET['CERTIFICATE'];
$COURSE = $_GET['COURSE'];
$ID = $_GET['ID'];

$selct_cert = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM certification_courses
LEFT JOIN certifications ON certification_courses.course_certificate = certifications.certification_id WHERE course_id ='$COURSE'"));

$course_name = $selct_cert['course_name']; 
$certification_name = $selct_cert['certification_name'];

$STATUS = isset($_GET['STATUS']) ? $_GET['STATUS'] : "Active";
$status = $STATUS;
$action = "topic_course='$COURSE' AND topic_status ='$status'";

$select_modules = mysqli_query($conn, "SELECT * FROM learning_topics 
LEFT JOIN learning_weeks ON learning_topics.topic_week = learning_weeks.week_id WHERE  $action");
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.7.570/pdf.min.js"></script>
<style>
    #pdfContainer {
        width: 150%;
        max-width: 900px;
        background-color: #f4f4f4;
        padding: 20px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        border-radius: 5px;
    }

    .pdf-page {
        width: 100%;
        margin-bottom: 40px;
        padding: 20px;
        background-color: white;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        border-radius: 5px;
        position: relative;
    }

    .watermark {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 30px;
        color: rgba(255, 0, 0, 0.5);
        font-weight: bold;
        pointer-events: none;
    }

    .page-separator {
        height: 2px;
        background-color: #ccc;
        margin: 20px 0;
    }

    iframe {
        display: none;
    }
</style>

<!--/Header-->
<div class="flex flex-1">
    <?php include('side_bar_courses.php'); ?>
    <main class="bg-white-500 flex-1 p-3 overflow-hidden">
        <div class="flex flex-col">
            <div class="flex flex-1 flex-col md:flex-row lg:flex-row mx-2">
            </div>

            <div class="flex flex-1 flex-col md:flex-row lg:flex-row mx-2">
                <div class="mb-2 border-solid border-gray-300 rounded border shadow-sm w-full">
                    <div class="bg-gray-200 px-2 py-3 border-solid border-gray-200 border-b">
                        <a href="Modules_per_Certification?CERTIFICATE=<?php echo $CERTIFICATE; ?>">
                            <button class='bg-blue-400 hover:bg-green-300 text-white font-bold py-2 px-4 rounded'>Back</button>
                        </a>
                    </div>
                    <div class="p-3">
                        <p><strong><big>Main Topics of the Course</big></strong></p> 
                        <p>Course Name: <strong><big><?php echo $course_name; ?></big></strong></p>
                        <p>Certificate Name: <strong><big><?php echo $certification_name; ?></big></strong></p>
                        <p>Status: <strong><big><?php echo $status; ?></big></strong></p> 

                        <div id="pdfContainer"></div>
                        <script>
                            var url = 'view_pdf.php?ID=<?php echo $ID; ?>';

                            var pdfjsLib = window['pdfjs-dist/build/pdf'];
                            pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.7.570/pdf.worker.min.js';

                            var pdfContainer = document.getElementById('pdfContainer');

                            pdfjsLib.getDocument(url).promise.then(function(pdf) {
                                for (var pageNumber = 1; pageNumber <= pdf.numPages; pageNumber++) {
                                    pdf.getPage(pageNumber).then(function(page) {
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

                                        var watermark = document.createElement('div');
                                        watermark.className = 'watermark';
                                        watermark.textContent = "Confidential";
                                        canvas.parentNode.appendChild(watermark);

                                        if (pageNumber < pdf.numPages) {
                                            var separator = document.createElement('div');
                                            separator.className = 'page-separator';
                                            pdfContainer.appendChild(separator);
                                        }
                                    });
                                }
                            });

                            document.addEventListener("contextmenu", function(e) {
                                e.preventDefault();
                            }, false);

                            document.addEventListener("keydown", function(e) {
                                if (e.ctrlKey && (e.key === "s" || e.key === "p" || e.key === "u" || e.key === "c" || e.key === "j" || e.key === "i")) {
                                    e.preventDefault();
                                }
                                if (e.key === "F12") {
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

<?php include('footer.php')?>
<script src="../../main.js"></script>
</body>
</html>
