<?php
ob_start();
include('header.php');
//$setting_min_year = 6;

// Create upload directories if they don't exist
$profileDir = "uploads/profiles/";
$reportDir = "uploads/reportcards/";

if (!file_exists($profileDir)) {
    mkdir($profileDir, 0777, true);
}
if (!file_exists($reportDir)) {
    mkdir($reportDir, 0777, true);
}

// Calculate the date $setting_min_year years ago from today
$minDate = date('Y-m-d', strtotime("-$setting_min_year years"));
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add New Student</title>
</head>
<body class="h-screen font-sans login bg-cover">

<!--/Header-->
<div class="flex flex-1">
    <!--Sidebar-->
    <?php include('dynamic_side_bar.php'); ?>
    <!--/Sidebar-->
    
    <!--Main-->
    <div class="container mx-auto h-full flex flex-1 justify-center items-center">
        <div class="w-full max-w-lg">
            <div class="leading-loose">
                <form action="" method="POST" enctype="multipart/form-data" class="max-w-xl m-4 p-10 bg-white rounded shadow-xl">
                    <p class="text-gray-800 font-medium">Add New Student <strong> </strong> </p>
                     <?php
					if(isset($_POST['Update'])){
					    
	$student_first_name  = $_POST['student_first_name'];
	$student_midle_name = $_POST['student_midle_name'];
	$student_last_name = $_POST['student_last_name']; // Fixed variable name
	$student_dob = $_POST['student_dob'];
	$student_nationality = $_POST['student_nationality'];
	$student_gender = $_POST['student_gender'];
	$student_class   = $_POST['student_class'];
	
	$select_class = mysqli_query($conn,"SELECT * FROM school_classes
        LEFT JOIN school_levels ON school_classes.class_level = school_levels.level_id 
        LEFT JOIN schools ON school_classes.class_school = schools.school_id  
        WHERE class_id ='$student_class'");
	$find_level = mysqli_fetch_array($select_class);
	$student_level =$find_level['class_level'];
 
	$student_school =$school_ref;
	$student_country =$user_country;
	$student_region =$school_region;
	
	if($parent_gender=="Male"){
        $student_mather = 0;
        $stmather_relation ="";
        $student_father =$session_id;
        $stfather_relation = $_POST['Relationship'];
    } else if($parent_gender=="Female"){
        $student_mather = $session_id;
        $stmather_relation = $_POST['Relationship'];
        $student_father =0;
        $stfather_relation  = "";                                 
    }
	
	$student_status = $_POST['student_status'] ;
	
	// File upload handling
	$student_profile = '';
	$student_reportcard = '';
	
	// Profile picture upload
	if(isset($_FILES['student_profile']) && $_FILES['student_profile']['error'] == UPLOAD_ERR_OK) {
		$profile_tmp = $_FILES['student_profile']['tmp_name'];
		$profile_name = uniqid() . '_' . basename($_FILES['student_profile']['name']);
		$profile_path = $profileDir . $profile_name;
		if(move_uploaded_file($profile_tmp, $profile_path)) {
			$student_profile = $profile_path;
		} else {
            // Handle upload error
            ?><div class="bg-yellow-500 mb-2 border border-yellow-300 text-white px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Profile Upload Failed</strong>
                <span class="block sm:inline">Could not upload profile picture</span>
            </div><?php
        }
	}
	
	// Report card upload
	if(isset($_FILES['student_last_reportcard']) && $_FILES['student_last_reportcard']['error'] == UPLOAD_ERR_OK) {
		$report_tmp = $_FILES['student_last_reportcard']['tmp_name'];
		$report_name = uniqid() . '_' . basename($_FILES['student_last_reportcard']['name']);
		$report_path = $reportDir . $report_name;
		if(move_uploaded_file($report_tmp, $report_path)) {
			$student_reportcard = $report_path;
		} else {
            // Handle upload error
            ?><div class="bg-yellow-500 mb-2 border border-yellow-300 text-white px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Report Card Upload Failed</strong>
                <span class="block sm:inline">Could not upload report card</span>
            </div><?php
        }
	}
	
	$student_promotion = "";
	$internal_regno = 0;
 
	////////////////////////
	$currentYear = date('Y');
    $regPrefix = "BG/$currentYear/";

    // Get the last inserted registration number for the current year
    $result = $conn->query("SELECT student_regno FROM student_list WHERE student_regno LIKE '$regPrefix%' ORDER BY student_regno DESC LIMIT 1");
    $lastRegNo = $result->fetch_assoc();

    if ($lastRegNo) {
        $lastNumber = intval(substr($lastRegNo['student_regno'], strrpos($lastRegNo['student_regno'], '/') + 1));
        $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
    } else {
        $newNumber = '00001';
    }

     $student_regno = $regPrefix . $newNumber;
	///////////////////////
	
	// Generate a random password instead of getting from POST
	$student_password = bin2hex(random_bytes(8)); // Generate secure random password
	
	// Fixed variable name (was $student_last_nam)
	$select_student = mysqli_query($conn,"SELECT * FROM student_list 
        WHERE student_first_name='$student_first_name' 
        AND student_last_name='$student_last_name' 
        AND student_dob='$student_dob'");
        
	$st_count = mysqli_num_rows($select_student);
	if($st_count>0){
	?><div class="bg-red-500 mb-2 border border-red-300 text-white px-4 py-3 rounded relative" role="alert">
        <strong class="font-bold">Duplicate Error</strong>
        <span class="block sm:inline">Student <?php echo $student_first_name." ".$student_last_name;?> already exists!</span>
        <span class="absolute top-0 right-0 px-4 py-3">
            <i class="fa fa-times float-right"></i>
        </span>
    </div><?php	
	} else {
	    // Fixed variable name in INSERT (was $student_last_nam)
	    $insert = mysqli_query($conn,"INSERT INTO student_list 
            (student_id, student_first_name, student_midle_name, student_last_name, student_dob, student_nationality, 
            student_gender, student_class, student_level, student_school, student_country, student_region, 
            student_mather, stmather_relation, student_father, stfather_relation, student_status, 
            student_profile, student_last_reportcard, student_promotion, student_regno, internal_regno, student_password) 
            VALUES 
            (NULL, '$student_first_name', '$student_midle_name', '$student_last_name', '$student_dob', 
            '$student_nationality', '$student_gender', '$student_class', '$student_level', '$student_school', 
            '$student_country', '$student_region', '$student_mather', '$stmather_relation', 
            '$student_father', '$stfather_relation', '$student_status', '$student_profile', 
            '$student_reportcard', '$student_promotion', '$student_regno', '$internal_regno', '$student_password')");
            
        if($insert) {
        ?><div class="bg-green-500 mb-2 border border-green-300 text-white px-4 py-3 rounded relative" role="alert">
            <strong class="font-bold">Success!</strong>
            <span class="block sm:inline">Student <?php echo $student_first_name." ".$student_last_name;?> added successfully</span>
            <span class="absolute top-0 right-0 px-4 py-3">
                <i class="fa fa-check float-right"></i>
            </span>
        </div><?php
        header("Refresh: 2.5; url=Students_perschool");
        } else {
        ?><div class="bg-red-500 mb-2 border border-red-300 text-white px-4 py-3 rounded relative" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline">Database error: <?php echo mysqli_error($conn); ?></span>
            <span class="absolute top-0 right-0 px-4 py-3">
                <i class="fa fa-times float-right"></i>
            </span>
        </div><?php	
        } 
    }	
					}
					?>
                    
                    <!-- Form Fields Remain Unchanged -->
                    <div class=""> 
                        <label class="block text-sm text-gray-600" for="firstname">First Name</label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="student_first_name" name="student_first_name"  type="text" placeholder="Type First Name" required >
                    </div>
					 
					  <div class=""> 
                        <label class="block text-sm text-gray-600" for="student_midle_name">Middle Name</label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="student_midle_name" name="student_midle_name"  type="text"  placeholder="Type Middle Name"   >
                    </div>
					 
					 
                    <div class=""> 
                        <label class="block text-sm text-gray-600" for="student_last_name">Last Name</label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="student_last_name" name="student_last_name"  type="text"  placeholder="Type Last Name" required >
                    </div>
                    
                    <div class=""> 
                        <label class="block text-sm text-gray-600" for="student_nationality">Nationality</label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="student_nationality" name="student_nationality"  placeholder="Type Nationality" type="text" required >
                    </div>
                    
                    <div class="">
                        <label class="block text-sm text-gray-600" for="dob">Date of Birth</label>
                        <input type ="date" class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="dob" name="student_dob"  required>
                    </div> 
                    
                    <!-- Student Profile Picture Upload -->
                    <div class="mt-4">
                        <label class="block text-sm text-gray-600" for="student_profile">Student Profile (PDF)</label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="student_profile" name="student_profile" type="file" accept=".pdf">
                    </div>
                    
                    <!-- Report Card Upload -->
                    <div class="mt-4">
                        <label class="block text-sm text-gray-600" for="student_last_reportcard">Last Report Card (PDF)</label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="student_last_reportcard" name="student_last_reportcard" type="file" accept=".pdf">
                    </div>
                    
                    <div class="mt-4 w-full md:w-2/2 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="student_gender">Gender</label>
                        <div class="relative">
                            <select name="student_gender" class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey" id="student_gender" required>
                                <option value="">SELECT GENDER</option>
                                <option value="Male">Male</option>
							    <option value="Female">Female</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mt-4 w-full md:w-2/2 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="Relationship">Relationship</label>
                        <div class="relative">
                            <select name="Relationship" class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey" id="Relationship" required>
                                <?php 
                                if($parent_gender=="Male"){
                                    ?><option value="Father">Father</option><?php
                                }else if($parent_gender=="Female"){
                                    ?><option value="Mother">Mother</option><?php
                                }
                                ?>
                                <option value="Family Member">Family Member</option> 
                                <option value="NGO Representative">NGO Representative</option>
                                <option value="External Sponsor">External Sponsor</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mt-4 w-full md:w-2/2 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="student_class">Class</label>
                        <div class="relative">
                            <select name="student_class" class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey" id="student_class">
                                <?php
                                $stmt = $conn->prepare("
    SELECT sc.class_id, sc.class_name, sl.level_name 
    FROM school_classes sc
    INNER JOIN school_levels sl ON sc.class_level = sl.level_id
    INNER JOIN schools s ON sc.class_school = s.school_id
    WHERE sc.class_school = ?
");

$stmt->bind_param("i", $school_ref);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    echo '<option value="' . $row['class_id'] . '">'
        . $row['level_name'] . ' - ' . $row['class_name'] .
        '</option>';
}
                               ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mt-4 w-full md:w-1/1 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="student_status">Student Status</label>
                        <div class="relative">
                            <select name="student_status" class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey" id="student_status">
                                <option value="Active">Active</option>
                            </select>
                        </div>
                    </div>
                     
                    <div class="mt-4">
                        <button type="submit" name="Update" class="px-4 py-1 text-white font-light tracking-wider bg-green-500 rounded">Add New Student</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>

<?php
ob_end_flush();
?>