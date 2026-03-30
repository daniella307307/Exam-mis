<?php
ob_start();
include('header.php');
$STUDENT =$_GET['STUDENT'];
$STATUS =$_GET['STATUS']; 
$student_details = mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM student_list
LEFT JOIN countries ON student_list.student_country = countries.id
LEFT JOIN schools ON student_list.student_school = schools.school_id 
 WHERE student_id='$STUDENT'"));
$select_user = mysqli_query($conn, "SELECT * FROM students_invoice
LEFT JOIN countries ON students_invoice.invoice_country = countries.id
LEFT JOIN schools ON students_invoice.invoice_school = schools.school_id
LEFT JOIN regions_table ON students_invoice.ivoice_region = regions_table.region_id
LEFT JOIN certifications ON students_invoice.invoice_certificate = certifications.certification_id WHERE invoice_student='$STUDENT'");
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
                        
                        <a href="Students_perschool?STATUS=Active"><button class='bg-green-500 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded'>Active</button></a>
                        <a href="Students_perschool?STATUS=Inactive"><button class='bg-red-500 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'>Inactive</button></a>
                        <a href="Add_New_Student"><button class='bg-blue-800 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'><i class="fas fa-plus"></i>&nbsp;&nbsp;Add</button></a>
                        <a href="upload_Students"><button class='bg-blue-800 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'><i class="fas fa-upload"></i>&nbsp;&nbsp; Upload </button></a>
                        <button onclick="window.print();" class='bg-blue-800 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'><i class="fas fa-print"></i>&nbsp;&nbsp; Print</button>
                        <a href="export_students.php?format=pdf"><button class='bg-blue-800 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'><i class="fas fa-file-pdf"></i>&nbsp;&nbsp;PDF</button></a>
                        <a href="export_students.php?format=excel"><button class='bg-blue-800 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'><i class="fas fa-file-excel"></i>&nbsp;&nbsp;Excel</button></a>
                    </div>
                    <div class="p-3">
					<center><big>All Invoices for :<strong><?PHP echo $student_details['student_first_name']."&nbsp;".$student_details['student_last_name']?></strong></big></center><br>
                        <table class="table-responsive w-full rounded">
                            <thead>
                                <tr>
                                    <th class="border w-1/12 px-4 py-2">#</th>
                                    <th class="border w-1/8 px-4 py-2">Certification</th>
									 <th class="border w-1/8 px-4 py-2">Year</th>
                                    <th class="border w-1/8 px-4 py-2">Amount to pay</th>
                                    <th class="border w-1/9 px-4 py-2">Paid</th>
                                    <th class="border w-1/9 px-4 py-2">Balance</th>
									<th class="border w-1/9 px-4 py-2">Action</th>
                                </tr>
                            </thead>
                            <tbody>
 <?php while($users_details = mysqli_fetch_array($select_user)) {
	 $invoice_id   =$users_details['invoice_id'];
	 $currency = $users_details['Country_currency_code'];
					$find_payment =mysqli_query($conn,"SELECT promotion_year,invoice_id,certification_id ,SUM(invoice_usd) AS usd  ,certification_name, SUM(invoice_local) AS local,SUM(pay_amount_usd) AS paid_usd,SUM(pay_amount_local) AS paid_local,Country_currency_code  FROM students_invoice   
LEFT JOIN schools ON students_invoice.invoice_school = schools.school_id
LEFT JOIN countries ON students_invoice.invoice_country = countries.id
LEFT JOIN invoice_payments ON students_invoice.invoice_id = invoice_payments.pay_invoice
LEFT JOIN certifications ON students_invoice.invoice_certificate = certifications.certification_id
LEFT JOIN students_promotion ON students_invoice.invoice_promotion= students_promotion.promotion_id  WHERE invoice_student='$STUDENT' GROUP BY invoice_id");
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
					$Balance2 = $local-$paid_local;
					
								?>
                                    <tr>
									
                                        <td class="border px-4 py-2"><?php echo $users_details['certification_id']; ?></td>
                                        <td class="border px-4 py-2"><?php echo $users_details['certification_name']; ?></td> 
                                        <td class="border px-4 py-2"><?php echo $payments['promotion_year']; ?></td>
										<td class="border px-4 py-2"><?php echo number_format($local,2)."&nbsp;".$Country_currency_code;?></td>
										 <td class="border px-4 py-2"><?php echo number_format($paid_local,2)."&nbsp;".$Country_currency_code;?></td>
                                         <td class="border px-4 py-2"><?php echo number_format($Balance2,2)."&nbsp;".$Country_currency_code;?></td>
                                        
										<td class="border px-4 py-2">
										<?PHP
										if($Balance2>0){
										?><a href="Pay_Student_invoices?STUDENT=<?PHP echo $STUDENT ?>&INVOICE=<?php echo $users_details['invoice_id']; ?>&STATUS=<?php echo $STATUS;?>" class="bg-blue-600 cursor-pointer rounded p-1 mx-1 text-white">Receive Payment  &nbsp;<i class="fas fa-book"></i></a>
                                        <?php	
										}
										else{
									?><a href="#" class="bg-red-600 cursor-pointer rounded p-1 mx-1 text-white">Paid  &nbsp;<i class="fas fa-book"></i></a>
                                        <?php		
										}
										?>
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
        
    </main>
    <!--/Main-->
</div>

<!--Footer-->
<?php include('footer.php')?>
<!--/footer-->

<script src="../../main.js"></script>

</body>
</html>
