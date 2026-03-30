<?php
session_start();
include('../../db.php');

if ($_SERVER["REQUEST_METHOD"] == "GET") { 
	$STATUS  = mysqli_real_escape_string($conn, $_GET['STATUS']);
	 $BANK = mysqli_real_escape_string($conn, $_GET['BANK']);;
	 $COUNTRY =mysqli_real_escape_string($conn, $_GET['COUNTRY']);
	 $CURRENT= mysqli_real_escape_string($conn, $_GET['CURRENT']); 
   $URL = "Banks_in_Countries?STATUS=$CURRENT&COUNTRY=$COUNTRY";
         // echo $message;
			$Update = mysqli_query($conn,"UPDATE banks SET bank_status = '$STATUS' WHERE bank_id =$BANK");
			if($Update){
			 	
			//  header('location:'.$URL.'');
			?> 
			 <script>window.setTimeout(function(){

        // Move to a new location or you can do something else
        window.location.href = "<?php echo $URL;?>";

    }, 10);</script>  <?php 	
			} 
			else{
				echo "Error";
			}
    } 

?> 