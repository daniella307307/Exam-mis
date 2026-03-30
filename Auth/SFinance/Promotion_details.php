 <?php include('header.php');
 if(isset($_GET['PROMOTION'])){
	 $ID =$_GET['PROMOTION'];
 }
 
 $details_user =mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM students_promotion 
LEFT JOIN regions_table ON students_promotion.promotion_region = regions_table.region_id
LEFT JOIN countries ON students_promotion.promotion_country =countries.id 
LEFT JOIN certifications ON students_promotion.promotion_certification = certifications.certification_id  WHERE promotion_id =$ID"));
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
        
   <div style="position:center" class="mb-2 border-solid border-green-300 rounded border shadow-sm w-full">
                            <div class="bg-green-200 px-2 py-3 border-solid border-gray-200 border-b ">
                          <big><strong><?php echo $details_user['promotion_name']; ?> </strong></big> Promotion  Details  &nbsp; &nbsp; &nbsp; <a href="Schools_Payments?SCHOOL=5&COUNTRY=186&REGION=3&STATUS=Active"><button class='bg-green-500 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded'><i class="fas fa-users angle-right float-left"></i> &nbsp; Back</button></a>  
                               
							
				 <div class="flex flex-1 flex-col md:flex-row lg:flex-row mx-2">
                        <div class="shadow-lg bg-white-vibrant border-l-8 hover:bg-red-vibrant-dark border-red-vibrant-dark mb-2 p-2 md:w-1/2 mx-2">
                       <p>&nbsp;Region:&nbsp;<strong><?php echo $details_user['region_name']; ?></strong></p>
                 <p>&nbsp;Country:&nbsp;<strong><?php echo $details_user['Country_name']; ?></strong></p>
        

							  </div>
						 <div class="shadow-lg bg-white-vibrant border-l-8 hover:bg-red-vibrant-dark border-red-vibrant-dark mb-2 p-2 md:w-1/2 mx-2">
                             <p>&nbsp;ID:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong><?php echo $details_user['promotion_id']; ?></strong></p>
  <p>&nbsp;  Name :&nbsp;<strong><?php echo $details_user['promotion_name']; ?></strong></p>
  <p>&nbsp;Certification Name :&nbsp;<strong><?php echo  $details_user['certification_name']; ?></strong></p>
  <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Amount To pay USD :&nbsp;<strong><?php echo  number_format($details_user['promotion_pay_usd'],2); ?> $</strong></p>
  <p>&nbsp;&nbsp;Amount To pay Local :&nbsp;<strong><?php echo  number_format($details_user['promotion_pay_local'],2)."&nbsp;". $details_user['Country_currency_code']; ?></strong></p>
  <p>&nbsp;&nbsp;From :&nbsp;<strong><?php echo  $details_user['promotion_from']; ?></strong></p>
  <p>&nbsp;&nbsp;To:&nbsp;<strong><?php echo  $details_user['promotion_to']; ?></strong></p>
  <p>&nbsp;&nbsp;Year:&nbsp;<strong><?php echo  $details_user['promotion_year']; ?></strong></p>
   <p>&nbsp;Status:&nbsp;<strong><?php echo  $details_user['promotion_status']; ?></strong></p>
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