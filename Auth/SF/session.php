<?php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
require_once(__DIR__ . '/../../db.php');
$directory = __DIR__;
$current_folder = basename($directory);
$this_year = DATE("Y");

/**
 * Session-guard helpers used by every authenticated page.
 *
 * - If no user_id in session → redirect to login (was: blank exit, which
 *   is what produced the "blank page when idle" report).
 * - If last_activity is older than the idle limit → destroy session and
 *   bounce to login with ?expired=1 so the form can show a friendly
 *   "you were signed out for inactivity" message.
 * - Otherwise refresh last_activity so an active user never gets kicked.
 */
$IDLE_LIMIT_SECONDS = 30 * 60; // 30 minutes

function session_redirect_login(string $reason = ''): void {
    $base = APP_BASE_URL . '/';
    $url  = $base . 'Administrator_login.php' . ($reason ? '?reason=' . urlencode($reason) : '');
    if (!headers_sent()) {
        header('Location: ' . $url);
        exit;
    }
    echo '<meta http-equiv="refresh" content="0;url=' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '">';
    exit;
}

if (!isset($_SESSION['user_id'])) {
    session_redirect_login();
}

if (isset($_SESSION['last_activity']) && (time() - (int)$_SESSION['last_activity']) > $IDLE_LIMIT_SECONDS) {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
    session_redirect_login('idle');
}
$_SESSION['last_activity'] = time();
{
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
	$school_language =$user_data['school_language'];
	 $user_image =   $user_data['user_image'];
	 $status =$user_data['status'];
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
 
$students= mysqli_fetch_array(mysqli_query($conn,"SELECT count(student_id) as Available_students FROM student_list WHERE student_school='$school_ref' AND student_status='Active'"));
$Available_students = $students['Available_students'];
	
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