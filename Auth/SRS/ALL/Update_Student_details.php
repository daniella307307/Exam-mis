<?php
ob_start();
include('header.php');
$ID =$_GET['ID'];
// Fetch user details
$details_user = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM student_list 
LEFT JOIN schools ON student_list.student_school = schools.school_id
LEFT JOIN school_classes ON student_list.student_class = school_classes.class_id
LEFT JOIN school_levels ON student_list.student_level = school_levels.level_id 
WHERE student_id =$ID"));

$minDate = date('Y-m-d', strtotime("-$setting_min_year years"));



?>

<!DOCTYPE html>
<html>
<head>
    <title>Update User Details</title>
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
                <form action="" method="POST" class="max-w-xl m-4 p-10 bg-white rounded shadow-xl">
                    <p class="text-gray-800 font-medium">Update &nbsp;<strong><?php echo $details_user['student_first_name']."&nbsp;".$details_user['student_last_name']; ?></strong>&nbsp; Details</p>
                   <a href="Upload_student_profile?ID=<?php echo $ID;?>"> <img class="inline-block h-12 w-12 rounded-full" src="../<?php echo $details_user['student_profile'];?>" alt=""></a>
                    
					<?php

 

$ID = intval($_GET['ID']); // Safer
// Fetch user details
$details_user = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM student_list 
LEFT JOIN schools ON student_list.student_school = schools.school_id
LEFT JOIN school_classes ON student_list.student_class = school_classes.class_id
LEFT JOIN school_levels ON student_list.student_level = school_levels.level_id 
WHERE student_id = $ID"));

$minDate = date('Y-m-d', strtotime("-$setting_min_year years"));

// Default values if not defined
$school_ref = $details_user['student_school'] ?? '';
$user_country = $details_user['student_country'] ?? '';
$user_region = $details_user['student_region'] ?? '';
$student_promotion = $details_user['student_promotion'] ?? '';

if (isset($_POST['Update'])) {
    $student_first_name = $_POST['student_first_name'];
    $student_last_name = $_POST['student_last_name'];
    $student_dob = $_POST['student_dob'];
    $student_gender = $_POST['student_gender'];
    $class_ref = $_POST['class_ref'];
    $student_status = $_POST['student_status'];
    $password = $_POST['Reset_password'];

    // Get level from class
    $select_class = mysqli_query($conn, "SELECT * FROM school_classes WHERE class_id = '$class_ref'");
    $find_level = mysqli_fetch_array($select_class);
    $student_level = $find_level['class_level'];
    $student_class = $class_ref;

    $encripted = empty($password) ? $details_user['student_password'] : md5($password);

    // Check for duplicate
    $select_student = mysqli_query($conn, "SELECT * FROM student_list WHERE student_first_name='$student_first_name' AND student_last_name='$student_last_name' AND student_dob='$student_dob' AND student_id != $ID");
    if (mysqli_num_rows($select_student) > 0) {
        echo "<div class='bg-red-500 mb-2 text-white px-4 py-3 rounded relative'><strong class='font-bold'>Duplicate Error</strong><span class='block sm:inline'>This Student ($student_first_name $student_last_name) already exists! Try different names.</span></div>";
    } else {
        $Update = mysqli_query($conn, "UPDATE student_list SET 
            student_first_name = '$student_first_name', 
            student_last_name = '$student_last_name',
            student_dob = '$student_dob',
            student_gender = '$student_gender',
            student_class = '$student_class',
            student_level = '$student_level',
            student_school = '$school_ref',
            student_country = '$user_country',
            student_region = '$user_region',
            student_status = '$student_status',
            student_promotion = '$student_promotion', 
            student_password = '$encripted'  
            WHERE student_id = $ID");

        echo "<div class='bg-green-500 mb-2 text-white px-4 py-3 rounded relative'><strong class='font-bold'>Success!</strong><br><span class='block sm:inline'>Student details ($student_first_name $student_last_name)<br> have been updated.</span></div>";
    }
}
?>

					
                    <div class="">
                        <label class="block text-sm text-gray-600" for="user_id">ID</label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="user_id" name="user_id" value="<?php echo $details_user['student_id']; ?>" type="text" required="" readonly>
                    </div>
                          
 
  
                    <div class=""> 
                        <label class="block text-sm text-gray-600" for="firstname">First Name</label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="student_first_name" name="student_first_name" value="<?php echo $details_user['student_first_name']; ?>" type="text" required>
                    </div>
					 
                    <div class=""> 
                        <label class="block text-sm text-gray-600" for="firstname">Last Name</label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="student_last_name" name="student_last_name" value="<?php echo $details_user['student_last_name']; ?>" type="text" required>
                    </div>
                    
                    <div class="">
                        <label class="block text-sm text-gray-600" for="lastname">DOB</label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="lastname" name="student_dob"  value="<?php echo $details_user['student_dob']; ?>" type="date"  required>
                    </div> 
					  <div class="w-full md:w-2/2 px-3 mb-6 md:mb-0">
                            <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="access_level">Gender</label>
                            <div class="relative">
                                <select name="student_gender" class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey" id="access_level">
                                    <option value="<?php echo $details_user['student_gender']; ?>"><?php echo $details_user['student_gender']; ?></option>
								   <option value="Male">Male</option>
								    <option value="Female">Female</option>
								   
                                </select>
                            </div>
                        </div> 
					  
                   
                    <div class="w-full md:w-1/1 px-3 mb-6 md:mb-0">
                            <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="access_level">Class</label>
                            <div class="relative">
                                 <select name="class_ref" class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey" id="school_ref">
                                    <option value="<?php echo $details_user['class_id']; ?>"><?php echo $details_user['level_name']."@".$details_user['class_name'];; ?></option>
                                   

								   <?php
                                    $select_school = mysqli_query($conn, "SELECT * FROM school_class_rooms
LEFT JOIN school_levels ON school_class_rooms.room_level = school_levels.level_id
LEFT JOIN school_classes ON school_class_rooms.room_class = school_classes.class_id
LEFT JOIN schools ON school_class_rooms.room_school = schools.school_id
WHERE room_school='$school_ref'");
                                    while ($find_school = mysqli_fetch_array($select_school)) {
                                        echo '<option value="' . $find_school['class_id'] . '">' . $find_school['level_name'] .'@'.$find_school['class_name'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                         <div class="">
                        <label class="block text-sm text-gray-600" for="lastname">Student Password</label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="lastname" name="Reset_password"  value="" type="password">
                    </div>
                       <div class="flex flex-wrap -mx-3 mb-2">
                    <div class="w-full md:w-1/3 px-3 mb-6 md:mb-0">
                            <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="status">Student Status</label>
                            <div class="relative">
                                <select name="student_status" class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey" id="status">
                                    <option value="<?php echo $details_user['student_status']; ?>"><?php echo $details_user['student_status']; ?></option>
                                     
                                </select>
                            </div>
                        </div>
                        
  </div>

                    <div class="mt-4">
                        <button type="submit" name="Update" class="px-4 py-1 text-white font-light tracking-wider bg-green-500 rounded">Update User Details</button>
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
