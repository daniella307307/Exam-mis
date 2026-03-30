<?php
include('header.php'); 
// Language parameter handling
$LANG = isset($_GET['LANG']) ? $_GET['LANG'] : $school_language;

$CERTIFICATE = $_GET['CERTIFICATE'];
$COURSE = $_GET['COURSE']; 
$selct_cert = mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM certification_courses
LEFT JOIN certifications ON certification_courses.course_certificate = certifications.certification_id WHERE course_id ='$COURSE'"));
$course_name = $selct_cert['course_name']; 
$certification_name = $selct_cert['certification_name'];

// Get the actual language setting from database for this school
$language_setting = $school_language; // This should come from your database configuration

if(isset($_GET['STATUS'])){
    $STATUS = $_GET['STATUS'];
    if($STATUS == "Active"){
        $status = "Active"; 
    } else {
        $status = "Inactive";  
    }
    $action = "topic_course='$COURSE' AND topic_status ='$status'"; 
} else {
    $STATUS = "Active";	
    $status = $STATUS; 
    $action = "topic_course='$COURSE' AND topic_status ='$STATUS'";
}
?>
  
<!--/Header-->

<div class="flex flex-1">
    <!--Sidebar-->
    <?php include('side_bar_courses.php');?>
    <!--/Sidebar-->
    <!--Main-->
    <main class="bg-white-500 flex-1 p-3 overflow-hidden">

        <div class="flex flex-col">
            <!-- Card Sextion Starts Here -->
            <div class="flex flex-1  flex-col md:flex-row lg:flex-row mx-2">
                <!--Horizontal form-->
                
                <!--/Horizontal form-->

                <!--Underline form-->
                 
                <!--/Underline form-->
            </div>
            <!-- /Cards Section Ends Here -->

            <!--Grid Form-->

            <div class="flex flex-1  flex-col md:flex-row lg:flex-row mx-2">
                <div class="mb-2 border-solid border-gray-300 rounded border shadow-sm w-full">
                    <div class="bg-gray-200 px-2 py-3 border-solid border-gray-200 border-b flex justify-between items-center">
                        <!-- Left side: Back button and Active/Inactive buttons -->
                        <div class="flex items-center">
                            <a href="Modules_per_Certification?CERTIFICATE=<?php echo $CERTIFICATE;?>&LANG=<?php echo $LANG;?>" class="mr-3">
                                <button class='bg-blue-400 hover:bg-green-300 text-white font-bold py-2 px-4 rounded'>Back</button>
                            </a>  
                            <a href="Module_topics?COURSE=<?php echo $COURSE;?>&CERTIFICATE=<?php echo $CERTIFICATE;?>&STATUS=Active&LANG=<?php echo $LANG; ?>" class="mr-3">
                                <button class='bg-green-500 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded'>Active</button>
                            </a>  
                            <a href="Module_topics?COURSE=<?php echo $COURSE;?>&CERTIFICATE=<?php echo $CERTIFICATE;?>&STATUS=Inactive&LANG=<?php echo $LANG; ?>">
                                <button class='bg-red-500 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'>Inactive</button>
                            </a>  
                        </div>
                        
                        <!-- Right side: Language buttons - Only show if language setting is Bilingual -->
                        <div class="flex items-center">
                            <?php if($language_setting == "Bilingual"): ?>
                                <!-- Show both language buttons for switching -->
                                <?php if($LANG == "French" || $LANG == "FR"): ?>
                                    <!-- Currently in French, show English button to switch -->
                                    <a href="Module_topics?COURSE=<?php echo $COURSE;?>&CERTIFICATE=<?php echo $CERTIFICATE;?>&STATUS=<?php echo $STATUS; ?>&LANG=ENG" class="mr-3">
                                        <button class='bg-purple-500 hover:bg-purple-800 text-white font-bold py-2 px-4 rounded'>Switch to ENG</button>
                                    </a>
                                <?php else: ?>
                                    <!-- Currently in English, show French button to switch -->
                                    <a href="Module_topics?COURSE=<?php echo $COURSE;?>&CERTIFICATE=<?php echo $CERTIFICATE;?>&STATUS=<?php echo $STATUS; ?>&LANG=FR">
                                        <button class='bg-blue-500 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded'>Switch to FR</button>
                                    </a>
                                <?php endif; ?>
                            
                            <?php else: ?>
                                <!-- Single language mode - show current language as disabled button -->
                                <button class='bg-gray-400 cursor-not-allowed text-white font-bold py-2 px-4 rounded'>
                                    <?php 
                                    if($language_setting == "French" || $language_setting == "FR") {
                                        echo "FR";
                                    } else {
                                        echo "ENG";
                                    }
                                    ?>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                     
                    <div class="p-3">
                        <div class="mb-4">
                            <p class="text-lg font-bold mb-2">Main Topics of the course</p> 
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <div>
                                    <p class="text-sm text-gray-600">Course Name:</p>
                                    <p class="font-semibold"><?php echo $course_name; ?></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Certificate Name:</p>
                                    <p class="font-semibold"><?php echo $certification_name; ?></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Status:</p>
                                    <p class="font-semibold"><?php echo $status; ?></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Current Display Language:</p>
                                    <p class="font-semibold"><?php echo $LANG; ?></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">System Language Setting:</p>
                                    <p class="font-semibold"><?php echo $language_setting; ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full table-auto border-collapse border border-gray-300">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="border border-gray-300 px-3 py-2 text-sm font-medium text-center">#</th>
                                        <th class="border border-gray-300 px-3 py-2 text-sm font-medium">Week</th>
                                        <th class="border border-gray-300 px-3 py-2 text-sm font-medium">Topic Details</th> 										
                                        <th class="border border-gray-300 px-3 py-2 text-sm font-medium text-center">Status</th>
                                        <th class="border border-gray-300 px-3 py-2 text-sm font-medium text-center">Course</th>
                                        <th class="border border-gray-300 px-3 py-2 text-sm font-medium text-center">Video</th> 
                                        <th class="border border-gray-300 px-3 py-2 text-sm font-medium text-center">Visibility</th> 
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $select_modules = mysqli_query($conn,"SELECT * FROM learning_topics 
                                    LEFT JOIN learning_weeks ON learning_topics.topic_week = learning_weeks.week_id WHERE $action");
                                    
                                    while($module_details = mysqli_fetch_array($select_modules)){
                                        $topic_video = $module_details['topic_video'];
                                        $topic_visibility = $module_details['topic_visibility'];
                                        $topic_id123 = $module_details['topic_id'];
                                        $topic_document12345 = $module_details['topic_document'];
                                        
                                        if(!empty($topic_document12345)){
                                            $url = "Open_course_details?COURSE=$COURSE&CERTIFICATE=$CERTIFICATE&CURRENT=$STATUS&STATUS=Active&ID=$topic_id123&LANG=$LANG";  
                                            $btn = "bg-green-500 hover:bg-green-600";
                                            $text = "Open";
                                        } else {
                                            $url = "#";  
                                            $btn = "bg-red-500 cursor-not-allowed";
                                            $text = "N/A"; 
                                        }
                                        
                                        $find = mysqli_query($conn,"SELECT * FROM topics_management WHERE management_cert='$CERTIFICATE' AND management_course='$COURSE' AND management_topic ='$topic_id123' AND management_school='$school_ref' ");
                                        $hide = mysqli_num_rows($find);
                                    ?>
                                    <tr class="hover:bg-gray-50">   
                                        <td class="border border-gray-300 px-3 py-2 text-sm text-center"><?php echo $module_details['topic_id'];?></td>
                                        <td class="border border-gray-300 px-3 py-2 text-sm"><?php echo $module_details['week_description'];?></td>
                                        <td class="border border-gray-300 px-3 py-2 text-sm">
                                            <?php 
                                            // Display topic title based on current language
                                            if($LANG == "English" || $LANG == "ENG"){
                                                echo $module_details['topic_title'];
                                            } elseif($LANG == "French" || $LANG == "FR"){
                                                // Use French title if available, fallback to English
                                                echo !empty($module_details['topic_french']) ? $module_details['topic_french'] : $module_details['topic_title'];
                                            } else {
                                                echo $module_details['topic_title'];
                                            }
                                            ?>
                                        </td>
                                        <td class="border border-gray-300 px-3 py-2 text-center">
                                            <?php if($STATUS == "Active"){ ?>
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <i class="fas fa-unlock mr-1"></i> Active
                                                </span>
                                            <?php } else { ?>
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    <i class="fas fa-lock mr-1"></i> Inactive
                                                </span>
                                            <?php } ?>
                                        </td>
                                        <td class="border border-gray-300 px-3 py-2 text-center">
                                            <a href="<?php echo $url; ?>" class="<?php echo $btn; ?> text-white text-xs font-medium py-1 px-2 rounded inline-flex items-center transition duration-200">
                                                <i class="fas fa-book-open mr-1"></i><?php echo $text; ?>
                                            </a>
                                        </td>
                                        <td class="border border-gray-300 px-3 py-2 text-center">
                                            <?php if(!empty($topic_video)){ ?>
                                                <a href="Topic_video.php?COURSE=<?php echo $COURSE;?>&CERTIFICATE=<?php echo $CERTIFICATE;?>&CURRENT=<?php echo $STATUS;?>&STATUS=Active&ID=<?php echo $module_details['topic_id'];?>&LANG=<?php echo $LANG; ?>" class="bg-green-500 hover:bg-green-600 text-white text-xs font-medium py-1 px-2 rounded inline-flex items-center transition duration-200">
                                                    <i class="fas fa-play mr-1"></i> Play
                                                </a>
                                            <?php } else { ?>
                                                <span class="bg-red-500 text-white text-xs font-medium py-1 px-2 rounded inline-flex items-center">
                                                    <i class="fas fa-ban mr-1"></i> No Video
                                                </span>
                                            <?php } ?>
                                        </td>
                                        <td class="border border-gray-300 px-3 py-2 text-center">
                                            <?php if($hide < 1){
                                                // SHOW / HIDE BY TEACHER
                                                if($topic_visibility == "Visible"){ ?>
                                                    <a href="update_visibility_inclass?TOPIC=<?php echo $module_details['topic_id'];?>&COURSE=<?php echo $COURSE;?>&CERTIFICATE=<?php echo $CERTIFICATE;?>&CURRENT=<?php echo $STATUS;?>&STATUS=Active&ID=<?php echo $module_details['topic_id'];?>&LANG=<?php echo $LANG; ?>" class="bg-green-500 hover:bg-green-600 text-white text-xs font-medium py-1 px-2 rounded inline-flex items-center transition duration-200">
                                                        <i class="fas fa-eye mr-1"></i> Visible
                                                    </a>
                                                <?php } else { ?>
                                                    <span class="bg-yellow-500 text-white text-xs font-medium py-1 px-2 rounded inline-flex items-center">
                                                        <i class="fas fa-eye-slash mr-1"></i> Hidden by Admin
                                                    </span>
                                                <?php }
                                            } else { ?>
                                                <a href="update_visibility_inclass?TOPIC=<?php echo $module_details['topic_id'];?>&COURSE=<?php echo $COURSE;?>&CERTIFICATE=<?php echo $CERTIFICATE;?>&CURRENT=<?php echo $STATUS;?>&STATUS=Active&ID=<?php echo $module_details['topic_id'];?>&LANG=<?php echo $LANG; ?>" class="bg-red-500 hover:bg-red-600 text-white text-xs font-medium py-1 px-2 rounded inline-flex items-center transition duration-200">
                                                    <i class="fas fa-eye-slash mr-1"></i> Hidden by Teacher
                                                </a>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
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
<?php include('footer.php')?>
<!--/footer-->

</div>

</div>

<script src="../../main.js"></script>

</body>

</html>