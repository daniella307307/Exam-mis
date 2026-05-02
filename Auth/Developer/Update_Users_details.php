<?php
ob_start();
include('header.php');
if(isset($_GET['ID'])){
	 $ID =$_GET['ID'];
 }

// Hard-delete handler. Removes the user row entirely (not a status flag).
// Wrapped in prepared statement to avoid SQL injection on the integer id.
if (isset($_POST['HardDelete']) && isset($ID)) {
    $delId = (int)$ID;
    $del = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $del->bind_param('i', $delId);
    if ($del->execute() && $del->affected_rows > 0) {
        $del->close();
        header('Location: Users.php');
        exit;
    }
    $del->close();
    $delete_error = 'Could not delete this user — they may be referenced by other records (students, exams, etc.). Set status to "Deleted" instead.';
}
 
 $details_user =mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM users  
LEFT JOIN user_permission ON users.access_level=user_permission.permissio_id
LEFT JOIN schools ON users.school_ref = schools.school_id 
LEFT JOIN countries ON users.user_country = countries.id
LEFT JOIN regions_table ON users.user_region = regions_table.region_id WHERE user_id=$ID"));
$current_pass = $details_user['password'];
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
                <p class="text-gray-800 font-medium">Update &nbsp;<strong><?php echo $details_user['firstname']."&nbsp;".$details_user['lastname']?></strong>&nbsp; Details</p>
      <center><a href="Upload_user_profile_picture?USER=<?php echo $ID;?>"> <img onclick="profileToggle()" class="inline-block h-10 w-10 rounded-full" src="../<?php echo $details_user['user_image'];?>" alt=""></a></center> 
         <?php
if(isset($_POST['Update'])){
$firstname =$_POST['firstname'];
$lastname =$_POST['lastname'];
$email_address =$_POST['email_address'];
$phone_number =$_POST['phone_number'];
$status =$_POST['status'];
$password =$_POST['Reset_password'];
$access_level =$_POST['access_level'];
$user_country = $_POST['user_country'];
$reg = mysqli_query($conn,"SELECT * FROM countries WHERE id='$user_country'");
$find_region = mysqli_fetch_array($reg);
$user_region =$find_region['Country_region'];
;$school_ref =$_POST['school_ref']; 
if(!empty($password)){
$encripted = md5($password);	
}
else{
	$encripted = $current_pass;	
}
;
 
$update = mysqli_query($conn,"UPDATE users SET 
firstname = '$firstname', 
lastname = '$lastname',
 email_address = '$email_address', 
 phone_number = '$phone_number', 
 password = '$encripted', 
 access_level = '$access_level',
 school_ref = '$school_ref',
 user_country ='$user_country',
 user_region='$user_region',
 status ='$status' WHERE user_id =$ID");
 if($update){
 	header('location:Users'); 
?><div class="bg-green-300 mb-2 border border-red-300 text-white px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">User Updated Successfully </strong>
                                    <span class="block sm:inline">Redirecting to Users Page <br><?php echo "Country:".$user_country."/Region:".$user_region;?></span>
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
	 <div class="">
				 
                   <label class="block text-sm text-gray-600" for="cus_email">ID</label>
                    <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="cus_name" name="user_id" value ="<?php echo $details_user['user_id'];?>" type="text" required="" placeholder="First Name" aria-label="Name" readonly>
                </div>
				
				<div class="">
				 
                   <label class="block text-sm text-gray-600" for="cus_email">First Name</label>
				   <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="cus_name"  name="firstname" value ="<?php echo $details_user['firstname'];?>" type="text" required="" placeholder="First Name" aria-label="Name">
                </div>
				<div class=""> 
                   <label class="block text-sm text-gray-600" for="cus_email">Last Name</label>
                    <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="cus_name"  name="lastname" value ="<?php echo $details_user['lastname'];?>" type="text" required="" placeholder="Last Name" aria-label="Name">
                </div>
				<div class="mt-2">
                    <label class="block text-sm text-gray-600" for="cus_email">Phone No</label>
                    <input class="w-full px-5  py-4 text-gray-700 bg-gray-200 rounded" id="cus_email" name="phone_number" value ="<?php echo $details_user['phone_number'];?>" type="text" required="" placeholder="Phone No" aria-label="Email">
                </div>
                <div class="mt-2">
                    <label class="block text-sm text-gray-600" for="cus_email">Email</label>
                    <input class="w-full px-5  py-4 text-gray-700 bg-gray-200 rounded" id="cus_email" name="email_address" value ="<?php echo $details_user['email_address'];?>" type="email" placeholder="Email address (optional)" aria-label="Email">


			   </div>
			    
					 <div class="w-full md:w-1/1 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="grid-state">
                           User Country
                        </label>
                        <div class="relative">
                            <select name="user_country"
                                class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey"
                                id="grid-state">
 <option value="<?php echo  $details_user['id']; ?>"><?php echo  $details_user['Country_name']."@". $details_user['region_name']; ?></option> 	
								                      
                               <?php
								$select_country= mysqli_query($conn,"SELECT * FROM countries
LEFT JOIN regions_table ON countries.Country_region = regions_table.region_id
WHERE Country_status='Active'");
								while($find_country= mysqli_fetch_array($select_country)){
								?><option value="<?php echo $find_country['id']; ?>"><?php echo $find_country['id']; ?>/<?php echo $find_country['Country_name']."/".$find_country['region_name']; ?></option><?php	
								}
								?> 
                            </select>
                             
                        </div>
                    </div>
                <div class="w-full md:w-1/3 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="grid-state">
                           Reset Password
                        </label>
                        <div class="relative">
                            <select name="Reset_password"
                                class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey"
                                id="grid-state">
								   <option value="">No</option>
								<option value="123456">Reset To (123456)</option> 
								<option value="001122">Reset To (001122)</option> 
								<option value="334455">Reset To (334455)</option> 
                            </select>
                             
                        </div>
                    </div>
                 <div class="flex flex-wrap -mx-3 mb-2">
                    
					 <div class="w-full md:w-1/3 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="grid-state">
                           User Role
                        </label>
                        <div class="relative">
                            <select name="access_level"
                                class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey"
                                id="grid-state">
                                <option value="<?php echo $details_user['permissio_id']; ?>"><?php echo $details_user['permission']; ?></option>
                               <?php
								$select_role= mysqli_query($conn,"SELECT * FROM user_permission WHERE  per_status='Active'");
								while($find_role = mysqli_fetch_array($select_role)){
								?><option value="<?php echo $find_role['permissio_id']; ?>"><?php echo $find_role['permission']; ?></option><?php	
								}
								?>
                            </select>
                             
                        </div>
                    </div>
					 <div class="w-full md:w-1/3 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="grid-state">
                           User School
                        </label>
                        <div class="relative">
                            <select name="school_ref"
                                class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey"
                                id="grid-state">
                                  <option value="<?php echo $details_user['school_ref']; ?>"><?php echo $details_user['school_name']; ?></option>
                               
								<?php
								$select_chool= mysqli_query($conn,"SELECT * FROM schools");
								while($find_school = mysqli_fetch_array($select_chool)){
								?><option value="<?php echo $find_school['school_id']; ?>"><?php echo $find_school['school_name']; ?></option><?php	
								}
								?>
                               
                                 
                            </select>
                             
                        </div>
                    </div>
                    <div class="w-full md:w-1/3 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="grid-state">
                           User Status
                        </label>
                        <div class="relative">
                            <select name="status"
                                class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey"
                                id="grid-state">
								 <option value="<?php echo $details_user['status']; ?>"><?php echo $details_user['status']; ?></option>
                                <option value="Active">Active</option>
								<option value="Inactive">Inactive</option>
                                <option value="Burned">Burned</option>
                                <option value="Suspended">Suspended</option>
								
                                <option value="Deleted">Deleted</option>
                            </select>
                             
                        </div>
                    </div>
                    
                </div>

                <div class="mt-4">
                    <button type="submit" name ="Update" class="px-4 py-1 text-white font-light tracking-wider bg-green-500 rounded" type="submit">Update User Details</button>
                    <button type="submit" name="HardDelete"
                            onclick="return confirm('This will permanently DELETE this user from the database. This cannot be undone. Continue?');"
                            class="px-4 py-1 text-white font-light tracking-wider bg-red-700 rounded ml-2">
                        Delete Forever
                    </button>
                </div>
                <?php if (!empty($delete_error)): ?>
                <div class="mt-3 bg-red-300 border border-red-300 text-red-800 px-4 py-3 rounded">
                    <?= htmlspecialchars($delete_error, ENT_QUOTES, 'UTF-8') ?>
                </div>
                <?php endif; ?>

            </form>
        </div>
    </div>
</div>

</body>
</html>






