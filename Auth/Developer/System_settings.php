<?php
ob_start(); 
include('header.php');
 
 
 $details_school =mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM settings_table WHERE setting_id=1 LIMIT 1"));
$percent =$details_school['setting_pay_min']*100;
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
                <p class="text-gray-800 font-medium">Update &nbsp;<strong> Security System Settings</strong>&nbsp; Details</p>
    
			
			
			
			
				 					 
<?php
if(isset($_POST['Update'])){ 
$setting_login_attempts = mysqli_real_escape_string($conn,$_POST['setting_login_attempts']); 
$setting_maxrole_no =  mysqli_real_escape_string($conn,$_POST['setting_maxrole_no']);
$setting_email =  mysqli_real_escape_string($conn,$_POST['setting_email']); 
$setting_email_passwd =  mysqli_real_escape_string($conn,$_POST['setting_email_passwd']); 
$setting_min_year =mysqli_real_escape_string($conn,$_POST['setting_min_year']); 
$setting_timeout = mysqli_real_escape_string($conn,$_POST['setting_timeout']);
$setting_pay_min =mysqli_real_escape_string($conn,$_POST['setting_pay_min']);
$setting_term =mysqli_real_escape_string($conn,$_POST['setting_term']);
$setting_year =mysqli_real_escape_string($conn,$_POST['setting_year']);
$percent11 = $setting_pay_min/100;
$time = $setting_timeout*60;
  
$update = mysqli_query($conn,"UPDATE settings_table SET 
setting_login_attempts = '$setting_login_attempts', 
setting_maxrole_no = '$setting_maxrole_no', 
setting_email = '$setting_email', 
setting_email_passwd = '$setting_email_passwd', 
setting_min_year ='$setting_min_year',
setting_pay_min='$percent11',
setting_timeout = '$time',
setting_term = '$setting_term',
setting_year = '$setting_year' WHERE setting_id=1");
 if($update){
 header('location:System_settings'); 
?><div class="bg-green-500 mb-2 border border-green-500 text-black px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">Success !</strong>
                                    <span class="block sm:inline">Settings Updated</span>
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


?>
         
				<div class="">
				 
                   <label class="block text-sm text-gray-600" for="cus_email">Number of Login Atempts</label>
				   <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="cus_name"  name="setting_login_attempts" value ="<?php echo $details_school['setting_login_attempts'];?>" type="number" min="1"   max="10"  placeholder="Login Atempts"   required>
                </div>
				<div class=""> 
                   <label class="block text-sm text-gray-600" for="cus_email">Inactive Login Timeout(In Min)</label>
                    <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="cus_name"  name="setting_timeout" value ="<?php echo ($details_school['setting_timeout']/60);?>"  type="number"  min="1"   max="5" placeholder="Email Paswd"   required>
                </div>
                <div class=""> 
                   <label class="block text-sm text-gray-600" for="setting_min_year">Student Min Age</label>   	 
 
                    <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="setting_min_year"  name="setting_min_year" value ="<?php echo $details_school['setting_min_year'];?>" type="number"  placeholder="Maximum Roles"   required>
                </div>
				<div class=""> 
                   <label class="block text-sm text-gray-600" for="cus_email">User Maximum Roles</label>
                    <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="cus_name"  name="setting_maxrole_no" value ="<?php echo $details_school['setting_maxrole_no'];?>" type="number"  min="1"   max="<?php echo $roles;?>" placeholder="Maximum Roles"   required>
                </div>
				
				<div class=""> 
                   <label class="block text-sm text-gray-600" for="cus_email">System Email</label>   	 
 
                    <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="cus_name"  name="setting_email" value ="<?php echo $details_school['setting_email'];?>" type="text"  placeholder="Maximum Roles"   required>
                </div>
				<div class=""> 
                   <label class="block text-sm text-gray-600" for="cus_email">System Email Password</label>
                    <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="cus_name"  name="setting_email_passwd" value ="<?php echo $details_school['setting_email_passwd'];?>" type="text"  placeholder="Email Paswd"   required>
                </div>
				<div class=""> 
                   <label class="block text-sm text-gray-600" for="cus_email">Min Payment in %</label>   	 
 
                    <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="setting_pay_min"  name="setting_pay_min" value ="<?php echo $percent;?>" type="text"  placeholder="Min Payment in %"   required>
                </div>
                 
                
                <div class="w-full md:w-2/2 px-3 mb-6 md:mb-0">
                            <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="access_level">Current Term</label>
                            <div class="relative">
                                <select name="setting_term" class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey" id="access_level">
                                    <option value="<?php echo $details_school['setting_term'];?>"><?php echo $details_school['setting_term'];?></option>
								    <option value="Term I">Term I</option>
                                   <option value="Term II">Term II</option>
                                   <option value="Term III">Term III</option>
								    
								    
								    
								    
								    
								   
                                </select>
                            </div>
                        </div>
                
                	<div class=""> 
                   <label class="block text-sm text-gray-600" for="cus_email">Current Year</label>   	 
 
                    <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" 
       id="setting_year" 
       name="setting_year" 
       value="<?php echo htmlspecialchars($details_school['setting_year'] ?? ''); ?>" 
       type="number" 
       placeholder="Enter year" 
       min="<?php echo date('Y'); ?>" 
       max="<?php echo date('Y') + 2; ?>"
       required>
                </div>
				 
				 
				 
				 
				 
				
				 
				 
                 
       <div class="mt-4">
                    <button type="submit" name ="Update" class="px-4 py-1 text-white font-light tracking-wider bg-green-500 rounded" type="submit">Update Security System Settings</button>
                </div>
                 </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>





