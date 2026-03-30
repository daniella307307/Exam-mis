<?php
ob_start();
include('header.php');
//$setting_min_year = 6;

// Calculate the date $setting_min_year years ago from today
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
        
                  
                  
                  
                    <p class="text-gray-800 font-medium">Add New Student :::<?php echo $setting_min_year;?>&nbsp;<strong> </strong> </p>
                     <?php
					if(isset($_POST['Update'])){
    $student_first_name  = $_POST['student_first_name'];
	$student_last_name = $_POST['student_last_name'];
	$student_dob = $_POST['student_dob'];
	$student_gender = $_POST['student_gender'];
	$student_class = $_POST['student_class'];
	$select_class = mysqli_query($conn,"SELECT * FROM school_classes  WHERE class_id ='$student_class'");
	$find_level = mysqli_fetch_array($select_class);
	$student_level =$find_level['class_level'];
	$student_school =$school_ref; 
	$student_contact = $_POST['student_contact'];
	$student_status = $_POST['student_status'];
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
	
	//$student_regno  =$_POST['student_regno'];
	 
	$student_profile =""; //$_files['student_profile'];	
$select_student = mysqli_query($conn,"SELECT * FROM student_list WHERE student_first_name='$student_first_name' AND student_last_name='$student_last_name' AND student_dob='$student_dob'");
$st_count = mysqli_num_rows($select_student);
if($st_count>0){
?><div class="bg-red-500 mb-2 border border-red-300 text-white px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">Duplicate Error</strong>
                                    <span class="block sm:inline">This Student (<?php echo $student_first_name."&nbsp;".$student_last_name;?>) Exist!!! <br> Ttry with different Names</span>
                                    <span class="absolute top-0 right-0 px-4 py-3">
                                     <i class="fa fa-t float-right"></i>
									 </span>
                                  </div><?php	
}
else{
	$insert= mysqli_query($conn,"INSERT INTO student_list(student_id,student_first_name, student_last_name, student_dob, student_gender, student_class, student_level, student_school, student_contact, student_status, student_profile, student_regno) VALUES 
	                                                    (NULL,'$student_first_name','$student_last_name','$student_dob','$student_gender','$student_class','$student_level','$student_school','$student_contact','$student_status','$student_profile','$student_regno')");
if($insert){
?><div class="bg-green-500 mb-2 border border-red-300 text-white px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">Success !!!!</strong>
                                    <span class="block sm:inline">This Student Details (<?php echo $student_first_name."&nbsp;".$student_last_name;?>)  Has been Updated</span>
                                    <span class="absolute top-0 right-0 px-4 py-3">
                                      <i class="fa fa-yes float-right"></i>
									  </span>
                                  </div>
								 <!--<script>window.setTimeout(function(){

        // Move to a new location or you can do something else
        window.location.href = "Students_perschool";

    }, 500);</script> --> 
 <?php
}
else{
?><div class="bg-red-500 mb-2 border border-red-300 text-white px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">Opps !!!!</strong>
                                    <span class="block sm:inline">Internal Server Error</span>
                                    <span class="absolute top-0 right-0 px-4 py-3">
                                      <i class="fa fa-yes float-right"></i>
									  </span>
                                  </div><?php	
} 
}	
					}
			
					
					
					
					?>
					
					
					
				 
                    
                    <div class=""> 
                        <label class="block text-sm text-gray-600" for="firstname">First Name</label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="student_first_name" name="student_first_name"  type="text" required >
                    </div>
					 
                    <div class=""> 
                        <label class="block text-sm text-gray-600" for="firstname">Last Name</label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="student_last_name" name="student_last_name"  type="text" required >
                    </div>
                    
                    <div class="">
                        <label class="block text-sm text-gray-600" for="lastname">DOB</label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="dob" name="student_dob" min="<?php echo $minDate;?>"  value="<?php echo $minDate;?>" type="date"  required>
                    </div> 
					  <div class="w-full md:w-2/2 px-3 mb-6 md:mb-0">
                            <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="access_level">Gender</label>
                            <div class="relative">
                                <select name="student_gender" class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey" id="access_level" required>
                                    <option value="Male">Male</option>
								    <option value="Female">Female</option>
								   
                                </select>
                            </div>
                        </div>
						  <div class="">
                        <label class="block text-sm text-gray-600" for="lastname">Student Contacts</label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="lastname" name="student_contact"  type="text" required>
                    </div> 
                    <div class="w-full md:w-2/2 px-3 mb-6 md:mb-0">
                            <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="access_level">Class</label>
                            <div class="relative">
                                 <select name="student_class" class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey" id="school_ref">
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
                    <div class="w-full md:w-1/3 px-3 mb-6 md:mb-0">
                            <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="status">User Status</label>
                            <div class="relative">
                                <select name="student_status" class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey" id="status">
                                     <option value="Active">Active</option>
                                    
                                </select>
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
