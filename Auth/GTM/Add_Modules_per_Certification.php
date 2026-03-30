<?php
ob_start(); 
include('header.php');
//$COURSE=$_GET['COURSE'];
$CERTIFICATE=$_GET['CERTIFICATE'];
$STATUS=$_GET['STATUS'];

 $details_user =mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM certifications WHERE certification_id='$CERTIFICATE'"));
$certification_name = $details_user['certification_name'];
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
                <p class="text-gray-800 font-medium">Add New Module To:<br> <strong><strong><?php echo $certification_name;?> Certificate &nbsp;</strong> </strong> </p>
       		<div class="">
				 
                   <label class="block text-sm text-gray-600" for="cus_email">Module Code</label>
				   <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="cus_name"  name="course_code" value ="" type="text"  placeholder="Module Code" aria-label="Name" required>
                </div>
				
				<div class="">
				 
                   <label class="block text-sm text-gray-600" for="cus_email">Module Name</label>
				   <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="cus_name"  name="course_name" value ="" type="text"  placeholder="Module Name" aria-label="Name" required>
                </div>
				 	<div class="">
				 
                   <label class="block text-sm text-gray-600" for="cus_email">Module Name French</label>
				   <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="course_french"  name="course_french" value ="" type="text"  placeholder="Module Name in French" aria-label="Name" required>
                </div>
				 
                
                
                 <div class="flex flex-wrap -mx-3 mb-2">
                  
					 
                    <div class="w-full md:w-1/1 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="grid-state">
                          Status
                        </label>
                        <div class="relative">
                            <select name="course_status"
                                class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey"
                                id="grid-state">
								 <option value="">=========== Select  Status =============</option>
                                <option value="Active">Active</option>
								<option value="Inactive">Inactive</option>  
                            </select>
                             
                        </div>
                    </div>
                    
                </div>
<?php
if(isset($_POST['Update'])){
//$topic_course =$COURSE; 
        
$course_code =mysqli_real_escape_string($conn,$_POST['course_code']);
$course_name =mysqli_real_escape_string($conn,$_POST['course_name']);
$course_french =mysqli_real_escape_string($conn,$_POST['course_french']);
$course_status =mysqli_real_escape_string($conn,$_POST['course_status']);
$topic_certification =$CERTIFICATE;
$topic_status =$STATUS;  
$select =mysqli_num_rows(mysqli_query($conn,"SELECT * FROM certification_courses WHERE course_name='$course_name' OR course_code='$course_code'")); 
if($select>0){
?><div class="bg-red-300 mb-2 border border-red-300 text-red-dark px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">Oops!</strong>
                                    <span class="block sm:inline">This | Couser:<strong class="font-bold"><?php echo $course_name>" or code:".$course_code;?> </strong>Existe in the system!!!.</span>
                                    <span class="absolute top-0 top-0 right-0 px-4 py-3">
                                      <svg class="fill-current h-6 w-6 text-red" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                                    </span>
                                </div><?php 	
}
else{
$Add_data = mysqli_query($conn,"INSERT INTO certification_courses (course_id, course_code, course_name,course_french, course_certificate, course_status) VALUES
                                                                  (NULL, '$course_code', '$course_name','$course_french', '$CERTIFICATE', '$course_status')");
if($Add_data){
	header('location:Modules_per_Certification?CERTIFICATE='.$topic_certification.'&STATUS='.$topic_status.''); 
?><div class="bg-green-300 mb-2 border border-red-300 text-red-dark px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">Module Added!</strong>
                                    <span class="block sm:inline">Redirecting to the main page !!!.</span>
                                    <span class="absolute top-0 top-0 right-0 px-4 py-3">
                                       </span>
                                </div><?php 	
	
 }
 else{
	?><div class="bg-red-300 mb-2 border border-red-300 text-red-dark px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">Oops!</strong>
                                    <span class="block sm:inline">Something went wrong Try again !!!.</span>
                                    <span class="absolute top-0 top-0 right-0 px-4 py-3">
                                      </span>
                                </div><?php 
 }	
} 

}


?>
                <div class="mt-4">
                    <center><button type="submit" name ="Update" class="px-4 py-1 text-white font-light tracking-wider bg-green-500 rounded" type="submit">Add New  Module  Details</button></center>
                </div>
                
            </form>
        </div>
    </div>
</div>

</body>
</html>






