<?php
 
include('header.php');
if($_GET['USER123']OR $_GET['UPTODATE']){
$USER123  = $_GET['USER123']; 
$COUNTRY=$_GET['COUNTRY'];
$find_country =mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM countries WHERE id='$COUNTRY'"));
$Country_region =$find_country['Country_region'];
$id =$find_country['id'];

$inviyation =mysqli_query($conn,"SELECT * FROM users
    LEFT JOIN user_permission ON users.access_level = user_permission.permissio_id
    LEFT JOIN schools ON users.school_ref = schools.school_id
    WHERE  phone_number=$USER123");
 $details_user = mysqli_fetch_array($inviyation); 
 $user=$details_user['phone_number'];
 $user_id =$details_user['user_id'];
 $user_image =$details_user['user_image'];
				 
}else{
?> <script>window.setTimeout(function(){

        // Move to a new location or you can do something else
        window.location.href = "index";

    }, 800);</script> <?php	
}
 
 
?>
 
<!--/Header-->
<div class="flex flex-1"> 
    
    <!--Main-->

				 <div class="container mx-auto h-full flex flex-1 justify-center items-center">
        <div class="w-full max-w-lg">
            <div class="leading-loose">	
                <form action="" method="POST" class="max-w-xl m-4 p-10 bg-white rounded shadow-xl">
                    <center>
					<h2 class="text-gray-800 font-medium"><big><big><strong>welcome <?php echo  $user; ?></strong></big></big></h2>
					<h3 class="text-gray-800 font-medium"><big><big><strong>Register as New User</strong></big></big></h3>
					
					</center>
                                        <?php
                    if (isset($_POST['Update'])) {
                        $firstname = $_POST['firstname'];
                        $lastname = $_POST['lastname'];
                        $email_address = $_POST['email_address'];
                        $phone_number = $_POST['phone_number'];
                        $password="123456"; 
                        if (!empty($password)) {
                            $encrypted = md5($password);
                        } else {
                            $encrypted = $password;
                        }
                        $status = $_POST['status'];
                        $access_level = $_POST['access_level'];
                        $school_ref = $_POST['school_ref'];
	$select_school = mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM schools WHERE school_id='$school_ref'"));					
						
           $country_ref =$select_school['country_ref'];
		   $school_region =  $select_school['school_region'];       
//////////////////////UPLOARD PICTURE//////////////
   // Upload the image
   $update = mysqli_query($conn, "UPDATE users SET 
   firstname = '$firstname',
   lastname = '$lastname', 
   email_address = '$email_address', 
   password = '$encrypted', 
   status = '$status', 
   access_level = '$access_level', 
   school_ref = '$school_ref', 
   user_country = '$country_ref',
   user_region = '$school_region'  WHERE 	phone_number ='$phone_number'");

                        if ($update) {
                           // header('location:User_profile');
						   
////////////////UPLOARD FILE ON THE SERVER//////////////

///////////////UPLOARD FILE ON THE SERVER END////////////////////////						 
						   
						   
							?>
					<div class="bg-red-300 mb-2 border border-red-300 text-red-dark px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">Success </strong>
                                    <span class="block sm:inline">Your Default Password is in the braket :: <big><strong>(<?php echo $password;?>)</strong></big></span>
                                    <span class="absolute top-0 right-0 px-4 py-3">
                                      <svg class="fill-current h-6 w-6 text-red" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                                    </span>
                                  </div>		
							 <script>window.setTimeout(function(){

        // Move to a new location or you can do something else
        window.location.href = "Register?USER123=<?php echo $phone_number; ?>&UPTODATE=ACTION&COUNTRY=<?php echo $COUNTRY;?>";

    }, 4000);</script> 
							<?php
							
							
                        } else {
                            echo '<div class="bg-red-300 mb-2 border border-red-300 text-red-dark px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">Oops!</strong>
                                    <span class="block sm:inline">Something went wrong. Try again!</span>
                                    <span class="absolute top-0 right-0 px-4 py-3">
                                      <svg class="fill-current h-6 w-6 text-red" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                                    </span>
                                  </div>';
                        }	
					}
	 
	 
//////////////////////END OF UPLOARD PICTURE///////


                       
                    
                    ?>

				  <div class="">
                      <label class="block text-sm text-gray-600" for="user_id">ID</label> 
                        <input class="w-full px-5 py-1 text-gray-700 bg-blue-200 rounded" id="user_id" name="user_id" value="<?php echo $details_user['user_id']; ?>" type="hidden"  readonly>
                    </div>
                    
                    <div class="">
                        <label class="block text-sm text-gray-600" for="firstname">First Name</label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-blue-200 rounded" id="firstname" name="firstname" value="<?php echo $details_user['firstname']; ?>" type="text" required>
                    </div>
                    
                    <div class="">
                        <label class="block text-sm text-gray-600" for="lastname">Last Name</label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-blue-200 rounded" id="lastname" name="lastname" value="<?php echo $details_user['lastname']; ?>" type="text" required>
                    </div>
                    
                    <div class="mt-2">
                        <label class="block text-sm text-gray-600" for="phone_number">Phone No</label>
                        <input class="w-full px-5 py-4 text-gray-700 bg-blue-200 rounded" id="phone_number" name="phone_number" value="<?php echo $details_user['phone_number']; ?>" type="text"   placeholder="Phone No" required>
                    </div>
                    
                    <div class="mt-2">
                        <label class="block text-sm text-gray-600" for="email_address">Email</label>
                        <input class="w-full px-5 py-4 text-gray-700 bg-blue-200 rounded" id="email_address" name="email_address" value="<?php echo $details_user['email_address']; ?>" type="text" required>
                    </div>
					 <div class="w-full md:w-1/1 px-3 mb-6 md:mb-0">
                            <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="school_ref">Country</label>
                            <div class="relative">
                                <select name="COUNTRY" class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey" id="school_ref" required>
                                   <?php
                                    $select_country123 = mysqli_query($conn, "SELECT * FROM countries WHERE Country_status='Active' AND id='$COUNTRY' ");
                                    while ($find_country = mysqli_fetch_array($select_country123)) {
                                        echo '<option value="' . $find_country['id'] . '">' . $find_country['Country_name'] . '</option>';
                                    }  
                                    ?>
                                </select>
                            </div>
                        </div>
					 <div class="flex flex-wrap -mx-3 mb-2">
                        <div class="w-full md:w-1/3 px-3 mb-6 md:mb-0">
                            <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="access_level">User Role </label>
                            <div class="relative">
                                <select name="access_level" class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey" id="access_level">
                                   <?php
                                    $role1234 = mysqli_query($conn, "SELECT * FROM user_permission  WHERE permission ='School Facilitator'");
                                    while ($find_1234 = mysqli_fetch_array($role1234)) {
                                        echo '<option value="' . $find_1234['permissio_id'] . '">' . $find_1234['permission'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="w-full md:w-1/3 px-3 mb-6 md:mb-0">
                            <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="school_ref">User School</label>
                            <div class="relative">
                                <select name="school_ref" class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey" id="school_ref" required>
                                    <?php
                                    $select_school123 = mysqli_query($conn, "SELECT * FROM schools WHERE  country_ref='$COUNTRY'");
                                    while ($find_school = mysqli_fetch_array($select_school123)) {
                                        echo '<option value="' . $find_school['school_id'] . '">' . $find_school['school_name'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="w-full md:w-1/3 px-3 mb-6 md:mb-0">
                            <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="status">User Status</label>
                            <div class="relative">
                                <select name="status" class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey" id="status" required>
                                    <option value="Active">Active</option>
                                     
                                </select>
                            </div>
                        </div>
                    </div>


                    <div class="mt-4">
                        <button type="submit" name="Update" class="px-4 py-1 text-white font-light tracking-wider bg-green-500 rounded">Update your Details</button>
                    </div>
					
                </form> 
				
				
            </div>
        </div>
    </div>
	    <div class="container mx-auto h-full flex flex-1 justify-center items-center">
        <div class="w-full max-w-lg">
            <div class="leading-loose">
			<form action="" method="POST"  enctype="multipart/form-data"class="max-w-xl m-4 p-10 bg-white rounded shadow-xl">
					<?php
 
include('header.php');

// Ensure the user is set
if (isset($_GET['USER123'])) {
    $USER123 = $_GET['USER123']; 

    $inviyation = mysqli_query($conn, "SELECT * FROM users
        LEFT JOIN user_permission ON users.access_level = user_permission.permissio_id
        LEFT JOIN schools ON users.school_ref = schools.school_id
        WHERE phone_number=$USER123");
    $details_user = mysqli_fetch_array($inviyation); 
    $user = $details_user['phone_number'];
	$user_id  = $details_user['user_id'];
	$user_image = $details_user['user_image'];
	//echo "Phone:::".$user."/".$user_id ;
} else {
    echo "<script>window.setTimeout(function() {
        window.location.href = 'index';
    }, 800);</script>";
    exit();
}

// Handle the form submission
if (isset($_POST['Update_profile'])) {
    // File upload configuration
    $targetDir = "Auth/profiles/";
    $fileName = basename($_FILES['profile_picture']['name']);
    $targetFilePath = $targetDir.$fileName;
    $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
    $allowedTypes = array('jpg', 'jpeg', 'png');
 
		 if (file_exists($user_image)) {
			 	unlink($user_image); 
		 }
		 else{
			 
		 }
		
		
        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFilePath)) {
            // Update the database
            $userId = $details_user['user_id'];
            $updateQuery = "UPDATE users SET user_image = '$targetFilePath' WHERE user_id = $userId";
            $update = mysqli_query($conn, $updateQuery);
            
		   
            if ($update) {
                
				?>
				<div class="bg-green-500 mb-2 border border-red-300 text-white px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">Success </strong>
                                    <span class="block sm:inline">Profile picture updated successfully.</strong></big></span>
                                     
                                  </div>
								  <script>window.setTimeout(function(){

        // Move to a new location or you can do something else
        window.location.href = "Facilitator_login";

    }, 800);</script> 
				<?php 
            } else {
                
				?>
				<div class="bg-red-300 mb-2 border border-red-300 text-red-dark px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">Database Error</strong>
                                    <span class="block sm:inline"><?php echo "Database update failed: " . mysqli_error($conn);?></strong></big></span>
                                     
                                  </div>
				<?php 
            }
        } else {
            
		?>
				<div class="bg-red-500 mb-2 border border-red-300 text-red-dark px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">Uploard Error</strong>
                                    <span class="block sm:inline">There was an error uploading the file.</strong></big></span>
                                     
                                  </div>
				<?php 	
			
        }
 } 

?>

					<?php
				// Handle the form submission
 if (isset($_GET['UPTODATE'])) {
					?>
					
					<div class="mt-2">
                        <label class="block text-sm text-gray-600" for="email_address">User Profile</label>
                        
 <center> <img class="inline-block h-30 w-30 rounded-full" src="<?php echo $user_image;?>" alt=""></center>
                   
						<input class="w-full px-5 py-4 text-gray-700 bg-blue-200 rounded" type="file" id="profile_picture" name="profile_picture" required>

				   </div>
				   
				   <div class="mt-4">
                        <button type="submit" name="Update_profile" class="px-4 py-1 text-white font-light tracking-wider bg-green-500 rounded">Update profile Picture</button>
                    </div>
					
					<?php	
					}
					else{}
					
					?>
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