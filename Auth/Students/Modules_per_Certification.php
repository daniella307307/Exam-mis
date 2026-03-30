 <?php include('header.php');
 include('Access.php');
 $CERTIFICATE =$_GET['CERTIFICATE'];
 
 $selct_cert = mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM certifications WHERE certification_id='$CERTIFICATE'"));
 $certification_name =$selct_cert['certification_name'];
 $certification_duration =$selct_cert['certification_duration'];
 if(isset($_GET['STATUS'])){
	 $STATUS =$_GET['STATUS'];
	 if($STATUS=="Active"){
		$status ="Active"; 
	 }
	 else{
		$status ="Inactive";  
	 }
	$action = "course_certificate='$CERTIFICATE' AND course_status ='$status'"; 
 }
 else{
$STATUS ="Active";	
$status = $STATUS; 
$action = "course_certificate='$CERTIFICATE' AND course_status ='$STATUS'";
 }
 $select_modules = mysqli_query($conn,"SELECT * FROM certification_courses WHERE  $action");
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
					<a href="Modules_per_Certification?CERTIFICATE=<?php echo $CERTIFICATE;?>&STATUS=Active"><button class='bg-green-500 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded'>Active</button></a>  
                   <a href="Modules_per_Certification?CERTIFICATE=<?php echo $CERTIFICATE;?>&STATUS=Inactive"><button class='bg-red-500 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'>Inactive</button></a>  
                           
							
							</div>
                            <div class="p-3">
							 <p><strong><big><?php echo $certification_name;?> Courses</big></strong></p> 
							 <p><strong><big>Duration:<?php echo $certification_duration;?> &nbsp;months</big></strong></p><br>
							    <table class="table-responsive w-full rounded">
                                    <thead>
                                      <tr>
                                        <th class="border w-1/12 px-4 py-2">#</th>
										<th class="border w-1/8 px-4 py-2">Module Code</th>
                                        <th class="border w-1/8 px-4 py-2">Module Name</th> 										
                                        <th class="border w-1/10 px-4 py-2">Status</th> 
                                      </tr>
                                    </thead>
                                    <tbody>
									<?php
									
									          
									
								 
									
									while($module_details = mysqli_fetch_array($select_modules)){
									?><tr>   
									 <td class="border px-4 py-1"><?php echo $module_details['course_id'];?></td>
                                          
                                            <td class="border px-4 py-1"><a href="Module_topics?COURSE=<?php echo $module_details['course_id'];?>&CERTIFICATE=<?php echo $CERTIFICATE;?>"><?php echo $module_details['course_code'];?> <i class="fas fa-book text-green-500 mx-2"></i></a></td>
                                           <td class="border px-4 py-2"><?php echo $module_details['course_name'];?> </td>
										   
                                              <td class="border px-4 py-2">
											<?php if($STATUS =="Active"){
												?> <i class="fas fa-unlock text-green-500 mx-2"></i><?php
											}else{
											?> <i class="fas fa-lock text-red-500 mx-2"></i><?php	
											}?>
                                               
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