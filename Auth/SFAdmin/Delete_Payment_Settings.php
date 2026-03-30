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

$find_ref = mysqli_query($conn,"SELECT * FROM school_invoice WHERE invc_ref='$SETTING'");
$ref_count = mysqli_num_rows($find_ref);

 ?>
 
<?php

if($ref_count>0){
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
        
                                         <div class="p-4 mb-4 text-red-700 bg-red-100 rounded-lg border border-red-300">
    <strong>Warning:</strong>Deleting this item will permanently remove all connected student invoices 
    <br> (affecting multiple students).<br> in :<strong><?php echo $CLASS_DETAILS['level_name']."/".$CLASS_DETAILS['class_name'];?></strong>
</div> 
        
                    <?php
					if(isset($_POST['Update'])){
					    
					    $Decision = $_POST['Decision'];
					    
					   if($Decision=="Yes"){
					       
 $delete = mysqli_query($conn,"DELETE FROM school_payment_settings WHERE spayment_id ='$SETTING'");  
  if($delete){
      $delete_everything = mysqli_query($conn,"DELETE FROM school_invoice WHERE  invc_ref ='$SETTING'");  
    
      ?>
      <script>window.setTimeout(function(){

        // Move to a new location or you can do something else
        window.location.href = "Payment_Settings_per_class_room?CLASS=<?php echo $CLASS;?>";

    }, 120);</script>
      <?php   
     
  }     
					       
					       
					   }else{
					     ?><div class="bg-green-500 mb-2 border border-red-300 text-white px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">Action Aborted (No Data affected)</strong>
                                    <span class="block sm:inline">Redirecting to the main Page</span>
                                    <span class="absolute top-0 right-0 px-4 py-3">
                                     <i class="fa fa-t float-right"></i>
									 </span>
                                  </div>
                                   <script>window.setTimeout(function(){

        // Move to a new location or you can do something else
        window.location.href = "Payment_Settings_per_class_room?CLASS=<?php echo $CLASS;?>";

    },1000);</script>
                                  <?php  
					   }
					    
 	
 
 				}
			
					
					
					
					?>
				 
                                  

                                  
                                  
                                  
					<div class="w-full md:w-1/1 px-3 mb-6 md:mb-0">
                            <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="status">Are you Sure You want to Procede this Action??</label>
                            <div class="relative">
                                <select name="Decision" class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey" id="status" required>
                                    <option value="">======== SELECT YOUR CHOICE ===========</option>
                                    <option value="No">No</option>
                                    <option value="Yes">Yes</option>
                                    
                                     
                                </select>
                            </div>
                        </div>
				 
                    
                    
					 
                   
                     
                     
 

                    <div class="mt-4">
                        <button type="submit" name="Update" class="px-4 py-1 text-white font-light tracking-wider bg-red-500 rounded">Delete Fee Structure and Connected Invoices</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>


<?php    
}
else{
  $delete = mysqli_query($conn,"DELETE FROM school_payment_settings WHERE spayment_id ='$SETTING'");  
  if($delete){
    
      ?>
      <script>window.setTimeout(function(){

        // Move to a new location or you can do something else
        window.location.href = "Payment_Settings_per_class_room?CLASS=<?php echo $CLASS;?>";

    }, 120);</script>
      <?php   
     
  }
}


?>


<?php
ob_end_flush();
?>
