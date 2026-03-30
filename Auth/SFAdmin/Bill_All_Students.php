<?php
ob_start(); 
include('header.php');
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
                    <form action="" method="POST" class="max-w-xl m-4 p-10 bg-white rounded shadow-xl">
                        <p class="text-gray-800 font-medium">Bill All Students in <strong><?php echo htmlspecialchars($school_name); ?></strong></p>
                        
                        <?php
                        if(isset($_POST['Update'])){
                            // Prepare the select students query
                            $selectStudents = mysqli_prepare($conn, "SELECT * FROM student_list WHERE student_school = ?");
                            mysqli_stmt_bind_param($selectStudents, "s", $school_ref);
                            mysqli_stmt_execute($selectStudents);
                            $result = mysqli_stmt_get_result($selectStudents);
                            
                            $billsGenerated = 0;
                            $errors = 0;
                            
                            while($student_details = mysqli_fetch_array($result)){
                                $student_id = $student_details['student_id'];    
                                $student_class = $student_details['student_class']; 	
                                $student_level = $student_details['student_level'];	
                                $student_school = $student_details['student_school'];	
                                $student_country = $student_details['student_country'];	
                                $student_region = $student_details['student_region'];

                                // Prepare payment settings query
                                $selectCharges = mysqli_prepare($conn, 
                                    "SELECT * FROM school_payment_settings 
                                    WHERE spayment_level = ? 
                                    AND spayment_class = ?
                                    AND spayment_school = ? 
                                    AND spayment_term = ? 
                                    AND spayment_year = ?");
                                mysqli_stmt_bind_param($selectCharges, "sssss", 
                                    $student_level, 
                                    $student_class,
                                    $student_school,
                                    $setting_term,
                                    $setting_year);
                                mysqli_stmt_execute($selectCharges);
                                $chargesResult = mysqli_stmt_get_result($selectCharges);
                                
                                if(mysqli_num_rows($chargesResult) > 0){
                                    while($found_charges = mysqli_fetch_array($chargesResult)){
                                        $spayment_amount = $found_charges['spayment_amount'];  
                                        $spayment_id = $found_charges['spayment_id'];
                                        
                                        // Check for existing invoice
                                        $checkInvoice = mysqli_prepare($conn, 
                                            "SELECT * FROM school_invoice 
                                            WHERE invc_student = ? 
                                            AND invc_ref = ? 
                                            AND invc_term = ?
                                            AND invc_year = ?");
                                        mysqli_stmt_bind_param($checkInvoice, "ssss", 
                                            $student_id,
                                            $spayment_id,
                                            $setting_term,
                                            $setting_year);
                                        mysqli_stmt_execute($checkInvoice);
                                        $invoiceResult = mysqli_stmt_get_result($checkInvoice);
                                        
                                        if(mysqli_num_rows($invoiceResult) == 0){
                                            // Insert new invoice if it doesn't exist
                                            $insertInvoice = mysqli_prepare($conn, 
                                                "INSERT INTO school_invoice 
                                                (invc_ref, invc_student, invc_class, invc_level, invc_school, 
                                                invc_country, invc_region, invc_amount, invc_term, invc_year) 
                                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                                            mysqli_stmt_bind_param($insertInvoice, "sssssssdss", 
                                                $spayment_id,
                                                $student_id,
                                                $student_class,
                                                $student_level,
                                                $student_school,
                                                $student_country,
                                                $student_region,
                                                $spayment_amount,
                                                $setting_term,
                                                $setting_year);
                                            
                                            if(mysqli_stmt_execute($insertInvoice)){
                                                $billsGenerated++;
                                            } else {
                                                $errors++;
                                            }
                                        }
                                    }
                                }
                            }
                            
                            // Show result message
                            echo '<div class="mt-4 p-4 bg-green-500 text-white rounded">';
                            echo "Billing process completed:<br>";
                            echo "- $billsGenerated new bills generated<br>";
                            if($errors > 0){
                                echo "- $errors errors encountered<br>";
                            }
                            echo '</div>';
                        }
                        ?>
        
                        <div class="mt-6 w-full md:w-1/1 px-3 mb-6 md:mb-0">
                            <center>
                                <button type="submit" name="Update" class="px-4 py-1 text-white font-light tracking-wider bg-green-500 rounded">
                                    Generate Bills for All Students
                                </button>
                            </center>
                        </div> 
                    </form>
                </div>
            </div>
        </div>  
    </body>
</html>