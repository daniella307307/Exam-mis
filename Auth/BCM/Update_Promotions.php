<?php
session_start();
include('../../db.php');

if ($_SERVER["REQUEST_METHOD"] == "GET") {
	$STATUS  = mysqli_real_escape_string($conn, $_GET['STATUS']);
	 $REGION = mysqli_real_escape_string($conn, $_GET['REGION']);
	 $SCHOOL= mysqli_real_escape_string($conn, $_GET['SCHOOL']);
	 $COUNTRY= mysqli_real_escape_string($conn, $_GET['COUNTRY']);
	$ID  = mysqli_real_escape_string($conn, $_GET['ID']); 
	$CURRENT= mysqli_real_escape_string($conn, $_GET['CURRENT']); 
   
 
			$Update = mysqli_query($conn,"UPDATE students_promotion SET promotion_status = '$STATUS' WHERE promotion_id ='$ID'");
			if($Update){
			 
			 
			 
			   
				
			?> 
			 <script>window.setTimeout(function(){

        // Move to a new location or you can do something else
        window.location.href = "Schools_Payments?SCHOOL=<?php echo $SCHOOL;?>&COUNTRY=<?php echo $COUNTRY;?>&REGION=<?php echo $REGION;?>&STATUS=<?php echo $CURRENT;?>";

    }, 10);</script> --><?php 	
			} 
    } 

?> 