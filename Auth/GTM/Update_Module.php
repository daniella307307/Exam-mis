<?php
session_start();
include('../../db.php');

if ($_SERVER["REQUEST_METHOD"] == "GET") {
	$STATUS  = mysqli_real_escape_string($conn, $_GET['STATUS']); 
	$ID  = mysqli_real_escape_string($conn, $_GET['ID']); 
	$CURRENT= mysqli_real_escape_string($conn, $_GET['CURRENT']);
     $CERTIFICATE = mysqli_real_escape_string($conn, $_GET['CERTIFICATE']);	
   
 
			$Update =mysqli_query($conn,"UPDATE certification_courses SET course_status = '$STATUS' WHERE course_id =$ID");
			if($Update){
			?> 
			 <script>window.setTimeout(function(){

        // Move to a new location or you can do something else
        window.location.href = "Modules_per_Certification?CERTIFICATE=<?php echo $CERTIFICATE;?>&STATUS=<?php echo $CURRENT;?>";

    }, 10);</script> <?php 	
			} 
    } 

?>