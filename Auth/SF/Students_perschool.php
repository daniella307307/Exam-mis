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
        $action = "student_status ='$status'";
    } else {
        $status = "Inactive";
        $action = "student_status !='Active' OR student_status ='$status'";
    }
} else {
    $STATUS = "Active";
    $status = $STATUS;
    $action = "student_status ='Active'";
}

// Check if search form was submitted
if (isset($_GET['search']) && !empty($_GET['search_term'])) {
    $search_term = mysqli_real_escape_string($conn, $_GET['search_term']);
    $search_query = " AND (student_first_name LIKE '%$search_term%' 
                          OR student_last_name LIKE '%$search_term%'
                          OR student_regno LIKE '%$search_term%'
                          OR student_contact LIKE '%$search_term%')";
}

// Count total records
$count_query = "SELECT COUNT(*) FROM student_list WHERE student_school ='$school_ref' AND $action $search_query";
$count_result = mysqli_query($conn, $count_query);
$total_records = mysqli_fetch_array($count_result)[0];

// Fetch records with limit and offset
$select_user = mysqli_query($conn, "SELECT * FROM student_list 
                                   WHERE student_school ='$school_ref' AND $action $search_query 
                                   ORDER BY student_regno DESC 
                                   LIMIT $offset, $records_per_page");
?>

<!-- PRINT STYLE (ONLY THING ADDED/EDITED) -->
<style>
@media print {

    /* Hide everything by default */
    body * {
        visibility: hidden !important;
    }

    /* Show only H1s */
    h1, h1 * {
        visibility: visible !important;
    }

    /* Show only the table */
    table, table * {
        visibility: visible !important;
    }

    /* Keep print layout aligned */
    h1, table {
        position: relative !important;
        left: 0 !important;
        top: 0 !important;
    }

    /* HIDE ACTIONS COLUMN (header + cells) */
    table th:last-child,
    table td:last-child {
        display: none !important;
        visibility: hidden !important;
    }
}
</style>
<!-- END PRINT STYLE -->

<!--/Header-->

<div class="flex flex-1">
    <!--Sidebar-->
    <?php include('dynamic_side_bar.php');?>
    <!--/Sidebar-->
    <!--Main-->
    <main class="bg-white-500 flex-1 p-3 overflow-hidden">
        <div class="flex flex-col">
            <div class="flex flex-1 flex-col md:flex-row lg:flex-row mx-2"></div>

            <!--Grid Form-->
            <div class="flex flex-1 flex-col md:flex-row lg:flex-row mx-2">
                <div class="mb-2 border-solid border-gray-300 rounded border shadow-sm w-full">
                    <div class="bg-gray-200 px-2 py-3 border-solid border-gray-200 border-b flex justify-between items-center">
                        <div>
                            Students &nbsp;
                            <a href="Students_perschool?STATUS=Active"><button class='bg-green-500 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded'>Active</button></a>
                            <a href="Students_perschool?STATUS=Inactive"><button class='bg-red-500 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'>Inactive</button></a>
                            <a href="Add_New_Student"><button class='bg-blue-800 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'><i class="fas fa-plus"></i>&nbsp;&nbsp;Add</button></a>
                            <a href="upload_Students"><button class='bg-blue-800 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'><i class="fas fa-upload"></i>&nbsp;&nbsp; Upload </button></a>
                            <button onclick="window.print();" class='bg-blue-800 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'><i class="fas fa-print"></i>&nbsp;&nbsp; Print</button>
                            <a href="export_students.php?format=pdf"><button class='bg-blue-800 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'><i class="fas fa-file-pdf"></i>&nbsp;&nbsp;PDF</button></a>
                            <a href="export_students.php?format=excel"><button class='bg-blue-800 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'><i class="fas fa-file-excel"></i>&nbsp;&nbsp;Excel</button></a>
                        </div>
                    </div>

                    <br><br>

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
                                <a href="Students_perschool?STATUS=<?php echo $STATUS; ?>" 
                                   class="ml-2 bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                    Clear
                                </a>
                            <?php endif; ?>
                        </form>
                    </div>

                    <div class="p-3">
                        <h1>Student List</h1>
                        <h1>School Name:<strong><big><?php echo $school_name;?></big></strong></h1>
                        <h1>Year:<strong><big><?php echo DATE("Y");?></big></strong></h1>

                        <table class="table-responsive w-full rounded">
                            <thead>
                                <tr>
                                    <th class="border w-1/12 px-4 py-2">REG No</th>
                                    <th class="border w-1/8 px-4 py-2">Names</th>
                                    <th class="border w-1/8 px-4 py-2">Gender</th>
                                    <th class="border w-1/6 px-4 py-2">Dob</th> 
                                    <th class="border w-1/9 px-4 py-2">Status</th>
                                    <th class="border w-1/6 px-4 py-2">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($select_user) > 0): ?>
                                    <?php while($users_details = mysqli_fetch_array($select_user)): ?>
                                        <tr>
                                            <td class="border px-4 py-2"><?php echo $users_details['student_regno']; ?></td>
                                            <td class="border px-4 py-2"><?php echo $users_details['student_first_name']." ".$users_details['student_last_name']; ?></td>
                                            <td class="border px-4 py-2"><?php echo $users_details['student_gender']; ?></td>
                                            <td class="border px-4 py-2"><?php echo $users_details['student_dob']; ?></td> 
                                            <td class="border px-4 py-2">
                                                <?php if($users_details['student_status'] == "Active"): ?>
                                                    <i class="fas fa-check text-green-500 mx-2"></i>
                                                <?php elseif($users_details['student_status'] == "Inactive"): ?>
                                                    <i class="fas fa-times text-red-500 mx-2"></i>
                                                <?php elseif($users_details['student_status'] == "Deleted"): ?>
                                                    <i class="fas fa-trash text-red-500 mx-2"></i>
                                                <?php elseif($users_details['student_status'] == "Burned"): ?>
                                                    <i class="fas fa-globe text-red-500 mx-2"></i>
                                                <?php elseif($users_details['student_status'] == "Suspended"): ?>
                                                    <i class="fas fa-pause text-red-500 mx-2"></i>
                                                <?php endif; ?>
                                            </td>
                                            <td class="border px-4 py-2">
                                                <a href="Student_details?ID=<?php echo $users_details['student_id']; ?>" class="bg-blue-500 cursor-pointer rounded p-1 mx-1 text-white"><i class="fas fa-eye"></i></a>
                                                <a href="Update_Student_details?ID=<?php echo $users_details['student_id']; ?>" class="bg-green-600 cursor-pointer rounded p-1 mx-1 text-white"><i class="fas fa-edit"></i></a>
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

        <!-- Pagination -->
        <div class="flex justify-center items-center mt-4">
            <?php
            $total_pages = ceil($total_records / $records_per_page);
            
            if ($total_pages > 1) {

                if ($current_page > 1) {
                    echo "<a href='Students_perschool.php?page=".($current_page-1)."&STATUS=$STATUS".(!empty($search_term) ? "&search_term=$search_term&search=1" : "")."' 
                            class='mx-1 px-4 py-2 bg-gray-200 rounded-lg hover:bg-blue-500 hover:text-white'>
                            <i class='fas fa-chevron-left'></i> Previous
                          </a>";
                }

                $start_page = max(1, $current_page - 2);
                $end_page = min($total_pages, $current_page + 2);

                if ($start_page > 1) {
                    echo "<a href='Students_perschool.php?page=1&STATUS=$STATUS' class='mx-1 px-4 py-2 bg-gray-200 rounded-lg'>1</a>";
                    if ($start_page > 2) echo "<span class='mx-1 px-2 py-2'>...</span>";
                }

                for ($i = $start_page; $i <= $end_page; $i++) {
                    if ($i == $current_page) {
                        echo "<a href='#' class='mx-1 px-4 py-2 bg-blue-500 text-white rounded-lg'>$i</a>";
                    } else {
                        echo "<a href='Students_perschool.php?page=$i&STATUS=$STATUS' class='mx-1 px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300'>$i</a>";
                    }
                }

                if ($end_page < $total_pages) {
                    if ($end_page < $total_pages - 1) echo "<span class='mx-1 px-2 py-2'>...</span>";
                    echo "<a href='Students_perschool.php?page=$total_pages&STATUS=$STATUS' class='mx-1 px-4 py-2 bg-gray-200 rounded-lg'>$total_pages</a>";
                }

                if ($current_page < $total_pages) {
                    echo "<a href='Students_perschool.php?page=".($current_page+1)."&STATUS=$STATUS' 
                            class='mx-1 px-4 py-2 bg-gray-200 rounded-lg hover:bg-blue-500 hover:text-white'>
                            Next <i class='fas fa-chevron-right'></i>
                          </a>";
                }

                echo "<div class='ml-4 text-gray-600'>
                        Showing ".($offset + 1)." to ".min($offset + $records_per_page, $total_records)." of $total_records records
                      </div>";
            }
            ?>
        </div>

    </main>
</div>

<?php include('footer.php')?>
<script src="../../main.js"></script>

</body>
</html>
