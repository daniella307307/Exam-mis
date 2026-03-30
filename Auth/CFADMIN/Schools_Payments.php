 <?php include('header.php');
 
 $SCHOOL=$_GET['SCHOOL'];
 $COUNTRY= $_GET['COUNTRY'];
 $REGION= $_GET['REGION']; 
 $find_schol = mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM schools WHERE school_id='$SCHOOL'"));
 
 
 if(isset($_GET['STATUS'])){
	 $STATUS =$_GET['STATUS'];
	 if($STATUS=="Active"){
		$status ="Active"; 
	 }
	 else{
		$status ="Inactive";  
	 }
	$action = "promotion_school='$SCHOOL' AND promotion_status='$STATUS' AND promotion_year='$this_year'"; 
 }
 else{
$STATUS ="Active";	
$status = $STATUS; 
$action = "promotion_school='$SCHOOL' AND promotion_status='$STATUS' AND promotion_year=$this_year";
 }
 $select_promotions = mysqli_query($conn,"SELECT * FROM students_promotion 
LEFT JOIN regions_table ON students_promotion.promotion_region = regions_table.region_id
LEFT JOIN countries ON students_promotion.promotion_country =countries.id 
LEFT JOIN certifications ON students_promotion.promotion_certification = certifications.certification_id 
LEFT JOIN schools ON students_promotion.promotion_school = schools.school_id  WHERE $action ");
 ?>
  
 
        <!--/Header-->

        <div class="flex flex-1">
            <!--Sidebar-->
           <?php include('payment_side_bar.php');?>
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
                                
					<a href="Schools?COUNTRY=<?php echo $COUNTRY;?>&REGION=<?php echo $REGION;?>"><button class='bg-blue-500 hover:bg-yellow-300 text-white font-bold py-2 px-4 rounded'> <i class="fas fa-angle-left float-left">&nbsp; Back</i></button></a>       
							   
					<a href="Schools_Payments?SCHOOL=<?php echo $SCHOOL;?>&COUNTRY=<?php echo $COUNTRY;?>&REGION=<?php echo $REGION;?>&STATUS=Active"><button class='bg-green-500 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded'><i class="fas fa-home float-left">&nbsp; Active</i></button></a>  
                   <a href="Schools_Payments?SCHOOL=<?php echo $SCHOOL;?>&COUNTRY=<?php echo $COUNTRY;?>&REGION=<?php echo $REGION;?>&STATUS=Inactive"><button class='bg-red-500 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'><i class="fas fa-times float-left">&nbsp;Inactive</i></button></a>  
                   &nbsp;<a href="Add_Payment_setting?SCHOOL=<?php echo $SCHOOL;?>&COUNTRY=<?php echo $COUNTRY;?>&REGION=<?php echo $REGION;?>&STATUS=<?php echo $STATUS;?>"><button class='bg-orange-500 hover:bg-orange-800 text-white font-bold py-2 px-4 rounded'><i class="fas fa-plus float-left">&nbsp;Add New </i></button></a>  
                    		
							</div>
                            <div class="p-3">
							<p><big><strong><?php echo $find_schol['school_name'];?>&nbsp; <?php echo $STATUS;?> &nbsp; Payment  Settings</strong></big></p><br>
                                <table class="table-responsive w-full rounded">
                                    <thead>
                                      <tr>
                                        <th class="border w-1/12 px-4 py-2">ID</th>
                                        <th class="border w-1/8 px-4 py-2">Promtion</th>
                                        <th class="border w-1/8 px-4 py-2">Certification</th>
										 <th class="border w-1/8 px-4 py-2">Country </th> 
										 <th class="border w-1/8 px-4 py-2">School</th>
										 <th class="border w-1/8 px-4 py-2">Year</th>
                                        <th class="border w-1/10 px-4 py-2">Status</th>
                                        <th class="border w-1/10 px-4 py-2">Actions</th>
                                      </tr>
                                    </thead>
                                    <tbody>
									<?php
									while($promotion_details = mysqli_fetch_array($select_promotions)){
										$promotion_id =$promotion_details['promotion_id'];
										$promotion_payment =$promotion_details['promotion_payment'];
										if($promotion_payment=="Enable"){
										$icon="fas fa-unlock";
										$BG= "bg-green-500";
										$title = "You are going to Desable the payment Settings, this will overide all Payment setting inthis promotion and student will learn for free up to when you will change this setting";
										}else{ 
										$icon="fas fa-lock"; 
										$BG= "bg-red-500";
										$title = "You are going to Enable , This will apply Payment Setting ";
										
										}
									?><tr>      
                                            <td class="border px-4 py-1"><?php echo $promotion_details['promotion_id'];?></td>
											<td class="border px-4 py-1"><a href="Enable_Desable_Payment?PROMOTION=<?php echo $promotion_id;?>&SCHOOL=<?php echo $SCHOOL;?>&PAYMENT=<?php echo $promotion_payment;?>&COUNTRY=<?php echo $COUNTRY;?>&REGION=<?php echo $REGION;?>&STATUS=<?php echo $STATUS;?>"><button title="<?php echo $title ;?>" class='<?php echo $BG;?> hover:bg-orange-800 text-white font-bold py-2 px-4 rounded'><i class="<?php echo $icon;?> float-left">&nbsp;<?php echo $promotion_details['promotion_name'];?> </i></button></a>  
                    	</td>
                                            <td class="border px-4 py-2"><?php echo $promotion_details['certification_name'];?></td>
										    <td class="border px-4 py-2"><?php echo $promotion_details['Country_name'];?></td>
											 <td class="border px-4 py-2"><?php echo $promotion_details['school_name'];?></td>
											<td class="border px-4 py-2"><?php echo $promotion_details['promotion_year'];?></td> 
											
											
											
											 
 


                                             <td class="border px-4 py-2">
											<?php if($STATUS =="Active"){
												?> <a href="Promotion_details?SCHOOL=<?php echo $SCHOOL;?>&COUNTRY=<?php echo $COUNTRY;?>&REGION=<?php echo $REGION;?>&PROMOTION=<?php echo $promotion_id;?>&STATUS=<?php echo $STATUS;?>"><i class="fas fa-unlock text-green-500 mx-2"></i></a><?php
											}else{
											?> <a href="Promotion_details?SCHOOL=<?php echo $SCHOOL;?>&COUNTRY=<?php echo $COUNTRY;?>&REGION=<?php echo $REGION;?>&PROMOTION=<?php echo $promotion_id;?>&STATUS=<?php echo $STATUS;?>">  <i class="fas fa-lock text-red-500 mx-2"></i></a><?php	
											}?>
                                               
                                            </td>
                                            <td class="border px-4 py-2">
                                              
                                               
                                               <?php if($STATUS =="Active"){
												?> <a href="Update_Promotions?REGION=<?php echo $REGION;?>&COUNTRY=<?php echo $COUNTRY;?>&SCHOOL=<?php echo $SCHOOL;?>&CURRENT=<?php echo $STATUS;?>&STATUS=Inactive&ID=<?php echo $promotion_details['promotion_id'];?>" class="bg-teal-300 cursor-pointer rounded p-1 mx-1 text-white">
                                                       <i class="fas fa-lock text-red-500 mx-2"></i>  </a><?php
											}else{
											?> <a href="Update_Promotions?REGION=<?php echo $REGION;?>&COUNTRY=<?php echo $COUNTRY;?>&SCHOOL=<?php echo $SCHOOL;?>&CURRENT=<?php echo $STATUS;?>&STATUS=Active&ID=<?php echo $promotion_details['promotion_id'];?>" class="bg-teal-300 cursor-pointer rounded p-1 mx-1 text-white">
                                                       <i class="fas fa-unlock text-green-500 mx-2"></i></a><?php	
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