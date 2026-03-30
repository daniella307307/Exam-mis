<?php
session_start();
include('../../db.php');

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    
	$BANK=mysqli_real_escape_string($conn, $_GET['BANK']);
	$ACCOUNT_ID=mysqli_real_escape_string($conn, $_GET['ACCOUNT_ID']); 
   
 
			$Update =mysqli_query($conn,"DELETE FROM bank_account WHERE acount_id =$ACCOUNT_ID");
			if($Update){
			?> 
			 <script>window.setTimeout(function(){

        // Move to a new location or you can do something else
        window.location.href = "Bank_account_numbers?BANK=<?PHP ECHO $BANK;?>";

    }, 10);</script> <?php 	
			} 
    } 

?>