<?php
 
include('../../db.php');
include('session.php');

if ($_SERVER["REQUEST_METHOD"] == "GET") {
	$Role  = mysqli_real_escape_string($conn, $_GET['Role']);  
$Update1 =mysqli_query($conn,"UPDATE users 
SET access_level = '$Role' 
WHERE  user_id ='$session_id'");
$Update2 =mysqli_query($conn,"UPDATE active_user_permission 
SET permission_status = 'Active' 
WHERE  active_permission ='$Role' AND Active_user_ref =$session_id");
$Update3 =mysqli_query($conn,"UPDATE active_user_permission 
SET permission_status = '' WHERE active_permission !='$Role' AND Active_user_ref =$session_id");
 if($Update1 AND $Update2 AND $Update3){
			 echo "UPDATED".$session_id."/".$Role;
 	$user123 =  mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM users 
    LEFT JOIN user_permission ON users.access_level = user_permission.permissio_id  WHERE users.user_id = '$session_id'"));	
	if($user123){
	 echo "UPDATED   1234";
		
		?> 
		 <script>window.setTimeout(function(){

        // Move to a new location or you can do something else
        window.location.href = "../<?php echo $user123['permissio_location'];?>/Roles_per_User";

    }, 10);</script> 
	<?php 
	}
				
			} 
    } 

?>