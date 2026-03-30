<?php
session_start(); 
include('../../db.php');

$directory = __DIR__;
$current_folder = "Auth/".basename($directory)."/";
$this_year = date("Y");

// Redirect if not logged in
if (!isset($_SESSION['parent_id'])) {
    header("Location: ../../index.php");
    exit;
}

// Get session data
$session_id = $_SESSION['parent_id'];
$parent_fname = $_SESSION['parent_fname'];
$parent_lname = $_SESSION['parent_lname'];
$permissio_location = $_SESSION['permissio_location'];

// Update last activity time
$_SESSION['last_activity'] = time();

// Get parent details from database
$user_details = mysqli_query($conn, 
    "SELECT * FROM students_parent_details
     LEFT JOIN schools ON students_parent_details.parent_school = schools.school_id
     LEFT JOIN countries ON schools.country_ref = countries.id
     LEFT JOIN regions_table ON schools.school_region = regions_table.region_id
     WHERE parent_id = '$session_id'");

$user_data = mysqli_fetch_array($user_details);

// Check if we got valid data
if (!$user_data) {
    // Handle case where no data was found
    header("Location: ../../index.php");
    exit;
}

// Extract user data
$permissio_location = $_SESSION['permissio_location'];
$school_region =$user_data['school_region'];
$user_country = $user_data['country_ref'];
$school_ref = $user_data['parent_school'];
$parent_gender=$user_data['parent_gender'];
$school_name = $user_data['school_name'];
$user_image = $user_data['parent_profile'];

$Country_currency_code = $user_data['Country_currency_code'];

$user_region = $user_data['region_name'];
$Country_flag = $user_data['Country_flag'];
$parent_status = $user_data['parent_status'];
// Get system settings
$settings_query = mysqli_query($conn, "SELECT * FROM settings_table WHERE setting_id=1 LIMIT 1");
$details_settings = mysqli_fetch_array($settings_query);

if ($details_settings) {
    $setting_maxrole_no = $details_settings['setting_maxrole_no'];
    $setting_timeout = $details_settings['setting_timeout'];
    $setting_min_year = $details_settings['setting_min_year'];
    $setting_pay_min = $details_settings['setting_pay_min'];
} else {
    // Default values if settings not found
    $setting_maxrole_no = 5;
    $setting_timeout = 1800; // 30 minutes
    $setting_min_year = 2000;
    $setting_pay_min = 10;
}

// Get invoice details
$invoice_query = mysqli_query($conn, 
    "SELECT SUM(invoice_usd) AS USD, 
            SUM(invoice_local) AS local, 
            COUNT(invoice_id) AS invoices, 
            SUM(pay_amount_usd) AS pay_USD, 
            SUM(pay_amount_local) AS pay_local 
     FROM students_invoice 
     LEFT JOIN invoice_payments ON students_invoice.invoice_id = invoice_payments.pay_invoice 
     WHERE invoice_school = '$school_ref'");

$invoice_details = mysqli_fetch_array($invoice_query);

if ($invoice_details) {
    $USD = $invoice_details['USD'] ?? 0;
    $local = $invoice_details['local'] ?? 0;
    $invoices = $invoice_details['invoices'] ?? 0;
    $pay_USD = $invoice_details['pay_USD'] ?? 0;
    $pay_local = $invoice_details['pay_local'] ?? 0;
    $bal_usd = $USD - $pay_USD;
    $Balance_local = $local - $pay_local;
} else {
    // Default values if no invoices found
    $USD = $local = $invoices = $pay_USD = $pay_local = $bal_usd = $Balance_local = 0;
}

// Get student count
$students_query = mysqli_query($conn, 
    "SELECT COUNT(student_id) AS Available_students 
     FROM student_list 
     WHERE student_school = '$school_ref' 
     AND student_status = 'Active'");

$students = mysqli_fetch_array($students_query);
$Available_students = $students['Available_students'] ?? 0;

// Check if user is in the correct location
if (empty($permissio_location)) {
    // Redirect after delay
    header("Refresh: 2.5; url=../../index.php");
} elseif ($permissio_location === $current_folder) {
  
}
else{
   header("Refresh: 2.5; url=../../$permissio_location"); 
}
?>