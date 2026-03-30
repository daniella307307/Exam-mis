<?php
ob_start();
 include('header.php');
 $SETTING =$_GET['SETTING'];
 $CLASS  = $_GET['CLASS'];
 $select_class = mysqli_query($conn,"SELECT * FROM school_payment_settings
LEFT JOIN school_levels ON school_payment_settings.spayment_level =school_levels.level_id
LEFT JOIN school_classes ON school_payment_settings.spayment_class=school_classes.class_id
LEFT JOIN schools ON school_payment_settings.spayment_school=schools.school_id
LEFT JOIN countries ON school_payment_settings.spayment_country=countries.id
LEFT JOIN regions_table ON school_payment_settings.spayment_region=regions_table.region_id WHERE spayment_id ='$SETTING'");

$CLASS_DETAILS=mysqli_fetch_array($select_class);

$class_level1 = $CLASS_DETAILS['class_level'];
$class_school1 = $CLASS_DETAILS['class_school'];
$class_country1 = $CLASS_DETAILS['class_country'];
$Country_region1 =$CLASS_DETAILS['Country_region'];
$spayment_term1 =$CLASS_DETAILS['spayment_term'];
$spayment_year1 =$CLASS_DETAILS['spayment_year'];
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
        
                  
                  
                  
                    <p class="text-gray-800 font-medium">Update Fee Structure to :<br><strong><?php echo $CLASS_DETAILS['level_name']."/".$CLASS_DETAILS['class_name'];?></strong>&nbsp; </p>
                     <?php
					if(isset($_POST['Update'])){
    $spayment_description  = $_POST['spayment_description'];
    $spayment_amount  = $_POST['spayment_amount'];
$select_student = mysqli_query($conn,"SELECT * FROM school_payment_settings WHERE  spayment_id!='$SETTING' AND spayment_class ='$CLASS' AND spayment_description='$spayment_description' AND  spayment_term='$setting_term' AND spayment_year='$setting_year' AND spayment_school='$school_ref'");
 $st_count = mysqli_num_rows($select_student);
if($st_count>0){
?><div class="bg-red-500 mb-2 border border-red-300 text-white px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">Duplicate Error</strong>
                                    <span class="block sm:inline">This Description (<?php echo  $spayment_description ;?>) Exist!!! <br> Ttry with different Description</span>
                                    <span class="absolute top-0 right-0 px-4 py-3">
                                     <i class="fa fa-t float-right"></i>
									 </span>
                                  </div><?php	
}
else{
	$insert= mysqli_query($conn,"UPDATE school_payment_settings SET 
	spayment_description = '$spayment_description',
	spayment_amount = '$spayment_amount' WHERE  spayment_id ='$SETTING'");
if($insert){
?><div class="bg-green-500 mb-2 border border-red-300 text-white px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">Success !!!!</strong>
                                    <span class="block sm:inline">This Description (<?php echo  $spayment_description ;?>)  Has been Updated</span>
                                    <span class="absolute top-0 right-0 px-4 py-3">
                                      <i class="fa fa-yes float-right"></i>
									  </span>
                                  </div>
							 <script>window.setTimeout(function(){

        // Move to a new location or you can do something else
        window.location.href = "Payment_Settings_per_class_room?CLASS=<?php echo $CLASS;?>";

    }, 500);</script>  
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
                        <label class="block text-sm text-gray-600" for="firstname">Description</label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="spayment_description" name="spayment_description" Value="<?php echo $CLASS_DETAILS['spayment_description'];?>" type="text" required >
                    </div>
					 
                    <div class=""> 
                        <label class="block text-sm text-gray-600" for="firstname">Amount</label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="spayment_amount" Value="<?php echo $CLASS_DETAILS['spayment_amount'];?>" name="spayment_amount"  type="number" required >
                    </div>
                   
                     
                     
 

                    <div class="mt-4">
                        <button type="submit" name="Update" class="px-4 py-1 text-white font-light tracking-wider bg-green-500 rounded">Update Fee Structure</button>
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
