
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
                <p class="text-gray-800 font-medium">Add  New Class in <?php echo $school_name;?></strong></p>
   <?php
if(isset($_POST['Update'])){
 
$Class_Name =$_POST['Class_Name']; 
$Class_Level =$_POST['Class_Level'];
$class_status =$_POST['class_status'];
 
$select_loc = mysqli_query($conn,"SELECT * FROM school_classes WHERE class_name='$Class_Name' AND class_school='$school_ref' AND class_level='$Class_Level'");
$select_num =mysqli_num_rows($select_loc);
if($select_num>0){
?><div class="bg-red-300 mb-2 border border-red-300 text-red-dark px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">Duplicate Error</strong>
                                    <span class="block sm:inline">This Class has been asigned to  <?php echo $school_name;?> .Try with different Class !!!.</span>
                                    
                                </div><?php	
}
else{ 
$update = mysqli_query($conn,"INSERT INTO school_classes (class_id,class_name,class_level,class_school,class_country,class_status) VALUES 
                                                          (NULL, '$Class_Name', '$Class_Level', '$school_ref', '$user_country', '$class_status')");
 if($update){
	 header('location:School_class_rooms'); 
	 
	
?><div class="bg-green-300 mb-2 border border-green-300 text-white px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">Success !</strong>
                                    <span class="block sm:inline">Class Inserted !!!.</span>
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
        
	 
                 <div class="flex flex-wrap -mx-3 mb-2">
          
				  <div class="w-full md:w-1/1 px-3 mb-6 md:mb-0">
					 <div class="w-full md:w-3/3 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="grid-state">
                           Level Name
                        </label>
                        <div class="relative">
                            <select name="Class_Level"
                                class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey"
                                id="Class_Level" required>
								 <option value="">===========Select Level ========</option> 	
								
                              <?php
								$select_class= mysqli_query($conn,"SELECT * FROM school_levels WHERE 1");    
								while($find_class = mysqli_fetch_array($select_class)){
								?><option value="<?php echo $find_class['level_id']; ?>"><?php echo $find_class['level_name']; ?></option><?php	
								}
								 
								?>
                            </select>
							
                               
                        </div>
						
                    </div>   
                </div>
                 <div class="w-full mb-6">
                        <label class="block text-gray-700 text-sm mb-1">Class Name</label>
                        <input type="text" name="Class_Name" class="w-full px-4 py-2 rounded bg-gray-200" required>
                    </div>

				 <div class="w-full md:w-1/1 px-3 mb-6 md:mb-0">
					 <div class="w-full md:w-3/3 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="grid-state">
                           STATUS
                        </label>
                        <div class="relative">
                            <select name="class_status"
                                class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey"
                                id="class_status" required>
								 <option value="">===========Select STATUS ========</option> 	
								<option value="Active">Active</option>
									<option value="Inactive">Inactive</option>
                              
                            </select>
							
                               
                        </div>
						
                    </div>   
                </div>
        <div class="mt-4">
                    <center><button type="submit" name ="Update" class="px-4 py-1 text-white font-light tracking-wider bg-green-500 rounded" type="submit">Update User Details</button></center>
                </div> 
                
            </form>
        </div>
    </div>
</div>  
 
</body>
</html>















