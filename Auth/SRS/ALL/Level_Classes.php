<?php
ob_start();
include('header.php');

// Set the number of records to display per page
$records_per_page = 30;

// Get the current page or set a default
if (isset($_GET['page']) && is_numeric($_GET['page'])) {
    $current_page = (int)$_GET['page'];
} else {
    $current_page = 1;
}

// Calculate the offset for the query
$offset = ($current_page - 1) * $records_per_page;

// Initialize search variables
$search_query = '';
$search_term = '';

if (isset($_GET['STATUS'])) {
    $STATUS = $_GET['STATUS'];
    if ($STATUS == "Active") {
        $status = "Active";
        $action = "class_status ='$status'";
    } else {
        $status = "Inactive";
        $action = "class_status !='Active' OR class_status ='$status'";
    }
} else {
    $STATUS = "Active";
    $status = $STATUS;
    $action = "class_status ='Active'";
}

// Check if search form was submitted
if (isset($_GET['search']) && !empty($_GET['search_term'])) {
    $search_term = mysqli_real_escape_string($conn, $_GET['search_term']);
    $search_query = " AND (class_name LIKE '%$search_term%' 
                          OR level_name LIKE '%$search_term%')";
                          
                            
                          
                          
}

// Count total records
$count_query = " SELECT COUNT(*)  FROM school_classes WHERE class_school ='$school_ref'";
$count_result = mysqli_query($conn, $count_query);
$total_records = mysqli_fetch_array($count_result)[0];

// Fetch records with limit and offset
$select_user = mysqli_query($conn, "SELECT * FROM school_classes
LEFT JOIN school_levels ON school_classes.class_level = school_levels.level_id
LEFT JOIN countries ON school_classes.class_country = countries.id
LEFT JOIN schools ON school_classes.class_school = schools.school_id WHERE $action AND  class_school='$school_ref' $search_query ");
?>

<!--/Header-->

<div class="flex flex-1">
    <!--Sidebar-->
    <?php include('dynamic_side_bar.php');?>
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
                    <div class="bg-gray-200 px-2 py-3 border-solid border-gray-200 border-b flex justify-between items-center">
                        <div>
                            Students &nbsp;
                            <a href="Students_perschool?STATUS=Active"><button class='bg-green-500 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded'>Active</button></a>
                            <a href="Students_perschool?STATUS=Inactive"><button class='bg-red-500 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'>Inactive</button></a>
                            <a href="Add_New_Class_inschool"><button class='bg-blue-800 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'><i class="fas fa-plus"></i>&nbsp;&nbsp;Add New</button></a> 
                        </div>
                        
                    </div>
                    <br> <br>
                    <div>
                            <form method="GET" action="" class="flex">
                                <input type="hidden" name="STATUS" value="<?php echo $STATUS; ?>">
                                <input type="text" name="search_term" placeholder="Search by name, reg no, or contact" 
                                       value="<?php echo htmlspecialchars($search_term); ?>" 
                                       class="px-4 py-2 border rounded-l focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <button type="submit" name="search" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-r">
                                    <i class="fas fa-search"></i> Search
                                </button>
                                <?php if (!empty($search_term)): ?>
                                    <a href="Students_perschool?STATUS=<?php echo $STATUS; ?>" class="ml-2 bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                        Clear
                                    </a>
                                <?php endif; ?>
                            </form>
                        </div>
                    <div class="p-3">
                        <table class="table-responsive w-full rounded">
                            <thead>
                                <tr>
                                    <th class="border w-1/12 px-4 py-2">ID</th>
                                    <th class="border w-1/8 px-4 py-2">Class Name</th>
                                    <th class="border w-1/8 px-4 py-2">Level</th> 
                                    <th class="border w-1/9 px-4 py-2">Status</th>
                                    <th class="border w-1/6 px-4 py-2">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($select_user) > 0): ?>
                                    <?php while($users_details = mysqli_fetch_array($select_user)): ?>
                                        <tr>
                                            <td class="border px-4 py-2"><?php echo $users_details['class_id']; ?></td>
                                            <td class="border px-4 py-2"><?php echo $users_details['class_name']; ?></td>
                                            <td class="border px-4 py-2"><?php echo $users_details['level_name']; ?></td> 
                                            <td class="border px-4 py-2">
                                                <?php if($users_details['class_status'] == "Active"): ?>
                                                    <i class="fas fa-check text-green-500 mx-2"></i>
                                                <?php elseif($users_details['class_status'] == "Inactive"): ?>
                                                    <i class="fas fa-times text-red-500 mx-2"></i>
                                                <?php elseif($users_details['class_status'] == "Deleted"): ?>
                                                    <i class="fas fa-trash text-red-500 mx-2"></i>
                                                <?php elseif($users_details['class_status'] == "Burned"): ?>
                                                    <i class="fas fa-globe text-red-500 mx-2"></i>
                                                <?php elseif($users_details['class_status'] == "Suspended"): ?>
                                                    <i class="fas fa-pause text-red-500 mx-2"></i>
                                                <?php endif; ?>
                                            </td>
                                            <td class="border px-4 py-2">
                                                   
                                                     <a href="Update_delete_classschool?ACTION=UPDATE&ID=<?php echo $users_details['class_id']; ?>" class="bg-green-500 cursor-pointer rounded p-1 mx-1 text-white"><i class="fas fa-edit"></i></a>
                                          
                                                <a href="Update_delete_classschool?ACTION=DELETE&ID=<?php echo $users_details['class_id']; ?>" class="bg-red-500 cursor-pointer rounded p-1 mx-1 text-white"><i class="fas fa-trash"></i></a>
                                              </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="border px-4 py-2 text-center">No students found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!--/Grid Form-->
        </div>
        
        <!-- Enhanced Pagination -->
        <div class="flex justify-center items-center mt-4">
            <?php
            $total_pages = ceil($total_records / $records_per_page);
            
            if ($total_pages > 1) {
                // Previous button
                if ($current_page > 1) {
                    echo "<a href='Students_perschool.php?page=".($current_page-1)."&STATUS=$STATUS".(!empty($search_term) ? "&search_term=$search_term&search=1" : "")."' class='mx-1 px-4 py-2 bg-gray-200 rounded-lg hover:bg-blue-500 hover:text-white'>
                            <i class='fas fa-chevron-left'></i> Previous
                          </a>";
                }
                
                // Page numbers
                $start_page = max(1, $current_page - 2);
                $end_page = min($total_pages, $current_page + 2);
                
                if ($start_page > 1) {
                    echo "<a href='Students_perschool.php?page=1&STATUS=$STATUS".(!empty($search_term) ? "&search_term=$search_term&search=1" : "")."' class='mx-1 px-4 py-2 bg-gray-200 rounded-lg'>1</a>";
                    if ($start_page > 2) {
                        echo "<span class='mx-1 px-2 py-2'>...</span>";
                    }
                }
                
                for ($i = $start_page; $i <= $end_page; $i++) {
                    if ($i == $current_page) {
                        echo "<a href='Students_perschool.php?page=$i&STATUS=$STATUS".(!empty($search_term) ? "&search_term=$search_term&search=1" : "")."' class='mx-1 px-4 py-2 bg-blue-500 text-white rounded-lg'>$i</a>";
                    } else {
                        echo "<a href='Students_perschool.php?page=$i&STATUS=$STATUS".(!empty($search_term) ? "&search_term=$search_term&search=1" : "")."' class='mx-1 px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300'>$i</a>";
                    }
                }
                
                if ($end_page < $total_pages) {
                    if ($end_page < $total_pages - 1) {
                        echo "<span class='mx-1 px-2 py-2'>...</span>";
                    }
                    echo "<a href='Students_perschool.php?page=$total_pages&STATUS=$STATUS".(!empty($search_term) ? "&search_term=$search_term&search=1" : "")."' class='mx-1 px-4 py-2 bg-gray-200 rounded-lg'>$total_pages</a>";
                }
                
                // Next button
                if ($current_page < $total_pages) {
                    echo "<a href='Students_perschool.php?page=".($current_page+1)."&STATUS=$STATUS".(!empty($search_term) ? "&search_term=$search_term&search=1" : "")."' class='mx-1 px-4 py-2 bg-gray-200 rounded-lg hover:bg-blue-500 hover:text-white'>
                            Next <i class='fas fa-chevron-right'></i>
                          </a>";
                }
                
                // Records count
                echo "<div class='ml-4 text-gray-600'>
                        Showing ".($offset + 1)." to ".min($offset + $records_per_page, $total_records)." of $total_records records
                      </div>";
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