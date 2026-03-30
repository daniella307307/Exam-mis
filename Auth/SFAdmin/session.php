<?php
session_start(); 
include('../../db.php');
 
$directory = __DIR__;
$current_folder = basename($directory);
$this_year =DATE("Y");
if (!isset($_SESSION['user_id'])) {
   header("Location:../../index.php");
   exit;
}
	
else{
	$session_id = $_SESSION['user_id'];
	$user_details= mysqli_query($conn, "SELECT * FROM users
LEFT JOIN  user_permission ON users.access_level = user_permission.permissio_id
LEFT JOIN  schools ON users.school_ref = schools.school_id
LEFT JOIN  countries ON users.user_country = countries.id
LEFT JOIN  regions_table ON users.user_region = regions_table.region_id  WHERE user_id ='$session_id'");
$user_data= mysqli_fetch_array($user_details);	
	$permissio_location =$user_data['permissio_location'];
	$school_ref  =   $user_data['school_ref'];
	$user_country = $user_data['user_country'];
	$Country_currency_code =$user_data['Country_currency_code'];
	$user_region= $user_data['user_region'];
	$school_name =   $user_data['school_name'];
	 $user_image =   $user_data['user_image'];
	 $roles = mysqli_num_rows(mysqli_query($conn,"SELECT * FROM user_permission WHERE 1"));
	$details_settings =mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM settings_table WHERE setting_id=1 LIMIT 1"));
	$setting_maxrole_no =$details_settings['setting_maxrole_no'];
	$setting_timeout =$details_settings['setting_timeout'];
	$setting_min_year =$details_settings['setting_min_year'];
	$setting_pay_min =$details_settings['setting_pay_min'];
	
$Find_invoice =mysqli_query($conn,"SELECT SUM(invoice_usd) AS USD,SUM(invoice_usd) AS local,count(invoice_id) as invoices, SUM(pay_amount_usd) AS pay_USD,SUM(pay_amount_local) AS pay_local FROM students_invoice LEFT JOIN invoice_payments on students_invoice.invoice_id =invoice_payments.pay_invoice  WHERE invoice_school='$school_ref'"); 
$invoice_details = mysqli_fetch_array($Find_invoice);
$USD   = $invoice_details['USD'];
$local   = $invoice_details['local'];
$invoices   = $invoice_details['invoices'];
$pay_USD   = $invoice_details['pay_USD'];
$pay_local    = $invoice_details['pay_local'];
$bal_usd = $USD-$pay_USD;
$Balance_local= $local-$pay_local ;
$settings= mysqli_fetch_array(mysqli_query($conn,"SELECT setting_year,setting_term FROM settings_table WHERE setting_id=1"));
$setting_year = $settings['setting_year'];
$setting_term= $settings['setting_term'];

//$USD  $local  $invoices $pay_USD   $pay_local $bal_usd   $Balance_local
$students= mysqli_fetch_array(mysqli_query($conn,"SELECT count(student_id) as students FROM student_list WHERE student_school='$school_ref' AND student_status='Active'"));
$Available_students = $students['students'];
	
 if(empty($permissio_location)){
		?> <script>window.setTimeout(function(){

        // Move to a new location or you can do something else
        window.location.href = "../../index";

    }, 2500);</script><?php
	}else if($permissio_location==$current_folder){
		
	}
else{
?> <script>window.setTimeout(function(){

        // Move to a new location or you can do something else
        window.location.href = "../../Auth/<?php echo $permissio_location;?>";

    }, 2500);</script><?php	
}	
}

?>