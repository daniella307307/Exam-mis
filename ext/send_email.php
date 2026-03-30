<?php
$to =$email;
$subject = 'PASSWORD RESET';
$message = 'Dear '.$firstname.'  '.$lastname.' your password has been set to a Random number :('.$password.')  Use it for Loging in ';
$headers = 'From:robotics@blisglobal.org' . "\r\n" .
           'Reply-To: robotics@blisglobal.org' . "\r\n" .
           'X-Mailer: PHP/' . phpversion();

if (mail($to, $subject, $message, $headers)) {
  ?><div class="bg-green-300 mb-2 border border-red-300 text-white px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">Password reset Successfully</strong>
                                    <span class="block sm:inline">Dear <?php echo $firstname."&nbsp;".$lastname;?> , your password has been sent to : <?php echo $email;?> you have  to  check your Email or your SPAM  </span>
                                    <span class="absolute top-0 top-0 right-0 px-4 py-3">
                                      <svg class="fill-current h-6 w-6 text-red" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                                    </span>
                                </div>
                                
            <script>window.setTimeout(function(){

        // Move to a new location or you can do something else
        window.location.href = "Administrator_login";

    },500);</script>
           
                                
                                <?
} else {
?><div class="bg-red-300 mb-2 border border-red-300 text-red-dark px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">Password reset Failed</strong>
                                    <span class="block sm:inline">Dear <?php echo $firstname."&nbsp;".$lastname;?> , your password has been reset but Email was not sent to : <?php echo $email;?> <br> Try again  </span>
                                    <span class="absolute top-0 top-0 right-0 px-4 py-3">
                                      <svg class="fill-current h-6 w-6 text-red" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                                    </span>
                                </div><? 
 
}
?>
