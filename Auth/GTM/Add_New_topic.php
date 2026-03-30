<?php
ob_start(); 
include('header.php');
$COURSE=$_GET['COURSE'];
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
                <p class="text-gray-800 font-medium">Add New Topic  To: <?php echo $certification_name;?> Certificate &nbsp;<strong> </strong> </p>
       		
				<div class="">
				 
                   <label class="block text-sm text-gray-600" for="cus_email">Topic  Details</label>
				   <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="cus_name"  name="topic_title" value ="" type="text"  placeholder="Topic Details" aria-label="Name" required>
                </div>
                <div class="">
				 
                   <label class="block text-sm text-gray-600" for="cus_email">Topic  Details in French</label>
				   <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="topic_french"  name="topic_french"  type="text"  placeholder="Topic Details" aria-label="Name" required>
                </div>	
				 
				  <div class="">
				 
                   <label class="block text-sm text-gray-600" for="topic_document">Topic Document</label>
				   <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="topic_document"  name="topic_document"  type="url"  placeholder="Topic Document URL" aria-label="Name" >
                </div> 
                 <div class="">
				 
                   <label class="block text-sm text-gray-600" for="topic_document">Topic Document in French</label>
				   <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="topic_document"  name="topic_document"  type="url"  placeholder="Topic Document URL" aria-label="Name" >
                </div> 
			
                <div class="">  
				 
                   <label class="block text-sm text-gray-600" for="topic_video">Topic Video</label>
				   <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="topic_video"  name="topic_video"    type="url"  placeholder="Topic Video URL" aria-label="Name">
                </div>
                
                
                 <div class="flex flex-wrap -mx-3 mb-2">
                    
					 <div class="w-full md:w-1/2 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="grid-state">
                           Week
                        </label>
                        <div class="relative">
                            <select name="topic_week"
                                class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey"
                                id="grid-state">
                               <?php
								$select_role= mysqli_query($conn,"SELECT * FROM learning_weeks WHERE 1");
								while($find_role = mysqli_fetch_array($select_role)){
								?><option value="<?php echo $find_role['week_id']; ?>"><?php echo $find_role['week_description']; ?></option><?php	
								}
								?>
                            </select>
                             
                        </div>
                    </div>
					 
                    <div class="w-full md:w-1/2 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="grid-state">
                          Status
                        </label>
                        <div class="relative">
                            <select name="topic_status" required
                                class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey"
                                id="grid-state">
								 <option value="">=== Status ===</option>
                                <option value="Active">Active</option>
								<option value="Inactive">Inactive</option>  
                            </select>
                             
                        </div>
                    </div>
                    
                </div>
<?php
if(isset($_POST['Update'])){
$topic_course =$COURSE;        
$topic_week =mysqli_real_escape_string($conn,$_POST['topic_week']);
$topic_title =mysqli_real_escape_string($conn,$_POST['topic_title']);
$topic_french =mysqli_real_escape_string($conn,$_POST['topic_french']);
$topic_document    =mysqli_real_escape_string($conn,$_POST['topic_document']);
$topic_document_french   =mysqli_real_escape_string($conn,$_POST['topic_document_french']);
$topic_video  =mysqli_real_escape_string($conn,$_POST['topic_video']);



$topic_certification =$CERTIFICATE;
$topic_status =$STATUS;  
$select =mysqli_num_rows(mysqli_query($conn,"SELECT * FROM learning_topics WHERE topic_title='$topic_title' AND topic_course='$topic_course'")); 
if($select>0){
?><div class="bg-red-300 mb-2 border border-red-300 text-red-dark px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">Oops!</strong>
                                    <span class="block sm:inline">This |Topic:<strong class="font-bold"><?php echo $topic_title;?> </strong>Existe in the system!!!.</span>
                                    <span class="absolute top-0 top-0 right-0 px-4 py-3">
                                      <svg class="fill-current h-6 w-6 text-red" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                                    </span>
                                </div><?php 	
}
else{
$Add_data = mysqli_query($conn,"INSERT INTO learning_topics (topic_id,topic_course,topic_week, topic_title,topic_french,topic_certification,topic_document,topic_document_french,topic_video, topic_status) VALUES 
                                                          (NULL, '$topic_course', '$topic_week', '$topic_title','$topic_french', '$topic_certification','$topic_document','$topic_document_french','$topic_video', '$topic_status')");
if($Add_data){
	header('location:Module_topics?COURSE='.$topic_course.'&CERTIFICATE='.$topic_certification.'&STATUS='.$topic_status.''); 
?><div class="bg-green-300 mb-2 border border-red-300 text-red-dark px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">Topic Added!</strong>
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
                    <center><button type="submit" name ="Update" class="px-4 py-1 text-white font-light tracking-wider bg-green-500 rounded" type="submit">Add New Topic  Details</button></center>
                </div>
                
            </form>
        </div>
    </div>
</div>

</body>
</html>






