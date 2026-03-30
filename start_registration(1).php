<?php
include('db.php');
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="./dist/styles.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.13/css/all.css" integrity="sha384-DNOHZ68U8hZfKXOrtjWvjxusGo9WQnrNx2sqG0tfsghAvtVlRW3tvkXWZh58N9jp"
          crossorigin="anonymous">
    <style>
        .login{
            background: url('./dist/images/Microprocessor.jpg')
        }
    </style>
    <title>Register</title>
</head>
<body class="h-screen font-sans login bg-cover">
<div class="container mx-auto h-full flex flex-1 justify-center items-center">
    <div class="w-full max-w-lg">
        <div class="leading-loose">
              <form action="" method="POST" class="max-w-xl m-4 p-10 bg-white rounded shadow-xl">
                    <center><h2 class="text-gray-800 font-medium"><big><big><strong>Verify your invitation</strong></big></big></h2></center>
         <?php
                    if (isset($_POST['Verify'])) {
                       $phone_number = str_replace(' ', '',$_POST['phone_number']); 
					   $COUNTRY = $_POST['COUNTRY'];
			     $find_inviyation =mysqli_query($conn,"SELECT * FROM users WHERE phone_number='$phone_number'");
				 $case =mysqli_num_rows($find_inviyation);
				 
				 if($case>0){
				 $find_Email =mysqli_fetch_array($find_inviyation);
				 $email_address =$find_Email['email_address']; 
					 if(!empty($email_address)){
						?>
					<div class="bg-red-500 mb-2 border border-red-300 text-black px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">Duplicate Error </strong><br>
                                    <span class="block sm:inline">Your phone No (<?php echo $phone_number;?>) is associated with an Email in the system. Means that you are registered &nbsp;<a class="text-white" href="Facilitator_login">Login Here </a></span>
                                    
                                  </div>
					<?php 
					 }
					 else{
						?> 
<div class="bg-green-500 mb-2 border border-red-300 text-white px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">Success !!! known phone <i class="fas fa-phone"></i> number </strong><br>
                                    <span class="block sm:inline">You are redirecting to the registration page </span>
                                    
                                  </div>				
			 <script>window.setTimeout(function(){

        // Move to a new location or you can do something else
        window.location.href = "Register?USER123=<?php echo $phone_number;?>&COUNTRY=<?php echo $COUNTRY;?>";

    },100);</script> 
				<?php 
					 }
				 
				 }
				 else{
				?><div class="bg-red-300 mb-2 border border-red-300 text-red-dark px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">Oops! Unknown phone number <i class="fas fa-phone"></i></strong><br>
                                    <span class="block sm:inline">Your Phone Number (<?php echo $phone_number;?>) is not known in our system <br>Contact the admin</span>
                                    
                                  </div><?php	 
				 }
                    }
                    ?>
                    <div class="w-full md:w-1/1 px-3 mb-6 md:mb-0">
                            <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="school_ref">Region</label>
                            <div class="relative">
                                <select name="COUNTRY" class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey" id="school_ref" required>
                                   <option value="">======== SELECT REGION ========</option>
								   <?php
                                    $select_country123 = mysqli_query($conn, "SELECT * FROM countries WHERE Country_status='Active'");
                                    while ($find_country = mysqli_fetch_array($select_country123)) {
                                        echo '<option value="' . $find_country['id'] . '">' . $find_country['Country_name'] . '</option>';
                                    }  
                                    ?>
                                </select>
                            </div>
                        </div>
					 <div class="w-full md:w-1/1 px-3 mb-6 md:mb-0">
                            <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="school_ref">Country</label>
                            <div class="relative">
                                <select name="COUNTRY" class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey" id="school_ref" required>
                                   <option value="">======== SELECT COUNTRY ========</option>
								   <?php
                                    $select_country123 = mysqli_query($conn, "SELECT * FROM countries WHERE Country_status='Active'");
                                    while ($find_country = mysqli_fetch_array($select_country123)) {
                                        echo '<option value="' . $find_country['id'] . '">' . $find_country['Country_name'] . '</option>';
                                    }  
                                    ?>
                                </select>
                            </div>
                        </div>
                        
                        	 <div class="w-full md:w-1/1 px-3 mb-6 md:mb-0">
                            <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="school_ref">School</label>
                            <div class="relative">
                                <select name="COUNTRY" class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey" id="school_ref" required>
                                   <option value="">======== SELECT School ========</option>
								   <?php
                                    $select_country123 = mysqli_query($conn, "SELECT * FROM countries WHERE Country_status='Active'");
                                    while ($find_country = mysqli_fetch_array($select_country123)) {
                                        echo '<option value="' . $find_country['id'] . '">' . $find_country['Country_name'] . '</option>';
                                    }  
                                    ?>
                                </select>
                            </div>
                        </div>
                    
                    <div class="mt-2">
                        <label class="block text-sm text-gray-600" for="phone_number">Phone No</label>
                        <input class="w-full px-5 py-4 text-gray-700 bg-gray-200 rounded" id="phone_number" name="phone_number"   type="text"  placeholder="Phone No" required>
                    </div> 
                   <div class="mt-2">
    <label class="block text-sm text-gray-600" for="phone_number">Phone No</label>
    <input 
        class="w-full px-5 py-4 text-gray-700 bg-gray-200 rounded" 
        id="phone_number" 
        name="phone_number" 
        type="tel" 
        placeholder="Phone No" 
        pattern="^\+\d{7,15}$" 
        required
        title="Phone number must start with + and contain 7 to 15 digits without spaces.">
</div>


			 

                   

                    <div class="mt-4">
                       <button type="submit" name="Verify" class="px-4 py-1 text-white font-light tracking-wider bg-yellow-500 rounded"> <i class="fas fa-paper-plane"></i>Go</button>
                  &nbsp;&nbsp;  <a class="inline-block right-0 align-baseline font-bold text-sm text-500 hover:text-blue-800" href="index">
          Go Home
        </a>
					  </div>
                </form>
        </div>
    </div>
</div>

</body>
</html>






