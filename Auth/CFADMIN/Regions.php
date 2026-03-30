 <?php include('header.php');
 if(isset($_GET['STATUS'])){
	 $STATUS =$_GET['STATUS'];
	 if($STATUS=="Active"){
		$status ="Active"; 
	 }
	 else{
		$status ="Inactive";  
	 }
	$action = "region_status ='$status'"; 
 }
 else{
$STATUS ="Active";	
$status = $STATUS; 
$action = "region_status ='$STATUS'";
 }
 $select_countries = mysqli_query($conn,"SELECT * FROM regions_table WHERE  $action");
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
					<a href="Regions?STATUS=Active"><button class='bg-green-500 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded'>Active</button></a>  
                   <a href="Regions?STATUS=Inactive"><button class='bg-red-500 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'>Inactive</button></a>  
                           
							
							</div>
                            <div class="p-3">
                                <table class="table-responsive w-full rounded">
                                    <thead>
                                      <tr>
                                        <th class="border w-1/12 px-4 py-2">ID</th>
                                        <th class="border w-1/8 px-4 py-2">Region Name</th> 
										 <th class="border w-1/8 px-4 py-2">Countries</th> 
                                        <th class="border w-1/10 px-4 py-2">Status</th> 
                                      </tr>
                                    </thead>
                                    <tbody>
									<?php
									while($region_details = mysqli_fetch_array($select_countries)){
										$region_status=$region_details['region_status'];
										$region_ref=$region_details['region_id'];
									$countries = mysqli_num_rows(mysqli_query($conn,"SELECT * FROM  countries WHERE  Country_region ='$region_ref' AND  Country_status ='Active'"));	
										if($countries>0){
											$bg ="bg-green-500";
											$link ="Countries?REGION=$region_ref";
										} else{
											$bg ="bg-red-500";
											$link ="#";
										}  
										
									?><tr>                                                        
                                            <td class="border px-4 py-1"><?php echo $region_details['region_id'];?></td>
                <td class="border px-4 py-1"><?php echo $region_details['region_name'];?></td>
 <td class="border px-4 py-2"><a href="<?php echo $link;?>"><button class='<?php echo $bg;?> hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'> <i class="fas fa-map-marker-alt text-white-500 mx-2"></i><?php echo $countries;?>&nbsp;Countries</button></a></td>   
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
        <?php include('footer.php')?>
        <!--/footer-->

    </div>

</div>

<script src="../../main.js"></script>

</body>

</html>