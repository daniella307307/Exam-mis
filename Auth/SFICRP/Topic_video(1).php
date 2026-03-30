<?php 
include('header.php');
//include('Access.php');

if (isset($_GET['CERTIFICATE'], $_GET['COURSE'], $_GET['ID'])) {
    $CERTIFICATE = $_GET['CERTIFICATE'];
    $COURSE = $_GET['COURSE'];
    $ID = $_GET['ID'];

    // Fetch certification and course details
    $selct_cert_query = "SELECT * FROM certification_courses
    LEFT JOIN certifications ON certification_courses.course_certificate = certifications.certification_id 
    WHERE course_id ='$COURSE'";

    $selct_cert = mysqli_fetch_array(mysqli_query($conn, $selct_cert_query));
    $course_name = $selct_cert['course_name']; 
    $certification_name = $selct_cert['certification_name'];

    // Fetch module details
    $select_modules_query = "SELECT * FROM learning_topics 
    LEFT JOIN learning_weeks ON learning_topics.topic_week = learning_weeks.week_id 
    WHERE topic_id='$ID'";

    $module_video = mysqli_fetch_array(mysqli_query($conn, $select_modules_query));
} else {
    echo "Error: Required parameters missing!";
    exit;
}
?>

<!--/Header-->

<div class="flex flex-1">
    <!--Sidebar-->
    <?php include('side_bar_courses.php'); ?>
    <!--/Sidebar-->
    <!--Main-->
    <main class="bg-white-500 flex-1 p-3 overflow-hidden">

        <div class="flex flex-col">
            <!-- Card Section Starts Here -->
            <div class="flex flex-1 flex-col md:flex-row lg:flex-row mx-2">
            </div>
            <!-- /Cards Section Ends Here -->

            <!--Grid Form-->
            <div class="flex flex-1 flex-col md:flex-row lg:flex-row mx-2">
                <div class="mb-2 border-solid border-gray-300 rounded border shadow-sm w-full">
                    <div class="bg-gray-200 px-2 py-3 border-solid border-gray-200 border-b">
                        <a href="Modules_per_Certification?CERTIFICATE=<?php echo $CERTIFICATE; ?>">
                            <button class='bg-blue-400 hover:bg-green-300 text-white font-bold py-2 px-4 rounded'>
                                Back
                            </button>
                        </a>
                    </div>
                    <div class="p-3">
                        <p><strong><big>Main Topics of the course</big></strong></p> 
                        <p>Course Name:&nbsp;<strong><big><?php echo $course_name; ?></big></strong></p>
                        <p>Certificate Name:&nbsp;<strong><big><?php echo $certification_name; ?></big></strong></p> 
                        <br> 
                        <p>Topic:&nbsp;<strong><big><?php echo $module_video['topic_title']; ?></big></strong></p> 
                        <br> 
                       <!-- <div class="video-container">
                            <iframe src="<?php echo $module_video['topic_video']; ?>?rel=0&controls=0&modestbranding=1&showinfo=0" 
                                title="YouTube video player" frameborder="0" 
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                referrerpolicy="strict-origin-when-cross-origin" allowfullscreen oncontextmenu="return false">
                            </iframe>
                        </div>-->
                        
                        
                        
                        <video width="960" height="450" controls>
  <source src="<?php echo $module_video['topic_video']; ?>" type="video/mp4"> 
  Your browser does not support the video tag.
</video>
                    </div>
                </div>
            </div>
            <!--/Grid Form-->
        </div>
    </main>
    <!--/Main-->
</div>

<!--Footer-->
<?php include('footer.php'); ?>
<!--/footer-->

<script src="../../main.js"></script>

<style>
    .video-container {
        width: 100%;
        max-width: 960Px; /* Sets max width to ensure it doesn't exceed typical video sizes */
        height: 340px;
        padding-bottom: 56.25%; /* Maintain 16:9 aspect ratio */
        position: relative;
        display: flex;
        justify-content: center;
        align-items: center;
        overflow: hidden;
    }

    .video-container iframe {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border: 0;
    }
</style>

</body>
</html>
