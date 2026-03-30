<?php
ob_start();
include('header.php');
 
$details_student = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM student_list 
LEFT JOIN schools ON student_list.student_school = schools.school_id
LEFT JOIN school_classes ON student_list.student_class = school_classes.class_id
LEFT JOIN school_levels ON student_list.student_level = school_levels.level_id 
WHERE student_id =$session_id"));
$student_regno=str_replace("/","",$details_student['student_regno']);
$student_profile = $details_student['student_profile'];
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
                <form action="" method="POST" enctype="multipart/form-data" class="max-w-xl m-4 p-10 bg-white rounded shadow-xl">
                    <p class="text-gray-800 font-medium">Update Your Profile Picture &nbsp;<strong> </strong>&nbsp; Details</p>
                    <img class="inline-block h-12 w-12 rounded-full" src="../<?php echo $details_student['student_profile'];?>" alt="">
                    
                    <?php
                    if (isset($_POST['Update'])) {
                        $filename = basename($_FILES["uploadfile"]["name"]);
                        $tempname = $_FILES["uploadfile"]["tmp_name"];
						$fileExtension = pathinfo($filename, PATHINFO_EXTENSION);
						$newFileName =  $student_regno. '.' . $fileExtension;
                        $folder ="Student_profiles/".$newFileName; // Use absolute path
						 // Check if file was uploaded without errors
						 if(file_exists("../".$student_profile)){
							if(unlink("../".$student_profile)){
							//echo $folder."Deleted!!";	
							}; 
						 }
                        if ($_FILES["uploadfile"]["error"] == UPLOAD_ERR_OK) {
                            if (move_uploaded_file($tempname, "../".$folder)) {
                                 
						 $Update=mysqli_query($conn,"UPDATE student_list  SET student_profile  = '$folder' WHERE  student_id =$session_id");		
                            if($Update){
							?> <div class="bg-green-500 mb-2 border border-red-300 text-white px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">Success !!!!</strong>
                                    <span class="block sm:inline">Profile picture Has been Updated</span>
                                    <span class="absolute top-0 right-0 px-4 py-3">
                                      <i class="fa fa-yes float-right"></i>
									  </span>
                                  </div>
								 <script>window.setTimeout(function(){

        // Move to a new location or you can do something else
        window.location.href = "Your_Profile";

    }, 500);</script>  
								  
								 
							<?php	
							}
							else{
								
							}
							} else {
                                echo "<h3>Failed to upload image!</h3>";
                            }
                        }  
                    }
                    ?>

                    <div class="">
                        <label class="block text-sm text-gray-600" for="student_profile">Your Profile Picture </label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" type="file" name="uploadfile" value="" required />
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
