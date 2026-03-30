<?php
ob_start();
include('header.php');
$COUNTRY =$_GET['COUNTRY'];
$select_user=mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM countries   WHERE id='$COUNTRY'"));
$Country_flag=$select_user['Country_flag']; 
$Country_name =$select_user['Country_name']; 

?>

<!DOCTYPE html>
<html>
<head>
    <title>upload Flag</title>
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
                    <p class="text-gray-800 font-medium">Uplod Flag  on <strong> <big><big><?php echo $Country_name; ?></big></big> </strong></p>
                   <center> <img class="inline-block h-12 w-12 rounded-full" src="../<?php echo $Country_flag;?>" alt=""></center>
                    
                    <?php
                    if (isset($_POST['Update'])) {
                        $filename = basename($_FILES["uploadfile"]["name"]);
                        $tempname = $_FILES["uploadfile"]["tmp_name"];
                        $folder ="Country/" . $filename; // Use absolute path
						 // Check if file was uploaded without errors
						 if(file_exists("../".$user_image)){
							if(unlink("../".$user_image)){
							//echo $folder."Deleted!!";	
							}; 
						 }
                        if ($_FILES["uploadfile"]["error"] == UPLOAD_ERR_OK) {
                            if (move_uploaded_file($tempname, "../".$folder)) {
                                 
						 $Update=mysqli_query($conn,"UPDATE countries SET Country_flag  = '$folder' WHERE id ='$COUNTRY'");		
                            if($Update){
							?> <div class="bg-green-500 mb-2 border border-red-300 text-white px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">Success !!!!</strong>
                                    <span class="block sm:inline">Flag Has been uploaded</span>
                                    <span class="absolute top-0 right-0 px-4 py-3">
                                      <i class="fa fa-yes float-right"></i>
									  </span>
                                  </div>
							 	
								  
								 
							<?php
							header('location:Update_Country_details?CURRENT='.$CURRENT.'&STATUS='.$STATUS.'&COUNTRY='.$COUNTRY.'');
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
                        <label class="block text-sm text-gray-600" for="student_profile">Your country Flag </label>
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
