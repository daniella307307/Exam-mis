 <?php include('header.php');
 if(isset($_GET['STATUS'])){
	 $STATUS =$_GET['STATUS'];
	 if($STATUS=="Active"){
		$status ="Active"; 
		$action = "category_status='$status'"; 
		$select_countries = mysqli_query($conn,"SELECT * FROM Equipment_categories WHERE $action");
	 }
	 else{
		$status ="Inactive";  
		$action = "category_status!='Active'"; 
		$select_countries = mysqli_query($conn,"SELECT * FROM Equipment_categories WHERE $action");
	 }

 }
 else{
$STATUS ="Active";	
$status = $STATUS; 
$action = "category_status	 ='$STATUS'";

 $select_countries = mysqli_query($conn,"SELECT * FROM Equipment_categories WHERE $action");
 }
 
 ?>
 
        <!--/Header-->

        <div class="flex flex-1">
            <!--Sidebar-->
            <?php include('Robotics_materials_side_bar.php');?>
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
                               Robotics Categories
					<a href="Robotics_Categories_List?STATUS=Active"><button class='bg-green-500 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded'>Active</button></a>  
                   <a href="Robotics_Categories_List?STATUS=Inactive"><button class='bg-red-500 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'>Inactive</button></a>  
                    
                           
							
							</div>
                            <div class="p-3">
                                <table class="table-responsive w-full rounded">
                                    <thead>
                                      <tr>  
                                     
                                        <th class="border w-1/12 px-4 py-2">ID</th>
                                        <th class="border w-1/8 px-4 py-2">Category Name</th>
                                           <th class="border w-1/8 px-4 py-2">Subcategories</th>
										<th class="border w-3/8 px-4 py-2">Status</th>  
                                      </tr>
                                    </thead>
                                    <tbody>
									<?php
									while($Category_details = mysqli_fetch_array($select_countries)){
									?><tr>
									     
 
 
									    
									    
									    
                                            <td class="border px-4 py-1"><?php echo $Category_details['category_id'];?></td>
                                            <td class="border px-4 py-2"><?php echo $Category_details['category_name'];?></td>
                                             <td class="border px-4 py-2"><a href="Robotics_Sub_Categories_List?CATEGORY=<?php echo $Category_details['category_id'];?>" class="bg-teal-300 cursor-pointer rounded p-1 mx-1 text-white">
                                                       <i class="fas fa-open-eil text-red-500 mx-2"></i>  View Details</a></td>
                                            <td class="border px-4 py-2"><?php echo $Category_details['category_status'];?></td> 
                                            
										  
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