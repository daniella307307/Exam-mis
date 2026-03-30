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

// Search functionality
$search_query = "";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $search_query = " AND (student_regno LIKE '%$search%' OR 
                          student_first_name LIKE '%$search%' OR 
                          student_last_name LIKE '%$search%')";
}

// Count total records
$count_query = "SELECT COUNT(*) FROM student_list WHERE student_school ='$school_ref' AND $action $search_query";
$count_result = mysqli_query($conn, $count_query);
$total_records = mysqli_fetch_array($count_result)[0];

// Fetch records with limit and offset
$select_user = mysqli_query($conn, "SELECT * FROM student_list
LEFT JOIN countries ON student_list.student_country = countries.id 
WHERE student_school ='$school_ref' AND $action $search_query 
LIMIT $offset, $records_per_page");
?>

<!--/Header-->

<div class="flex flex-1">
    <!--Sidebar-->
    <?php include('payment_side_bar.php');?>
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
                        <a href="Students_perschool?STATUS=Active"><button class='bg-green-500 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded'>Active</button></a>
                        <a href="Students_perschool?STATUS=Inactive"><button class='bg-red-500 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'>Inactive</button></a>
                        <a href="Add_New_Student"><button class='bg-blue-800 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'><i class="fas fa-plus"></i>&nbsp;&nbsp;Add</button></a>
                        <a href="upload_Students"><button class='bg-blue-800 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'><i class="fas fa-upload"></i>&nbsp;&nbsp; Upload </button></a>
                        <button onclick="window.print();" class='bg-blue-800 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'><i class="fas fa-print"></i>&nbsp;&nbsp; Print</button>
                        <a href="export_students.php?format=pdf"><button class='bg-blue-800 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'><i class="fas fa-file-pdf"></i>&nbsp;&nbsp;PDF</button></a>
                        <a href="export_students.php?format=excel"><button class='bg-blue-800 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'><i class="fas fa-file-excel"></i>&nbsp;&nbsp;Excel</button></a>
                        <br><br>
                        <!-- Search Form -->
                        <form method="GET" action="" class="inline-block ml-2">
                            <input type="hidden" name="STATUS" value="<?php echo $STATUS; ?>">
                            <div class="flex">
                                <input type="text" name="search" placeholder="Search by name or reg no" 
                                       value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                                       class="px-4 py-2 border rounded-l focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-r">
                                    <i class="fas fa-search"></i> Search
                                </button>
                                <?php if(isset($_GET['search']) && !empty($_GET['search'])): ?>
                                    <a href="Students_Payments?STATUS=<?php echo $STATUS; ?>" class="ml-2 bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                        Clear
                                    </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                    <div class="p-3">
                        <table class="table-responsive w-full rounded">
                            <thead>
                                <tr>
                                    <th class="border w-1/12 px-4 py-2">REG No</th>
                                    <th class="border w-1/8 px-4 py-2">Names</th> 
                                    <th class="border w-1/9 px-4 py-2">To pay</th>
                                    <th class="border w-1/9 px-4 py-2">Paid</th>
                                    <th class="border w-1/9 px-4 py-2">Balance</th>
                                    <th class="border w-1/9 px-4 py-2">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                
                               
                                
 <?php while($users_details = mysqli_fetch_array($select_user)) {
     $student_id  =$users_details['student_id'];
     $currency = $users_details['Country_currency_code'];
                    $find_payment =mysqli_query($conn,"SELECT sum(invc_amount) AS topay FROM school_invoice WHERE invc_student='$student_id' and invc_term ='$setting_term' AND invc_year='$setting_year'");
                  
            
                    $inc=mysqli_num_rows($find_payment);
                    if($inc>0){
                    $payments =mysqli_fetch_array($find_payment);
                    $topay =(float)$payments['topay'];
                     $select_paID = mysqli_query($conn, " SELECT SUM(spay_amount) AS paid  FROM school_payment_details WHERE spay_student ='$student_id' AND spay_year='$setting_year'AND  spay_term='$setting_term'");
$pay_details = mysqli_fetch_array($select_paID);
                    
                     $paid =(float)$pay_details['paid'];
                    
                    
                    }
                    else{
                   $topay =0;
                    $paid =0;
                    
                    }
                    $Balance = $topay-$paid; 
                    
                                ?>
                                    <tr>
                                    
                                        <td class="border px-4 py-2"><?php echo $users_details['student_regno']; ?></td>
                                        <td class="border px-4 py-2"><?php echo $users_details['student_first_name']." ".$users_details['student_last_name']; ?></td> 
                                         
                                         <td class="border px-4 py-2 bg-blue-600 text-white"><?php echo number_format($topay,0);?> FRW</td>
 <td class="border px-4 py-2 bg-green-600 text-white"> <?php echo number_format($paid,2) ;?> FRW</td>
                                         <td class="border px-4 py-2 bg-red-600 text-white"><?php echo number_format($Balance,2);?> FRW</td>
                                        <td class="border px-4 py-2">
                                             <a href="Student_invoices?STUDENT=<?php echo $users_details['student_id']; ?>&STATUS=<?php echo $STATUS;?>" class="bg-blue-600 cursor-pointer rounded p-1 mx-1 text-white"> &nbsp;<i class="fas fa-eye"></i></a>
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
                        echo "<a href='Students_Payments?page=$i&STATUS=$STATUS".(isset($_GET['search']) ? "&search=".urlencode($_GET['search']) : "")."' class='mx-1 p-2 bg-blue-500 text-white'>$i</a>";
                    } else {
                        echo "<a href='Students_Payments?page=$i&STATUS=$STATUS".(isset($_GET['search']) ? "&search=".urlencode($_GET['search']) : "")."' class='mx-1 p-2 bg-gray-200'>$i</a>";
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