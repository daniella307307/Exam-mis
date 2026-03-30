<?php
session_start();
include('../../db.php');

if ($_SERVER["REQUEST_METHOD"] == "GET") {
	$USER=mysqli_real_escape_string($conn, $_GET['USER']);
	$STATUS =mysqli_real_escape_string($conn, $_GET['STATUS']);
	$ID=mysqli_real_escape_string($conn, $_GET['ID']); 
   
 
			$Update =mysqli_query($conn,"DELETE FROM allocation_schools WHERE allocation_id =$ID");
			if($Update){
			?> 
			 <script>window.setTimeout(function(){

        // Move to a new location or you can do something else
        window.location.href = "Users_School_allocations?USER=<?PHP ECHO $USER?>&STATUS=<?PHP ECHO $STATUS;?>";

    }, 10);</script> <?php 	
			} 
    } 

?>