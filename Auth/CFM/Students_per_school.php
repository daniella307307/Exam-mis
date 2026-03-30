<?php
ob_start();
include('header.php');
 
// Set the number of records to display per page
$records_per_page = 100;

// Get the current page or set a default
if (isset($_GET['page']) && is_numeric($_GET['page'])) {
    $current_page = (int)$_GET['page'];
} else {
    $current_page = 1;
}

// Calculate the offset for the query
$offset = ($current_page - 1) * $records_per_page;

if (isset($_GET['STATUS'])) {
    $STATUS = $_GET['STATUS'];
    if ($STATUS == "Active") {
        $status = "Active";
        $action = "school_status ='$status'";
    } else {
        $status = "Inactive";
        $action = "school_status !='Active' OR school_status='$status'";
    }
} else {
    $STATUS = "Active";
    $status = $STATUS;
    $action = "school_status ='Active'";
}

// Count total records
$count_query = "SELECT * FROM schools WHERE country_ref='$user_country' AND $action";
$count_result = mysqli_query($conn, $count_query);
$total_records = mysqli_num_rows($count_result);

// Fetch records with limit and offset
$select_schools = mysqli_query($conn, " SELECT * FROM schools WHERE country_ref='$user_country' LIMIT $offset, $records_per_page");
?>

<!--/Header-->

<div class="flex flex-1">
    <!--Sidebar-->
    <?php include('side_bar_finance.php');?>
    <!--/Sidebar-->
    <!--Main-->
    <main class="bg-white-500 flex-1 p-3 overflow-hidden">
        <div class="flex flex-col">
            <!-- Card Section Starts Here -->
            <div class="flex flex-1 flex-col md:flex-row lg:flex-row mx-2">
                <!--Horizontal form-->
                <!--/Horizontal form-->
                <!--Underline form-->
                <!--/Underline form-->
            </div>
            <!-- /Cards Section Ends Here -->

            <!--Grid Form-->
            <div class="flex flex-1 flex-col md:flex-row lg:flex-row mx-2">
                <div class="mb-2 border-solid border-gray-300 rounded border shadow-sm w-full">
                    <div class="bg-gray-200 px-2 py-3 border-solid border-gray-200 border-b">
                        Students &nbsp;
                        <a href="Students_per_school?STATUS=Active"><button class='bg-green-500 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded'>Active</button></a>
                        <a href="Students_per_school?STATUS=Inactive"><button class='bg-red-500 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'>Inactive</button></a>
                       
                      <!--
                       <button onclick="window.print();" class='bg-blue-800 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'><i class="fas fa-print"></i>&nbsp;&nbsp; Print</button>
                      <a href="export_students.php?format=pdf"><button class='bg-blue-800 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'><i class="fas fa-file-pdf"></i>&nbsp;&nbsp;PDF</button></a>
                        <a href="export_students.php?format=excel"><button class='bg-blue-800 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'><i class="fas fa-file-excel"></i>&nbsp;&nbsp;Excel</button></a>
                        -->
                    </div>
                    
                    <div class="p-3">
                        <table class="table-responsive w-full rounded">
                            <thead>
                                <tr>
                                    <th class="border w-1/12 px-4 py-2">#</th>
                                    <th class="border w-1/8 px-4 py-2">School Names</th>
                                    
                                    <th class="border w-1/8 px-4 py-2">Students Number</th>
                                    <th class="border w-1/8 px-4 py-2">Studens</th>  
                                </tr>
                            </thead>    
                            <tbody>
                                <?php while($Details_schools = mysqli_fetch_array($select_schools)) { 
                                    
                                 $ID = $Details_schools['school_id'];
                                $select_students = mysqli_query($conn,"SELECT * FROM  student_list WHERE student_school='$ID'");
                                $Stu = mysqli_num_rows($select_students);
                               
                                ?>
                                    <tr>
                                        <td class="border px-4 py-2"><?php echo $Details_schools['school_id']; ?></td>
                                        
                        
                                        <td class="border px-4 py-2"><?php echo $Details_schools['school_name']; ?></td> 
                                        
                                        <td class="border px-4 py-2"><strong><big><?php echo $Stu;?></big></strong> Student<?php if($Stu>0){echo "s";}elseif($Stu==0){echo "s";}else{echo "";}?></td> 
                                        <td class="border px-4 py-2"> <a href="Students_per_school?STATUS=Active"><button class='bg-green-500 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded'><i class="fas fa-users"></i> View Students(<?php echo $Stu;?>)</button></a></td> 
                                      
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!--/Grid Form-->
        </div>
        
        <!-- Pagination -->
        <div class="flex justify-center">
            <?php
            $total_pages = ceil($total_records / $records_per_page);
            if ($total_pages > 1) {
                for ($i = 1; $i <= $total_pages; $i++) {
                    if ($i == $current_page) {
                        echo "<a href='Students_perschool.php?page=$i&STATUS=$STATUS' class='mx-1 p-2 bg-blue-500 text-white'>$i</a>";
                    } else {
                        echo "<a href='Students_perschool.php?page=$i&STATUS=$STATUS' class='mx-1 p-2 bg-gray-200'>$i</a>";
                    }
                }
            }
            ?>
        </div>
    </main>
    <!--/Main-->
</div>

<!--Footer-->
<?php include('footer.php')?>
<!--/footer-->

<script src="../../main.js"></script>

</body>
</html>
