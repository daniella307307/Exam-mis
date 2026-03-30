<?php include('header.php');
// include('Access.php');
$CERTIFICATE = $_GET['CERTIFICATE'];
// Language parameter handling - get from URL or use school default
$LANG = isset($_GET['LANG']) ? $_GET['LANG'] : $school_language;

$selct_cert = mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM certifications WHERE certification_id='$CERTIFICATE'"));
$certification_name = $selct_cert['certification_name'];
$certification_duration = $selct_cert['certification_duration'];

// Get the actual language setting from database for this certification/school
$language_setting = $school_language; // This should come from your database configuration

if(isset($_GET['STATUS'])){
    $STATUS = $_GET['STATUS'];
    if($STATUS == "Active"){
        $status = "Active"; 
    } else {
        $status = "Inactive";  
    }
    $action = "course_certificate='$CERTIFICATE' AND course_status ='$status'"; 
} else {
    $STATUS = "Active";	
    $status = $STATUS; 
    $action = "course_certificate='$CERTIFICATE' AND course_status ='$STATUS'";
}

$select_modules = mysqli_query($conn,"SELECT * FROM certification_courses WHERE $action");
?>
  
<!--/Header-->

<div class="flex flex-1">
    <?php include('side_bar_courses.php');?>
    
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
                        <!-- Left side: Active/Inactive buttons -->
                        <div class="flex items-center">
                            <span class="mr-4">Certification</span>
                            <a href="Modules_per_Certification?CERTIFICATE=<?php echo $CERTIFICATE;?>&LANG=<?php echo $LANG;?>&STATUS=Active" class="mr-3">
                                <button class='bg-green-500 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded'>Active</button>
                            </a>  
                            <a href="Modules_per_Certification?CERTIFICATE=<?php echo $CERTIFICATE;?>&LANG=<?php echo $LANG;?>&STATUS=Inactive">
                                <button class='bg-red-500 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'>Inactive</button>
                            </a> 
                        </div>
                        
                        <!-- Right side: Language buttons - Only show if language setting is Bilingual -->
                        <div class="flex items-center">
                            <?php if($language_setting == "Bilingual"): ?>
                                <!-- Show both language buttons for switching -->
                                <?php if($LANG == "French" || $LANG == "FR"): ?>
                                    <!-- Currently in French, show English button to switch -->
                                    <a href="Modules_per_Certification?CERTIFICATE=<?php echo $CERTIFICATE;?>&STATUS=<?php echo $STATUS; ?>&LANG=ENG" class="mr-3">
                                        <button class='bg-purple-500 hover:bg-purple-800 text-white font-bold py-2 px-4 rounded'>Switch to ENG</button>
                                    </a>
                                <?php else: ?>
                                    <!-- Currently in English, show French button to switch -->
                                    <a href="Modules_per_Certification?CERTIFICATE=<?php echo $CERTIFICATE;?>&STATUS=<?php echo $STATUS; ?>&LANG=FR">
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
                        <p><strong><big><?php echo $certification_name;?> Courses</big></strong></p> 
                        <p><strong><big>Duration:<?php echo $certification_duration;?> &nbsp;months</big></strong></p><br>
                        <p><strong>Current Language: <?php echo $LANG; ?></strong></p>
                        <p><strong>System Language Setting: <?php echo $language_setting; ?></strong></p>
                        <br>
                        <table class="table-responsive w-full rounded">
                            <thead>
                                <tr>
                                    <th class="border w-1/12 px-4 py-2">#</th>
                                    <th class="border w-1/8 px-4 py-2">Module Code</th>
                                    <th class="border w-1/8 px-4 py-2">Module Name</th> 										
                                    <th class="border w-1/10 px-4 py-2">Status</th>  
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                while($module_details = mysqli_fetch_array($select_modules)){
                                    $course_visibility = $module_details['course_visibility'];
                                    $course_id = $module_details['course_id'];
                                ?>
                                <tr>   
                                    <td class="border px-4 py-1"><?php echo $module_details['course_id'];?></td>
                                    <td class="border px-4 py-1">
                                        <a href="Module_topics?COURSE=<?php echo $module_details['course_id'];?>&CERTIFICATE=<?php echo $CERTIFICATE;?>&LANG=<?php echo $LANG;?>">
                                            <?php echo $module_details['course_code'];?> <i class="fas fa-book text-green-500 mx-2"></i>
                                        </a>
                                    </td>
                                    <td class="border px-4 py-2">
                                        <?php 
                                        if($LANG == "English" || $LANG == "ENG"){
                                            echo $module_details['course_name'];
                                        } elseif($LANG == "French" || $LANG == "FR"){
                                            // Use French name if available, fallback to English
                                            echo !empty($module_details['course_french']) ? $module_details['course_french'] : $module_details['course_name'];
                                        } else {
                                            echo $module_details['course_name'];
                                        }
                                        ?>
                                    </td>
                                    <td class="border px-4 py-2">
                                        <?php if($STATUS == "Active"){ ?>
                                            <i class="fas fa-unlock text-green-500 mx-2"></i>
                                        <?php } else { ?>
                                            <i class="fas fa-lock text-red-500 mx-2"></i>
                                        <?php } ?>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
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