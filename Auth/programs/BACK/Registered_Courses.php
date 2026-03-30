<?php 
include('header.php');

// Check if connection is successful
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Ensure session variables are set
if (!isset($session_id)) {
    die("Session ID is missing.");
}

$action = "invoice_student='$session_id'";	
$select_certifications = mysqli_query($conn,"SELECT * FROM students_invoice
    LEFT JOIN certifications ON students_invoice.invoice_certificate = certifications.certification_id
    LEFT JOIN students_promotion ON students_invoice.invoice_promotion = students_promotion.promotion_id
    WHERE $action");

// Check if the query was successful
if (!$select_certifications) {
    die("Query failed: " . mysqli_error($conn));
}
?>
  
<!--/Header-->

<div class="flex flex-1">
    <!--Sidebar-->
    <?php include('side_bar_courses.php'); ?>
    <!--/Sidebar-->

    <!--Main-->
    <main class="bg-white-500 flex-1 p-3 overflow-hidden">
        <div class="flex flex-col">
            <!-- Card Section Starts Here -->
            <div class="flex flex-1 flex-col md:flex-row lg:flex-row mx-2">
                <!--Horizontal form and Underline form are removed for cleanup-->
            </div>
            <!-- /Cards Section Ends Here -->

            <!--Grid Form-->
            <div class="flex flex-1 flex-col md:flex-row lg:flex-row mx-2">
                <div class="mb-2 border-solid border-gray-300 rounded border shadow-sm w-full">
                    <div class="bg-gray-200 px-2 py-3 border-solid border-gray-200 border-b">
                        <center><strong><big><p>Courses @<?php echo htmlspecialchars($student_data['school_name']); ?></p></big></strong></center>
                        <p>Invoiced: <?php echo number_format($invoice_local, 2) . " " . htmlspecialchars($Country_currency_code); ?></p>
                        <p>Paid: <?php echo htmlspecialchars($paid_local) . " " . htmlspecialchars($Country_currency_code); ?></p>
                        <p>Balance: <?php echo htmlspecialchars($ball) . " " . htmlspecialchars($Country_currency_code); ?></p>
                        <p>Payment in Percentage: <?php echo htmlspecialchars($paid_percent); ?>%</p>
                    </div>

                    <div class="p-3">
                        <table class="table-responsive w-full rounded">
                            <thead>
                                <tr>
                                    <th class="border w-1/12 px-4 py-2">ID</th>
                                    <th class="border w-1/8 px-4 py-2">Certification Name</th>
                                    <th class="border w-1/8 px-4 py-2">Training Period</th>
                                    <th class="border w-1/10 px-4 py-2">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                while ($certificate_details = mysqli_fetch_array($select_certifications)) {
                                    $invoice_status12 = $certificate_details['invoice_status'];
                                    $promotion_payment = $certificate_details['promotion_payment'];
                                    $certification = $certificate_details['certification_id'];

                                    // Determine URL and button styles
                                    $URL = "#";
                                    $BG = "bg-white-500"; // Default background

                                    if ($promotion_payment == "Enable") {
                                        if ($invoice_status12 == "Active" && $this_date <= $promotion_fp_date) {
                                            $URL = ($paid_percent >= 50) ? "Modules_per_Certification?CERTIFICATE=$certification" : "#";
                                            $BG = ($paid_percent >= 50) ? "bg-blue-500" : "bg-white-500";
                                        } elseif ($paid_percent < 100) {
                                            $URL = "#";
                                            $BG = "bg-white-500";
                                        } else {
                                            $URL = "Modules_per_Certification?CERTIFICATE=$certification";
                                            $BG = "bg-blue-500";
                                        }
                                    } else {
                                        $URL = "Modules_per_Certification?CERTIFICATE=$certification";
                                        $BG = "bg-green-500";
                                    }
                                ?>
                                <tr>      
                                    <td class="border px-4 py-1"><?php echo htmlspecialchars($certificate_details['certification_id']); ?></td>
                                    <td class="border px-4 py-2">
                                        <a href="<?php echo $URL; ?>" class='<?php echo $BG; ?> hover:bg-blue-200 text-blue-800 font-bold py-2 px-4 rounded'>
                                            <?php echo htmlspecialchars($certificate_details['certification_name']); ?>
                                        </a>
                                    </td>
                                    <td class="border px-4 py-2"><?php echo htmlspecialchars($certificate_details['certification_duration']); ?> Months</td>
                                    <td class="border px-4 py-2">
                                        <?php if ($invoice_status12 == "Active") { ?>
                                            <i class="fas fa-unlock text-green-500 mx-2"></i>
                                        <?php } else { ?>
                                            <i class="fas fa-lock text-red-500 mx-2"></i>
                                        <?php } ?>
                                    </td>
                                </tr>
                                <?php
                                }
                                ?>
                            </tbody>
                        </table>
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
