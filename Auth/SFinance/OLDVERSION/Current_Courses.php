 <?php include('header.php');
  
 $find_schol = mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM schools 
LEFT JOIN regions_table ON schools.school_region = regions_table.region_id
LEFT JOIN countries ON schools.country_ref = countries.id WHERE school_id='$school_ref'"));
 
 
 if(isset($_GET['STATUS'])){
	 $STATUS =$_GET['STATUS'];
	 if($STATUS=="Active"){
		$status ="Active"; 
	 }
	 else{
		$status ="Inactive";  
	 }
	$action = "promotion_school='$school_ref' AND promotion_status='$STATUS' AND promotion_year='$this_year'"; 
 }
 else{
$STATUS ="Active";	
$status = $STATUS; 
$action = "promotion_school='$school_ref' AND promotion_status='$STATUS' AND promotion_year=$this_year";
 }
 $select_promotions = mysqli_query($conn,"SELECT * FROM students_promotion 
LEFT JOIN regions_table ON students_promotion.promotion_region = regions_table.region_id
LEFT JOIN countries ON students_promotion.promotion_country =countries.id 
LEFT JOIN certifications ON students_promotion.promotion_certification = certifications.certification_id 
LEFT JOIN schools ON students_promotion.promotion_school = schools.school_id  WHERE $action");
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
                                
					 <a href="Current_Courses?STATUS=Active"><button class='bg-green-500 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded'><i class="fas fa-home float-left">&nbsp; Active</i></button></a>  
                   <a href="Current_Courses?STATUS=Inactive"><button class='bg-red-500 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'><i class="fas fa-times float-left">&nbsp;Inactive</i></button></a>  
                    	
							</div>
                            <div class="p-3">
							<p><big><strong><?php echo $find_schol['school_name'];?>&nbsp; <?php echo $STATUS;?> &nbsp; Payment  Settings</strong></big></p><br>
                                <table class="table-responsive w-full rounded">
                                    <thead>
                                      <tr>
                                        <th class="border w-1/12 px-4 py-2">ID</th>
                                        <th class="border w-1/8 px-4 py-2">Promtion</th>
                                        <th class="border w-1/4 px-4 py-2">Certification</th>
										 <th class="border w-1/8 px-4 py-2">Amount in USD</th> 
										 <th class="border w-1/8 px-4 py-2">Amount </th>
										  <th class="border w-1/8 px-4 py-2">Year</th> 
                                        <th class="border w-1/10 px-4 py-2">Actions</th>
                                      </tr>
                                    </thead>   
                                    <tbody>
									<?php
									while($promotion_details = mysqli_fetch_array($select_promotions)){
										$promotion_id =$promotion_details['promotion_id'];
										$count_students = mysqli_query($conn,"SELECT * FROM students_invoice WHERE invoice_promotion='$promotion_id'");
										$students = mysqli_num_rows($count_students);
										$invoice_certificate =$promotion_details['promotion_certification'];
									?><tr>      
                                            <td class="border px-4 py-1"><?php echo $promotion_details['promotion_id'];?></td>
											<td class="border px-4 py-1"><a href="Modules_per_Certification?CERTIFICATE=<?php echo $invoice_certificate; ?>"  class='bg-blue-500 hover:bg-blue-200 text-blue-800 font-bold py-2 px-4 rounded' > <?php echo $promotion_details['promotion_name'];?></a></td>
                                            <td class="border px-4 py-2"><?php echo $promotion_details['certification_name'];?>  </td>
										    
											<td class="border px-4 py-2"><?php echo number_format($promotion_details['promotion_pay_usd'], 2);?> USD</td>
											<td class="border px-4 py-2"><?php echo  number_format($promotion_details['promotion_pay_local'], 2)."&nbsp;".$promotion_details['Country_currency_code'];?></td>
											 <td class="border px-4 py-2"><?php echo $promotion_details['promotion_year'];?></td> 
											
											 
                                            <td class="border px-4 py-2">
<a href="Update_Promotions_name?ID=<?php echo $promotion_details['promotion_id'];?>&STATUS=<?php echo $STATUS;?>" class="bg-teal-300 cursor-pointer rounded p-1 mx-1 text-white"><i class="fas fa-pen text-green-500 mx-2"></i></a> 
<a href="Students_in_Promotions?ID=<?php echo $promotion_details['promotion_id'];?>&STATUS=<?php echo $STATUS;?>" class="bg-blue-500 cursor-pointer rounded p-1 mx-1 text-white">
                                                       <?php echo $students;?> <i class="fas fa-users text-white-500 mx-2"></i></a>                                           
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