 <?php include('header.php');
  
 $select_countries = mysqli_query($conn,"SELECT * FROM countries
LEFT JOIN regions_table ON countries.Country_region = regions_table.region_id WHERE Country_status='Active'");
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
                               Countries List
					    
							  
							</div>
                            <div class="p-3">
                                <table class="table-responsive w-full rounded">
                                    <thead>
                                      <tr>
                                        <th class="border w-1/12 px-4 py-2">ID</th>
                                        <th class="border w-1/8 px-4 py-2">Country Name</th> 
										 <th class="border w-1/8 px-4 py-2">Region</th> 
										 <th class="border w-1/8 px-4 py-2">Currency</th> 
										 <th class="border w-1/8 px-4 py-2">Exchange (local to Usd)</th> 
                                        <th class="border w-1/10 px-4 py-2">Bank list</th> 
                                      </tr>
                                    </thead>
                                    <tbody>
									<?php
									while($region_details = mysqli_fetch_array($select_countries)){
										$id = $region_details['id'];
										$region_status=$region_details['region_status'];
										$region_ref=$region_details['region_id'];
									$countries = mysqli_num_rows(mysqli_query($conn,"SELECT * FROM banks WHERE bank_country ='$id' AND bank_status='Active'"));	
										if($countries>0){
											$bg ="bg-green-500";
											$link ="Banks_in_Countries?COUNTRY=$id";
										} else{
											$bg ="bg-red-500";
											$link ="#";
										}  
										
									?><tr>  
							  <td class="border px-4 py-1"><?php echo $region_details['id'];?></td>
							  <td class="border px-4 py-1"><?php echo $region_details['Country_name'];?></td>
							  <td class="border px-4 py-1"><?php echo $region_details['region_name'];?></td>
							  
							  <td class="border px-4 py-1"><?php echo $region_details['Country_currency_code'];?></td>
							  <td class="border px-4 py-1"><?php echo $region_details['currency_usd'];?></td>
                                             
 <td class="border px-4 py-2"><a href="<?php echo $link;?>"><button class='<?php echo $bg;?> hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'> <i class="fas fa-map-marker-alt text-white-500 mx-2"></i><?php echo $countries;?>&nbsp;Banks</button></a></td>   
											 
                                            
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