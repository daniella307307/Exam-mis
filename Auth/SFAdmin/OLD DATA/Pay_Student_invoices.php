<?php
ob_start(); 
include('header.php');
if(isset($_GET['INVOICE'])){
	 $INVOICE =$_GET['INVOICE'];
	 $STUDENT =$_GET['STUDENT'];
	 $STATUS  =$_GET['STATUS'];
 }
 $select_student = mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM student_list WHERE student_id='$STUDENT'"));
 $details_invoice =mysqli_fetch_array(mysqli_query($conn,"SELECT currency_usd,student_promotion,Country_currency_code,invoice_id,certification_id ,SUM(invoice_usd) AS usd  ,certification_name, SUM(invoice_local) AS local,SUM(pay_amount_usd) AS paid_usd,SUM(pay_amount_local) AS paid_local,Country_currency_code FROM students_invoice
 LEFT JOIN invoice_payments ON students_invoice.invoice_id =invoice_payments.pay_invoice 
LEFT JOIN student_list  ON students_invoice.invoice_student=student_list.student_id
LEFT JOIN countries ON students_invoice.invoice_country = countries.id
LEFT JOIN schools ON students_invoice.invoice_school = schools.school_id
LEFT JOIN regions_table ON students_invoice.ivoice_region = regions_table.region_id
LEFT JOIN certifications ON students_invoice.invoice_certificate = certifications.certification_id  WHERE invoice_student='$STUDENT' AND invoice_id='$INVOICE'"));
  $currency_usd12 = $details_invoice['currency_usd'];
 $student_promotion12 = $details_invoice['student_promotion']; 
 $paid_local =$details_invoice['paid_local']??0;
 $local = $details_invoice['local']??0;
 $Balance_topay = $local-$paid_local;
 $min = $Balance_topay*$setting_pay_min; 
?>
  	   <!--/Header-->

        <div class="flex flex-1">
            <!--Sidebar-->
            <?php include('dynamic_side_bar.php');?>
            <!--/Sidebar-->
            <!--Main-->
<body class="h-screen font-sans login bg-cover">
<div class="container mx-auto h-full flex flex-1 justify-center items-center">
    <div class="w-full max-w-lg">
        <div class="leading-loose">
            <form  action ="" method= "POST" class="max-w-xl m-4 p-10 bg-white rounded shadow-xl">
                <p class="text-gray-800 font-medium">Receive payments from: &nbsp;<strong><?php echo $select_student['student_first_name']."&nbsp;".$select_student['student_last_name'];?></strong>&nbsp; Details</p>
      <?php
	  If(isset($_POST['Receive_payment'])){
		 $pay_invoice = $INVOICE;
		 $pay_bank_sleep =$_POST['pay_bank_sleep'];
		 $payment_student = $STUDENT;
		 $payment_promotion =$student_promotion12;
		 $pay_amount_local = $_POST['Balance_topay'];
		 $usd12 =  $pay_amount_local/$currency_usd12;
		 $pay_amount_usd = $usd12;
		 $payment_country = $user_country;
		 $payment_region =  $user_region;
		 
		 $payment_status  = "Paid";
		 
		 
	    $insert=mysqli_query($conn,"INSERT INTO invoice_payments(payment_id,pay_invoice,pay_bank_sleep,payment_student,payment_promotion,pay_amount_usd,pay_amount_local,payment_country,payment_region,payment_status) VALUES(NULL, '$pay_invoice', '$pay_bank_sleep', '$payment_student', '$payment_promotion', '$pay_amount_usd', '$pay_amount_local', '$payment_country', '$payment_region', '$payment_status')");
		if($insert){
		header('location:Student_invoices?STUDENT='.$STUDENT.'&STATUS='.$STUDENT.'');	
		}														  
																  
	  }
	  
	  ?>
	   
	   <div class="">
				 
                   <label class="block text-sm text-gray-600" for="cus_email">Amount to pay</label>
                    <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="cus_name" name="Amount" value ="<?php echo number_format($details_invoice['local'],2)."&nbsp;".$details_invoice['Country_currency_code'];?>" type="text"   required  placeholder="First Name" aria-label="Name" readonly>
                   <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="cus_name" name="pay_amount_local" value ="<?php echo $details_invoice['local'];?>" type="hidden"   required  placeholder="First Name" aria-label="Name" required>
                
				</div>
				<div class="">
				 
                   <label class="block text-sm text-gray-600" for="cus_email">Paid</label>
                    <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="cus_name" name="user_id" value ="<?php echo number_format($details_invoice['paid_local'],2);?>&nbsp;<?php echo $details_invoice['Country_currency_code'];?>" type="decimal" step="any" required  placeholder="Paid" aria-label="Name" readonly>
                </div>
				 
				<div class="">
				 
                   <label class="block text-sm text-gray-600" for="cus_email">Balance to pay</label>
				   <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="cus_name"  name="Balance_topay" value ="<?php echo $Balance_topay;?>" type="number" min="<?php echo $min;?>" max="<?php echo $Balance_topay; ?>" step="any" required  placeholder="Balance to pay" aria-label="Name">
                </div>
				  
                 <div class="flex flex-wrap -mx-3 mb-2">
                    
					 <div class="w-full md:w-1/1 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="grid-state">
                          BANK SLEEP NO <a href="Add_new_Bank_sleep?STATUS=<?php echo $STATUS;?>&CALL_BACK=Pay_Student_invoices?STUDENT=<?php echo $STUDENT;?>&INVOICE=<?php echo $INVOICE;?>&STATUS=<?php echo $STATUS;?>" class="bg-blue-600 cursor-pointer rounded p-1 mx-1 text-white"><i class="fas fa-plus"></i></a>
                        </label>
                        <div class="relative">
                            <select name="pay_bank_sleep" 
                                class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey"
                                id="grid-state" required>
                               <?php
								$select_sleep= mysqli_query($conn,"SELECT * FROM bank_sleeps
LEFT JOIN banks ON bank_sleeps.sleep_bank = banks.bank_id
LEFT JOIN countries ON banks.bank_country = countries.id WHERE sleep_school='$school_ref' AND sleep_status='Active'");
								while($find_sleep = mysqli_fetch_array($select_sleep)){
								    $sleep_id = $find_sleep['sleep_id'];
							$select_payments = mysqli_fetch_array(mysqli_query($conn,"SELECT SUM(pay_amount_local) AS PAY_LOCAL FROM invoice_payments WHERE pay_bank_sleep ='$sleep_id'"));
								$pay_amount_local = $select_payments['PAY_LOCAL'];
								$sleep_amount_local =  $find_sleep['sleep_amount_local'];
								$diff = $sleep_amount_local-$pay_amount_local;
								 if($diff>0){
								?><option value="<?php echo $find_sleep['sleep_id']; ?>"><?php echo "No:".$find_sleep['sleep_no']."Balance:".number_format($diff,2)."&nbsp;".$find_sleep['Country_currency_code']."@".$find_sleep['bank_name']; ?></option><?php	
									 
								 }
								 else{
								?><option value=""><?php echo "No Bank Sleep Available";?></option><?php	
									 
								 }
									
								 
								}
								?>
                            </select>
                             
                        </div>
                    </div>
					 
                     
                    
                </div>
  
                <div class="mt-4">
                    <center><button type="submit" name ="Receive_payment" class="px-4 py-1 text-white font-light tracking-wider bg-green-500 rounded" type="submit">Add Bank Sleep</button></center>
                </div>
                
            </form>
        </div>
    </div>
</div>

</body>
</html>






