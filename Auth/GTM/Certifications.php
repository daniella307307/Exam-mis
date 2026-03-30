 <?php include('header.php');
 if(isset($_GET['STATUS'])){
	 $STATUS =$_GET['STATUS'];
	 if($STATUS=="Active"){
		$status ="Active"; 
	 }
	 else{
		$status ="Inactive";  
	 }
	$action = "certification_status ='$status'"; 
 }
 else{
$STATUS ="Active";	
$status = $STATUS; 
$action = "certification_status ='$STATUS'";
 }
 $select_certifications = mysqli_query($conn,"SELECT * FROM certifications  WHERE  $action");
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
					<a href="Certifications?STATUS=Active"><button class='bg-green-500 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded'>Active</button></a>  
                   <a href="Certifications?STATUS=Inactive"><button class='bg-red-500 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'>Inactive</button></a>  
                    <a href="Add_Certifications"><button class='bg-blue-500 hover:bg-green-800 text-white font-bold py-2 px-4 rounded'>Add New</button></a>  
                           
							
							</div>
                            <div class="p-3">
                                <table class="table-responsive w-full rounded">
                                    <thead>
                                      <tr>
                                        <th class="border w-1/12 px-4 py-2">ID</th>
                                        <th class="border w-1/8 px-4 py-2">Certification Name</th>
                                         <th class="border w-1/8 px-4 py-2">Training Period</th>										
                                        <th class="border w-1/10 px-4 py-2">Status</th>
                                        <th class="border w-1/5 px-4 py-2">Actions</th>
                                      </tr>
                                    </thead>
                                    <tbody>
									<?php
									while($certificate_details = mysqli_fetch_array($select_certifications)){
									?><tr>      
                                            <td class="border px-4 py-1"><?php echo $certificate_details['certification_id'];?></td>
                                            <td class="border px-4 py-2"> <a href="Modules_per_Certification?CERTIFICATE=<?php echo $certificate_details['certification_id'];?>" class='bg-white hover:bg-blue-200 text-blue-800 font-bold py-2 px-4 rounded' ><?php echo $certificate_details['certification_name'];?></a></td>
											<td class="border px-4 py-2"><?php echo $certificate_details['certification_duration'];?> Months</td>
                                             <td class="border px-4 py-2">
											<?php if($STATUS =="Active"){
												?> <i class="fas fa-unlock text-green-500 mx-2"></i><?php
											}else{
											?> <i class="fas fa-lock text-red-500 mx-2"></i><?php	
											}?>
                                               
                                            </td>
                                            <td class="border px-4 py-2">
                                              
                                               
                                               <?php if($STATUS =="Active"){
												?> <a href="Update_Certification?CURRENT=<?php echo $STATUS;?>&STATUS=Inactive&ID=<?php echo $certificate_details['certification_id'];?>" class="bg-teal-300 cursor-pointer rounded p-1 mx-1 text-white">
                                                       <i class="fas fa-lock text-red-500 mx-2"></i>  </a><?php
											}else{
											?> <a href="Update_Certification?CURRENT=<?php echo $STATUS;?>&STATUS=Active&ID=<?php echo $certificate_details['certification_id'];?>" class="bg-teal-300 cursor-pointer rounded p-1 mx-1 text-white">
                                                       <i class="fas fa-unlock text-green-500 mx-2"></i></a><?php	
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