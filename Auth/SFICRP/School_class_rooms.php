 <?php include('header.php');
 if(isset($_GET['STATUS'])){
	 $STATUS =$_GET['STATUS'];
	 if($STATUS=="Active"){
		$status ="Active"; 
		$action = "class_status ='Active' AND class_school='$school_ref' AND class_country='$user_country'"; 
	 }
	 else{
		$status ="Inactive";  
		$action = "class_status !='Active' AND class_school='$school_ref' AND class_country='$user_country'"; 
	 }
	
 }   
 else{
$STATUS ="Active";	
$status = $STATUS; 
$action = "class_status ='$status' AND class_school='$school_ref'";
 }
 $select_user = mysqli_query($conn,"SELECT * FROM school_classes 
LEFT JOIN school_levels ON school_classes.class_level=school_levels.level_id
WHERE   $action");
 ?>
 
        <!--/Header-->

        <div class="flex flex-1">
            <!--Sidebar-->
            <?php include('dynamic_side_bar.php');?>
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
                                Class Settings &nbsp;  
								<a href="School_class_rooms?STATUS=Active"><button class='bg-green-500 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded'>Active</button></a>  
                                <a href="School_class_rooms?STATUS=Inactive"><button class='bg-red-500 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'>Inactive</button></a> &nbsp;&nbsp; 
                               <a href="Add_new_class"><button class='bg-yellow-500 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'> <i class="fas fa-pen"></i> Add New</button></a>  
                  
                            </div>
                            <div class="p-3">
                                <table class="table-responsive w-full rounded">
                                    <thead>
                                      <tr>
									  <th class="border w-1/12 px-4 py-2">ID</th>
                                        <th class="border w-1/8 px-4 py-2">Class Names</th> 
                                        <th class="border w-1/8 px-4 py-2">Level</th> 
                                        <th class="border w-1/8 px-4 py-2">No of Students</th> 
                                         <th class="border w-1/8 px-4 py-2">Status</th> 										
                                        <th class="border w-1/7 px-4 py-2">Actions</th>
                                      </tr>
                                    </thead>
                                    <tbody>   
									<?php
									while($users_details = mysqli_fetch_array($select_user)){
										$levels =$users_details['class_level'];
										$classes =$users_details['class_name'];
										$room_school=$users_details['class_school'];
										$select_students = mysqli_query($conn,"SELECT * FROM student_list WHERE student_class='$classes' AND student_level='$levels' AND student_school='$room_school'");
											$students_n = mysqli_num_rows($select_students);
									 ?><tr>
                                            <td class="border px-4 py-2"><?php echo $users_details['class_id'];?></td> 
											<td class="border px-4 py-2"><?php echo $users_details['class_name'];?></td>
											<td class="border px-4 py-2"> <?php echo $users_details['level_name'];?></td>
                                           <td class="border px-4 py-2"><?php echo $students_n;?><?php if($students_n>1){echo "&nbsp;Students";}else{echo "&nbsp;Student";};?></td>
                                            <td class="border px-4 py-2">
											 <i class="fas fa-check text-green-500 mx-2"></i> 
                                                
                                            </td>
											
											
											
                                            <td class="border px-4 py-2">
											<?php
											
											if($students_n>0){
											?> <a href="#" class="bg-green-500 cursor-pointer rounded p-1 mx-1 text-white">
                                                        <i class="fas fa-edit "></i>Can't modify</a><?php	
											}else{
											?><a href="Delete_classroom?BACK=<?php echo $users_details['class_id'];?>" class="bg-red-500 cursor-pointer rounded p-1 mx-1 text-white">
                                                        <i class="fas fa-trash"></i></a><?php	
											}
											?>
											
											
                                               
                                                
                                                
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