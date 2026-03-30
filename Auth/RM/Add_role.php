<?php
ob_start(); 
include('header.php');
if(isset($_GET['ID'])){
	 $ID =$_GET['ID'];
 }
 
 $details_user =mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM users  
LEFT JOIN user_permission ON users.access_level=user_permission.permissio_id
LEFT JOIN schools ON users.school_ref = schools.school_id WHERE user_id=$ID"));
$current_pass = $details_user['password'];
?>
  	   <!--/Header-->

        <div class="flex flex-1">
            <!--Sidebar-->
            <?php include('dynamic_side_bar.php');?>
            <!--/Sidebar-->
            <!--Main-->
<body class="h-screen font-sans login bg-cover">
<div class="container mx-auto h-full flex flex-1 justify-center items-center">
    <div class="w-full max-w-lg">
        <div class="leading-loose">
            <form  action ="" method= "POST" class="max-w-xl m-4 p-10 bg-white rounded shadow-xl">
                <p class="text-gray-800 font-medium">Add Role to  &nbsp;<strong><?php echo $details_user['firstname']."&nbsp;".$details_user['lastname']?></strong></p>
   
	 
                 <div class="flex flex-wrap -mx-3 mb-2">
                    
					 <div class="w-full md:w-1/1 px-3 mb-6 md:mb-0">
					 <div class="w-full md:w-3/3 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="grid-state">
                           User Role
                        </label>
                        <div class="relative">
                            <select name="access_level"
                                class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey"
                                id="grid-state">
                               <?php
								$select_role= mysqli_query($conn,"SELECT * FROM user_permission WHERE permissio_id>3 AND per_status='Active'");
								while($find_role = mysqli_fetch_array($select_role)){
								?><option value="<?php echo $find_role['permissio_id']; ?>"><?php echo $find_role['permission']; ?></option><?php	
								}
								?>
                            </select>
                             
                        </div>
                    </div> 
                </div>
<?php
if(isset($_POST['Update'])){
 
$access_level =$_POST['access_level'];  
$select_num = mysqli_num_rows(mysqli_query($conn,"SELECT * FROM  active_user_permission  WHERE   Active_user_ref ='$ID' AND  active_permission ='$access_level'"));
if($select_num>0){
?><div class="bg-red-300 mb-2 border border-red-300 text-red-dark px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">Duplicate Error</strong>
                                    <span class="block sm:inline">This Role has been asigned to  <?php echo $details_user['firstname']."&nbsp;".$details_user['lastname']?> .Try with different Role !!!.</span>
                                    <span class="absolute top-0 top-0 right-0 px-4 py-3">
                                      <svg class="fill-current h-6 w-6 text-red" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                                    </span>
                                </div><?php	
}
else{ 
$update = mysqli_query($conn,"INSERT INTO active_user_permission  ( active_permission_id , active_permission , Active_user_ref , permission_status ) VALUES 
                                                                   (NULL, '$access_level', '$ID', '')");
 if($update){
	header('location:Add_remove_Users_access?ID='.$ID.''); 
?><div class="bg-green-300 mb-2 border border-red-300 text-red-dark px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">Success !</strong>
                                    <span class="block sm:inline">Role Inserted !!!.</span>
                                    <span class="absolute top-0 top-0 right-0 px-4 py-3">
                                      <svg class="fill-current h-6 w-6 text-red" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                                    </span>
                                </div><?php 	
	
 }
 else{
	?><div class="bg-red-300 mb-2 border border-red-300 text-red-dark px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">Oops!</strong>
                                    <span class="block sm:inline">Something went wrong Try again !!!.</span>
                                    <span class="absolute top-0 top-0 right-0 px-4 py-3">
                                      <svg class="fill-current h-6 w-6 text-red" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                                    </span>
                                </div><?php 
 }
}
}


?>
                <div class="mt-4">
                    <center><button type="submit" name ="Update" class="px-4 py-1 text-white font-light tracking-wider bg-green-500 rounded" type="submit">Update User Details</button></center>
                </div> 
                
            </form>
        </div>
    </div>
</div>

</body>
</html>






