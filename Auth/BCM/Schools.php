 <?php include('header.php');
 $COUNTRY = $_GET['COUNTRY'];
 $REGION = $_GET['REGION'];
 $select_country = mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM countries WHERE id='$COUNTRY'"));
 $Country_name1 = $select_country['Country_name']; 
 $Country_region  =$select_country['Country_region'];
 if(isset($_GET['STATUS'])){
	 $STATUS =$_GET['STATUS'];
	 if($STATUS=="Active"){
		$status ="Active"; 
	 }
	 else{
		$status ="Inactive";  
	 }
	$action = "country_ref='$COUNTRY' AND school_status ='$status'"; 
 }
 else{
$STATUS ="Active";	
$status = $STATUS; 
$action = "country_ref='$COUNTRY' AND school_status ='$STATUS'";
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
					<a href="Schools?REGION=<?php echo $REGION;?>&COUNTRY=<?php echo $COUNTRY;?>&STATUS=Active"><button class='bg-green-500 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded'>Active</button></a>  
                   <a href="Schools?REGION=<?php echo $REGION;?>&COUNTRY=<?php echo $COUNTRY;?>&STATUS=Inactive"><button class='bg-red-500 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'>Inactive</button></a>  
                    <a href="Countries?REGION=<?php echo $REGION;?>"><button class='bg-yellow-500 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'>Back</button></a>       
							
							        
							
							</div>
                            <div class="p-3">
							 <p><strong><big>Schools located in <big><?php echo $Country_name1;?></big></big></strong></p> <br>
                                   
                                <table class="table-responsive w-full rounded">
                                    <thead>
                                      <tr>
                                        <th class="border w-1/12 px-4 py-2">ID</th>
                                        <th class="border w-1/8 px-4 py-2">School Name</th>
										<th class="border w-1/8 px-4 py-2">Abreviation</th>
										<th class="border w-1/8 px-4 py-2">Country</th>
										<th class="border w-1/8 px-4 py-2">Payment Settings</th>
                                        <th class="border w-1/10 px-4 py-2">Status</th> 
                                      </tr>
                                    </thead>
                                    <tbody>
									<?php
									while($country_details = mysqli_fetch_array($select_countries)){
										$school_region =$country_details['school_region'];
									?><tr>
                                            <td class="border px-4 py-1"><?php echo $country_details['school_id'];?></td>
                                            <td class="border px-4 py-2"><?php echo $country_details['school_name'];?></td> 
                                            <td class="border px-4 py-2"><?php echo $country_details['school_abreviation'];?></td> 
                                             <td class="border px-4 py-2"><?php echo $country_details['Country_name'];?></td> 
											 <td class="border px-4 py-2"><a href="Schools_Payments?SCHOOL=<?php echo $country_details['school_id'];?>&COUNTRY=<?php echo $COUNTRY;?>&REGION=<?php echo $school_region;?>&STATUS=<?php echo $STATUS;?>"><button class='bg-green-500 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'><i class="fas fa-school text-white-500 mx-2"></i>School Payment Settings</button></a>  
                   </td> 
											
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
        <footer class="bg-grey-darkest text-white p-2">
            <div class="flex flex-1 mx-auto">&copy; My Design</div>
        </footer>
        <!--/footer-->

    </div>

</div>

<script src="../../main.js"></script>

</body>

</html>