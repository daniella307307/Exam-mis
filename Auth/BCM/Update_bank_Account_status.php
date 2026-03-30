<?php
session_start();
include('../../db.php');

 
if ($_SERVER["REQUEST_METHOD"] == "GET") { 
	$STATUS  = mysqli_real_escape_string($conn, $_GET['STATUS']);
	 $BANK = mysqli_real_escape_string($conn, $_GET['Bank']);
	 $ACOUNT =mysqli_real_escape_string($conn, $_GET['ACOUNT']);
	 $COUNTRY =mysqli_real_escape_string($conn, $_GET['COUNTRY']);
	 $CURRENT= mysqli_real_escape_string($conn, $_GET['CURRENT']); 
	 
   $URL = "Accounts_in_Banks?Bank=$BANK&COUNTRY=$COUNTRY&STATUS=$STATUS";
         // echo $message;
			$Update = mysqli_query($conn,"UPDATE bank_account SET acount_status = '$STATUS' WHERE acount_id =$ACOUNT");
			if($Update){
			 	
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