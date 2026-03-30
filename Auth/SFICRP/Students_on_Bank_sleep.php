<?php
ob_start();
include('header.php');

// Set the number of records to display per page
$records_per_page = 100;
$SLEEP_No =$_GET['SLEEP_No'];
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
$count_query = "SELECT COUNT(*) FROM student_list WHERE student_school ='$school_ref' AND $action";
$count_result = mysqli_query($conn, $count_query);
$total_records = mysqli_fetch_array($count_result)[0];

// Fetch records with limit and offset
$select_user = mysqli_query($conn, "SELECT * FROM student_list
LEFT JOIN countries ON student_list.student_country = countries.id WHERE student_school ='$school_ref' AND $action LIMIT $offset, $records_per_page");
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
                        <a href="Students_perschool?STATUS=Active"><button class='bg-green-500 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded'>Active</button></a>
                        <a href="Students_perschool?STATUS=Inactive"><button class='bg-red-500 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'>Inactive</button></a> 
                        <a href="Bank_sleep_distribution.php?SLEEP_No=<?php echo $SLEEP_No;?>"><button class='bg-blue-800 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'><i class="fas fa-users"></i>&nbsp;&nbsp; Students Distribution</button></a>
                        <button onclick="window.print();" class='bg-blue-800 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'><i class="fas fa-print"></i>&nbsp;&nbsp; Print</button>
                       
                    </div>
                    <div class="p-3">
                        <table class="table-responsive w-full rounded">
                            <thead>
                                <tr>
                                    <th class="border w-1/12 px-4 py-2">REG No</th>
                                    <th class="border w-1/8 px-4 py-2">Names</th>
                                    <th class="border w-1/8 px-4 py-2">Gender</th> 
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
					$find_payment =mysqli_query($conn,"SELECT SUM(invoice_usd) AS usd  , SUM(invoice_local) AS local,SUM(pay_amount_usd) AS paid_usd,SUM(pay_amount_local) AS paid_local,Country_currency_code  FROM students_invoice   
LEFT JOIN schools ON students_invoice.invoice_school = schools.school_id
LEFT JOIN countries ON students_invoice.invoice_country = countries.id
LEFT JOIN invoice_payments ON students_invoice.invoice_id = invoice_payments.pay_invoice WHERE invoice_student='$student_id'");
					$inc=mysqli_num_rows($find_payment);
					if($inc>0){
					$payments =mysqli_fetch_array($find_payment);
					$usd =$payments['usd'];
					$local =$payments['local'];
					$paid_usd= $payments['paid_usd'];
					$paid_local= $payments['paid_local'];
					$Country_currency_code=$payments['Country_currency_code'];
					
					}
					else{
					$usd =0;
					$local =0;
					$paid_usd= 0;
					$paid_local= 0;
					$Country_currency_code=$currency;	
					}
					$Balance = $usd-$paid_usd;
					$Balance2 = $local-$paid_usd-$paid_local;
					
								?>
                                    <tr>
									
                                        <td class="border px-4 py-2"><?php echo $users_details['student_regno']; ?></td>
                                        <td class="border px-4 py-2"><?php echo $users_details['student_first_name']." ".$users_details['student_last_name']; ?></td>
                                        <td class="border px-4 py-2"><?php echo $users_details['student_gender']; ?></td>
                                         
                                         <td class="border px-4 py-2"><?php echo number_format($usd,2);?> $</td>
 <td class="border px-4 py-2"> <?php if(!empty($paid_usd)){echo number_format($paid_usd,2);}else{echo "0";}?> $</td>
										 <td class="border px-4 py-2"><?php echo number_format($Balance,2);?> $</td>
                                        <td class="border px-4 py-2">
                                             <a href="Student_invoices?STUDENT=<?php echo $users_details['student_id']; ?>&STATUS=<?php echo $STATUS;?>" class="bg-blue-600 cursor-pointer rounded p-1 mx-1 text-white">View Invoices &nbsp;<i class="fas fa-eye"></i></a>
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
