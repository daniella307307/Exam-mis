<?php 
include('header.php');

// Validate and initialize required variables
$school_ref = mysqli_real_escape_string($conn, $school_ref);
$user_country = mysqli_real_escape_string($conn, $user_country);
$setting_term = mysqli_real_escape_string($conn, $setting_term);
$setting_year = mysqli_real_escape_string($conn, $setting_year);

// Status handling
$status = "Active"; // Default status
$whereConditions = [
    "class_school = '$school_ref'",
    "(class_country = '0' OR class_country = '$user_country')"
];

if(isset($_GET['STATUS'])) {
    $input_status = $_GET['STATUS'];
    if($input_status === "Active") {
        $status = "Active";
        array_unshift($whereConditions, "class_status = 'Active'");
    } else {
        $status = "Inactive";
        array_unshift($whereConditions, "class_status != 'Active'");
    }
} else {
    array_unshift($whereConditions, "class_status = 'Active'");
}

$whereClause = implode(' AND ', $whereConditions);

// Fetch classes
$select_user = mysqli_query($conn, "
    SELECT sc.*, sl.level_name 
    FROM school_classes sc
    LEFT JOIN school_levels sl ON sc.class_level = sl.level_id
    WHERE $whereClause
    ORDER BY sc.class_id
");

function formatNumber($num) {
    return number_format($num, 2);
}
?>

<div class="flex flex-1">
    <?php include('dynamic_side_bar.php');?>

    <main class="bg-white-500 flex-1 p-3 overflow-hidden">
        <div class="flex flex-col">
            <div class="flex flex-1 flex-col md:flex-row lg:flex-row mx-2">
                <div class="mb-2 border-solid border-gray-300 rounded border shadow-sm w-full">
                    <div class="bg-gray-200 px-2 py-3 border-solid border-gray-200 border-b">
                        Class Settings &nbsp;  
                        <a href="School_class_rooms.php?STATUS=Active">
                            <button class='bg-green-500 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded'>Active</button>
                        </a>  
                        <a href="School_class_rooms.php?STATUS=Inactive">
                            <button class='bg-red-500 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'>Inactive</button>
                        </a>
                    </div>
                    
                    <div class="p-3">
                        <table class="table-responsive w-full rounded">
                            <thead>
                                <tr>
                                    <th class="border w-1/12 px-4 py-2">ID</th>
                                    <th class="border w-1/8 px-4 py-2">Class Name</th>  
                                    <th class="border w-1/8 px-4 py-2">Students</th> 
                                    <th class="border w-1/8 px-4 py-2">To Pay</th>                                     
                                    <th class="border w-1/7 px-4 py-2">Paid</th>
                                    <th class="border w-1/7 px-4 py-2">Balance</th>
                                    <th class="border w-1/7 px-4 py-2">Paid %</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($class = mysqli_fetch_assoc($select_user)): 
                                    $class_id = $class['class_id'];
                                    
                                    // Get student count
                                    $student_count = mysqli_query($conn, 
                                        "SELECT COUNT(*) AS total 
                                         FROM student_list 
                                         WHERE student_class = '$class_id' 
                                         AND student_school = '$school_ref'"
                                    );
                                    $count = mysqli_fetch_assoc($student_count)['total'];
                                    
                                    // Get financial data using original query structure
                                    $topay = 0;
                                    $paid = 0;
                                    
                                    // Get students in class
                                    $students_query = mysqli_query($conn,
                                        "SELECT student_id 
                                         FROM student_list 
                                         WHERE student_class = '$class_id'
                                         AND student_school = '$school_ref'"
                                    );
                                    
                                    while($student = mysqli_fetch_assoc($students_query)) {
                                        $student_id = $student['student_id'];
                                        
                                        // Original invoice query
                                        $invoice_query = mysqli_query($conn,
                                            "SELECT SUM(invc_amount) AS topay
                                             FROM school_invoice
                                             WHERE invc_student = '$student_id'
                                             AND invc_class = '$class_id'
                                             AND invc_term = '$setting_term'
                                             AND invc_year = '$setting_year'"
                                        );
                                        $invoice = mysqli_fetch_assoc($invoice_query);
                                        $topay += (float)($invoice['topay'] ?? 0);
                                        
                                        // Original payment query
                                        $payment_query = mysqli_query($conn,
                                            "SELECT SUM(spay_amount) AS paid
                                             FROM school_payment_details
                                             WHERE spay_student = '$student_id'
                                             AND spay_term = '$setting_term'
                                             AND spay_year = '$setting_year'"
                                        );
                                        $payment = mysqli_fetch_assoc($payment_query);
                                        $paid += (float)($payment['paid'] ?? 0);
                                    }
                                    
                                    $balance = $topay - $paid;
                                    $percentage = $topay > 0 ? ($paid / $topay) * 100 : 0;
                                ?>
                                <tr>
                                    <td class="border px-4 py-2"><?= $class['class_id'] ?></td>
                                    <td class="border px-4 py-2"><?= htmlspecialchars($class['class_name']) ?></td>
                                    <td class="border px-4 py-2">
                                        <a href="students_per_class_room.php?CLASS=<?= $class_id ?>" 
                                           class="bg-<?= $count > 0 ? 'green' : 'red' ?>-500 cursor-pointer rounded p-1 mx-1 text-white">
                                            <?= $count ?> Student<?= $count != 1 ? 's' : '' ?>
                                            <i class="fas fa-users"></i>
                                        </a>
                                    </td>
                                    <td class="border px-4 py-2"><?= formatNumber($topay) ?></td>
                                    <td class="border px-4 py-2"><?= formatNumber($paid) ?></td>
                                    <td class="border px-4 py-2"><?= formatNumber($balance) ?></td>
                                    <td class="border px-4 py-2">
                                        <?= formatNumber($percentage) ?>%
                                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                                            <div class="bg-<?= $percentage >= 100 ? 'green' : ($percentage >= 50 ? 'yellow' : 'red') ?>-600 h-2.5 rounded-full" 
                                                 style="width: <?= min($percentage, 100) ?>%"></div>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include('footer.php') ?>