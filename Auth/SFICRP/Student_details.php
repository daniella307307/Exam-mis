 <?php include('header.php');
 if(isset($_GET['ID'])){
	 $ID =$_GET['ID'];
 }
 
 $details_user =mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM student_list 
LEFT JOIN schools ON student_list.student_school = schools.school_id
LEFT JOIN school_classes ON student_list.student_class = school_classes.class_id
LEFT JOIN school_levels ON student_list.student_level = school_levels.level_id 
WHERE student_id =$ID"));
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
                          <big><strong><?php echo $details_user['student_first_name']."&nbsp;".$details_user['student_last_name']; ?> </strong></big> Details  &nbsp; &nbsp; &nbsp; <a href="Users"><button class='bg-green-500 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded'><i class="fas fa-users angle-right float-left"></i> &nbsp; Back</button></a>  
                               
							
				 <div class="flex flex-1 flex-col md:flex-row lg:flex-row mx-2">
                        <div class="shadow-lg bg-white-vibrant border-l-8 hover:bg-red-vibrant-dark border-red-vibrant-dark mb-2 p-2 md:w-1/2 mx-2">
                               <img class="inline-block h-120 w-120 rounded-full" src="../<?php echo $details_user['student_profile'];?>">
  
                        </div>
						 <div class="shadow-lg bg-white-vibrant border-l-8 hover:bg-red-vibrant-dark border-red-vibrant-dark mb-2 p-2 md:w-1/2 mx-2">
                             <p>&nbsp;ID:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong><?php echo $details_user['student_id']; ?></strong></p>
  <p>&nbsp;First Name :&nbsp;<strong><?php echo $details_user['student_first_name']; ?></strong></p>
  <p>&nbsp;Last Name :&nbsp;<strong><?php echo  $details_user['student_last_name']; ?></strong></p>
  <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;DoB :&nbsp;<strong><?php echo  $details_user['student_dob']; ?></strong></p>
  <p>&nbsp;&nbsp;Gender:&nbsp;<strong><?php echo  $details_user['student_gender']; ?></strong></p>
   <p>&nbsp;&nbsp;Level:&nbsp;<strong><?php echo  $details_user['level_name']; ?></strong></p>
  <p>&nbsp;&nbsp;Class:&nbsp;<strong><?php echo  $details_user['class_name']; ?></strong></p>
  <p>&nbsp;&nbsp;School:&nbsp;<strong><?php echo  $details_user['school_name']; ?></strong></p>
  
  <p>&nbsp;&nbsp;&nbsp;&nbsp;Contacts: &nbsp;<strong><?php echo  $details_user['student_contact']; ?></strong></p>
   <p>&nbsp;Status:&nbsp;<strong><?php echo  $details_user['student_status']; ?></strong></p>
                        </div>
</div>						
							
							
							
							
							
							
							  
 
 
  
  </div>
    
						 
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