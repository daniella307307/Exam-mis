<?php
ob_start();
include('header.php');
//$setting_min_year = 6;

if(isset($_GET['CALL_BACK'])){
	
	$url = $_GET['CALL_BACK'];
}
else{
$url = "Deposite_Amount";	
}
// Calculate the date $setting_min_year years ago from today
$minDate = date('Y-m-d', strtotime("-$setting_min_year years"));
?>

<!DOCTYPE html>
<html>
<head>
    <title>Update User Details</title>
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
                <form action="" method="POST" class="max-w-xl m-4 p-10 bg-white rounded shadow-xl">
        
                  
                  
                  
                    <p class="text-gray-800 font-medium">Add New Bank Sleep<strong> </strong> </p>
                    <?php echo $CALL_BACK;?>

					<?php
					if(isset($_POST['Update'])){
			$bank_id   = $_POST['bank_id'];
			$Account =$_POST['Account'];
			$sleep_no  = $_POST['sleep_no'];
			$sleep_date = $_POST['sleep_date'];
			$sleep_amount_local	= $_POST['sleep_amount_local'];	
			
			
    	$select_bank = mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM banks  
LEFT JOIN countries ON  banks.bank_country =countries.id WHERE bank_id='$bank_id'"));
    	$bank_country  =$select_bank['bank_country'];
    	$bank_region =$select_bank['bank_region'];
    	$unity_usd =$select_bank['currency_usd'];
    	$sleep_amount_usd=$sleep_amount_local/$unity_usd;
    	
	$select_sleep = mysqli_query($conn,"SELECT * FROM  bank_sleeps WHERE sleep_no ='$sleep_no' AND  sleep_country ='$bank_country'");
	$sleep = mysqli_num_rows($select_sleep); 
 
if($sleep >0){
?><div class="bg-red-500 mb-2 border border-red-300 text-white px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">Duplicate Error</strong>
                                    <span class="block sm:inline">This Bank sleep  (<?php echo $sleep_no;?>) Exist!!! <br> Ttry with different Number</span>
                                    <span class="absolute top-0 right-0 px-4 py-3">
                                     <i class="fa fa-t float-right"></i>
									 </span>
                                  </div><?php	
}
else{
	$insert= mysqli_query($conn,"INSERT INTO bank_sleeps (sleep_id, sleep_bank,sleep_bank_acount, sleep_school, sleep_country, sleep_region, sleep_no, sleep_amount_usd, sleep_amount_local, sleep_date, sleep_document, sleep_status) VALUES 
	                                                       (NULL, '$bank_id','$Account', '$school_ref', '$bank_country', '$bank_region', '$sleep_no', '$sleep_amount_usd', '$sleep_amount_local', '$sleep_date', 'document', 'Waiting')");
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
        window.location.href = "<?php echo $url;?>";

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
					
					 <div class="w-full md:w-2/2 px-3 mb-6 md:mb-0">
                            <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="access_level">Bank</label>
                            <div class="relative">
                                 <select name="bank_id"  onChange="getstate(this.value);"  id="country"  class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey" required>
                                     <option value=""> === SELECT BANK ====</option>
                                    <?php
                                    $select_school = mysqli_query($conn, "SELECT * FROM banks WHERE bank_country='$user_country' AND bank_status='Active'");
                                    while ($find_school = mysqli_fetch_array($select_school)) {
                                        echo '<option value="' . $find_school['bank_id'] . '">' . $find_school['bank_name'] .'</option>';
                                    }
                                    ?>
                                </select> 
                            </div>
                        </div>
                        <div class="w-full md:w-2/2 px-3 mb-6 md:mb-0">
                            <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="access_level">Bank Account</label>
                            <div class="relative">
                                 <select name="Account"  id="statelist" onChange="getcity(this.value);" class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey" required>
                               <option value=""> === SELECT ACCOUNT ====</option>
                                    
                                </select> 
                            </div>
                        </div>
					
			 
                    
                    <div class=""> 
                        <label class="block text-sm text-gray-600" for="sleep_no">Deposite Date</label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="sleep_date" name="sleep_date"  type="date"  max="<?php echo DATE("Y-m-d");?>" required >
                    </div>
                     <div class=""> 
                        <label class="block text-sm text-gray-600" for="sleep_no">Bank Sleep No</label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="sleep_no" name="sleep_no"  type="text" required >
                    </div>
					 
                    <div class=""> 
                        <label class="block text-sm text-gray-600" for="sleep_amount_local">Amount</label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="sleep_amount_local" name="sleep_amount_local"  type="number" required >
                    </div>
                    
                  
 

                  <center>  <div class="mt-4">
                        <button type="submit" name="Update" class="px-4 py-1 text-white font-light tracking-wider bg-green-500 rounded">Add Deposit Amount in the system </button>
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
