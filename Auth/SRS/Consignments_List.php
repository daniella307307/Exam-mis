 <?php include('header.php');
 if(isset($_GET['STATUS'])){
	 $STATUS =$_GET['STATUS'];
	 if($STATUS=="Active"){
		$status ="Active"; 
		$action = "consignment_status='$status'"; 
		$select_Consignments = mysqli_query($conn,"SELECT * FROM  Equipment_consignment  WHERE  $action");
	 }
	 else{
		$status ="Inactive";  
		$action = "consignment_status!='Active'"; 
		$select_Consignments = mysqli_query($conn,"SELECT * FROM  Equipment_consignment  WHERE  $action");
	 }

 }
 else{
$STATUS ="Active";	
$status = $STATUS; 
$action = "consignment_status	 ='$STATUS'";

 $select_Consignments = mysqli_query($conn,"SELECT * FROM  Equipment_consignment  WHERE  $action");
 }
 
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

                    <!--Grid For
                    
                    
                    m-->

                    <div class="flex flex-1  flex-col md:flex-row lg:flex-row mx-2">
                        <div class="mb-2 border-solid border-gray-300 rounded border shadow-sm w-full">
                            <div class="bg-gray-200 px-2 py-3 border-solid border-gray-200 border-b">
                              
					<a href="Consignments_List?STATUS=Active"><button class='bg-blue-800 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded'>Active</button></a>  
                   <a href="Consignments_List?STATUS=Inactive"><button class='bg-red-500 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'>Inactive</button></a>  
                    <a href="Add_New_Consignments"><button class='bg-green-500 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'> <i class="fas fa-plus text-white mx-2"></i>Add New</button></a>  
                           
                        
							
							</div>
							<h2><strong>All received Conseignments</h2></strong></center>
                            <div class="p-3">
                                <table class="table-responsive w-full rounded">
                                    <thead>
                                      <tr>  
                                     
                                      
                                        <th class="border w-1/8 px-4 py-2">consignment Code</th>
                                         <th class="border w-1/8 px-4 py-2">From</th>
                                           <th class="border w-1/8 px-4 py-2">Amount</th> 
                                           
                                           <th class="border w-1/8 px-4 py-2">Items</th> 
                                            <th class="border w-1/8 px-4 py-2">Details</th> 
                                           
                                        <th class="border w-2/12 px-4 py-2">Actions</th>
                                      </tr>
                                    </thead>
                                    <tbody>
									<?php
									while($Consignments_details = mysqli_fetch_array( $select_Consignments)){
									?><tr>
									     
 
  
									    
									    
									     
                                            <td class="border px-4 py-2"><?php echo $Consignments_details['consignment_number'];?></td>
                                            <td class="border px-4 py-2"><?php echo $Consignments_details['consignment_origine'];?></td>
                                             <td class="border px-4 py-2"><?php echo bcdiv($Consignments_details['consignment_total'], 1, 2)  ;?></td>
                                             <!--   <td class="border px-4 py-2"><?php echo $Consignments_details['consignment_transport'];?></td>
                                             
                                             <td class="border px-4 py-2"><?php echo $Consignments_details['consignment_taxes'];?></td>
                                             -->
                                             <td class="border px-4 py-2"><a href="Consignments_details_List?FREF=<?php echo $Consignments_details['consignment_id'];?>&STATUS=Active"><button class='bg-green-500 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded'>Item List</button></a> </td> 
                                              <td class="border px-4 py-2"><a href="Consignments_parameters?FREF=<?php echo $Consignments_details['consignment_id'];?>&STATUS=Active"><button class='bg-blue-800 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded'> Details</button></a> </td> 
                                            
                                            
										 
                                            <td class="border px-4 py-2">
                                              <a href="#" class="bg-teal-300 cursor-pointer rounded p-1 mx-1 text-white">
                                                       <i class="fas fa-pen text-green-500 mx-2"></i></a>
                                               
                                             <a href="#" class="bg-teal-300 cursor-pointer rounded p-1 mx-1 text-white">
                                                       <i class="fas fa-trash text-red-500 mx-2"></i></a>
                                            
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