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
 $select_countries = mysqli_query($conn,"SELECT * FROM  countries  WHERE  $action");
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
                               Countries
					<a href="User_by_Countries?STATUS=Active"><button class='bg-green-500 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded'>Active</button></a>  
                   <a href="User_by_Countries?STATUS=Inactive"><button class='bg-red-500 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'>Inactive</button></a>  
                           
							
							</div>
                            <div class="p-3">
                                <table class="table-responsive w-full rounded">
                                    <thead>
                                      <tr>
                                        <th class="border w-1/12 px-4 py-2">ID</th>
                                        <th class="border w-1/8 px-4 py-2">Country Name</th> 
										 <th class="border w-1/4 px-4 py-2">Number Of Schools</th> 
                                        <th class="border w-1/4 px-4 py-2">Actions</th>
                                      </tr>
                                    </thead>
                                    <tbody>
									<?php
									while($country_details = mysqli_fetch_array($select_countries)){
									       $c_ref =  $country_details['id'];
									       
									       $use = mysqli_num_rows(mysqli_query($conn,"SELECT * FROM users WHERE user_country='$c_ref'"));
									    $school = mysqli_num_rows(mysqli_query($conn,"SELECT * FROM schools WHERE country_ref='$c_ref'"));
									     
									  ?>
									    <tr>
                                            <td class="border px-4 py-1"><?php echo  $country_details['id'];?></td>
                                            <td class="border px-4 py-2"><?php echo $country_details['Country_name'];?></td>  
										     <td class="border px-4 py-2">  <a href="Schools?COUNTRY=<?php echo $c_ref;?>&STATUS=<?php echo $STATUS;?>"><button class='bg-green-500 hover:bg-blue-400 text-white font-bold py-2 px-4 rounded'><i class="fas fa-school text-white-500 mx-2"></i><?php  echo $school; ?> Schools</button></a></td> 
										   
<td class="border px-4 py-2"> <a href="Users?COUNTRY=<?php echo $c_ref;?>&STATUS=<?php echo $STATUS;?>"><button class='bg-green-500 hover:bg-blue-400 text-white font-bold py-2 px-4 rounded'> <?php  echo $use; ?> <i class="fas fa-users text-white-500 mx-2"></i> View Users</button></a></td>
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