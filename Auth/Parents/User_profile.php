<?php
ob_start();
include('header.php');

// Fetch user details
$details_user = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM students_parent_details
     LEFT JOIN schools ON students_parent_details.parent_school = schools.school_id
     LEFT JOIN countries ON schools.country_ref = countries.id
     LEFT JOIN regions_table ON schools.school_region = regions_table.region_id
     WHERE parent_id = '$session_id'
"));
$db_password =$details_user['parent_password'];
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
                    <p class="text-gray-800 font-medium">Update &nbsp;<strong><?php echo $details_user['parent_fname'] . " " . $details_user['parent_lname']; ?></strong>&nbsp; Details</p>
                    <center><a href="Upload_profile_picture"> <img onclick="profileToggle()" class="inline-block h-10 w-10 rounded-full" src="../<?php echo$user_data['parent_profile'];?>" alt=""></a></center> 
                         <?php
                    if (isset($_POST['Update'])) {
                          $parent_school = mysqli_real_escape_string($conn, $_POST['parent_school']);
    $parent_fname = mysqli_real_escape_string($conn, $_POST['parent_fname']);
    $parent_lname = mysqli_real_escape_string($conn, $_POST['parent_lname']);
    $parent_gender = mysqli_real_escape_string($conn, $_POST['parent_gender']);
    $parent_phone = mysqli_real_escape_string($conn, $_POST['parent_phone']);
    $parent_email = mysqli_real_escape_string($conn, $_POST['parent_email']);
    $parent_profession = mysqli_real_escape_string($conn, $_POST['parent_profession']);
    $parent_work_place = mysqli_real_escape_string($conn, $_POST['parent_work_place']);
    $parent_password = mysqli_real_escape_string($conn, $_POST['parent_password']);
    $parent_login_chanel = mysqli_real_escape_string($conn, $_POST['parent_login_chanel']);
    $parent_status = "Active";
    $find_dup = mysqli_num_rows(mysqli_query($conn,"SELECT * FROM students_parent_details WHERE (parent_phone='$parent_phone' OR  parent_email='$parent_email') and  parent_id!='$session_id' "));
    if($find_dup>0){
     echo '<div class="bg-red-300 mb-2 border border-red-300 text-red-dark px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">Oops!</strong>
                                    <span class="block sm:inline">Duplicate Email Or Phone Number !!!!</span>
                                    <span class="absolute top-0 right-0 px-4 py-3">
                                      <svg class="fill-current h-6 w-6 text-red" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                                    </span>
                                  </div>';   
        
    }else{

                        if (!empty($parent_password)) {
                            $encrypted = md5($parent_password);
                        } else {
                            $encrypted = $db_password;
                        }

                        

                        $update = mysqli_query($conn, "
                            UPDATE  students_parent_details SET 
                            parent_fname = '$parent_fname', 
                            parent_lname = '$parent_lname', 
                            parent_gender = '$parent_gender',
                            parent_phone = '$parent_phone',
                            parent_email = '$parent_email', 
                            parent_profession = '$parent_profession',
                            parent_work_place = '$parent_work_place',
                            parent_password = '$encrypted', 
                            parent_login_chanel = '$parent_login_chanel', 
                            parent_profile = 'profiles/Evan.png s' WHERE parent_id= $session_id");

                        if ($update) {
                            echo '<div class="bg-green-300 mb-2 border border-red-300 text-red-dark px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">Oops!</strong>
                                    <span class="block sm:inline">Success! Details Updated </span>
                                    <span class="absolute top-0 right-0 px-4 py-3">
                                      <svg class="fill-current h-6 w-6 text-red" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                                    </span>
                                  </div>';
                            
                            
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
                    }
                    ?>
                      
                      
                       <div class="">
                        <label class="block text-sm text-gray-600" for="user_id">ID</label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="user_id" name="user_id" value="<?php echo $details_user['parent_id']; ?>" type="text" required="" readonly>
                    </div>
                    
                    <div class="">
                        <label class="block text-sm text-gray-600" for="firstname">School Name</label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="parent_school" name="parent_school" value="<?php echo $details_user['school_name']; ?>" type="text" required readonly>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="parent_school" name="parent_school" value="<?php echo $details_user['parent_school']; ?>" type="hidden" required >
                    </div>
                    
 






 
                    <div class="">
                        <label class="block text-sm text-gray-600" for="parent_fname">Fist Name</label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="parent_fname" name="parent_fname" value="<?php echo $details_user['parent_fname']; ?>" type="text" required="" readonly>
                    </div>
                     <div class="">
                        <label class="block text-sm text-gray-600" for="parent_lname">Last Name</label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="parent_lname" name="parent_lname" value="<?php echo $details_user['parent_lname']; ?>" type="text" required="" readonly>
                    </div>
                    <div class="w-full md:w-1/3 px-3 mb-6 md:mb-0">
                            <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="access_level">Gender</label>
                            <div class="relative">
                                <select name="parent_gender" class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey" id="access_level">
                                   <option value="<?php echo $details_user['parent_gender'];?>"><?php echo $details_user['parent_gender'];?></option>
                                   <option value="Male">Male</option>
                                   <option value="Female">Female</option>
								    
                                </select>
                            </div>
                        </div>
                    
                    <div class="mt-2">
                        <label class="block text-sm text-gray-600" for="parent_phone">Phone No</label>
                        <input class="w-full px-5 py-4 text-gray-700 bg-gray-200 rounded" id="parent_phone" name="parent_phone" value="<?php echo $details_user['parent_phone']; ?>" type="text" required placeholder="Phone No">
                    </div>
                     <div class="mt-2">
                        <label class="block text-sm text-gray-600" for="parent_email">Email</label>
                        <input class="w-full px-5 py-4 text-gray-700 bg-gray-200 rounded" id="parent_email" name="parent_email" value="<?php echo $details_user['parent_email']; ?>" type="email" required placeholder="Email">
                    </div>
                    
                    <div class="mt-2">
                        <label class="block text-sm text-gray-600" for="parent_profession">Profession </label>
                        <input class="w-full px-5 py-4 text-gray-700 bg-gray-200 rounded" id="parent_profession" name="parent_profession" value="<?php echo $details_user['parent_profession']; ?>" type="text" required>
                    </div>
                    
                     <div class="mt-2">
                        <label class="block text-sm text-gray-600" for="parent_work_place">Work Place</label>
                        <input class="w-full px-5 py-4 text-gray-700 bg-gray-200 rounded" id="pparent_work_place" name="parent_work_place" value="<?php echo $details_user['parent_work_place']; ?>" type="text" required>
                    </div>
                    
                    
                    
                    <div class="mt-2">
                        <label class="block text-sm text-gray-600" for="parent_password">Password</label>
                        <input class="w-full px-5 py-4 text-gray-700 bg-gray-200 rounded" id="parent_password" name="parent_password"   type="password">
                    </div>
                    
                     <div class="w-full md:w-1/1 px-3 mb-6 md:mb-0">
                            <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="access_level">While logging in, I will use</label>
                            <div class="relative">
                                <select name="parent_login_chanel" class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey" id="access_level">
                                    
                                    <?php
                                    
                                   if($details_user['parent_login_chanel']=="Password"){
                                    ?><option value="Password">Emai & Password</option><?php   
                                   }
                                   else{
                                       ?> <option value=" Phone<">Emai & Phone Number</option><?php
                                       
                                   } 
                                    
                                    
                                    ?>
                                  <option value="Password">Emai & Password</option> 
                                  <option value=" Phone<">Emai & Phone Number</option>
                                    
                                  
                                 
                                 
                                 
                                 
                                  
                                   
								    
                                </select>
                            </div>
                        </div>
                    
                    <div class="flex flex-wrap -mx-3 mb-2">
                       
                        
                         
                        
                        <div class="w-full md:w-1/1 px-3 mb-6 md:mb-0">
                            <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="status">User Status</label>
                            <div class="relative">
                                <select name="status" class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey" id="status">
                                    <option value="<?php echo $parent_status ; ?>"><?php echo $parent_status ; ?></option>
                                     
                                </select>
                            </div>
                        </div>
                    </div>

                 

                    <div class="mt-4">
                        <button type="submit" name="Update" class="px-4 py-1 text-white font-light tracking-wider bg-green-500 rounded">Update your Details</button>
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
