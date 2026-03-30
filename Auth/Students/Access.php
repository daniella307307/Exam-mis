<?php
if($promotion_payment=="Enable"){
if($this_date<=$promotion_fp_date){
	if($paid_percent>=$payment_percent){
		
	}
	else{
	header('location:Registered_Courses');	
	}
}else{
if($paid_percent<$payment_percent*2){
header('location:Registered_Courses');
	}
else{}	
}
}
else{
	
}
?>