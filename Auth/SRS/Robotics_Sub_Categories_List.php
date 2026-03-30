<?php 
include('header.php');
$CATEGORY = $_GET['CATEGORY'];
 
if(isset($_GET['STATUS'])){
    $STATUS = $_GET['STATUS'];
    if($STATUS == "Active"){
        $status = "Active"; 
        $action = "subcategory_status = 'Active'";
    }
    else{
        $status = "Inactive";  
        $action = "subcategory_status != 'Active' ";
    }
}
else{
    $STATUS = "Active";	
    $status = $STATUS; 
    $action = "subcategory_status = 'Active'";
}
 
$find = mysqli_query($conn,"SELECT * FROM Equipment_categories WHERE category_id='$CATEGORY'");
$find_result = mysqli_fetch_array($find);
$category_name  =  $find_result['category_name'];
 
$select_subcategory = mysqli_query($conn,"SELECT * FROM Equipment_sub_categories 
LEFT JOIN Equipment_categories ON Equipment_sub_categories.category_ref = Equipment_categories.category_id
WHERE category_ref='$CATEGORY' AND $action");
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

            <!--Grid Form-->
            <div class="flex flex-1  flex-col md:flex-row lg:flex-row mx-2">
                <div class="mb-2 border-solid border-gray-300 rounded border shadow-sm w-full">
                    <div class="bg-gray-200 px-2 py-3 border-solid border-gray-200 border-b">
                        <a href="Robotics_Categories_List?CATEGORY=<?php echo  $CATEGORY;?>&STATUS=Active"><button class='bg-blue-500 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded'>Back</button></a>  
                              
                        <a href="Robotics_Sub_Categories_List?CATEGORY=<?php echo  $CATEGORY;?>&STATUS=Active"><button class='bg-green-500 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded'>Active</button></a>  
                        <a href="Robotics_Sub_Categories_List?CATEGORY=<?php echo  $CATEGORY;?>&STATUS=Inactive"><button class='bg-red-500 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'>Inactive</button></a>  
                           
                    </div>
                    
                    <center><strong><big><h2>List of All : <?php echo  $category_name . " (" . $status . ")"; ?></h2></big></strong></center>
                    
                    <div class="p-3">
                        <table class="table-responsive w-full rounded">
                            <thead>
                                <tr>  
                                    <th class="border w-1/12 px-4 py-2">ID</th>
                                    <th class="border w-1/8 px-4 py-2">Sub Category Name</th>
                                    <th class="border w-3/8 px-4 py-2">Status</th>   
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                while($subcategory_details = mysqli_fetch_array($select_subcategory)){
                                ?>
                                <tr>
                                    <td class="border px-4 py-1"><?php echo $subcategory_details['subcategory_id'];?></td>
                                    <td class="border px-4 py-2"><?php echo $subcategory_details['subcategory_name'];?></td>
                                    <td class="border px-4 py-2"><?php echo $subcategory_details['subcategory_status'];?></td> 
                                    
                                </tr>
                                <?php	
                                }
                                
                                if(mysqli_num_rows($select_subcategory) == 0):
                                ?>
                                <tr>
                                    <td colspan="4" class="border px-4 py-4 text-center text-gray-500">
                                        No <?php echo strtolower($status); ?> subcategories found.
                                    </td>
                                </tr>
                                <?php endif; ?>
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