<?php
session_start();
include('../../db.php');

if ($_SERVER["REQUEST_METHOD"] == "GET") {
	 $BACK=mysqli_real_escape_string($conn, $_GET['BACK']); 
   
 
			$Update =mysqli_query($conn,"DELETE FROM school_classes WHERE class_id  =$BACK");
			if($Update){
			?> 
			 <script>window.setTimeout(function(){

        // Move to a new location or you can do something else
        window.location.href = "School_class_rooms";

    }, 10);</script> <?php 	
			} 
    } 

?>