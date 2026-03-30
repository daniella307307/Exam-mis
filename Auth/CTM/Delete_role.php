<?php
session_start();
include('../../db.php');

if ($_SERVER["REQUEST_METHOD"] == "GET") {
	$USER=mysqli_real_escape_string($conn, $_GET['USER']);
	$ID=mysqli_real_escape_string($conn, $_GET['ID']); 
   
 
			$Update =mysqli_query($conn,"DELETE FROM  active_user_permission  WHERE  active_permission_id  =$ID");
			if($Update){
			?> 
			 <script>window.setTimeout(function(){

        // Move to a new location or you can do something else
        window.location.href = "Add_remove_Users_access?ID=<?PHP ECHO $USER?>";

    }, 10);</script> <?php 	
			} 
    } 

?>