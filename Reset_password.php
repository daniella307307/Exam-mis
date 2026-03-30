<!doctype html>
<html lang="en">
<head>
  <title>Login | Reset Pasword</title>
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
session_start();
include('db.php');
if (isset($_POST['Reset'])){
    $email = mysqli_real_escape_string($conn, $_POST['email_address']);
    $password = mysqli_real_escape_string($conn,rand(10000,99000));
	$hashed_password = MD5($password);
 
  $sql = "SELECT * FROM users WHERE email_address= '$email'";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result)>0){
        $row = mysqli_fetch_array($result);
        $firstname= $row['firstname'];
        $lastname= $row['lastname'];
       $update = mysqli_query($conn,"UPDATE users SET password = '$hashed_password' WHERE email_address='$email'");
     include('send_email.php');
         
      }else{
    ?><div class="flex items-center mb-2 bg-red-500 text-white text-sm font-bold px-4 py-3" role="alert">
      <p>Password reset action Has failed</p> </div><?php
        } 
     
}
?>

	  
	  
	  
        <p class="text-gray-800 font-medium text-center text-lg font-bold">Password Reset</p>
        <div class="">
          <label class="block text-sm text-gray-00" for="username">User Email</label>
          <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="username" name="email_address" type="text" required="" placeholder="Your Email Here" aria-label="Your Email Here">
        </div>
         
        <div class="mt-8 items-center justify-between">
         <center> <button class="px-24 py-2 text-white font-light tracking-wider bg-gray-900 rounded" name ="Reset" type="submit">Reset Password</button></center>
           
        </div>
        <a class="inline-block right-0 align-baseline font-bold text-sm text-500 hover:text-blue-800" href="start_registration">
          Not registered ?
        </a>
      </form>
    </div>
  </div>
</div>
</body>
</html>
