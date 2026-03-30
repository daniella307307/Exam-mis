<?php
ob_start();
include('header.php');
$STUDENT=$_GET['STUDENT'];
$STATUS =$_GET['STATUS']; 
$select_student = mysqli_query($conn,"SELECT * FROM student_list
LEFT JOIN school_levels ON   student_list.student_level=school_levels.level_id
LEFT JOIN school_classes ON   student_list.student_class=school_classes.class_id WHERE student_id='$STUDENT'");
$student_details = mysqli_fetch_array($select_student);

 


?>

<!DOCTYPE html>
<html>
<head>
    <title>Update User Details</title>
</head>
<body class="h-screen font-sans login bg-cover">

<!--/Header-->
<div class="flex flex-1">
    <!--Sidebar-->
    <?php include('dynamic_side_bar.php'); ?>
    <!--/Sidebar-->
    
    <!--Main-->
    <div class="container mx-auto h-full flex flex-1 justify-center items-center">
        <div class="w-full max-w-lg">
            <div class="leading-loose">
                <form action="" method="POST" class="max-w-xl m-4 p-10 bg-white rounded shadow-xl">
        
                  
                  
                  
                    <p class="text-gray-800 font-medium"><center>Add New Payment to :</center><?php echo $student_details['student_first_name']."&nbsp;".$student_details['student_last_name'];?>&nbsp;<strong> </strong> </p>
                     <?php
					if(isset($_POST['Update'])){
					 $spay_amount =$_POST['spay_amount'];  
					 $Account_umber = $_POST['Account_umber'];
                  $Payment_mode  =$_POST['Payment_mode'];
                  $Transaction_ID =$_POST['Transaction_ID'];
                  $Registerd_Date  =$_POST['Registerd_Date'];
                  $Transaction_Date =$_POST['Transaction_Date'];
                  $Payment_status  =$_POST['Payment_status'];
                  
                  $Select_duplicated =  mysqli_query($conn,"SELECT * FROM school_payment_details 
LEFT JOIN student_list ON school_payment_details.spay_student = student_list.student_id
LEFT JOIN school_levels ON  student_list.student_level =school_levels.level_id
LEFT JOIN school_classes ON  student_list.student_level =school_classes.class_id WHERE spay_reference='$Transaction_ID' ");
                  $count_dup = mysqli_num_rows($Select_duplicated);
                  if($count_dup>0){
                      
        $details_find = mysqli_fetch_array($Select_duplicated) ; 
        ?>
        <div class="bg-red-300 mb-2 border border-red-300 text-red-dark px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">Duplicate Error</strong>
                                    <span class="block sm:inline">This This Transaction ID is connected to :  <?php echo $details_find['student_first_name']."&nbsp;".$details_find['student_last_name'];?><br> 
                                   Class:<?php echo $details_find['class_name']."/".$details_find['level_name'];?><br>
                                    Amount :<?php echo $details_find['spay_amount'];?> .Try with different Transaction Number<br>
                                    
                                    </span>
                                    
                                </div>
        
        
        
        <?php
                      
                  }
                  else{
                  
                  
                     $insertpay = mysqli_query($conn,"INSERT INTO school_payment_details
                     (spay_id,spay_student,spay_school,spay_country,spay_region,spay_amount,spay_account,spay_date,spay_tansaction_date,spay_reference,spay_mode,spay_term,spay_year,spay_status) VALUES 
                     (NULL, '$STUDENT', '$school_ref', '$user_country', '$user_region', '$spay_amount','$Account_umber', '$Registerd_Date', '$Transaction_Date', '$Transaction_ID', '$Payment_mode', '$setting_term', '$setting_year', '$Payment_status')") ; 
                     
                    ?>
                     <div class="bg-red-300 mb-2 border border-red-300 text-red-dark px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">Success </strong>
                                    <span class="block sm:inline">Payment of  <?php echo $spay_amount;?> FRW has been added to :<?php echo $student_details['student_first_name']."&nbsp;".$student_details['student_last_name'];?><br> 
                                   
                                    
                                    </span>
                                    
                                </div>
                     <script>window.setTimeout(function(){

        // Move to a new location or you can do something else
        window.location.href = "Student_invoices?STUDENT=<?php echo $STUDENT;?>&STATUS=<?php echo$STATUS;?>";

    }, 2500);</script>
                    
                    <?php 
                     
					}
					}
					
					
					
					?>
					<div class="">
                        <label class="block text-sm text-gray-600" for="lastname">Registerd Date </label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="lastname" name="Registerd_Date" value="<?php echo DATE("Y-m-d");?>" type="date" required readonly>
                    </div> 
                    	<div class="">
                        <label class="block text-sm text-gray-600" for="lastname">Transaction Date </label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="lastname" name="Transaction_Date" value="" type="date" required>
                    </div> 
                    
                    <div class="">
                        <label class="block text-sm text-gray-600" for="lastname">Amount</label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="lastname" name="spay_amount" value="" type="Nnumber" required>
                    </div> 
					  
					  <div class="">
                            <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="access_level">Payment Mode</label>
                            <div class="relative">
                                <select name="Payment_mode" class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey" id="access_level" required>
                                    <option value="MOMO">Mobile Monney</option>
								    <option value="Bank Deposit">Bank Deposite</option> 
								    <option value="Bank Transfer">Bank Transfer</option> 
								   
                                </select>
                                  
                            </div>    
                        </div>
                        <div class="">
                            <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="access_level">Account Number</label>
                            <div class="relative">
                                <select name="Account_umber" class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey" id="access_level" required>
                                    <option value="Male">===== SELECT ACCOUNT =====</option>
								   <?php
								$select_role= mysqli_query($conn,"SELECT * FROM bank_account
LEFT JOIN banks on bank_account.acount_bank = banks.bank_id WHERE acount_status='Active' AND bank_country='$user_country' ");
								while($find_role = mysqli_fetch_array($select_role)){
								?><option value="<?php echo $find_role['acount_id']; ?>"><?php echo $find_role['bank_name']."/".$find_role['acount_number']; ?></option><?php	
								}
								?>
								    
								   
                                </select>
                                  
                            </div>    
                        </div>
                        <div class="">
                            <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="access_level">Status</label>
                            <div class="relative">
                                <select name="Payment_status" class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey" id="access_level" required>
                                    <option value="Active">Active</option> 
                                </select>
                                  
                            </div>
                        </div>
						  <div class="">
                        <label class="block text-sm text-gray-600" for="lastname">Transaction ID </label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="lastname" name="Transaction_ID"  type="text" required>
                    </div> 
                   

                    <div class="mt-4">
                        <button type="submit" name="Update" class="px-4 py-1 text-white font-light tracking-wider bg-green-500 rounded">Update User Details</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>

<?php
ob_end_flush();
?>
