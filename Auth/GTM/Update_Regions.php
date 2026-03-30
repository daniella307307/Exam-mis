<?php
session_start();
include('../../db.php');

if ($_SERVER["REQUEST_METHOD"] == "GET") {
	$STATUS  = mysqli_real_escape_string($conn, $_GET['STATUS']); 
	$ID  = mysqli_real_escape_string($conn, $_GET['ID']); 
	$CURRENT= mysqli_real_escape_string($conn, $_GET['CURRENT']); 
   
 
			$Update =mysqli_query($conn,"UPDATE regions_table SET region_status = '$STATUS' WHERE regions_table.region_id ='$ID'");
			if($Update){
			?> 
			 <script>window.setTimeout(function(){

        // Move to a new location or you can do something else
        window.location.href = "Regions?STATUS=<?php echo $CURRENT;?>";

    }, 10);</script> <?php 	
			} 
    } 

?>