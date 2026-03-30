<?php
ob_start();
include('header.php');
$SLEEP_No= $_GET['SLEEP_No'];
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
$count_query = "SELECT COUNT(*) FROM student_list WHERE student_school ='$school_ref' AND $action";
$count_result = mysqli_query($conn, $count_query);
$total_records = mysqli_fetch_array($count_result)[0];

// Fetch records with limit and offset
$select_user = mysqli_query($conn, " SELECT student_id,SUM(pay_amount_local) AS PAID,student_first_name,student_last_name,student_gender,student_regno FROM invoice_payments 
LEFT JOIN student_list ON invoice_payments.payment_student =student_list.student_id
WHERE  pay_bank_sleep='1' GROUP BY   payment_student  LIMIT $offset, $records_per_page");
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
                         <button onclick="window.print();" class='bg-blue-800 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'><i class="fas fa-print"></i>&nbsp;&nbsp; Print</button>
                          <big>
<?php
$find_sleep = mysqli_query($conn,"SELECT bank_name,sleep_amount_local,sleep_no,SUM(pay_amount_local) AS USED FROM bank_sleeps
LEFT JOIN banks ON bank_sleeps.sleep_bank = banks.bank_id 
LEFT JOIN invoice_payments ON bank_sleeps.sleep_id = invoice_payments.pay_bank_sleep

WHERE sleep_id='$SLEEP_No'");
$sleep_details = mysqli_fetch_array($find_sleep);


$sleep_amount_local =$sleep_details['sleep_amount_local'];
$USED = $sleep_details['USED'];
$Balance12 = $sleep_amount_local-$USED;
?>

						<p>Bank Sleep No:<?php echo $sleep_details['sleep_no'];?></p>
						  <p>Bank Name:<?php echo $sleep_details['bank_name'];?></p>
						  <p>Deposite Amount:&nbsp;<strong><?php echo number_format($sleep_details['sleep_amount_local'],2)."&nbsp;".$Country_currency_code;?></strong></p>
						  <p>Used Amount:&nbsp;<strong><?php echo number_format($sleep_details['USED'],2)."&nbsp;".$Country_currency_code;?></strong></p>
						  <p>Balance: &nbsp;<strong><?php echo  number_format($Balance12,2)."&nbsp;".$Country_currency_code;?></strong></p>
						  </big>
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
   $paid = $users_details['PAID'];
   $find_invoice = mysqli_fetch_array(mysqli_query($conn,"SELECT SUM(invoice_local) AS invoices FROM students_invoice  WHERE  invoice_student='$student_id'"));
   $invoices = $find_invoice['invoices'];	
   $Balance13 = $invoices- $paid;  
					
					
					
					
								?>
                                    <tr>
									
                                        <td class="border px-4 py-2"><?php echo $users_details['student_regno']; ?></td>
                                        <td class="border px-4 py-2"><?php echo $users_details['student_first_name']." ".$users_details['student_last_name']; ?></td>
                                        <td class="border px-4 py-2"><?php echo $users_details['student_gender']; ?></td>
                                         
                                         <td class="border px-4 py-2"><?php echo number_format($invoices,2)."&nbsp;".$Country_currency_code;?></td>
 <td class="border px-4 py-2"> <?php echo number_format( $paid,2)."&nbsp;".$Country_currency_code;?> </td>
										 <td class="border px-4 py-2"><?php echo number_format( $Balance13,2);?></td>
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
