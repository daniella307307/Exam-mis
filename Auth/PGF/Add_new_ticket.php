<?php
ob_start(); 
include('header.php');
 
 
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
                <p class="text-gray-800 font-medium">Add New Ticket  &nbsp;<strong> </strong> </p>
  
                    
			<?php
if(isset($_POST['Update'])){
 
$received_number =$_POST['received_number'];
$received_type =$_POST['received_type'];
$Find_amount = mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM tickets_types WHERE ticket_id='$received_type'"));
$ticket_type =$Find_amount['ticket_type'];
$ticket_price =$Find_amount['ticket_price'];
$received_amount =$ticket_price;
$received_user =$session_id;
$received_status ="Generated";
$received_aprouved_by ="";

$select =mysqli_num_rows(mysqli_query($conn,"SELECT * FROM ticket_received WHERE received_number='$received_number'")); 
if($select>0){
?><div class="bg-green-300 mb-2 border border-red-300 text-red-dark px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">Oops !!!</strong>
                                    <span class="block sm:inline">Ticket number  Exist !!!.</span>
                                    <span class="absolute top-0 top-0 right-0 px-4 py-3">
                                      
                                    </span>
                                </div><?php 	
	
}
else{
$Add_data = mysqli_query($conn,"INSERT INTO ticket_received 
(received_id,received_school, received_number, received_amount, received_type, received_user, received_date, received_status, received_aprouved_by) VALUES (NULL,'$school_ref', '$received_number', '$received_amount', '$received_type', '$received_user', current_timestamp(), '$received_status', '')");
 if($Add_data){
	header('location:playground_tickets'); 
?><div class="bg-green-300 mb-2 border border-red-300 text-red-dark px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">Ticket  INserted!</strong>
                                    <span class="block sm:inline">Redirecting to the main page !!!.</span>
                                    <span class="absolute top-0 top-0 right-0 px-4 py-3">
                                      
                                    </span>
                                </div><?php 
 
 }
 else{
	?><div class="bg-red-300 mb-2 border border-red-300 text-red-dark px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">Oops!</strong>
                                    <span class="block sm:inline">Something went wrong Try again !!!.</span>
                                    <span class="absolute top-0 top-0 right-0 px-4 py-3">
                                      
                                    </span>
                                </div><?php 
 }
}
}


?>
    <div class="w-full md:w-1/1 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="grid-state">
                          Ticket Type
                        </label>
                        <div class="relative">
                            <select name="received_type"
                                class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey"
                                id="grid-state">
 <option value="">=======  Select Ticket Type =========</option>
 <?php
								$select_category= mysqli_query($conn,"SELECT * FROM tickets_types WHERE ticket_school='$school_ref'");
								while($find_category = mysqli_fetch_array($select_category)){
								?><option value="<?php echo $find_category['ticket_id']; ?>"><?php echo $find_category['ticket_type']."&nbsp;@".$find_category['ticket_price']; ?>Ksh</option><?php	
								}    
								?>
                            </select>
  
 
                            </select>
                             
                        </div>
                    </div>
				<div class="">
				 
                   <label class="block text-sm text-gray-600" for="cus_email">Ticket No</label>
				   <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="received_number"  name="received_number"   type="text"  placeholder="Ticket number" aria-label="Name" required >
                </div>
				 
					 
					<!--<div class="w-full md:w-1/1 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="grid-state">
                          Status
                        </label>
                        <div class="relative">
                            <select name="equipments_status"
                                class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey"
                                id="grid-state">
 <option value="">============= Select Status ==============</option>
  <option value="Active">Active</option>
  <option value="Inactive">Inactive</option>
  <option value="Deleted">Deleted</option>
 
                            </select>
                             
                        </div>
                    </div>
					
					
					
                    
                </div>-->
            <div class="mt-4">
                    <center><button type="submit" name ="Update" class="px-4 py-1 text-white font-light tracking-wider bg-green-500 rounded" type="submit">Update Topic  Details</button></center>
                </div>
                
            </form>
        </div>
    </div>
</div>

</body>
</html>






