<?php 
include('header.php');

// Fetch school info
$find_schol = mysqli_fetch_array(mysqli_query($conn,"
    SELECT * FROM schools 
    LEFT JOIN regions_table ON schools.school_region = regions_table.region_id
    LEFT JOIN countries ON schools.country_ref = countries.id 
    WHERE school_id='$school_ref'
"));

// Check STATUS
if (isset($_GET['STATUS'])) {
    $STATUS = $_GET['STATUS'];
    $status = ($STATUS == "Active") ? "Active" : "Inactive";

    // FIXED: Added missing space before ORDER BY
    $action = "
        promotion_school='$school_ref' 
        AND promotion_status='$STATUS' 
        AND promotion_year='$this_year' 
        ORDER BY promotion_certification ASC
    ";
} else {
    $STATUS = "Active";
    $status = $STATUS;

    // FIXED: Missing space added before ORDER BY
    $action = "
        promotion_school='$school_ref' 
        AND promotion_status='$STATUS' 
        AND promotion_year='$this_year' 
        ORDER BY promotion_certification ASC
    ";
}

// Fetch promotions
$select_promotions = mysqli_query($conn,"
    SELECT * FROM students_promotion 
    LEFT JOIN regions_table ON students_promotion.promotion_region = regions_table.region_id
    LEFT JOIN countries ON students_promotion.promotion_country = countries.id 
    LEFT JOIN certifications ON students_promotion.promotion_certification = certifications.certification_id 
    LEFT JOIN schools ON students_promotion.promotion_school = schools.school_id  
    WHERE $action
");
?>

<!--/Header-->

<div class="flex flex-1">
    <!--Sidebar-->
    <?php include('side_bar_courses.php'); ?>
    <!--/Sidebar-->

    <!--Main-->
    <main class="bg-white-500 flex-1 p-3 overflow-hidden">
        <div class="flex flex-col">

            <!--Grid Form-->
            <div class="flex flex-1 flex-col md:flex-row lg:flex-row mx-2">
                <div class="mb-2 border-solid border-gray-300 rounded border shadow-sm w-full">

                    <div class="bg-gray-200 px-2 py-3 border-solid border-gray-200 border-b">
                        <a href="Current_Courses?STATUS=Active">
                            <button class='bg-green-500 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded'>
                                <i class="fas fa-home float-left">&nbsp; Active</i>
                            </button>
                        </a>

                        <a href="Current_Courses?STATUS=Inactive">
                            <button class='bg-red-500 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'>
                                <i class="fas fa-times float-left">&nbsp;Inactive</i>
                            </button>
                        </a>
                    </div>

                    <div class="p-3">
                        <p>
                            <big><strong>
                                <?php echo $find_schol['school_name']; ?> 
                                &nbsp; <?php echo $STATUS; ?> &nbsp; Payment Settings
                            </strong></big>
                        </p><br>

                        <div class="overflow-x-auto">
                            <table class="min-w-full table-auto border-collapse border border-gray-300">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="border px-3 py-2 text-sm">ID</th>
                                        <th class="border px-3 py-2 text-sm">Promotion</th>
                                        <th class="border px-3 py-2 text-sm">Certification</th>
                                        <th class="border px-3 py-2 text-sm">Amount in USD</th>
                                        <th class="border px-3 py-2 text-sm">Amount</th>
                                        <th class="border px-3 py-2 text-sm">Year</th>
                                        <th class="border px-3 py-2 text-sm">Actions</th>
                                    </tr>
                                </thead>   

                                <tbody>
                                    <?php while ($promotion_details = mysqli_fetch_array($select_promotions)) { 
                                        $promotion_id = $promotion_details['promotion_id'];

                                        $count_students = mysqli_query($conn,"
                                            SELECT * FROM students_invoice 
                                            WHERE invoice_promotion='$promotion_id'
                                        ");

                                        $students = mysqli_num_rows($count_students);
                                        $invoice_certificate = $promotion_details['promotion_certification'];
                                    ?>
                                    <tr>
                                        <td class="border px-3 py-1 text-sm text-center">
                                            <?php echo $promotion_details['promotion_id']; ?>
                                        </td>

                                        <td class="border px-3 py-1 text-sm">
                                            <a href="Modules_per_Certification?CERTIFICATE=<?php echo $invoice_certificate; ?>"  
                                               class='bg-blue-500 hover:bg-blue-200 text-blue-800 font-bold py-1 px-2 rounded text-xs' >
                                                <?php echo $promotion_details['promotion_name']; ?>
                                            </a>
                                        </td>

                                        <td class="border px-3 py-1 text-sm">
                                            <?php echo $promotion_details['certification_name']; ?>
                                        </td>

                                        <td class="border px-3 py-1 text-sm text-right">
                                            <?php echo number_format($promotion_details['promotion_pay_usd'], 2); ?> USD
                                        </td>

                                        <td class="border px-3 py-1 text-sm text-right">
                                            <?php 
                                                echo number_format($promotion_details['promotion_pay_local'], 2)
                                                     ." ".$promotion_details['Country_currency_code'];
                                            ?>
                                        </td>

                                        <td class="border px-3 py-1 text-sm text-center">
                                            <?php echo $promotion_details['promotion_year']; ?>
                                        </td>

                                        <td class="border px-3 py-1 text-sm text-center">
                                            <div class="flex justify-center space-x-1">
                                                <a href="Update_Promotions_name?ID=<?php echo $promotion_id; ?>&STATUS=<?php echo $STATUS; ?>" 
                                                   class="bg-teal-300 rounded p-1 text-white hover:bg-teal-400">
                                                    <i class="fas fa-pen text-green-700"></i>
                                                </a>

                                                <a href="Students_in_Promotions?ID=<?php echo $promotion_id; ?>&STATUS=<?php echo $STATUS; ?>" 
                                                   class="bg-blue-500 rounded p-1 text-white hover:bg-blue-600">
                                                    <?php echo $students; ?>
                                                    <i class="fas fa-users ml-1"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>

                            </table>
                        </div>
                    </div>

                </div>
            </div>
            <!--/Grid Form-->

        </div>
    </main>
    <!--/Main-->
</div>

<?php include('footer.php'); ?>

<script src="../../main.js"></script>
</body>
</html>
