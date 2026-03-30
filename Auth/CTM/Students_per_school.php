<?php
ob_start();
include('header.php');
$SCHOOL =$_GET['SCHOOL'];
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

// Count total records
$count_query = "SELECT COUNT(*) FROM student_list WHERE student_school ='$SCHOOL' AND $action";
$count_result = mysqli_query($conn, $count_query);
$total_records = mysqli_fetch_array($count_result)[0];

// Fetch records with limit and offset
$select_user = mysqli_query($conn, "SELECT * FROM student_list WHERE student_school ='$SCHOOL' AND $action LIMIT $offset, $records_per_page");
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
                    <div class="bg-gray-200 px-2 py-3 border-solid border-gray-200 border-b">
                        Students &nbsp;
                        <a href="Students_per_school?SCHOOL=<?php echo $SCHOOL;?>&STATUS=Active"><button class='bg-green-500 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded'>Active</button></a>
                        <a href="Students_per_school?SCHOOL=<?php echo $SCHOOL;?>&STATUS=Inactive"><button class='bg-red-500 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'>Inactive</button></a>
                        <button onclick="window.print();" class='bg-blue-800 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'><i class="fas fa-print"></i>&nbsp;&nbsp; Print</button>
                        <a href="export_students.php?format=pdf"><button class='bg-blue-800 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'><i class="fas fa-file-pdf"></i>&nbsp;&nbsp;PDF</button></a>
                        <a href="export_students.php?format=excel"><button class='bg-blue-800 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'><i class="fas fa-file-excel"></i>&nbsp;&nbsp;Excel</button></a>
                    </div>
                    <div class="p-3">
                        <table class="table-responsive w-full rounded">
                            <thead>
                                <tr>
                                    <th class="border w-1/12 px-4 py-2">REG No</th>
                                    <th class="border w-1/8 px-4 py-2">Names</th>
                                    <th class="border w-1/8 px-4 py-2">Gender</th>
                                    <th class="border w-1/6 px-4 py-2">Dob</th>
                                    <th class="border w-1/6 px-4 py-2">Contact</th>
                                    <th class="border w-1/9 px-4 py-2">Status</th>
                                    <th class="border w-1/6 px-4 py-2">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($users_details = mysqli_fetch_array($select_user)) { ?>
                                    <tr>
                                        <td class="border px-4 py-2"><?php echo $users_details['student_regno']; ?></td>
                                        <td class="border px-4 py-2"><?php echo $users_details['student_first_name']." ".$users_details['student_last_name']; ?></td>
                                        <td class="border px-4 py-2"><?php echo $users_details['student_gender']; ?></td>
                                        <td class="border px-4 py-2"><?php echo $users_details['student_dob']; ?></td>
                                        <td class="border px-4 py-2"><?php echo $users_details['student_contact']; ?></td>
                                        <td class="border px-4 py-2">
                                            <?php if($users_details['student_status'] == "Active") { ?>
                                                <i class="fas fa-check text-green-500 mx-2"></i>
                                            <?php } elseif($users_details['student_status'] == "Inactive") { ?>
                                                <i class="fas fa-times text-red-500 mx-2"></i>
                                            <?php } elseif($users_details['student_status'] == "Deleted") { ?>
                                                <i class="fas fa-trash text-red-500 mx-2"></i>
                                            <?php } elseif($users_details['student_status'] == "Burned") { ?>
                                                <i class="fas fa-globe text-red-500 mx-2"></i>
                                            <?php } elseif($users_details['student_status'] == "Suspended") { ?>
                                                <i class="fas fa-pause text-red-500 mx-2"></i>
                                            <?php } ?>
                                        </td>
                                        <td class="border px-4 py-2">
                                            <a href="Student_details?ID=<?php echo $users_details['student_id']; ?>" class="bg-blue-500 cursor-pointer rounded p-1 mx-1 text-white"><i class="fas fa-eye"></i></a>
                                            <a href="Update_Student_details?ID=<?php echo $users_details['student_id']; ?>" class="bg-green-600 cursor-pointer rounded p-1 mx-1 text-white"><i class="fas fa-edit"></i></a>
                                        </td>
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
