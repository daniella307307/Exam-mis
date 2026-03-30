 <?php include('header.php');
 $CLASS =$_GET['CLASS'];
 
 $select_class = mysqli_query($conn,"SELECT * FROM school_classes
left join school_levels ON school_classes.class_level = school_levels.level_id
WHERE class_id='$CLASS'");

$CLASS_DETAILS=mysqli_fetch_array($select_class);
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
                            <a href="Add_new_Payment_Settings?CLASS=<?PHP echo $CLASS;?>"><button class='bg-yellow-500 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'> <i class="fas fa-pen"></i> Add New</button></a>
                  
                            </div>
                            <center><h1><strong><?php echo $CLASS_DETAILS['level_name']."/".$CLASS_DETAILS['class_name'];?></strong> &nbsp;&nbsp;Fee Structure </h1></center>
                            <div class="p-3">
                                 
                                <table class="table-responsive w-full rounded">
                                    <thead>
                                      <tr>
									  <th class="border w-1/12 px-4 py-2">ID</th>
                                        <th class="border w-1/8 px-4 py-2">Payment Description</th>  
                                        <th class="border w-1/8 px-4 py-2">Amount</th> 
                                        <th class="border w-1/8 px-4 py-2">Term</th> 
                                         <th class="border w-1/8 px-4 py-2">Year</th> 
                                         <th class="border w-1/8 px-4 py-2">Action</th>  
                                      </tr>
                                    </thead>
                                    <tbody>
									<?php
								
										 
										$select_students = mysqli_query($conn,"SELECT * FROM school_payment_settings 
LEFT JOIN school_classes ON school_payment_settings.spayment_class = school_classes.class_id
LEFT JOIN school_levels ON school_payment_settings.spayment_level = school_levels.level_id WHERE spayment_class='$CLASS'");
											$students_n = mysqli_num_rows($select_students);
											
											while($users_details = mysqli_fetch_array($select_students)){	
											
									 ?><tr>
									     
									     <td class="border px-4 py-2"><?php echo $users_details['spayment_id'];?></td> 
                                            <td class="border px-4 py-2"><?php echo $users_details['spayment_description'];?></td> 
											<td class="border px-4 py-2"><?php echo $users_details['spayment_amount'];?></td> 
                                            	<td class="border px-4 py-2"><?php echo $users_details['spayment_term'];?></td> 
                                            	<td class="border px-4 py-2"><?php echo $users_details['spayment_year'];?></td> 
                                             
										 <td class="border px-4 py-2"> 
<a href="Edit_Payment_Settings?CLASS=<?php echo $CLASS;?>&SETTING=<?php echo $users_details['spayment_id'];?>"><button class='bg-green-500 hover:bg-yellow-500 text-white font-bold py-2 px-4 rounded'> <i class="fas fa-pen"></i> Edite</button></a>
<a href="Delete_Payment_Settings?CLASS=<?php echo $CLASS;?>&SETTING=<?php echo $users_details['spayment_id'];?>"><button class='bg-red-500 hover:bg-yellow-500 text-white font-bold py-2 px-4 rounded'> <i class="fas fa-trash"></i> Delete</button></a>
										 
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