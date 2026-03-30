<?php
include('../../db.php');
$this_year = DATE("Y");
$this_date = DATE("Y-m-d");
session_start();
$directory = __DIR__;
$current_folder = basename($directory);

if (!isset($_SESSION['student_id'])) {
    header("Location:../../index.php");
    exit;
}
	
else{
	$session_id = $_SESSION['student_id'];
/////////////////////SETTINGS///////////////
$details_settings =mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM settings_table WHERE setting_id=1 LIMIT 1"));
	$setting_maxrole_no =$details_settings['setting_maxrole_no'];
	$setting_timeout =$details_settings['setting_timeout'];
	$setting_min_year =$details_settings['setting_min_year'];
	$setting_pay_min =$details_settings['setting_pay_min'];
	$payment_percent = $setting_pay_min*100;
/////////////////////SETTINGS END /////////	
	
	
	
	$user_details= mysqli_query($conn, "SELECT * FROM student_list
LEFT JOIN schools ON student_list.student_school = schools.school_id
LEFT JOIN countries ON student_list.student_country = countries.id
LEFT JOIN regions_table ON student_list.student_region = regions_table.region_id
LEFT JOIN school_classes ON student_list.student_class =school_classes.class_id
LEFT JOIN  school_levels ON student_list.student_level =school_levels.level_id 
LEFT JOIN  students_promotion ON student_list.student_promotion =students_promotion.promotion_id
LEFT JOIN  certifications ON students_promotion.promotion_certification =certifications.certification_id WHERE  student_id='$session_id'");
$student_data= mysqli_fetch_array($user_details);
$student_promotion=$student_data['student_promotion'];
$promotion_fp_date=$student_data['promotion_fp_date'];
$promotion_payment = $student_data['promotion_payment'];
$Country_currency_code=$student_data['Country_currency_code'];
///////////////////////////INVOICE ////////////////////////////////
$find_INVOICE = mysqli_query($conn,"SELECT * FROM students_invoice WHERE invoice_student='$session_id' AND invoice_promotion='$student_promotion'");
$cinc = mysqli_num_rows($find_INVOICE);
if($cinc>0){
$inc_details = mysqli_fetch_array($find_INVOICE);
$invoice = $inc_details['invoice_id'];
$invoice_local =$inc_details['invoice_local'];
///////////////////////////INVOICE END/////////////////////////////
///////////////////////////INVOICE PAYMENT ////////////////////////////////
$find_payment = mysqli_query($conn,"SELECT SUM(pay_amount_local) AS paid_local FROM invoice_payments WHERE pay_invoice='$invoice'");
$payment_details = mysqli_fetch_array($find_payment);
$paid_local = $payment_details['paid_local'];


$paid_percent = ($paid_local/$invoice_local)*100;
}else{
$invoice =0;
$invoice_local =0;	
$paid_local=0;
$paid_percent=0;
}
$ball = $invoice_local-$paid_local;
///////////////////////////INVOICE PAYMENT END/////////////////////////////

$details_settings =mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM settings_table WHERE setting_id=1 LIMIT 1"));
	$setting_maxrole_no =$details_settings['setting_maxrole_no'];
	$setting_timeout =$details_settings['setting_timeout'];
	$setting_min_year =$details_settings['setting_min_year'];
	$setting_pay_min =$details_settings['setting_pay_min'];	 	
}

?>