 <?php include('header.php');
 
 $select_user_role = mysqli_query($conn,"SELECT * FROM simulators WHERE sim_status='Active'");
 ?>

        <!--/Header-->
 
        <div class="flex flex-1">
            <!--Sidebar-->
            <?php include('Laboratory_side_bar.php');?>
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
									   <th class="border w-1/8 px-4 py-2">Name</th> 
                                        <th class="border w-1/8 px-4 py-2">Descrition</th> 
                                        <th class="border w-1/8 px-4 py-2">Field</th> 
                                        <th class="border w-1/7 px-4 py-2">Status</th>
                                         
                                      </tr>
                                    </thead>
                                    <tbody>
									<?php
									$x=0;
									while($users_roles_detail = mysqli_fetch_array($select_user_role)){
									$x++;
									?><tr>
									  
									    
                                            <td class="border px-4 py-2"><?php echo$users_roles_detail['sim_id'];?></td>
                                            <td class="border px-4 py-2"> <?php echo $users_roles_detail['sim_name'];?></td>
                                             <td class="border px-4 py-2"> <?php echo $users_roles_detail['sim_description'];?></td>
                                              <td class="border px-4 py-2"> <?php echo $users_roles_detail['sim_status'];?></td>
                                            <td class="border px-4 py-2">
                                            <?php
										  if($users_roles_detail['sim_status']=="Active"){
										?><a href="Open_Simulator?SIM=<?php echo$users_roles_detail['sim_id'];?>&STATUS=Active"><button class='bg-green-500 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded'>Open Simulator </button></a><?php	  
										  }else{
?>  <a href="#"><button class='bg-red-500 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'>Desabled</button></a><?php	  
										  	  
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