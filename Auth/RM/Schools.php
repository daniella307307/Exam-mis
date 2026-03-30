 <?php include('header.php');
 $COUNTRY=  $_GET['COUNTRY'];
 $select_countries = mysqli_query($conn,"SELECT * FROM  countries  WHERE  id='$COUNTRY'");
 $details_country = mysqli_fetch_array($select_countries);
 if(isset($_GET['STATUS'])){
	 $STATUS =$_GET['STATUS'];
	 if($STATUS=="Active"){
		$status ="Active"; 
	 }
	 else{
		$status ="Inactive";  
	 }
	$action = "school_status ='$status'"; 
 }
 else{
$STATUS ="Active";	
$status = $STATUS; 
$action = "school_status ='$STATUS'";
 }
 $select_countries = mysqli_query($conn,"SELECT * FROM schools
LEFT JOIN countries ON schools.country_ref= countries.id  WHERE  $action");
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
                               Schools
					<a href="Countries?STATUS=Active"><button class='bg-green-500 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded'>Active</button></a>  
                   <a href="Countries?STATUS=Inactive"><button class='bg-red-500 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'>Inactive</button></a>  
                       <br><br>
                 <center><p> Liste of Schools Located in :<big><strong><?php echo $details_country['Country_name'];?></strong></big></p></center>
                                 
							
							</div>
                            <div class="p-3">
                                <table class="table-responsive w-full rounded">
                                    <thead>
                                      <tr>
                                        <th class="border w-1/12 px-4 py-2">ID</th>
                                        <th class="border w-1/8 px-4 py-2">School Name</th>
										<th class="border w-1/8 px-4 py-2">Abreviation</th>
										<th class="border w-1/8 px-4 py-2">Country</th>
                                        <th class="border w-1/10 px-4 py-2">Status</th>
										 <th class="border w-1/10 px-4 py-2">Facilitators</th>
                                        <th class="border w-1/5 px-4 py-2">Actions</th>
                                      </tr>
                                    </thead>
                                    <tbody>
									<?php
									while($country_details = mysqli_fetch_array($select_countries)){
									$school = $country_details['school_id'];
									?><tr>
                                            <td class="border px-4 py-1"><?php echo $country_details['school_id'];?></td>
                                            <td class="border px-4 py-2"><?php echo $country_details['school_name'];?></td> 
                                            <td class="border px-4 py-2"><?php echo $country_details['school_abreviation'];?></td> 
                                             <td class="border px-4 py-2"><?php echo $country_details['Country_name'];?></td>
<?php
$select_suser=mysqli_query($conn,"SELECT * FROM users  
LEFT JOIN user_permission ON users.access_level=user_permission.permissio_id
LEFT JOIN schools ON users.school_ref = schools.school_id  WHERE school_ref ='$school' AND status='Active'");
$schooln = mysqli_num_rows($select_suser);
if($schooln>0){
?><td class="border px-4 py-2">	<a href="Users_per_school?SCHOOL=<?php echo $country_details['school_id'];?>"><button class='bg-green-500 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded'><?php echo $schooln;?>&nbsp;Facilitators</button></a></td><?php	
}
else{
?><td class="border px-4 py-2">	<a href="Users_per_school?SCHOOL=<?php echo $country_details['school_id'];?>"><button class='bg-red-500 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded'><?php echo $schooln;?>&nbsp;Facilitators</button></a></td><?php	
}

?>											 
  
											<td class="border px-4 py-2">
											<?php if($STATUS =="Active"){
												?> <i class="fas fa-unlock text-green-500 mx-2"></i><?php
											}else{
											?> <i class="fas fa-lock text-red-500 mx-2"></i><?php	
											}?>
                                               
                                            </td>
                                            <td class="border px-4 py-2">
                                              
                                               
                                             
                                          <a href="Update_school?ID=<?php echo $country_details['school_id'];?>" class="bg-teal-300 cursor-pointer rounded p-1 mx-1 text-white">
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
        <footer class="bg-grey-darkest text-white p-2">
            <div class="flex flex-1 mx-auto">&copy; My Design</div>
        </footer>
        <!--/footer-->

    </div>

</div>

<script src="../../main.js"></script>

</body>

</html>