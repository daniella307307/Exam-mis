<?php
ob_start(); 
include('header.php');
if(isset($_GET['USER'])){
	 $ID =$_GET['USER'];
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
                <p class="text-gray-800 font-medium">Update &nbsp;<strong><?php echo $details_user['firstname']."&nbsp;".$details_user['lastname']?></strong>&nbsp; Details</p>
      <center><a href="Upload_user_profile_picture?USER=<?php echo $ID;?>"> <img onclick="profileToggle()" class="inline-block h-10 w-10 rounded-full" src="../<?php echo $details_user['user_image'];?>" alt=""></a></center> 
                   
	  
				
				<div class="">
				 
                   <label class="block text-sm text-gray-600" for="cus_email">First Name</label>
				   <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="cus_name"  name="firstname" value ="<?php echo $details_user['firstname'];?>" type="text" required="" placeholder="First Name" aria-label="Name">
                </div>
				<div class=""> 
                   <label class="block text-sm text-gray-600" for="cus_email">Last Name</label>
                    <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="cus_name"  name="lastname" value ="<?php echo $details_user['lastname'];?>" type="text" required="" placeholder="Last Name" aria-label="Name">
                </div>
			 
                 <div class="flex flex-wrap -mx-3 mb-2">
                    
					 
					 <div class="w-full md:w-1/1 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="grid-state">
                          Country
                        </label>
                        <div class="relative">
                            <select name="Country_ref"
                                class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey"
                                id="grid-state">
                                   	<?php
								$select_country= mysqli_query($conn,"SELECT * FROM countries
LEFT JOIN regions_table ON countries.Country_region =  regions_table.region_id WHERE Country_region='$user_region' AND  Country_status='Active'");
								while($find_country = mysqli_fetch_array($select_country)){
								?><option value="<?php echo $find_country['id']; ?>"><?php echo $find_country['Country_name']."@".$user_region."/".$find_country['region_name']; ?></option><?php	
								}
								?>
                               
                                 
                            </select>
                             
                        </div>
                    </div>
                    
                    
                </div>
<?php
if(isset($_POST['Update'])){
$Country_ref =$_POST['Country_ref']; 
 $select_region = mysqli_query($conn,"SELECT * FROM countries
LEFT JOIN regions_table ON countries.Country_region =  regions_table.region_id 
WHERE  id='$Country_ref'");
 $region_result = mysqli_fetch_array($select_region);
 $Country_region =$region_result['Country_region'];
$update = mysqli_query($conn,"UPDATE users SET 
 school_ref='0',
 user_country  = '$Country_ref',
 user_region = '$Country_region' WHERE user_id =$ID");
 if($update){
	header('location:Users'); 
?><div class="bg-green-300 mb-2 border border-red-300 text-red-dark px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">User Transfered Success fully</strong>
                                    <span class="block sm:inline">Make Sure to Assign School to this user</span>
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
                <div class="mt-4">
                    <button type="submit" name ="Update" class="px-4 py-1 text-white font-light tracking-wider bg-green-500 rounded" type="submit">Update User Details</button>
                </div>
                
            </form>
        </div>
    </div>
</div>

</body>
</html>






