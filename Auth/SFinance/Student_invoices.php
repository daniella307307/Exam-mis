<?php
ob_start();
include('header.php');
$STUDENT = $_GET['STUDENT'];
$STATUS = $_GET['STATUS']; 
$student_details = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM student_list
LEFT JOIN countries ON student_list.student_country = countries.id
LEFT JOIN schools ON student_list.student_school = schools.school_id 
WHERE student_id='$STUDENT'"));

 ///////////////////
 $select_TOpay = mysqli_query($conn, "SELECT sum(invc_amount) AS topay FROM school_invoice WHERE invc_student='$STUDENT' and invc_term ='$setting_term' AND invc_year='$setting_year'");
$topay_details = mysqli_fetch_array($select_TOpay);
 $select_paID = mysqli_query($conn, " SELECT SUM(spay_amount) AS paid  FROM school_payment_details WHERE spay_student ='$STUDENT' AND spay_year='$setting_year'AND  spay_term='$setting_term'");
$pay_details = mysqli_fetch_array($select_paID);



$topay = (float)$topay_details['topay'];
$paid = (float)$pay_details['paid'];
$balance = $topay - $paid;
 
 //////////////////
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
                        <a href="Add_New_payment_to_Student?STUDENT=<?PHP echo $STUDENT;?>&STATUS=<?PHP echo $STATUS;?>">
                            <button class='bg-blue-800 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'>
                                <i class="fas fa-plus"></i>&nbsp;&nbsp;Add Payment details 
                            </button>
                        </a>
                        
                        <button onclick="window.print();" class='bg-blue-800 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'>
                            <i class="fas fa-print"></i>&nbsp;&nbsp; Print
                        </button>
                      
                        &nbsp; &nbsp;
                        <a href="Students_perschool?STATUS=Active">
                            <button class='bg-red-500 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded'>Term I</button>
                        </a>
                        &nbsp;<a href="Students_perschool?STATUS=Inactive">
                            <button class='bg-blue-500 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'>Term II</button>
                        </a>
                        &nbsp; <a href="Students_perschool?STATUS=Inactive">
                            <button class='bg-green-500 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'>Term III</button>
                        </a>
                    </div>
                    <div class="p-3">
                        <div class="max-w-2xl mx-auto"> <!-- Centering wrapper added here -->
                          <!--  <center>
                                <big>All Invoices for: <strong><?PHP echo $student_details['student_first_name']."&nbsp;".$student_details['student_last_name']?></strong></big>
                            </center><br>
                            <center>
                                <big>Year/Term: <strong><?PHP echo $setting_year."/".$setting_term;?></strong></big>
                            </center><br>
                            -->
                            <?PHP
                            $find_payment = mysqli_query($conn, "SELECT * FROM school_invoice
                            LEFT JOIN school_payment_settings ON school_invoice.invc_ref = school_payment_settings.spayment_id  
                            WHERE invc_student ='$STUDENT'  AND invc_term ='$setting_term ' AND invc_year='$setting_year'");
                            ?>
                            <br><br>
                            <table class="table-responsive w-full rounded">
                                <thead>
                                    <tr>
                                        <th colspan="4" class="border w-1/12 px-4 py-2">
                                            <big>Student Name: <strong><?PHP echo $student_details['student_first_name']."&nbsp;".$student_details['student_last_name']?></strong></big>
                                        </th>   
                                    </tr>
                                    <tr>
                                        <th colspan="4" class="border w-1/12 px-4 py-2">
                                            <big>Term/Year: <strong><?PHP echo $setting_term."/".$setting_year;?></strong></big>
                                        </th>   
                                    </tr>
                                    <tr>
                                        <th class="border w-1/12 px-4 py-2">#</th>
                                        <th class="border w-1/8 px-4 py-2">TYPE OF FEE</th>
                                        <th class="border w-1/8 px-4 py-2">Year</th>
                                        <th class="border w-1/8 px-4 py-2">Amount to pay</th>  
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $x = 0;
                                    while($payments = mysqli_fetch_array($find_payment)) {
                                        $x++;
                                    ?>
                                    <tr>
                                        <td class="border px-4 py-2"><?php echo $x;?></td>
                                        <td class="border px-4 py-2"><?php echo $payments['spayment_description'];?></td> 
                                        <td class="border px-4 py-2"><?php echo $payments['spayment_year'];?></td>
                                        <td class="border px-4 py-2"><?php echo (float)$payments['spayment_amount'];?>&nbsp; FRW</td>
                                    </tr>    
                                    <?php } ?>
                                    <tr>
                                        <td colspan="3" class="border px-4 py-2 bg-blue-300"><strong>Grand Total to Pay:</strong></td>
                                        <th class="border w-1/8 px-4 py-2 bg-blue-300"><?php echo  (float)$topay;?>&nbsp; FRW</th>  
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="border px-4 py-2 bg-green-300"><strong>Grand Total Paid:</strong></td>
                                        <th class="border w-1/8 px-4 py-2 bg-green-300"><?php echo (float)$paid;?>&nbsp; FRW</th>  
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="border px-4 py-2 bg-red-300"><strong>Balance:</strong></td>
                                        <th class="border w-1/8 px-4 py-2 bg-red-300"><?php echo (float)$balance;?>&nbsp; FRW</th>  
                                    </tr>
                                </tbody>
                            </table>
                        </div> <!-- End of centering wrapper -->
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

<script src="../../main.js"></script>

</body>
</html>