 <?php include('header.php');
  
 $find_schol = mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM schools 
LEFT JOIN regions_table ON schools.school_region = regions_table.region_id
LEFT JOIN countries ON schools.country_ref = countries.id WHERE school_id='$school_ref'"));
 
 
 if(isset($_GET['STATUS'])){
	 $STATUS =$_GET['STATUS'];
	 if($STATUS=="Active"){
		$status ="Active"; 
	 }
	 elseif($STATUS=="Inactive"){
		$status ="Inactive";  
	 }
	 else{
	    $status ="Waiting";
	 }
	$action = "	sleep_school='$school_ref' AND sleep_status='$STATUS'"; 
 }
 else{
$STATUS ="Active";	
$status = $STATUS; 
$action = "sleep_school='$school_ref' AND sleep_status='$STATUS'";
 }
 $select_promotions = mysqli_query($conn,"SELECT * FROM bank_sleeps
LEFT JOIN banks ON bank_sleeps.sleep_bank=banks.bank_id WHERE  $action");
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
                                
					 <a href="Deposite_Amount?STATUS=Active"><button class='bg-green-500 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded'><i class="fas fa-home float-left">&nbsp; Active</i></button></a>  
                   <a href="Deposite_Amount?STATUS=Inactive"><button class='bg-red-500 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'><i class="fas fa-times float-left">&nbsp;Inactive</i></button></a>  
                   <a href="Deposite_Amount?STATUS=Waiting"><button class='bg-blue-500 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'><i class="fas fa-wait float-left">&nbsp;Wating for Approval</i></button></a>  
                   
                     <a href="Add_new_Bank_sleep?STATUS=Inactive"><button class='bg-yellow-500 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'><i class="fas fa-plus float-left">&nbsp;Add New </i></button></a>  
                    	
							</div>
                            <div class="p-3">
							<p><big><strong><?php echo $find_schol['school_name'];?>&nbsp; <?php echo $STATUS;?> &nbsp; Payments</strong></big></p><br>
                                <table class="table-responsive w-full rounded">
                                    <thead>
                                      <tr>
                                        <th class="border w-1/8 px-4 py-2">Sleep No</th>
                                        <th class="border w-1/5 px-4 py-2">Bank Name</th> 
										 <th class="border w-1/8 px-4 py-2">Deposite</th>
										 <th class="border w-1/8 px-4 py-2">Used</th>
										 <th class="border w-1/8 px-4 py-2">Balance</th>
										  <th class="border w-1/8 px-4 py-2"> Date</th>
                                        <th class="border w-1/10 px-4 py-2">Usage</th>
                                        <th class="border w-1/10 px-4 py-2">Actions</th>
                                      </tr>
                                    </thead>   
                                    <tbody>
									<?php
									while($promotion_details = mysqli_fetch_array($select_promotions)){
										$sleep_id =$promotion_details['sleep_id'];
										$sleep_status =$promotion_details['sleep_status'];
										$count_used = mysqli_query($conn,"SELECT SUM(pay_amount_local) AS Used FROM invoice_payments WHERE pay_bank_sleep='$sleep_id'");
										$Sleep_used = mysqli_fetch_array($count_used);
										$Used = $Sleep_used['Used'];
										$sleep_amount_local =$promotion_details['sleep_amount_local'];
										$balance = $sleep_amount_local-$Used;
									?><tr>      
									
									
                                            <td class="border px-4 py-1"><?php echo $promotion_details['sleep_no'];?></td>
											
										    <td class="border px-4 py-2"><?php echo $promotion_details['bank_name'];?></td> 
                                            <td class="border px-4 py-2"><?php echo $promotion_details['sleep_amount_local']."&nbsp;".$Country_currency_code;?></td>
											<td class="border px-4 py-2"><?php echo number_format($Used,2)."&nbsp;".$Country_currency_code;?></td>
											
											<td class="border px-4 py-2"><?php echo number_format($balance,2)."&nbsp;".$Country_currency_code;?></td>
											<td class="border px-4 py-2"><?php echo $promotion_details['sleep_date'];?></td> 
											
											
											
											 
 


                                             <td class="border px-4 py-2">
											<?php if($balance !=0){
											    if($sleep_status=="Waiting"){
											   	?> <a href="#" class="bg-red-500 cursor-pointer rounded p-1 mx-1 text-white"><i class="fas fa-unlock text-white-500 mx-2"></i></a><?php
										     
											    }else{
												?> <a href="Bank_sleep_distribution?SLEEP_No=<?php echo $sleep_id;?>&STATUS=<?php echo $STATUS;?>" class="bg-blue-500 cursor-pointer rounded p-1 mx-1 text-white"><i class="fas fa-unlock text-white-500 mx-2"></i></a><?php
											}}else{
											?> <a href="#" class="bg-red-500 cursor-pointer rounded p-1 mx-1 text-white">  <i class="fas fa-lock text-white-500 mx-2" ></i></a><?php	
											}?>
                                               
                                            </td>
                                            <td class="border px-4 py-2">
 <a href="Students_on_Bank_sleep?SLEEP_No=<?php echo $sleep_id;?>&STATUS=<?php echo $STATUS;?>" class="bg-blue-500 cursor-pointer rounded p-1 mx-1 text-white">
                                                       <i class="fas fa-users text-white-500 mx-2"></i></a>                                           
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