<?php
include('../../db.php');
session_start();
$directory = __DIR__;
$current_folder = basename($directory);

if (!isset($_SESSION['user_id'])) {
    header("Location:../../index.php");
    exit;
}
	
else{
	$session_id = $_SESSION['user_id'];
	$user_details= mysqli_query($conn, "SELECT * FROM users
LEFT JOIN  user_permission ON users.access_level = user_permission.permissio_id
LEFT JOIN  schools ON users.school_ref = schools.school_id
LEFT JOIN  countries ON users.user_country = countries.id  WHERE user_id ='$session_id'");
$user_data= mysqli_fetch_array($user_details);	
	$permissio_location =$user_data['permissio_location'];
	$school_ref  =   $user_data['school_ref'];
	$school_name =  $user_data['school_name'];
	 $user_image =   $user_data['user_image'];
	
 if(empty($permissio_location)){
		?> <script>window.setTimeout(function(){

        // Move to a new location or you can do something else
        window.location.href = "../../index";

    }, 2500);</script><?php
	}else if($permissio_location==$current_folder){
		
	}
else{
?> <script>window.setTimeout(function(){

        // Move to a new location or you can do something else
        window.location.href = "../../Auth/<?php echo $permissio_location;?>";

    }, 2500);</script><?php	
}	
}

?>