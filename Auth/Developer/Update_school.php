<?php
ob_start(); 
include('header.php');
if(isset($_GET['ID'])){
	 $ID =$_GET['ID'];
 }
 
 $details_school =mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM schools
LEFT JOIN countries ON schools.country_ref= countries.id   WHERE school_id='$ID'"));
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
                <p class="text-gray-800 font-medium">Update &nbsp;<strong><?php echo $details_school['school_name'];?></strong>&nbsp; Details</p>
    <div class="">
				 
                   <label class="block text-sm text-gray-600" for="cus_email">ID</label>
                    <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="cus_name" name="school_id" value ="<?php echo $details_school['school_id'];?>" type="text" required="" placeholder="ID" aria-label="Name" readonly>
                </div>
				 
				<div class="">
				 
                   <label class="block text-sm text-gray-600" for="cus_email">School Name</label>
				   <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="cus_name"  name="school_name" value ="<?php echo $details_school['school_name'];?>" type="text" required="" placeholder="School Name" aria-label="Name">
                </div>
				<div class=""> 
                   <label class="block text-sm text-gray-600" for="cus_email">Abreviation</label>
                    <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="cus_name"  name="school_abreviation" value ="<?php echo $details_school['school_abreviation'];?>" type="text" required="" placeholder="Abreviation" aria-label="Name">
                </div>
				 
                 <div class="flex flex-wrap -mx-3 mb-2">
                    <div class="w-full md:w-2/2 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="grid-state">
                           Country Location  
                        </label>  
                         <div class="relative">
                            <select name="Country_Location"
                                class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey"
                                id="grid-state">
								 <option value="<?php echo  $details_school['id']; ?>"><?php echo  $details_school['Country_name']; ?></option> 	
                               <?php
								$select_role= mysqli_query($conn,"SELECT * FROM countries WHERE Country_status='Active'");
								while($find_role = mysqli_fetch_array($select_role)){
								?><option value="<?php echo $find_role['id']; ?>"><?php echo $find_role['Country_name']; ?></option><?php	
								}
								?>
                            </select>   
                             
                        </div>
                    </div>
					<div class="w-full md:w-2/2 px-3 mb-6 md:mb-0">
                            <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="access_level">Language</label>
                            <div class="relative">
                                <select name="school_language" class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey" id="access_level">
 <option value="<?php echo $details_school['school_language']; ?>"><?php echo $details_school['school_language']; ?></option> 
 <option value="English">English</option> 
  <option value="French">French</option> 
  <option value="Bilingual">Bilingual</option> 
                                </select>
                            </div>
                        </div>
					
					<div class="w-full md:w-2/2 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="grid-state">
                           Status 
                        </label>  
                        <div class="relative">
                            <select name="school_status"
                                class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey"
                                id="grid-state">
							 <option value="<?php echo $details_school['school_status']; ?>"><?php echo $details_school['school_status']; ?></option> 
                             <option value="Active">Active</option> 
                             <option value="Deleted">Delete</option>
                             <option value="Blocked">Block</option> 
                            </select>
                             
                        </div>
                    </div>
					 
<?php
if(isset($_POST['Update'])){
$school_status  =  mysqli_real_escape_string($conn,$_POST['school_status']); 
$school_abreviation  =  mysqli_real_escape_string($conn,$_POST['school_abreviation']); 
$Country_Location   =  mysqli_real_escape_string($conn,$_POST['Country_Location']);
$school_language =mysqli_real_escape_string($conn,$_POST['school_language']);  
$school_name =  mysqli_real_escape_string($conn,$_POST['school_name']);  
 $count = mysqli_num_rows(mysqli_query($conn,"SELECT * FROM schools WHERE school_id!='$ID' AND (school_name='$school_name' OR school_abreviation='$school_abreviation')")); 

if($count>1){
?><div class="bg-green-300 mb-2 border border-red-300 text-red-dark px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">Oops!</strong>
                                    <span class="block sm:inline">This School <?php echo $school_name;?> Exist</span>
                                    <span class="absolute top-0 top-0 right-0 px-4 py-3">
                                      <svg class="fill-current h-6 w-6 text-red" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                                    </span>
                                </div><?php	
}
else{
$update = mysqli_query($conn,"UPDATE schools SET 
school_name = '$school_name', 
school_abreviation = '$school_abreviation', 
country_ref ='$Country_Location',
school_status = '$school_status',
school_language ='$school_language' WHERE  school_id =$ID");
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
                    <button type="submit" name ="Update" class="px-4 py-1 text-white font-light tracking-wider bg-green-500 rounded" type="submit">Update School</button>
                </div>
                
            </form>
        </div>
    </div>
</div>

</body>
</html>






