<?php
include('../../db.php');
session_start();
$directory = __DIR__;
$current_folder = basename($directory);

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
	
else{
	$session_id = $_SESSION['user_id'];
	$user_details= mysqli_query($conn, "SELECT * FROM users
LEFT JOIN  user_permission ON users.access_level = user_permission.permissio_id
LEFT JOIN  schools ON users.school_ref = schools.school_id
LEFT JOIN  countries ON users.user_country = countries.id
LEFT JOIN  regions_table ON users.user_region = regions_table.region_id
  WHERE user_id ='$session_id'");
$user_data= mysqli_fetch_array($user_details);	
	 $permissio_location =$user_data['permissio_location'];
	 $school_ref  =   $user_data['school_ref'];
	 $school_name =  $user_data['school_name'];
	 $user_image =   $user_data['user_image'];
	 $Country_currency  =   $user_data['Country_currency'];  
	 $Country_name  =   $user_data['Country_name'];
	 $permissio_value = $user_data['permissio_value'];
	 $user_country= $user_data['user_country'];
	 $region_id = $user_data['region_id']; 
	 $region_name = $user_data['region_name']; 
	 
$uper_access = mysqli_fetch_array(mysqli_query($conn,"SELECT max(active_permission) AS uper_level FROM active_user_permission WHERE Active_user_ref ='$session_id'"));	 
	$uper_level = $uper_access['uper_level'];
		$details_settings =mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM settings_table WHERE setting_id=1 LIMIT 1"));
	$setting_maxrole_no =$details_settings['setting_maxrole_no'];
	$setting_timeout =$details_settings['setting_timeout'];
	$setting_min_year =$details_settings['setting_min_year'];
 	
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