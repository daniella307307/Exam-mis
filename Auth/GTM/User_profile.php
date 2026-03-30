<?php
ob_start();
include('header.php');

// Fetch user details
$details_user = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM users
    LEFT JOIN user_permission ON users.access_level = user_permission.permissio_id
    LEFT JOIN schools ON users.school_ref = schools.school_id
    WHERE user_id = $session_id
"));
$db_password =$details_user['password'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Update User Details</title>
</head>
<body class="h-screen font-sans login bg-cover">

<!--/Header-->
<div class="flex flex-1">
    <!--Sidebar-->
    <?php include('dynamic_side_bar.php'); ?>
    <!--/Sidebar-->
    
    <!--Main-->
    <div class="container mx-auto h-full flex flex-1 justify-center items-center">
        <div class="w-full max-w-lg">
            <div class="leading-loose">
                <form action="" method="POST" class="max-w-xl m-4 p-10 bg-white rounded shadow-xl">
                    <p class="text-gray-800 font-medium">Update &nbsp;<strong><?php echo $details_user['firstname'] . " " . $details_user['lastname']; ?></strong>&nbsp; Details</p>
                    <center><a href="Upload_profile_picture"> <img onclick="profileToggle()" class="inline-block h-10 w-10 rounded-full" src="../<?php echo$user_data['user_image'];?>" alt=""></a></center> 
                         <?php
                    if (isset($_POST['Update'])) {
                        $firstname = $_POST['firstname'];
                        $lastname = $_POST['lastname'];
                        $email_address = $_POST['email_address'];
                        $phone_number = $_POST['phone_number'];
                        $password =$_POST['password'];
						$status = $_POST['status'];
                        $access_level = $_POST['access_level'];
                        $school_ref = $_POST['school_ref'];

                        if (!empty($password)) {
                            $encrypted = md5($password);
                        } else {
                            $encrypted = $db_password;
                        }

                        

                        $update = mysqli_query($conn, "
                            UPDATE users SET 
                            firstname = '$firstname', 
                            lastname = '$lastname', 
                            email_address = '$email_address', 
                            phone_number = '$phone_number', 
                            password = '$encrypted', 
                            access_level = '$access_level', 
                            school_ref = '$school_ref' 
                            WHERE user_id = $session_id
                        ");

                        if ($update) {
                            header('location:index');
                        } else {
                            echo '<div class="bg-red-300 mb-2 border border-red-300 text-red-dark px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">Oops!</strong>
                                    <span class="block sm:inline">Something went wrong. Try again!</span>
                                    <span class="absolute top-0 right-0 px-4 py-3">
                                      <svg class="fill-current h-6 w-6 text-red" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                                    </span>
                                  </div>';
                        }
                    }
                    ?>
                      
                      
                       <div class="">
                        <label class="block text-sm text-gray-600" for="user_id">ID</label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="user_id" name="user_id" value="<?php echo $details_user['user_id']; ?>" type="text" required="" readonly>
                    </div>
                    
                    <div class="">
                        <label class="block text-sm text-gray-600" for="firstname">First Name</label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="firstname" name="firstname" value="<?php echo $details_user['firstname']; ?>" type="text" required="" readonly>
                    </div>
                    
                    <div class="">
                        <label class="block text-sm text-gray-600" for="lastname">Last Name</label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="lastname" name="lastname" value="<?php echo $details_user['lastname']; ?>" type="text" required="" readonly>
                    </div>
                    
                    <div class="mt-2">
                        <label class="block text-sm text-gray-600" for="phone_number">Phone No</label>
                        <input class="w-full px-5 py-4 text-gray-700 bg-gray-200 rounded" id="phone_number" name="phone_number" value="<?php echo $details_user['phone_number']; ?>" type="text" required="" placeholder="Phone No">
                    </div>
                    
                    <div class="mt-2">
                        <label class="block text-sm text-gray-600" for="email_address">Email</label>
                        <input class="w-full px-5 py-4 text-gray-700 bg-gray-200 rounded" id="email_address" name="email_address" value="<?php echo $details_user['email_address']; ?>" type="text" required="" readonly>
                    </div>
                    <div class="mt-2">
                        <label class="block text-sm text-gray-600" for="password">Password</label>
                        <input class="w-full px-5 py-4 text-gray-700 bg-gray-200 rounded" id="password" name="password"   type="password">
                    </div>
                    
                    <div class="flex flex-wrap -mx-3 mb-2">
                        <div class="w-full md:w-1/3 px-3 mb-6 md:mb-0">
                            <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="access_level">User Role </label>
                            <div class="relative">
                                <select name="access_level" class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey" id="access_level">
                                   <option value="<?php echo $details_user['access_level'];?>"><?php echo $details_user['permission'];?>@ Main</option>
								    
                                </select>
                            </div>
                        </div>
                        
                        <div class="w-full md:w-1/3 px-3 mb-6 md:mb-0">
                            <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="school_ref">User School</label>
                            <div class="relative">
                                <select name="school_ref" class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey" id="school_ref">
                                    <option value="<?php echo $details_user['school_ref']; ?>"><?php echo $details_user['school_name']; ?></option>
                                    
                                </select>
                            </div>
                        </div>
                        
                        <div class="w-full md:w-1/3 px-3 mb-6 md:mb-0">
                            <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="status">User Status</label>
                            <div class="relative">
                                <select name="status" class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey" id="status">
                                    <option value="<?php echo $details_user['status']; ?>"><?php echo $details_user['status']; ?></option>
                                     
                                </select>
                            </div>
                        </div>
                    </div>

                 

                    <div class="mt-4">
                        <button type="submit" name="Update" class="px-4 py-1 text-white font-light tracking-wider bg-green-500 rounded">Update User Details</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>

<?php
ob_end_flush();
?>
