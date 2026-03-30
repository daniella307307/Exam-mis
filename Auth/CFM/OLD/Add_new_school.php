<?php
ob_start(); 
include('header.php');
 
 
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
                <p class="text-gray-800 font-medium"><strong>Add New School  &nbsp;</strong> Details</p>
    
				 
				<div class="">
				 
                   <label class="block text-sm text-gray-600" for="cus_email">School Name</label>
				   <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="cus_name"  name="school_name"   type="text" required  placeholder="School Name" aria-label="Name">
                </div>
				<div class=""> 
                   <label class="block text-sm text-gray-600" for="cus_email">Abreviation</label>
                    <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="cus_name"  name="school_abreviation"   type="text" required  placeholder="Abreviation" aria-label="Name">
                </div>
                	 
				<div class=""> 
                   <label class="block text-sm text-gray-600" for="cus_email">Country</label>
                    <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="currency_usd"  name="Country" value ="<?php echo $Country_name;?>" type="text" required  placeholder="usd Value" aria-label="Name">
               <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="currency_usd"  name="Country_Location" value ="<?php echo $user_country;?>" type="hidden" required  placeholder="usd Value" aria-label="Name">
                
			   </div>
			   <div class=""> 
                   <label class="block text-sm text-gray-600" for="cus_email">Region</label>
                    <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="currency_usd"  name="Region" value ="<?php echo $region_name;?>" type="text" required  placeholder="usd Value" aria-label="Name">
               <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="currency_usd"  name="region_Location" value ="<?php echo  $region_id ;?>" type="hidden" required  placeholder="usd Value" aria-label="Name">
                
			   </div>
				 
                  
                  
					 
					 
<?php
if(isset($_POST['Add_school'])){
$school_name	 =  mysqli_real_escape_string($conn,$_POST['school_name']);  
$school_abreviation  =  mysqli_real_escape_string($conn,$_POST['school_abreviation']); 
$Country_Location   =  mysqli_real_escape_string($conn,$_POST['Country_Location']);
$region_Location   =  mysqli_real_escape_string($conn,$_POST['region_Location']);
$school_status  =  mysqli_real_escape_string($conn,$_POST['school_status']);
 
$count = mysqli_num_rows(mysqli_query($conn,"SELECT * FROM schools WHERE (school_name='$school_name' OR school_abreviation='$school_abreviation')")); 

if($count>0){
?><div class="bg-green-300 mb-2 border border-red-300 text-red-dark px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">Oops!</strong>
                                    <span class="block sm:inline">This School <?php echo $school_name;?> Exist</span>
                                    <span class="absolute top-0 top-0 right-0 px-4 py-3">
                                      <svg class="fill-current h-6 w-6 text-red" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                                    </span>
                                </div><?php	
}
else{
$update = mysqli_query($conn,"INSERT INTO schools (school_id, school_name, school_abreviation, country_ref, school_region, school_status) VALUES 
                                                   (NULL, '$school_name', '$school_abreviation', '$Country_Location', '$region_Location', 'Active')");
 if($update){
	header('location:Schools'); 
?><div class="bg-green-300 mb-2 border border-red-300 text-red-dark px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">Oops!</strong>
                                    <span class="block sm:inline">Something went wrong Try again !!!.</span>
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
                   <center> <button type="submit" name ="Add_school" class="px-4 py-1 text-white font-light tracking-wider bg-green-500 rounded" type="submit">Add New School</button></center>
                </div>
                
            </form>
        </div>
    </div>
</div>

</body>
</html>






