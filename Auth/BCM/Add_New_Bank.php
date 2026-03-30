<?php
//ob_start();
include('header.php');
//$setting_min_year = 6;

if(isset($_GET['SLEEP_No'])){
    
 $SLEEP_No = $_GET['SLEEP_No']; 
 $CALL_BACK = $_GET['CALL_BACK']."?COUNTRY=".$COUNTRY;
	
//	$url =  $CALL_BACK;
	
$select_country = mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM countries
LEFT join regions_table ON countries.Country_region = regions_table.region_id  WHERE id='$COUNTRY'"));
$Country_name   =$select_country['Country_name'];
$Country_region =$select_country['Country_region'];
?>


<!DOCTYPE html>
<html>
<head>
    <title>Add New Bank</title>
</head>
<body class="h-screen font-sans login bg-cover">
 <script>
function getstate(val) {
	//alert(val);
	$.ajax({
	type: "POST",
	url: "get_acount.php",
	data:'coutrycode='+val,
	success: function(data){
		$("#statelist").html(data);
	}
	});
}

function getcity(val) {
	//alert(val);
	$.ajax({
	type: "POST",
	url: "get_city.php",
	data:'statecode='+val,
	success: function(data){
		$("#city").html(data);
	}
	});
}

</script>	
<!--/Header-->
<div class="flex flex-1">
    <!--Sidebar-->
    <?php include('dynamic_side_bar.php'); ?>
    <!--/Sidebar-->
    
    <!--Main-->
    <div class="container mx-auto h-full flex flex-1 justify-center items-center">
        <div class="w-full max-w-lg">
            <div class="leading-loose">
                
                <div class="bg-gray-200 px-2 py-3 border-solid border-gray-200 border-b">
                        
                        <a href="Banks_in_Countries?STATUS=Active&COUNTRY=<?php echo $COUNTRY; ?>"><button class='bg-green-500 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded'> <i class="fas fa-school"></i> &nbsp; &nbsp; Go Back  to bank List </button></a>
                        
                          </div>
                <form action="" method="POST" class="max-w-xl m-4 p-10 bg-white rounded shadow-xl">
        
                  
                  
                  
                    <p class="text-gray-800 font-medium">Add New Bank in <?php echo $Country_name;?><strong> </strong> </p>
                    

					<?php
					if(isset($_POST['Update'])){
		 	$bank_name  =$_POST['bank_name'];
			$bank_country  =$_POST['bank_country'];
			$bank_region  =$Country_region;
			$bank_status  =$_POST['bank_status'];
			
    	$select_bank =  mysqli_query($conn,"SELECT * FROM banks  
LEFT JOIN countries ON  banks.bank_country =countries.id WHERE bank_name='$bank_name' AND bank_country='$bank_country'");
   $banks  = mysqli_num_rows($select_bank); 
 
if($banks >0){
?><div class="bg-red-500 mb-2 border border-red-300 text-white px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">Duplicate Error</strong>
                                    <span class="block sm:inline">This Bank  (<?php echo $bank_name;?>) Exist!!! <br> Ttry with different Name</span>
                                    <span class="absolute top-0 right-0 px-4 py-3">
                                     <i class="fa fa-t float-right"></i>
									 </span>
                                  </div><?php	
}
else{
	$insert= mysqli_query($conn,"INSERT INTO banks (bank_id,bank_name,bank_country,bank_region,bank_status) VALUES 
	                                               (NULL, '$bank_name', '$bank_country', '$bank_region', '$bank_status')");
if($insert){
?><div class="bg-green-500 mb-2 border border-red-300 text-white px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">Success !!!!</strong>
                                    <span class="block sm:inline">This Banksleep   Has been Added</span>
                                    <span class="absolute top-0 right-0 px-4 py-3">
                                      <i class="fa fa-yes float-right"></i>
									  </span>
                                  </div>
							 <script>window.setTimeout(function(){

        // Move to a new location or you can do something else
        window.location.href = "<?php echo  $CALL_BACK;?>";

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
                        <label class="block text-sm text-gray-600" for="sleep_no">Bank  Name</label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="sleep_no" name="bank_name"  type="text" placeholder="Bank  Name" required >
                    </div>
					 <div class="w-full md:w-2/2 px-3 mb-6 md:mb-0">
                            <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="access_level">Country Name</label>
                            <div class="relative">
                                 <select name="bank_country"    class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey" required>
                                     
                                    <?php
                                    $select_school = mysqli_query($conn, "SELECT * FROM countries WHERE id='$COUNTRY'  AND  Country_status='Active'");
                                    while ($find_school = mysqli_fetch_array($select_school)) {
                                        echo '<option value="' . $find_school['id'] . '">' . $find_school['Country_name'] .'</option>';
                                    }
                                    ?>
                                </select> 
                            </div>
                        </div>
                        <div class="w-full md:w-2/2 px-3 mb-6 md:mb-0">
                            <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="access_level">Status</label>
                            <div class="relative">
                                 <select name="bank_status"    class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey" required>
                                 <option value="Active">Active</option>
                                 <option value="Inactive">Inactive</option>
                                    
                                </select> 
                            </div>
                        </div>
                      
                  <center>  <div class="mt-4">
                        <button type="submit" name="Update" class="px-4 py-1 text-white font-light tracking-wider bg-green-500 rounded">Add  Bank in <?php echo $Country_name ;?></button>
                    </div> </center>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="//ajax.googleapis.com/ajax/libs/jquery/2.0.2/jquery.min.js"></script>
		<script src="js/bootstrap.min.js"></script>
</body>
</html>

<?php
ob_end_flush();
?>


<?php	
	
	
	
	
}
else{
$url = "Banks_Accounts";
?>
	 <script>window.setTimeout(function(){

        // Move to a new location or you can do something else
        window.location.href = "<?php echo $url;?>";

    }, 500);</script>
<?php
}



?>

