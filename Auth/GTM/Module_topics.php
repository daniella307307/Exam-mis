 <?php include('header.php');
 $CERTIFICATE =$_GET['CERTIFICATE'];
 $COURSE =$_GET['COURSE']; 
 $selct_cert = mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM certification_courses
LEFT JOIN certifications ON certification_courses.course_certificate = certifications.certification_id WHERE course_id ='$COURSE'"));
 $course_name =$selct_cert['course_name']; 
 $certification_name = $selct_cert['certification_name'];
 if(isset($_GET['STATUS'])){
	 $STATUS =$_GET['STATUS'];
	 if($STATUS=="Active"){
		$status ="Active"; 
	 }
	 else{
		$status ="Inactive";  
	 }
	$action = "topic_course='$COURSE' AND topic_status ='$status'"; 
 }
 else{
$STATUS ="Active";	
$status = $STATUS; 
$action = "topic_course='$COURSE' AND topic_status ='$STATUS'";
 }
 $select_modules = mysqli_query($conn,"SELECT * FROM learning_topics 
LEFT JOIN learning_weeks ON learning_topics.topic_week = learning_weeks.week_id WHERE  $action");
 ?>
  
        <!--/Header-->

        <div class="flex flex-1">
            <!--Sidebar-->
           <?php include('side_bar_courses.php');?>
            <!--/Sidebar-->
            <!--Main-->
            <main class="bg-white-500 flex-1 p-3 overflow-hidden">

                <div class="flex flex-col">
                    <!-- Card Sextion Starts Here -->
                    <div class="flex flex-1  flex-col md:flex-row lg:flex-row mx-2">
                        <!--Horizontal form-->
                        
                        <!--/Horizontal form-->

                        <!--Underline form-->
                         
                        <!--/Underline form-->
                    </div>
                    <!-- /Cards Section Ends Here -->

                    <!--Grid Form-->

                    <div class="flex flex-1  flex-col md:flex-row lg:flex-row mx-2">
                        <div class="mb-2 border-solid border-gray-300 rounded border shadow-sm w-full">
                            <div class="bg-gray-200 px-2 py-3 border-solid border-gray-200 border-b">
                               Certification 
					<a href="Module_topics?COURSE=<?php echo $COURSE;?>&CERTIFICATE=<?php echo $CERTIFICATE;?>&STATUS=Active"><button class='bg-green-500 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded'>Active</button></a>  
                   <a href="Module_topics?COURSE=<?php echo $COURSE;?>&CERTIFICATE=<?php echo $CERTIFICATE;?>&STATUS=Inactive"><button class='bg-red-500 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'>Inactive</button></a>  
                   <a href="Add_New_topic?COURSE=<?php echo $COURSE;?>&CERTIFICATE=<?php echo $CERTIFICATE;?>&STATUS=<?php echo $status;?>"><button class='bg-blue-500 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'>Add New</button></a>  
                           
							
							</div>
                            <div class="p-3">
							<p><strong><big>Main  Topics of the course </big></strong></p> 
							 
							 <p>Course Name:&nbsp;<strong><big><?php echo $course_name ;?></big></strong></p>
							 <p>Certificate Name:&nbsp;<strong><big><a href="Modules_per_Certification?CERTIFICATE=<?php echo $CERTIFICATE;?>"><button class='bg-blue-500 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'><?php echo $certification_name ;?></button></a></big></strong></p> <br> 
							<p>Status : &nbsp;<strong><big><?php echo $status;?></big></strong></p> <br> 
							<table class="table-responsive w-full rounded">
                                    <thead>
                                      <tr>
                                        <th class="border w-1/12 px-4 py-2">#</th>
										<th class="border w-1/8 px-4 py-2">WEEK</th>
                                        <th class="border w-1/8 px-4 py-2">Topic Details</th> 
                                        <th class="border w-1/10 px-4 py-2">Status</th>
                                        <th class="border w-1/10 px-4 py-2">Video</th>
                                        <th class="border w-1/5 px-4 py-2">Actions</th>
                                      </tr>
                                    </thead>
                                    <tbody>
									<?php
									
									          
									
									
									
									while($module_details = mysqli_fetch_array($select_modules)){
									?><tr>   
									 <td class="border px-4 py-1"><?php echo $module_details['topic_id'];?></td>
                                          
                                            <td class="border px-4 py-1"><?php echo $module_details['week_description'];?> </td>
                                           <td class="border px-4 py-2"><?php echo $module_details['topic_title'];?> </td>
										   
                                              <td class="border px-4 py-2">
											<?php if($STATUS =="Active"){
												?> <i class="fas fa-unlock text-green-500 mx-2"></i><?php
											}else{
											?> <i class="fas fa-lock text-red-500 mx-2"></i><?php	
											}?>
                                               
                                            </td>
                                            <td class="border px-4 py-2">
                                              
                                               
                                              <?php if(!empty($module_details['topic_video'])){
                                              ?><a href="Update_topic_details?COURSE=<?php echo $COURSE;?>&CERTIFICATE=<?php echo $CERTIFICATE;?>&CURRENT=<?php echo $STATUS;?>&STATUS=Active&ID=<?php echo $module_details['topic_id'];?>" class="bg-teal-300 cursor-pointer rounded p-1 mx-1 text-white">
                                                       <i class="fas fa-video text-green-500 mx-2"></i></a><?php
                                              
                                              }else{
                                             ?><a href="Update_topic_details?COURSE=<?php echo $COURSE;?>&CERTIFICATE=<?php echo $CERTIFICATE;?>&CURRENT=<?php echo $STATUS;?>&STATUS=Active&ID=<?php echo $module_details['topic_id'];?>" class="bg-teal-300 cursor-pointer rounded p-1 mx-1 text-white">
                                                       <i class="fas fa-video text-red-500 mx-2"></i></a><?php 
                                              }?>
                                             	
											  
                                            </td>
                                             <td class="border px-4 py-2">
                                              
                                               
                                               <?php if($STATUS =="Active"){
												?> <a href="Update_topic?COURSE=<?php echo $COURSE;?>&CERTIFICATE=<?php echo $CERTIFICATE;?>&CURRENT=<?php echo $STATUS;?>&STATUS=Inactive&ID=<?php echo $module_details['topic_id'];?>" class="bg-teal-300 cursor-pointer rounded p-1 mx-1 text-white">
                                                       <i class="fas fa-lock text-red-500 mx-2"></i>  </a><?php
											}else{ 
											?> <a href="Update_topic?COURSE=<?php echo $COURSE;?>&CERTIFICATE=<?php echo $CERTIFICATE;?>&CURRENT=<?php echo $STATUS;?>&STATUS=Active&ID=<?php echo $module_details['topic_id'];?>" class="bg-teal-300 cursor-pointer rounded p-1 mx-1 text-white">
                                                       <i class="fas fa-unlock text-green-500 mx-2"></i></a><?php	
											}?>
                                            <a href="Update_topic_details?COURSE=<?php echo $COURSE;?>&CERTIFICATE=<?php echo $CERTIFICATE;?>&CURRENT=<?php echo $STATUS;?>&STATUS=Active&ID=<?php echo $module_details['topic_id'];?>" class="bg-teal-300 cursor-pointer rounded p-1 mx-1 text-white">
                                                       <i class="fas fa-pen text-green-500 mx-2"></i></a> 	
											  
                                            </td>
                                            
                                        </tr><?php	
										
									}
									
									
									?>
                                        
										
                                        
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!--/Grid Form-->
                </div>
            </main>
            <!--/Main-->
        </div>
        <!--Footer-->
        <?php include('footer.php')?>
        <!--/footer-->

    </div>

</div>

<script src="../../main.js"></script>

</body>

</html>