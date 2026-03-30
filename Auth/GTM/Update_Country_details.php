<?php
ob_start(); 
include('header.php');
$STATUS=$_GET['STATUS'];
$CURRENT=$_GET['CURRENT'];
$ID = $_GET['COUNTRY']; 


 
 
 

 $details_country =mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM  countries 
LEFT JOIN regions_table ON countries.Country_region = regions_table.region_id WHERE id=$ID"));
 
?>
  	   <!--/Header-->

        <div class="flex flex-1">
            <!--Sidebar-->
            <?php include('dynamic_side_bar.php');?>
            <!--/Sidebar-->
            <!--Main-->
<body class="h-screen font-sans login bg-cover">
<div class="container mx-auto h-full flex flex-1 justify-center items-center">
    <div class="w-full max-w-lg">
        <div class="leading-loose">
            <form  action ="" method= "POST" class="max-w-xl m-4 p-10 bg-white rounded shadow-xl">
                <p class="text-gray-800 font-medium">UPDATE Country Details &nbsp;<strong> </strong> </p>
       		<center><a href="Upload_country_flag?COUNTRY=<?php echo $ID;?>"> <img onclick="profileToggle()" class="inline-block h-10 w-10 rounded-full" src="../<?php echo $details_country['Country_flag'];?>" alt=""></a></center> 
                    
			<?php
if(isset($_POST['Update'])){
 
$Country_name = $_POST['Country_name'];
 $Country_currency  =$_POST['Country_currency'];
 $Country_currency_code  =$_POST['Country_currency_code'];
 $currency_usd  =$_POST['currency_usd'];
 $Country_region  =$_POST['Country_region'];
 $Country_status  =$_POST['Country_status'];


$select =mysqli_num_rows(mysqli_query($conn,"SELECT * FROM countries WHERE Country_name='$Country_name' AND id !='$ID'")); 
if($select>0){
?><div class="bg-green-300 mb-2 border border-red-300 text-red-dark px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">Oops !!!</strong>
                                    <span class="block sm:inline">Topic Exist !!!.</span>
                                    <span class="absolute top-0 top-0 right-0 px-4 py-3">
                                      <svg class="fill-current h-6 w-6 text-red" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                                    </span>
                                </div><?php 	
	
}
else{
$Add_data = mysqli_query($conn,"UPDATE countries SET
Country_name='$Country_name',
Country_currency = '$Country_currency',
Country_currency_code = '$Country_currency_code',
currency_usd = '$currency_usd',
Country_region = '$Country_region',
Country_status = '$Country_status' WHERE id ='$ID'");
 if($Add_data){
	header('location:Countries?STATUS='.$STATUS.''); 
?><div class="bg-green-300 mb-2 border border-red-300 text-red-dark px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">Topic Added!</strong>
                                    <span class="block sm:inline">Redirecting to the main page !!!.</span>
                                    <span class="absolute top-0 top-0 right-0 px-4 py-3">
                                      <svg class="fill-current h-6 w-6 text-red" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                                    </span>
                                </div><?php 	
	
 }
 else{
	?><div class="bg-red-300 mb-2 border border-red-300 text-red-dark px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">Oops!</strong>
                                    <span class="block sm:inline">Something went wrong Try again !!!.</span>
                                    <span class="absolute top-0 top-0 right-0 px-4 py-3">
                                      <svg class="fill-current h-6 w-6 text-red" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                                    </span>
                                </div><?php 
 }
}
}


?>
    
				<div class="">
				 
                   <label class="block text-sm text-gray-600" for="cus_email">Country Name</label>
				   <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="Country_name"  name="Country_name" value ="<?php echo $details_country['Country_name'];?>" type="text"  placeholder="Country Name" aria-label="Name" required readonly>
                </div>
				<div class="">
				 
                   <label class="block text-sm text-gray-600" for="cus_email">Currency Name</label>
				   <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="Country_currency"  name="Country_currency" value ="<?php echo $details_country['Country_currency'];?>" type="text"  placeholder="Currency Name" aria-label="Name" required >
                </div>
				<div class="">
				
                   <label class="block text-sm text-gray-600" for="cus_email">Currency Code</label>
				   <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="Country_currency_code"  name="Country_currency_code" value ="<?php echo $details_country['Country_currency_code'];?>" type="text"  placeholder="Topic Details" aria-label="Name" required>
                </div>
				<div class="">
				 
                   <label class="block text-sm text-gray-600" for="currency_usd">Local to USD</label>
				   <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="currency_usd"  name="currency_usd" value ="<?php echo $details_country['currency_usd'];?>" type="number" min="0" step="any"  placeholder="1 Unity curreny equal to USD" aria-label="Name" required >
                </div>
				 
				 
                
                
                 <div class="flex flex-wrap -mx-3 mb-2">
                    
					 <div class="w-full md:w-1/2 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="grid-state">
                           Region
                        </label>
                        <div class="relative">
                            <select name="Country_region"
                                class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey"
                                id="grid-state">
								<option value="<?php echo $details_country['region_id'];?>"><?php echo $details_country['region_name']; ?>
                               <?php
								$select_role= mysqli_query($conn,"SELECT * FROM regions_table WHERE 1");
								while($find_role = mysqli_fetch_array($select_role)){
								?><option value="<?php echo $find_role['region_id']; ?>"><?php echo $find_role['region_name']; ?></option><?php	
								}    
								?>
                            </select>
                             
                        </div>
                    </div>
					 
                    <div class="w-full md:w-1/2 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="grid-state">
                          Status
                        </label>
                        <div class="relative">
                            <select name="Country_status"
                                class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey"
                                id="grid-state">
								 <option value="<?php echo $details_country['Country_status'];?>"><?php echo $details_country['Country_status'];?></option>
                                <option value="Active">Active</option>
								<option value="Inactive">Inactive</option>  
                            </select>
                             
                        </div>
                    </div>
                    
                </div>
            <div class="mt-4">
                    <center><button type="submit" name ="Update" class="px-4 py-1 text-white font-light tracking-wider bg-green-500 rounded" type="submit">Update Topic  Details</button></center>
                </div>
                
            </form>
        </div>
    </div>
</div>

</body>
</html>






