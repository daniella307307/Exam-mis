<?php
ob_start();
include('header.php');

$ID =$_GET['ID'];
$STATUS = $_GET['STATUS']; 
$details_student = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM students_promotion WHERE promotion_id=$ID"));
$promotion_name=$details_student['promotion_name']; 
?>
  
<!DOCTYPE html>
<html>
<head>
    <title>Update Promotion Name</title>
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
                    <p class="text-gray-800 font-medium">Update Promotion Name </p>
                     
                    <?php
                    if (isset($_POST['Update'])) {
                        $promotion_name = $_POST["promotion_name"];
                       
                                 
						 $Update=mysqli_query($conn,"UPDATE students_promotion SET promotion_name= '$promotion_name' WHERE promotion_id=$ID");		
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
        window.location.href = "Current_Courses?ID=<?php echo $ID;?>&STATUS=<?php echo $STATUS;?>";

    }, 500);</script>  
								  
								 
							<?php	
							} 
                         
                    }
                    ?>

                    <div class="">
                        <label class="block text-sm text-gray-600" for="student_profile">Promotion Name</label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" type="text" name="promotion_name"  value="<?php echo $promotion_name;?>" required />
                    </div> 

                    <div class="mt-4">
                        <button type="submit" name="Update" class="px-4 py-1 text-white font-light tracking-wider bg-green-500 rounded">Update Details</button>
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
