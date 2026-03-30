<?php 

?>
<!doctype html>
<html lang="en">
<head>
  <title>Login | BLIS MIS</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="stylesheet" href="./dist/styles.css">
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.13/css/all.css" integrity="sha384-DNOHZ68U8hZfKXOrtjWvjxusGo9WQnrNx2sqG0tfsghAvtVlRW3tvkXWZh58N9jp" crossorigin="anonymous">
  <style>
  .login {
    background: url('./dist/images/Microprocessor.jpg')
  }
  </style>  
</head>     
<body class="h-screen font-sans login bg-cover">
<div class="container mx-auto h-full flex flex-1 justify-center items-center">
  <div class="w-full max-w-lg">
    <div class="leading-loose">
      <form class="max-w-xl m-4 p-10 bg-white rounded shadow-xl" action="" method="POST">
	  <?php

include('db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email_address']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
	$hashed_password = MD5($password);
	
   /// $hashed_password = md5($password);
//echo "Input:".$hashed_password.'<br>';
    // Query to fetch the user
    $sql = "SELECT * FROM users
LEFT JOIN user_permission ON users.access_level=user_permission.permissio_id
LEFT JOIN schools ON users.school_ref = schools.school_id 
            WHERE email_address = '$email'";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
      
        if ($hashed_password==$row['password'])  {
         
         $access_level = $row['access_level'] ; 
         $user = $row['user_id'];
          $firstname = $row['firstname'];
          $lastname = $row['lastname'];
   $find_access = mysqli_query($conn,"SELECT * FROM `active_user_permission` WHERE active_permission='$access_level' AND Active_user_ref='$user' AND permission_status ='Active'");  
   $accss = mysqli_num_rows($find_access);
   
   if($accss>0){
       session_start();
     $_SESSION['logged_in'] = true;
                       $_SESSION['last_activity'] = time();
                       $_SESSION['user_id'] = $row['user_id'];
                     $_SESSION['firstname'] = $row['firstname'];
                      $_SESSION['lastname'] = $row['lastname'];
			$_SESSION['permissio_location'] = $row['permissio_location'];
			$_SESSION['permission'] = $row['permission'];
			
			$real_location = $_SESSION['permissio_location'];
            // Redirect to a protected page
           // header("Location:'.$real_location.'");
			?>
			<div class="flex items-center mb-2 bg-green-500 text-white text-sm font-bold px-4 py-3" role="alert">
                                     
                                    <p>Login successful! Welcome  </p>
                                </div>
	 <script>window.setTimeout(function(){

        // Move to a new location or you can do something else
        window.location.href = "Auth/<?php echo $real_location;?>";

    },10);</script> 
			<?php   
   }else{
       
   ?>	<div class="flex items-center mb-2 bg-red-500 text-white text-sm font-bold px-4 py-3" role="alert">
                                     
                                    <p>Dear &nbsp;<big><strong><?php echo $firstname."&nbsp;". $lastname ;?></strong></big> &nbsp; <br>You Don't have any Access in the system 
                                     <br>  Contact your Admin </p>
                                    
                                </div><?php    
   }
            
			           
        } else {
           // echo "Invalid password.";
			?>
			<div class="bg-red-300 mb-2 border border-red-300 text-red-dark px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">Oops!</strong>
                                    <span class="block sm:inline">Invalid password.</span>
                                    <span class="absolute top-0 top-0 right-0 px-4 py-3">
                                      <svg class="fill-current h-6 w-6 text-red" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                                    </span>
                                </div>
			<?php
        }
    } else {
        ?>
		 
								
	 <div class="bg-orange-300 border-l-4 mb-2 border-orange-500 text-orange-800 p-4" role="alert">
                                        <p class="font-bold">Oops!</p>
                                        <p>No user found with that email address.</p>
                                    </div>							
								
			<?php
    }
}
?>

	 
 
	  
	   <a class="inline-block right-0 align-baseline font-bold text-sm text-500 hover:text-blue-800" href="index">
        Back|Home
        </a>
        <p class="text-gray-800 font-medium text-center text-lg font-bold">Administrator Login</p>
        
 
<a class="inline-block right-0 align-baseline font-bold text-sm text-500 hover:text-blue-800" href="/Auth/programs/">
          Access School Program
        </a>
        
        <div class="">
          <label class="block text-sm text-gray-00" for="username">Your Email</label>
          <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="username" name="email_address" type="email" required="" placeholder="Your Email" aria-label="username">
        </div>
        <div class="mt-2">
          <label class="block text-sm text-gray-600" for="password">Password</label>
          <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="password" name="password" type="password" required="" placeholder="*******" aria-label="password">
        </div>
        <div class="mt-4 items-center justify-between">
          <button class="px-12 py-1 text-white font-light tracking-wider bg-gray-900 rounded" type="submit">Login</button>
          &nbsp;<a class="inline-block right-0 align-baseline font-bold text-sm text-500 hover:text-blue-800" href="Reset_password">
            Forgot Password?
          </a>
        </div>
        <a class="inline-block right-0 align-baseline font-bold text-sm text-500 hover:text-blue-800" href="start_registration">
          Not registered ?
        </a>&nbsp; &nbsp;&nbsp;&nbsp;
		 <a class="inline-block right-0 align-baseline font-bold text-sm text-500 hover:text-blue-800" href="index">
          Go back to the main page
        </a>
      </form>
    </div>
  </div>
</div>
</body>
</html>
