<?php
ob_start(); 
include('header.php');
 
 $BANK = $_GET['BANK'];
 $select_bank = mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM banks WHERE bank_id='$BANK'"));
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
                <p class="text-gray-800 font-medium"><strong>Add New Acount Details in<br> <?php echo  $select_bank['bank_name'];?>  &nbsp;</strong></p>
    
				 
				<div class="">
				 
                   <label class="block text-sm text-gray-600" for="acount_number">Account Number</label>
				   <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="acount_number"  name="acount_number"   type="text" required  placeholder="Account Number" aria-label="Name">
                </div>
			 	<div class="">
				 
                   <label class="block text-sm text-gray-600" for="acount_status">Status</label>
				   <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="acount_status"  name="acount_status"   type="text" value ="Active" required  placeholder="Status" aria-label="Status" readonly>
                </div>
			 
                
				 
                  
                  
					 
					 
<?php
if(isset($_POST['Add_school'])){
$acount_bank  =  mysqli_real_escape_string($conn,$BANK);  
$acount_number  =  mysqli_real_escape_string($conn,$_POST['acount_number']);  
$acount_status  =  mysqli_real_escape_string($conn,$_POST['acount_status']);  
 


 
$count = mysqli_num_rows(mysqli_query($conn,"SELECT * FROM bank_account WHERE acount_bank='$acount_bank' AND acount_number='$acount_number'")); 

if($count>0){
?><div class="bg-red-500 mb-2 border border-red-300 text-red-dark px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">Oops!</strong>
                                    <span class="block sm:inline">This Account <?php echo $acount_number;?> Exist</span>
                                    <span class="absolute top-0 top-0 right-0 px-4 py-3">
                                      <svg class="fill-current h-6 w-6 text-red" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                                    </span>
                                </div><?php	
}
else{
$insert = mysqli_query($conn,"INSERT INTO bank_account(acount_id,acount_bank,acount_number,acount_status) VALUES 
                                                      (NULL, '$acount_bank', '$acount_number', '$acount_status')");
 if($insert){
	header('location:Bank_account_numbers?BANK='. $BANK.''); 
?><div class="bg-green-300 mb-2 border border-red-300 text-red-dark px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">Success !!!!!</strong>
                                    <span class="block sm:inline">Redirecting to the main Page.</span>
                                    <span class="absolute top-0 top-0 right-0 px-4 py-3">
                                      <svg class="fill-current h-6 w-6 text-red" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                                    </span>
                                </div><?php 	
	
 }
 else{
	?><div class="bg-red-500 mb-2 border border-red-300 text-red-dark px-4 py-3 rounded relative" role="alert">
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
                <div class="mt-4">
                   <center> <button type="submit" name ="Add_school" class="px-4 py-1 text-white font-light tracking-wider bg-green-500 rounded" type="submit">Add New Account</button></center>
                </div>
                
            </form>
        </div>
    </div>
</div>

</body>
</html>






