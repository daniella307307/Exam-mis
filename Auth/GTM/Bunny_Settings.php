<?php
ob_start();
include('header.php');

// Fetch user details
$details_user = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM bunny_storages WHERE  bunny_status='Active' LIMIT 1"));
$db_password =$details_user['bunny_password'];

/*
 
	
	
	
	bunny_logo
	bunny_status
*/



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
                    <center><p class="text-gray-800 font-medium">Update &nbsp;<strong><strong><big>bunny.net</big></strong></strong>&nbsp;  Settings Details</p></center>
                    <center><a href="Upload_profile_picture"> <img onclick="profileToggle()" class="inline-block h-10 w-10 rounded-full" src="<?php echo $details_user['bunny_logo'];?>" alt=""></a></center> 
                         <?php
                    if (isset($_POST['Update'])) {
                        $bunny_user_name = $_POST['bunny_user_name'];
                        $bunny_password = $_POST['bunny_password'];
                        $api_key = $_POST['api_key'];
                        $storage_name = $_POST['storage_name'];
                        $region_name =$_POST['region_name'];
                        $bunny_logo =$_POST['bunny_logo'];
						$bunny_status = $_POST['bunny_status'];
                        
                          $update = mysqli_query($conn, "
                            UPDATE bunny_storages SET 
                            bunny_user_name = '$bunny_user_name', 
                            bunny_password = '$bunny_password', 
                            api_key = '$api_key', 
                            storage_name = '$storage_name', 
                            region_name = '$region_name',
                            bunny_logo ='$bunny_logo',
                            bunny_status ='$bunny_status' WHERE bunny_id ='1' ");

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
                      <div class="flex items-center mb-2 bg-blue-500 text-white text-sm font-bold px-4 py-3" role="alert">Bunny Login details</div>
                      
                    
                    <div class="">
                        <label class="block text-sm text-gray-600" for="bunny_user_name">User Name</label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="bunny_user_name" name="bunny_user_name" value="<?php echo $details_user['bunny_user_name']; ?>" type="text" required >
                    </div>
                    
                    <div class="">
                        <label class="block text-sm text-gray-600" for="bunny_password">Password</label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="bunny_password" name="bunny_password" value="<?php echo $details_user['bunny_password']; ?>" type="text" required >
                    </div>
                     <br>
                     <div class="flex items-center mb-2 bg-blue-500 text-white text-sm font-bold px-4 py-3" role="alert">Bunny Storage details</div>
                    
                    <div class="">
                        <label class="block text-sm text-gray-600" for="storage_name">Storage Name</label>
    <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="storage_name" name="storage_name" value="<?php echo $details_user['storage_name']; ?>" type="text" required  placeholder="Storage Name">
                    </div>
                    
                    <div class="mt-2">
                        <label class="block text-sm text-gray-600" for="api_key">API Key</label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="api_key" name="api_key" value="<?php echo $details_user['api_key']; ?>" type="text" required placeholder ="API Key">
                    </div>
                    <div class="mt-2">
                        <label class="block text-sm text-gray-600" for="region_name">Region</label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="region_name" name="region_name"   type="text">
                    </div>
                     <div class="mt-2">
                        <label class="block text-sm text-gray-600" for="region_name">Logo Location </label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="region_name" name="bunny_logo"  value="<?php echo $details_user['bunny_logo'];?>" type="text">
                    </div>
                    <div class="mt-2">
                        <label class="block text-sm text-gray-600" for="bunny_status">Status</label>
                         <div class="relative">
                                <select name="bunny_status" class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey" id="bunny_status">
                                    <option value="<?php echo $details_user['bunny_status']; ?>"><?php echo $details_user['bunny_status']; ?></option>
                                    <option value="Inactive">Inactive</option>
                                      <option value="Deleted">Deleted</option>
                                     
                                </select>
                            </div>
                    </div>
                     
                    

                 

                    <div class="mt-4">
                        <button type="submit" name="Update" class="px-4 py-1 text-white font-light tracking-wider bg-green-500 rounded">Update Storage Zone Details</button>
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
