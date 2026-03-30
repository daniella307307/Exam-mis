<?php  
ob_start(); 
include('header.php');

// Validate and sanitize CLASS input
if (!isset($_GET['CLASS']) || !is_numeric($_GET['CLASS'])) {
    die("Invalid class parameter");
}
$CLASS = (int)$_GET['CLASS'];

// Fetch class details with prepared statement
$stmt = $conn->prepare("SELECT class_name FROM school_classes WHERE class_id = ?");
$stmt->bind_param("i", $CLASS);
$stmt->execute();
$class_result = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$class_result) {
    die("Class not found");
}
$class_name123 = htmlspecialchars($class_result['class_name']);
?>

<div class="flex flex-1">
    <!--Sidebar-->
    <?php include('dynamic_side_bar.php'); ?>
    <!--/Main-->

    <div class="main-content">
        <div class="h-screen font-sans login bg-cover">
            <div class="container mx-auto h-full flex flex-1 justify-center items-center">
                <div class="w-full max-w-lg">
                    <div class="leading-loose">
                        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?CLASS=' . $CLASS; ?>" method="POST" enctype="multipart/form-data" class="max-w-xl m-4 p-10 bg-white rounded shadow-xl">
                            <p class="text-gray-800 font-medium">
                                Upload Students School Fee in: 
                                <big><strong><?php echo htmlspecialchars($school_name); ?> <br>Class: <?php echo $class_name123; ?></strong></big>
                            </p>

<?php
require '../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

if (isset($_POST['upload']) && isset($_FILES['excel_file']['tmp_name'])) {
    $file = $_FILES['excel_file']['tmp_name'];

    if (!empty($file) && file_exists($file)) {
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();
 

        for ($i = 0; $i < count($rows); $i++) {
            $row = $rows[$i];

            // Skip empty rows
            if (empty($row[0]) && empty($row[1]) && empty($row[2]) && empty($row[3])) {
                continue;
            }

            $regno = mysqli_real_escape_string($conn, trim($row[0]));

            // Find student details
            $student_q = $conn->prepare("SELECT * FROM student_list WHERE student_regno = ?");
            $student_q->bind_param("s", $regno);
            $student_q->execute();
            $result = $student_q->get_result();

            if ($result->num_rows === 0) {
                echo "<tr><td colspan='5' style='color:red;'>Student with RegNo $regno not found</td></tr>";
                continue;
            }

            $student = $result->fetch_assoc();
            $student_q->close();

            $student_id      = $student['student_id'];
            $student_class   = $student['student_class'];
            $student_level   = $student['student_level'];
            $student_school  = $student['student_school'];
            $student_country = $student['student_country'];
            $student_region  = $student['student_region'];

            $fullname = mysqli_real_escape_string($conn, trim($row[1]));
            $amount   = str_replace(',', '', (float) $row[2]) ; 
            $tr_id    = mysqli_real_escape_string($conn, trim($row[3]));
            $trans_date_raw = trim($row[4] ?? '');
            $spay_mode = mysqli_real_escape_string($conn, trim($row[5] ?? ''));

            if($spay_mode=="MOMO"){
                $spay_account =38; 
            } elseif($spay_mode=="BANK"){
                $spay_account =9;   
            } else{
                $spay_account =0;   
            }

            $spay_status = "Active";
            $spay_transaction_date = !empty($trans_date_raw) ? date('Y-m-d', strtotime($trans_date_raw)) : date('Y-m-d');
            $today = date("Y-m-d");

            // Check for duplicate
            $check_duplicate = $conn->prepare("SELECT spay_id FROM school_payment_details WHERE spay_student = ? AND spay_reference = ?");
            $check_duplicate->bind_param("ss", $student_id, $tr_id);
            $check_duplicate->execute();
            $check_result = $check_duplicate->get_result();

            if ($check_result->num_rows > 0) {
                // Duplicate found
                echo "<tr style='color:orange;'><td>" . htmlspecialchars($regno) . "</td>
                        <td>" . htmlspecialchars($fullname) . "</td>
                        <td>" . htmlspecialchars($amount) . "</td>
                        <td>" . htmlspecialchars($tr_id) . "</td>
                        <td>Duplicate – Skipped</td></tr>";
                $check_duplicate->close();
                continue;
            }
            $check_duplicate->close();

            // Insert payment
            $insert = $conn->prepare("INSERT INTO `school_payment_details` 
                (spay_student,spay_class, spay_school, spay_country, spay_region, spay_amount,spay_account, spay_date, spay_tansaction_date, spay_reference, spay_mode,spay_term,spay_year,spay_status)
                VALUES (?, ?, ?, ?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $insert->bind_param(
                "ssssssssssssss",
                $student_id,
                $student_class,
                $student_school,
                $student_country,
                $student_region,
                $amount,
                $spay_account,
                $today,
                $spay_transaction_date,
                $tr_id,
                $spay_mode,
                $setting_term,
                $setting_year,
                $spay_status
            );
            $insert->execute();
            $insert->close();
 
        }

        echo "<br><strong style='color:green;'>Upload process completed.</strong>";
         echo "<br><strong style='color:green;'>Redirecting to thw main Page </strong>";
        
       ?>
       <script>
  setTimeout(function() {
    window.location.href = "Payments_by_Class?STATUS=Active&CLASS_REF=<?php echo $CLASS;?>&format=excel"; // Change to your desired URL
  }, 2000); // 2000 milliseconds = 2 seconds
</script>
       
       
       
       <?php 
    } else {
        echo "<div style='color: red;'>Invalid file.</div>";
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<div style='color: red;'>No file uploaded!</div>";
}
?>

                            <div class="flex flex-wrap -mx-3 mb-2">
                                <div class="mt-2">
                                    <label class="block text-sm text-gray-600" for="file">Excel file</label>
                                    <input type="file" name="excel_file" accept=".xls,.xlsx,.csv" class="w-full px-5 py-4 text-gray-700 bg-gray-200 rounded" id="file" required>
                                </div>
                            </div>

                            <div class="mt-4">
                                <center>
                                    <button id = "button" type="submit" name="upload" class="px-4 py-1 text-white font-light tracking-wider bg-green-500 rounded hover:bg-green-600">
                                        Upload Students School Fees
                                    </button>
                                </center>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>
