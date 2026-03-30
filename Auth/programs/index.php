<?php 
session_start();
?>
<!doctype html>
<html lang="en">
<head>
  <title>Login | BLIS MIS</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="stylesheet" href="./../../dist/styles.css">
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.13/css/all.css" integrity="sha384-DNOHZ68U8hZfKXOrtjWvjxusGo9WQnrNx2sqG0tfsghAvtVlRW3tvkXWZh58N9jp" crossorigin="anonymous">
  <style>
  .login {
    background: url('./../../dist/images/Microprocessor.jpg')
  }
  </style>  
</head>     
<body class="h-screen font-sans login bg-cover">
<div class="container mx-auto h-full flex flex-1 justify-center items-center">
  <div class="w-full max-w-lg">
    <div class="leading-loose">
      <form class="max-w-xl m-4 p-10 bg-white rounded shadow-xl" action="" method="POST">
	  <?php

include('../../db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
      
$SCHOOL= mysqli_real_escape_string($conn, $_POST['SCHOOL']);
$ACCESS_KEY = mysqli_real_escape_string($conn, $_POST['ACCESS_KEY']);
  
    $sql = "SELECT * FROM schools WHERE school_id='18' AND school_accesskey='MAKINI@1983'";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
       // echo "Output:".$row['password'].'<br>';
        // Verify password
        if ($ACCESS_KEY==$row['school_accesskey'])  {
            // Set session variables
			            $_SESSION['logged_in'] = true;
                       $_SESSION['last_activity'] = time();
                       $_SESSION['school_id'] = $row['school_id'];
                     $_SESSION['school_name'] = $row['school_name']; 
			?>
			<div class="flex items-center mb-2 bg-red-500 text-white text-sm font-bold px-4 py-3" role="alert">
                                     
                                    <p>Login successful! Welcome  </p>
                                </div>
	 <script>window.setTimeout(function(){

        // Move to a new location or you can do something else
        window.location.href = "Auth_welcome.php";

    },10);</script> 
			<?php
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
                                        <p>No user found with that email address.<?php echo  $SCHOOL."/".$ACCESS_KEY;?></p>
                                    </div>							
								
			<?php
    }
}
?>

	  
	  
	   <a class="inline-block right-0 align-baseline font-bold text-sm text-500 hover:text-blue-800" href="index">
        Back|Home
        </a>
        <p class="text-gray-800 font-medium text-center text-lg font-bold">Administrator Progam Access </p>
        
        
         <div class="">
                            <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="access_level">Your School </label>
                            <div class="relative">
                                 <select name="SCHOOL" class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey" id="SCHOOL">
                                    <option value="">======= Select School =====</option>
                                   

								   <?php
                                    $select_school = mysqli_query($conn, "SELECT * FROM schools WHERE school_accesskey!=''");
                                    while ($find_school = mysqli_fetch_array($select_school)) {
                                        echo '<option value="' . $find_school['class_id'] . '">' . $find_school['school_name']. '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
        
        
        
        <div class="">
          <label class="block text-sm text-gray-00" for="username">School Access Key</label>
          <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="username" name="ACCESS_KEY" type="text" required="" placeholder="School Access Key" aria-label="username">
        </div>
       
        <div class="mt-4 items-center justify-between">
          <button class="px-12 py-1 text-white font-light tracking-wider bg-gray-900 rounded" type="submit">Login</button>
          &nbsp;<a class="inline-block right-0 align-baseline font-bold text-sm text-500 hover:text-blue-800" href="Reset_password">
            Forgot Password?
          </a>
        </div>
        
      </form>
    </div>
  </div>
</div>
</body>
</html>
