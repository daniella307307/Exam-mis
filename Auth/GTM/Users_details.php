 <?php include('header.php');
 if(isset($_GET['ID'])){
	 $ID =$_GET['ID'];
 }
 
 $details_user =mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM users  
LEFT JOIN user_permission ON users.access_level=user_permission.permissio_id
LEFT JOIN schools ON users.school_ref = schools.school_id WHERE user_id=$ID"));
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
        
   <div style="position:center" class="mb-2 border-solid border-green-300 rounded border shadow-sm w-full">
                            <div class="bg-green-200 px-2 py-3 border-solid border-gray-200 border-b ">
                          <big><strong><?php echo $details_user['firstname']."&nbsp;".$details_user['lastname']; ?> </strong></big> Details  &nbsp; &nbsp; &nbsp; <a href="Users"><button class='bg-green-500 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded'><i class="fas fa-users angle-right float-left"></i> &nbsp; Back</button></a>  
                               
							
				 <div class="flex flex-1 flex-col md:flex-row lg:flex-row mx-2">
                        <div class="shadow-lg bg-white-vibrant border-l-8 hover:bg-red-vibrant-dark border-red-vibrant-dark mb-2 p-2 md:w-1/2 mx-2">
                              <img src="./../../<?php echo $details_user['user_image']; ?>" alt="John" style="width:10%,Hight:10%">
  
                        </div>
						 <div class="shadow-lg bg-white-vibrant border-l-8 hover:bg-red-vibrant-dark border-red-vibrant-dark mb-2 p-2 md:w-1/2 mx-2">
                             <p>&nbsp;ID:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong><?php echo $details_user['user_id']; ?></strong></p>
  <p>&nbsp;First Name :&nbsp;<strong><?php echo $details_user['firstname']; ?></strong></p>
  <p>&nbsp;Last Name :&nbsp;<strong><?php echo  $details_user['lastname']; ?></strong></p>
  <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Phone :&nbsp;<strong><?php echo  $details_user['phone_number']; ?></strong></p>
  <p>&nbsp;&nbsp;User Email:&nbsp;<strong><?php echo  $details_user['email_address']; ?></strong></p>
  <p>&nbsp;&nbsp;User School:&nbsp;<strong><?php echo  $details_user['school_name']; ?></strong></p>
  
  <p>&nbsp;&nbsp;&nbsp;&nbsp;User Role:&nbsp;<strong><?php echo  $details_user['permission']; ?></strong></p>
   <p>&nbsp;User Status:&nbsp;<strong><?php echo  $details_user['status']; ?></strong></p>
                        </div>
</div>						
							
							
							
							
							
							
							  
 
 
  
  </div>
    
							
                                <!--<table class="table-responsive w-full rounded">
                                    <thead>
                                      <tr>
									  <th class="border w-1/6 px-4 py-2">Details</th>
                                        <th class="border w-1/6 px-4 py-2">Names</th>  
                                      </tr>
                                    </thead>
                                    <tbody>
									 <tr>
                                            <td class="border px-4 py-2"><?php echo $users_details['user_id'];?></td>
                                            <td class="border px-4 py-2"> <?php echo $users_details['firstname']."&nbsp;".$users_details['lastname'];?></td>
                                          
                                        </tr> 
									  
                                         
                                        
                                        
                                    </tbody>
                                </table>-->
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