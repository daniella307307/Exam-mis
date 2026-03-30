<?php
session_start();
include('../../db.php');

if ($_SERVER["REQUEST_METHOD"] == "GET") {
	$STATUS  = mysqli_real_escape_string($conn, $_GET['STATUS']); 
	$COURSE = mysqli_real_escape_string($conn, $_GET['COURSE']); 
	$ID  = mysqli_real_escape_string($conn, $_GET['ID']); 
	$CURRENT= mysqli_real_escape_string($conn, $_GET['CURRENT']); 
	$CERTIFICATE= mysqli_real_escape_string($conn, $_GET['CERTIFICATE']); 
	 
 
			$Update =mysqli_query($conn," UPDATE learning_topics SET topic_status = '$STATUS' WHERE  topic_id ='$ID'");
			if($Update){
			?> 
		 <script>window.setTimeout(function(){

        // Move to a new location or you can do something else
        window.location.href = "Module_topics?COURSE=<?php echo $COURSE;?>&CERTIFICATE=<?php echo $CERTIFICATE;?>&STATUS=<?php echo $CURRENT;?>";

    }, 10);</script> <?php 	
			} 
    } 

?>