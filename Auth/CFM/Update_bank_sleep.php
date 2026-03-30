<?php
ob_start(); 
include('header.php');
if(isset($_GET['SCHOOL'])){
	 $SCHOOL = $_GET['SCHOOL'];
	 $SLEEP_No =$_GET['SLEEP_No'];
	 $STATUS= $_GET['STATUS'];
 }
 
 $details_user =mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM bank_sleeps 
LEFT JOIN banks ON bank_sleeps.sleep_bank = banks.bank_id
WHERE sleep_id=$SLEEP_No AND sleep_school='$SCHOOL'")); 
$used = mysqli_num_rows(mysqli_query($conn,"SELECT * FROM  invoice_payments  WHERE  pay_bank_sleep ='$SLEEP_No'"));
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
                <p class="text-gray-800 font-medium">Update &nbsp; Bank sleep No:<strong><?php echo $details_user['sleep_no'];?></strong></p>
      <div class="">
				 
                   <label class="block text-sm text-gray-600" for="cus_email">Bank Name</label>
				   <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="cus_name"  name="firstname" value ="<?php echo $details_user['bank_name'];?>" type="text"  placeholder="First Name" aria-label="Name" readonly>
                </div>
				<div class="">
				 
                   <label class="block text-sm text-gray-600" for="cus_email">Bank Sleep No</label>
				   <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="cus_name"  name="firstname" value ="<?php echo $details_user['sleep_no'];?>" type="text" required="" placeholder="First Name" aria-label="Name" readonly>
                </div>
				 
				 
                
                <div class="w-full md:w-1/1 px-3 mb-6 md:mb-0">
                        
						<label class="block text-sm text-gray-600" for="cus_email">Bank Sleep Status</label>
                        <div class="relative">
                            <select name="sleep_status"
                                class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey"
                                id="grid-state">
								   <option value=""><?php echo $details_user['sleep_status'];?></option>
								<option value="Active">Active</option> 
								<?php
								if($used>0){
									
								}
								else{
								?><option value="Delete">Delete</option> <?php	
								}
								
								?>
								
                            </select>
                             
                        </div>
                    </div>
               
<?php
if(isset($_POST['Update'])){
$sleep_status =$_POST['sleep_status']; 
 if($sleep_status=="Delete"){
$update = mysqli_query($conn,"DELETE FROM bank_sleeps WHERE  sleep_id='$SLEEP_No'");	 
 }
 else{
$update = mysqli_query($conn,"UPDATE bank_sleeps SET sleep_status = '$sleep_status' WHERE  sleep_id='$SLEEP_No'");
 
 }

 if($update){
	header('location:Bank_sleeps_per_school?STATUS='.$STATUS.'&SCHOOL='.$SCHOOL.''); 
?><div class="bg-green-300 mb-2 border border-red-300 text-red-dark px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">Success</strong>
                                    <span class="block sm:inline">Status Updated Successfully</span>
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


?>
                <div class="mt-4">
                    <button type="submit" name ="Update" class="px-4 py-1 text-white font-light tracking-wider bg-green-500 rounded" type="submit">Update User Details</button>
                </div>
                
            </form>
        </div>
    </div>
</div>

</body>
</html>






