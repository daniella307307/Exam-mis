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

// Function to get YouTube embed URL from normal or short URL
function getYouTubeEmbedUrl($url) {
    // Parse URL and get video ID for standard YouTube URLs
    parse_str(parse_url($url, PHP_URL_QUERY), $queryParams);
    if (isset($queryParams['v'])) {
        return "https://www.youtube.com/embed/" . $queryParams['v'];
    }
    // Handle youtu.be short URLs
    $host = parse_url($url, PHP_URL_HOST);
    $path = parse_url($url, PHP_URL_PATH);
    if (strpos($host, 'youtu.be') !== false && $path) {
        return "https://www.youtube.com/embed" . $path;
    }
    // If can't parse, return original URL as fallback (not recommended)
    return $url;
}

$youtubeEmbedUrl = getYouTubeEmbedUrl($module_video['topic_video']);
?>

<!--/Header-->
<style>
  body {
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
  }
  img, iframe, video {
    -webkit-user-drag: none;
    user-drag: none;
  }
</style>

<script>
  // Disable right-click
  document.addEventListener('contextmenu', e => e.preventDefault());

  // Disable certain keys and shortcuts
  document.addEventListener('keydown', function(e) {
    // Block F12, Ctrl+Shift+I, Ctrl+U, Ctrl+S, Ctrl+C
    if (
      e.key === 'F12' ||
      (e.ctrlKey && e.shiftKey && e.key.toLowerCase() === 'i') ||
      (e.ctrlKey && e.key.toLowerCase() === 'u') ||
      (e.ctrlKey && e.key.toLowerCase() === 's') ||
      (e.ctrlKey && e.key.toLowerCase() === 'c')
    ) {
      e.preventDefault();
      alert('This action is disabled.');
      return false;
    }
  });

  // Disable copy event
  document.addEventListener('copy', e => e.preventDefault());

  // Disable dragstart
  document.addEventListener('dragstart', e => e.preventDefault());
</script>





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
                        <a href="Modules_per_Certification?CERTIFICATE=<?php echo urlencode($CERTIFICATE); ?>">
                            <button class='bg-blue-400 hover:bg-green-300 text-white font-bold py-2 px-4 rounded'>
                                Back
                            </button>
                        </a>
                    </div>
                    <div class="p-3">
                        <p><strong><big>Main Topics of the course</big></strong></p> 
                        <p>Course Name:&nbsp;<strong><big><?php echo htmlspecialchars($course_name); ?></big></strong></p>
                        <p>Certificate Name:&nbsp;<strong><big><?php echo htmlspecialchars($certification_name); ?></big></strong></p> 
                        <br> 
                        <p>Topic:&nbsp;<strong><big><?php echo htmlspecialchars($module_video['topic_title']); ?></big></strong></p> 
                        <br> 
                        
                        <div class="video-container">
                            <iframe 
                                src="<?php echo htmlspecialchars($youtubeEmbedUrl); ?>?rel=0&controls=0&modestbranding=1&disablekb=1&fs=0" 
                                title="YouTube video player" 
                                frameborder="0" 
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                allowfullscreen 
                                oncontextmenu="return false"
                            ></iframe>
                        </div>

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
    /* Responsive video container */
    .video-container {
        width: 100%;
        max-width: 960px; /* max width */
        height: 0;
        padding-bottom: 56.25%; /* 16:9 Aspect Ratio */
        position: relative;
        overflow: hidden;
    }

    .video-container iframe {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border: 0;
        user-select: none; /* prevent iframe selection */
    }

    /* Disable text selection site-wide */
    body {
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
    }
</style>

<script>
    // Disable right-click on entire page
    document.addEventListener('contextmenu', event => event.preventDefault());
</script>

</body>
</html>
