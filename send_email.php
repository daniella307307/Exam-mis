<?php
// Assuming these variables are declared in the main page
// $email, $firstname, $lastname, and $password should be set in the main page

$to = $email;
$subject = 'PASSWORD RESET';
$message = 'Dear ' . $firstname . ' ' . $lastname . ', your password has been set to a random number: (' . $password . '). Use it for logging in.';
$headers = 'From: robotics@blisglobal.org' . "\r\n" .
           'Reply-To: robotics@blisglobal.org' . "\r\n" .
           'X-Mailer: PHP/' . phpversion();

if (mail($to, $subject, $message, $headers)) {
    ?>
    <div class="bg-green-600 mb-2 border border-red-300 text-white px-4 py-3 rounded relative" role="alert">
        <strong class="font-bold">Password reset successfully</strong>
        <span class="block sm:inline">Dear <?php echo $firstname . "&nbsp;" . $lastname; ?>, your password has been sent to: <?php echo $email; ?>. You have to check your Email or your SPAM folder.</span>
        <span class="absolute top-0 top-0 right-0 px-4 py-3">
            
        </span>
    </div>

  <script>
        window.setTimeout(function () {
            // Redirect to Administrator login page
            window.location.href = "Administrator_login";
        }, 2500);
    </script> 
    <?php
} else {
    ?>
    <div class="bg-red-300 mb-2 border border-red-300 text-red-dark px-4 py-3 rounded relative" role="alert">
        <strong class="font-bold">Password reset failed</strong>
        <span class="block sm:inline">Dear <?php echo $firstname . "&nbsp;" . $lastname; ?>, your password has been reset but the email was not sent to: <?php echo $email; ?>. Please try again.</span>
        <span class="absolute top-0 top-0 right-0 px-4 py-3">
            
        </span>
    </div>
    <?php
}
?>
