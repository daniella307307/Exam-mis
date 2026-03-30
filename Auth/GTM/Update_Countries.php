<?php
session_start();
include('../../db.php');

if ($_SERVER["REQUEST_METHOD"] == "GET") {
	$STATUS  = mysqli_real_escape_string($conn, $_GET['STATUS']); 
	$ID  = mysqli_real_escape_string($conn, $_GET['ID']); 
	$CURRENT= mysqli_real_escape_string($conn, $_GET['CURRENT']); 
   
 
			$Update =mysqli_query($conn,"UPDATE countries SET Country_status = '$STATUS' WHERE id ='$ID'");
			if($Update){
			?> 
			 <script>window.setTimeout(function(){

        // Move to a new location or you can do something else
        window.location.href = "Countries?STATUS=<?php echo $CURRENT;?>";

    }, 10);</script> <?php 	
			} 
    } 

?>