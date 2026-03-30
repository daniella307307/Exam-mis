<?php
ob_start();
include('header.php');
$ID =$_GET['ID'];
// Fetch user details
$details_countries = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM countries WHERE id =$ID"));




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
                    <p class="text-gray-800 font-medium">Update &nbsp;<strong><?php echo $details_countries['Country_name']; ?></strong>&nbsp; Details</p>
                     <?php
					if(isset($_POST['Update'])){
	     $Country_name  = $_POST['Country_name'];
     $Country_currency  = $_POST['Country_currency'];
$Country_currency_code  = $_POST['Country_currency_code'];
          $currency_usd = $_POST['currency_usd'];
		         $Country_status  = $_POST['Country_status'];
                 $Country_region  = $_POST['Country_region'];
 $select_student = mysqli_query($conn,"SELECT * FROM  countries WHERE Country_name='$Country_name' AND id !=$ID");
$st_count = mysqli_num_rows($select_student); 
if($st_count>0){
?><div class="bg-red-500 mb-2 border border-red-300 text-white px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">Duplicate Error</strong>
                                    <span class="block sm:inline">This Country (<?php echo $Country_name ;?>) Existin the system !!! <br> Ttry with different Names</span>
                                    <span class="absolute top-0 right-0 px-4 py-3">
                                     <i class="fa fa-t float-right"></i>
									 </span>
                                  </div><?php	
}
else{
$update = mysqli_query($conn,"UPDATE countries SET 
Country_name = '$Country_name',
Country_currency = '$Country_currency',
Country_currency_code = '$Country_currency_code',
currency_usd = '$currency_usd',
Country_region = '$Country_region', 
Country_status = '$Country_status' WHERE id =$ID");	
?><div class="bg-green-500 mb-2 border border-red-300 text-white px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">Success !!!!</strong>
                                    <span class="block sm:inline"><?php echo $Country_name ;?> Details   Has been Updated</span>
                                    <span class="absolute top-0 right-0 px-4 py-3">
                                      <i class="fa fa-yes float-right"></i>
									  </span>
                                  </div>
							 <script>window.setTimeout(function(){

        // Move to a new location or you can do something else
        window.location.href = "Countries";

    }, 500);</script> 
								  
								  <?php	
}	
					}
			
					
					
					
					?>
					
					
					 
					  <div class=""> 
                        <label class="block text-sm text-gray-600" for="firstname">Country Name</label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="Country_region" name="Country_name" value="<?php echo $details_countries['Country_name']; ?>" type="text"   readonly>
                    </div>
					 
                    <div class=""> 
                        <label class="block text-sm text-gray-600" for="Country_currency">Country Currency</label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="Country_currency" name="Country_currency" value="<?php echo $details_countries['Country_currency']; ?>" type="text" required>
                    </div>
					 <div class=""> 
                        <label class="block text-sm text-gray-600" for="Country_currency_code">Currency Code</label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="Country_currency_code" name="Country_currency_code" value="<?php echo $details_countries['Country_currency_code']; ?>" type="text" required>
                    </div>
					 <div class=""> 
                        <label class="block text-sm text-gray-600" for="currency_usd">Currency to Usd</label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="currency_usd" name="currency_usd" value="<?php echo $details_countries['currency_usd']; ?>" min="0" step="0.000001" type="number" required>
                    </div>
                       <div class="w-full md:w-2/2 px-3 mb-6 md:mb-0">
                            <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="access_level">Region</label>
                            <div class="relative">
                                <select name="Country_region" class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey" id="access_level">
                                <?php
								$select_chool= mysqli_query($conn,"SELECT * FROM regions_table WHERE region_status ='Active'");
								while($find_school = mysqli_fetch_array($select_chool)){
								?><option value="<?php echo $find_school['region_id']; ?>"><?php echo $find_school['region_name']; ?></option><?php	
								}
								?>  
 
								   
                                </select>
                            </div>
                        </div>
					  <div class="w-full md:w-2/2 px-3 mb-6 md:mb-0">
                            <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="access_level">Status</label>
                            <div class="relative">
                                <select name="Country_status" class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey" id="access_level">
                                    <option value="<?php echo $details_countries['Country_status']; ?>"><?php echo$details_countries['Country_status']; ?></option>
								   <option value="Active">Active</option>
								    <option value="Inactive">Inactive</option>
								   
                                </select>
                            </div>
                        </div>
					 
                    
 

                    <div class="mt-4">
                        <button type="submit" name="Update" class="px-4 py-1 text-white font-light tracking-wider bg-green-500 rounded">Update Country Details</button>
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
