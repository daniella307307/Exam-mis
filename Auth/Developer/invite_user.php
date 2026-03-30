<!doctype html>
<html lang="en">
<head>
  <title>INVITE USER | BLIS MIS</title>
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
session_start();
include('../../db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") { 
	$phone_number  = mysqli_real_escape_string($conn, str_replace(' ', '', $_POST['phone_number']));  
     // Query to fetch the user
    $sql = "SELECT * FROM users 
            WHERE phone_number='$phone_number'";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        ?><div class="bg-red-300 mb-2 border border-red-300 text-red-dark px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">Oops!</strong>
                                    <span class="block sm:inline">This Phone Number (<?php echo $phone_number;?>) Has been Registred before.</span>
                                    <span class="absolute top-0 top-0 right-0 px-4 py-3">
                                      <svg class="fill-current h-6 w-6 text-red" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                                    </span>
                                </div>
								 <script>window.setTimeout(function(){

        // Move to a new location or you can do something else
        window.location.href = "Users_registration";

    }, 2500);</script> 
								
								
								<?php
          }
		  else{
			$insert =mysqli_query($conn,"INSERT INTO users (phone_number,access_level,status) VALUES 
                  ('$phone_number','4','Active')");
			if($insert){
			?><div class="flex items-center mb-2 bg-green-500 text-white text-sm font-bold px-4 py-3" role="alert">
                                    <svg class="fill-current w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M12.432 0c1.34 0 2.01.912 2.01 1.957 0 1.305-1.164 2.512-2.679 2.512-1.269 0-2.009-.75-1.974-1.99C9.789 1.436 10.67 0 12.432 0zM8.309 20c-1.058 0-1.833-.652-1.093-3.524l1.214-5.092c.211-.814.246-1.141 0-1.141-.317 0-1.689.562-2.502 1.117l-.528-.88c2.572-2.186 5.531-3.467 6.801-3.467 1.057 0 1.233 1.273.705 3.23l-1.391 5.352c-.246.945-.141 1.271.106 1.271.317 0 1.357-.392 2.379-1.207l.6.814C12.098 19.02 9.365 20 8.309 20z"/></svg>
                                    <p>User invited  successful!</p>
                                </div>
			 <script>window.setTimeout(function(){

        // Move to a new location or you can do something else
        window.location.href = "Users_registration";

    }, 2500);</script> <?php 	
			}else{
			?><div class="flex items-center mb-2 bg-green-500 text-white text-sm font-bold px-4 py-3" role="alert">
                                    <svg class="fill-current w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M12.432 0c1.34 0 2.01.912 2.01 1.957 0 1.305-1.164 2.512-2.679 2.512-1.269 0-2.009-.75-1.974-1.99C9.789 1.436 10.67 0 12.432 0zM8.309 20c-1.058 0-1.833-.652-1.093-3.524l1.214-5.092c.211-.814.246-1.141 0-1.141-.317 0-1.689.562-2.502 1.117l-.528-.88c2.572-2.186 5.531-3.467 6.801-3.467 1.057 0 1.233 1.273.705 3.23l-1.391 5.352c-.246.945-.141 1.271.106 1.271.317 0 1.357-.392 2.379-1.207l.6.814C12.098 19.02 9.365 20 8.309 20z"/></svg>
                                    <p>User invited  successful!</p>
                                </div>
								
			 <div role="alert" class="mb-2">
                                        <div class="bg-red-500 text-white font-bold rounded-t px-4 py-2">
                                            Warning
                                        </div>
                                        <div class="border border-t-0 border-red-300 rounded-b bg-red-300 px-4 py-3 text-red-800">
                                            <p>Data not inserted inthe system .</p>
                                        </div>
                                    </div>					
								
								
								<?php	
			}	  
			 
		  }
		  
    } 

?>

	  
	   
      </form>
    </div>
  </div>
</div>
</body>
</html>
