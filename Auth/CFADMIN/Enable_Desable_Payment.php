<?php
session_start();
include('../../db.php');
 
if ($_SERVER["REQUEST_METHOD"] == "GET") { 
    $PROMOTION =mysqli_real_escape_string($conn, $_GET['PROMOTION']);
    $PAYMENT =mysqli_real_escape_string($conn, $_GET['PAYMENT']);
	$SCHOOL  = mysqli_real_escape_string($conn, $_GET['SCHOOL']);
	 $REGION = mysqli_real_escape_string($conn, $_GET['REGION']);;
	 $COUNTRY =mysqli_real_escape_string($conn, $_GET['COUNTRY']);
	 $STATUS= mysqli_real_escape_string($conn, $_GET['STATUS']); 
   $URL = "Schools_Payments?SCHOOL=$SCHOOL&COUNTRY=$COUNTRY&REGION=$REGION&STATUS=$STATUS";
   if($PAYMENT=="Enable"){
	$promotion_payment ="Disable";   
   }
   else{
	 $promotion_payment ="Enable";     
   }
   $Update = mysqli_query($conn,"UPDATE  students_promotion SET promotion_payment = '$promotion_payment' WHERE promotion_id ='$PROMOTION'");
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