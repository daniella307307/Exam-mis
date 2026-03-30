 <?php include('header.php');
 if(isset($_GET['STATUS'])){
	 $STATUS =$_GET['STATUS'];
	 if($STATUS=="Active"){
		$status ="Active"; 
	 }
	 else{
		$status ="Inactive";  
	 }
	$action = "Country_status ='$status'"; 
 }
 else{
$STATUS ="Active";	
$status = $STATUS; 
$action = "Country_status ='$STATUS'";
 }
 $select_user = mysqli_query($conn,"SELECT * FROM users  
LEFT JOIN user_permission ON users.access_level=user_permission.permissio_id
LEFT JOIN schools ON users.school_ref = schools.school_id  WHERE permission='School Facilitators'");
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
                                System Users &nbsp;  
								<a href="Users_per_school?SCHOOL=<?php echo $SCHOOL; ?>&STATUS=Active"><button class='bg-green-500 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded'>Active</button></a>  
                                <a href="Users_per_school?SCHOOL=<?php echo $SCHOOL; ?>&STATUS=Inactive"><button class='bg-red-500 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'>Inactive</button></a>  
                               <a href="Users_per_school?SCHOOL=<?php echo $SCHOOL; ?>&STATUS=Invited"><button class='bg-yellow-500 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'>Invited</button></a>  
                  
                            </div>
                            <div class="p-3">
                                <table class="table-responsive w-full rounded">
                                    <thead>
                                      <tr>
									  <th class="border w-1/12 px-4 py-2">ID</th>
                                        <th class="border w-1/8 px-4 py-2">Names</th> 
                                        <th class="border w-1/8 px-4 py-2">Pnone No</th> 										
                                        <th class="border w-1/9 px-4 py-2">User Role</th>
										<th class="border w-1/4 px-4 py-2">School</th>
                                        <th class="border w-1/7 px-4 py-2">Status</th>
                                        <th class="border w-1/7 px-4 py-2">Actions</th>
                                      </tr>
                                    </thead>
                                    <tbody>
									<?php
									while($users_details = mysqli_fetch_array($select_user)){
									?><tr>
                                            <td class="border px-4 py-2"><?php echo $users_details['user_id'];?></td>
                                            <td class="border px-4 py-2"> <?php echo $users_details['firstname']."&nbsp;".$users_details['lastname'];?></td>
                                            <td class="border px-4 py-2"> <?php echo $users_details['phone_number'];?></td>  
											<td class="border px-4 py-2"> <?php echo $users_details['permission'];?></td>
											<td class="border px-4 py-2"><?php echo $users_details['school_name'];?></td>
                                           
                                            <td class="border px-4 py-2">
											<?php
											if($users_details['status']=="Active"){
											?><i class="fas fa-check text-green-500 mx-2"></i><?php	
											}elseif($users_details['status']=="Inactive"){
												?><i class="fas fa-times text-red-500 mx-2"></i><?php	
											}elseif($users_details['status']=="Deleted"){
												?><i class="fas fa-trash text-red-500 mx-2"></i><?php	
											}
											elseif($users_details['status']=="Burned"){
												?><i class="fas fa-globe text-red-500 mx-2"></i><?php	
											}
											elseif($users_details['status']=="Suspended"){
												?><i class="fas fa-pause text-red-500 mx-2"></i><?php	
											}
											
											
											?>
                                                
                                            </td>
											
											
											
                                            <td class="border px-4 py-2">
                                                <a href="Users_details?ID=<?php echo $users_details['user_id'];?>" class="bg-teal-300 cursor-pointer rounded p-1 mx-1 text-white">
                                                        <i class="fas fa-eye"></i></a>
                                                <a href="Update_Users_details?ID=<?php echo $users_details['user_id'];?>" class="bg-teal-300 cursor-pointer rounded p-1 mx-1 text-white">
                                                        <i class="fas fa-edit"></i></a>
                                                <a href="Deactivate_Users?ID=<?php echo $users_details['user_id'];?>"class="bg-teal-300 cursor-pointer rounded p-1 mx-1 text-red-500">
                                                        <i class="fas fa-wrench"></i>
                                                </a>
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
        <footer class="bg-grey-darkest text-white p-2">
            <div class="flex flex-1 mx-auto">&copy; My Design</div>
        </footer>
        <!--/footer-->

    </div>

</div>

<script src="../../main.js"></script>

</body>

</html>