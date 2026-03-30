 <?php include('header.php');
 
 $select_user_role = mysqli_query($conn,"SELECT * FROM  active_user_permission  
LEFT JOIN user_permission ON active_user_permission.active_permission  = user_permission.permissio_id
WHERE Active_user_ref='$session_id'");
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
                                System User Role &nbsp;  
								 
                            </div>
                            <div class="p-3">
                                <table class="table-responsive w-full rounded">
                                    <thead>
                                      <tr>
									  <th class="border w-1/12 px-4 py-2">ID</th>
                                        <th class="border w-1/8 px-4 py-2">Role</th> 
                                        <th class="border w-1/7 px-4 py-2">Status</th>
                                         
                                      </tr>
                                    </thead>
                                    <tbody>
									<?php
									$x=0;
									while($users_roles_detail = mysqli_fetch_array($select_user_role)){
									$x++;
									?><tr>
                                            <td class="border px-4 py-2"><?php echo $x;?></td>
                                            <td class="border px-4 py-2"> <?php echo $users_roles_detail['permission'];?></td>
                                            <td class="border px-4 py-2">
                                            <?php
										  if($users_roles_detail['permission_status']=="Active"){
										?><a href="#"><button class='bg-green-500 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded'>Active:: <?php echo $users_roles_detail['permission'];?></button></a><?php	  
										  }else{
?>  <a href="Update_Users_Role?Role=<?php echo $users_roles_detail['active_permission']; ?>&STATUS=Active"><button class='bg-red-500 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'>Switch here</button></a><?php	  
										  	  
										  } 
										   ?></td>
                                           
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