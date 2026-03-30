<?php
 
include('../../db.php');
include('session.php');

if ($_SERVER["REQUEST_METHOD"] == "GET") {

	$BANK  = mysqli_real_escape_string($conn, $_GET['BANK']);  
	$STATUS  = mysqli_real_escape_string($conn, $_GET['STATUS']);  
 
$Update2 =mysqli_query($conn,"UPDATE banks SET bank_status = '$STATUS' WHERE  bank_id=$BANK");
 
 if($Update2){
 
	 
		
		?> 
		 <script>window.setTimeout(function(){

        // Move to a new location or you can do something else
        window.location.href = "Banks";

    }, 10);</script> 
	<?php 
	}
				
			} 

?>