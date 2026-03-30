 <?php include('header.php');
 if(isset($_GET['STATUS'])){
	 $STATUS =$_GET['STATUS'];
	 if($STATUS=="Active"){
		$status ="Active"; 
	 }
	 else{
		$status ="Inactive";  
	 }
	$action ="country_ref  ='$user_country' AND school_status ='$status'"; 
 }
 else{
$STATUS ="Active";	
$status = $STATUS; 
$action = "country_ref  ='$user_country' AND school_status ='$STATUS'";
 }
 $select_countries = mysqli_query($conn,"SELECT * FROM schools
LEFT JOIN countries ON schools.country_ref= countries.id
LEFT JOIN regions_table ON schools.school_region= regions_table.region_id  WHERE  $action");
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
					<a href="Schools?STATUS=Active"><button class='bg-green-500 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded'>Active</button></a>  
                   <a href="Schools?STATUS=Inactive"><button class='bg-red-500 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'>Inactive</button></a>  
                  <!-- <a href="Add_new_school"><button class='bg-yellow-500 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'>Add New</button></a> --> 
                           <center><h1>List of Collected Bank Sleps  In&nbsp; <strong><strong><?php echo $Country_name;?></strong></strong></h1></center>
							
							</div>
                            <div class="p-3">
                                <table class="table-responsive w-full rounded">
                                    <thead>
                                      <tr>
                                        <th class="border w-1/12 px-4 py-2">ID</th>
                                        <th class="border w-1/8 px-4 py-2">School Name</th>
										<th class="border w-1/4 px-4 py-2">Aprouved Sleeps</th>
										<th class="border w-1/3 px-4 py-2">Waiting for Aprouval</th>
										
										<th class="border w-1/3 px-4 py-2">All</th>
 
                                      </tr>
                                    </thead>
                                    <tbody>
									<?php
									while($country_details = mysqli_fetch_array($select_countries)){
$school = $country_details['school_id'];
 $find_students = mysqli_fetch_array(mysqli_query($conn,"SELECT 
count(case when sleep_status='Active' then 1 end) as Active ,
count(case when sleep_status='Waiting' then 1 end) as Waiting 
FROM bank_sleeps WHERE sleep_school='$school'"));
									$Active =$find_students['Active'];
									$Waiting =$find_students['Waiting'];
									$all = $Active+$Waiting;
									 
									?><tr>
                                            <td class="border px-4 py-1"><?php echo $country_details['school_id'];?></td>
                                            <td class="border px-4 py-2"><?php echo $country_details['school_name'];?></td> 
                                             <td class="border px-4 py-2"><a href="Bank_sleeps_per_school?STATUS=Active&SCHOOL=<?php echo $country_details['school_id'];?>"><button class='bg-green-500 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded'><?php echo $Active;?>&nbsp;Bank Sleeps</button></a></td>
                                              <td class="border px-4 py-2"><a href="Bank_sleeps_per_school?STATUS=Waiting&SCHOOL=<?php echo $country_details['school_id'];?>"><button class='bg-red-500 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded'><?php echo $Waiting;?>&nbsp;Bank Sleeps</button></a></td>
                                              <td class="border px-4 py-2"><a href="Bank_sleeps_per_school?STATUS=ALL&SCHOOL=<?php echo $country_details['school_id'];?>"><button class='bg-blue-500 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded'><?php echo $all;?>&nbsp;Bank Sleeps</button></a></td>
 											 
  
										 
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