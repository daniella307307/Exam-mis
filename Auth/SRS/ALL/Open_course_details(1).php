<?php
include('header.php');
include('Access.php');

$CERTIFICATE = $_GET['CERTIFICATE'] ?? '';
$COURSE = $_GET['COURSE'] ?? '';
$ID = $_GET['ID'] ?? '';

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
$filePath = $File_found['topic_document'] ?? '';

if (!empty($filePath)) {
    // Remove unnecessary '../' and encode spaces properly
    $filePath = str_replace(' ', '%20', preg_replace('/^\.\.\//', '', $filePath));

    // Construct the full URL dynamically
    $filePath = "https://bluelackesadigital.com/Auth/" . ltrim($filePath, '/');
} else {
    $filePath = "";
}

// Set status
$STATUS = $_GET['STATUS'] ?? "Active";
$status = ($STATUS == "Active") ? "Active" : "Inactive";
$action = "topic_course='$COURSE' AND topic_status ='$status'";

$select_modules = mysqli_query($conn, "
    SELECT * FROM learning_topics
    LEFT JOIN learning_weeks ON learning_topics.topic_week = learning_weeks.week_id
    WHERE $action
");

?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.12.313/pdf.min.js"></script>
<style>
    body {
        font-family: Arial, sans-serif;
        text-align: center;
    }

    #pdf-container {
        width: 100%;
        margin: auto;
        background: #f4f4f4;
        padding: 20px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        border-radius: 5px;
        overflow: auto;
    }

    .pdf-page {
        margin-bottom: 20px;
        background: white;
        padding: 10px;
        box-shadow: 0 0 5px rgba(0, 0, 0, 0.2);
        border-radius: 5px;
        display: flex;
        justify-content: center;
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
                        <p>Status: &nbsp;<strong><big><?php echo htmlspecialchars($status); ?></big></strong></p>
                        <br>
                        <?php echo $filePath; ?>
                        <?php
                        if (!empty($filePath) && !file_exists(str_replace('%20',' ',str_replace("https://bluelackesadigital.com/Auth/","", $filePath)))){
                            ?>
                            <p><strong style="color: red;">File not found on server!</strong></p>
                            <?php
                        }
                        ?>

                        <?php if (!empty($filePath)): ?>

                            <div id="pdf-container"></div>

                        <?php else: ?>
                            <p><strong style="color: red;">File path is empty!</strong></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include('footer.php'); ?>

<script>
    var url = '<?php echo $filePath; ?>'; // Your PDF file URL

    if (url) {
        pdfjsLib.getDocument(url).promise.then(function (pdf) {
            var container = document.getElementById("pdf-container");

            function renderPage(pageNum) {
                pdf.getPage(pageNum).then(function (page) {
                    var scale = 1.5;
                    var viewport = page.getViewport({scale});

                    var canvas = document.createElement("canvas");
                    canvas.className = "pdf-page";
                    var context = canvas.getContext("2d");

                    canvas.height = viewport.height;
                    canvas.width = viewport.width;

                    container.appendChild(canvas);

                    var renderContext = {
                        canvasContext: context,
                        viewport: viewport
                    };

                    page.render(renderContext).promise.then(function () {
                        if (pageNum < pdf.numPages) {
                            renderPage(pageNum + 1); // Load the next page
                        }
                    });
                });
            }

            renderPage(1); // Start loading from the first page
        }).catch(function(error){
            console.error("PDF load error:", error);
            document.getElementById("pdf-container").innerHTML = "<p style='color:red;'>Failed to load PDF.</p>";
        });
    }

</script>

</body>
</html>