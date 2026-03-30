<?php 
ob_start(); 
include('header.php');
?>

<!--Header-->
<!--/Header-->

<div class="flex flex-1">
    <!--Sidebar-->
    <?php include('dynamic_side_bar.php'); ?>
    <!--/Sidebar-->

    <!--Main-->
    <div class="main-content">
        <div class="h-screen font-sans login bg-cover">
            <div class="container mx-auto h-full flex flex-1 justify-center items-center">
                <div class="w-full max-w-lg">
                    <div class="leading-loose">
                        <form action="" method="POST" enctype="multipart/form-data" class="max-w-xl m-4 p-10 bg-white rounded shadow-xl">
                            <p class="text-gray-800 font-medium text-center text-lg mb-6">Upload Students List in <?php echo htmlspecialchars($school_name); ?></p>

                            <?php
                            require_once '../../vendor/autoload.php';
                            use PhpOffice\PhpSpreadsheet\IOFactory;

                            if (isset($_POST['upload'])) {
                                $Class_ref = mysqli_real_escape_string($conn, $_POST['Class_ref']);
                                $find_level = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM school_classes WHERE class_id='$Class_ref'"));
                                
                                if (!$find_level) {
                                    echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                                            <strong class="font-bold">Error!</strong>
                                            <span class="block sm:inline">Invalid class selected.</span>
                                          </div>';
                                } else {
                                    $class_level = $find_level['class_level'];
                                    
                                    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
                                        echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                                                <strong class="font-bold">Error!</strong>
                                                <span class="block sm:inline">Please select a valid Excel file.</span>
                                              </div>';
                                    } else {
                                        $file = $_FILES['file']['tmp_name'];
                                        
                                        try {
                                            $spreadsheet = IOFactory::load($file);
                                            $sheet = $spreadsheet->getActiveSheet();
                                            $data = $sheet->toArray();

                                            $totalRows = count($data);
                                            $uploadedCount = 0;
                                            $duplicateCount = 0;
                                            $errorCount = 0;
                                            $skippedHeader = false;

                                            foreach ($data as $rowIndex => $row) {
                                                // Skip empty rows and header row (first row)
                                                if ($rowIndex === 0) {
                                                    $skippedHeader = true;
                                                    continue;
                                                }
                                                
                                                if (empty(array_filter($row))) {
                                                    $errorCount++;
                                                    continue;
                                                }

                                                // Validate required fields
                                                if (empty($row[0]) || empty($row[1]) || empty($row[2])) {
                                                    $errorCount++;
                                                    continue;
                                                }

                                                $student_id = mysqli_real_escape_string($conn, $row[0]);
                                                $student_first_name = mysqli_real_escape_string($conn, $row[1]);
                                                $student_last_name = mysqli_real_escape_string($conn, $row[2]);
                                                $student_dob = !empty($row[3]) ? date('Y-m-d', strtotime($row[3])) : null;
                                                $student_gender = mysqli_real_escape_string($conn, $row[4]);
                                                $student_class = $Class_ref;
                                                $student_level = $class_level;
                                                $student_school = $school_ref;
                                                $student_country = $user_country;
                                                $student_region = $user_region; 
                                                $student_status = !empty($row[5]) ? mysqli_real_escape_string($conn, $row[5]) : 'Active';
                                                $student_profile = !empty($row[6]) ? mysqli_real_escape_string($conn, $row[6]) : 'default.jpg';
                                                $student_password = md5("123456");

                                                $currentYear = date('Y');
                                                $regPrefix = "BG/$currentYear/";

                                                $result = $conn->query("SELECT student_regno FROM student_list WHERE student_regno LIKE '$regPrefix%' ORDER BY student_regno DESC LIMIT 1");
                                                $lastRegNo = $result->fetch_assoc();

                                                if ($lastRegNo) {
                                                    $lastNumber = intval(substr($lastRegNo['student_regno'], strrpos($lastRegNo['student_regno'], '/') + 1));
                                                    $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
                                                } else {
                                                    $newNumber = '00001';
                                                }

                                                $student_regno = $regPrefix . $newNumber;

                                                // Check for duplicate student_id
                                                $checkDuplicate = $conn->prepare("SELECT COUNT(*) FROM student_list WHERE student_id = ?");
                                                $checkDuplicate->bind_param("s", $student_id);
                                                $checkDuplicate->execute();
                                                $checkDuplicate->bind_result($count);
                                                $checkDuplicate->fetch();
                                                $checkDuplicate->close();

                                                if ($count == 0) {
                                                    $stmt = $conn->prepare("INSERT INTO student_list (student_id, student_first_name, student_last_name, student_dob, student_gender, student_class, student_level, student_school, student_country, student_region, student_status, student_profile, student_regno, student_password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                                                    
                                                    if ($stmt) {
                                                        $stmt->bind_param("ssssssssssssss", 
                                                            $student_id, 
                                                            $student_first_name, 
                                                            $student_last_name, 
                                                            $student_dob, 
                                                            $student_gender, 
                                                            $student_class, 
                                                            $student_level, 
                                                            $student_school, 
                                                            $student_country, 
                                                            $student_region, 
                                                            $student_status, 
                                                            $student_profile, 
                                                            $student_regno, 
                                                            $student_password
                                                        );
                                                        
                                                        if ($stmt->execute()) {
                                                            $uploadedCount++;
                                                        } else {
                                                            $errorCount++;
                                                        }
                                                        $stmt->close();
                                                    } else {
                                                        $errorCount++;
                                                    }
                                                } else {
                                                    $duplicateCount++;
                                                }
                                            }

                                            echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                                                    <strong class="font-bold">Processing Complete!</strong>
                                                    <div class="mt-2">
                                                        <span class="block">Total rows processed: ' . ($totalRows - 1) . '</span>
                                                        <span class="block">Successfully inserted: ' . $uploadedCount . ' students</span>
                                                        <span class="block">Duplicate records: ' . $duplicateCount . '</span>
                                                        <span class="block">Errors/empty rows: ' . $errorCount . '</span>
                                                    </div>
                                                  </div>';

                                            echo '<script>
                                                    setTimeout(function() {
                                                        window.location.href = "Students_perschool";
                                                    }, 5000);
                                                  </script>';

                                        } catch (Exception $e) {
                                            echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                                                    <strong class="font-bold">Error!</strong>
                                                    <span class="block sm:inline">Error reading Excel file: ' . htmlspecialchars($e->getMessage()) . '</span>
                                                  </div>';
                                        }
                                    }
                                }
                            }
                            ?>

                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2" for="Class_ref">Select Class</label>
                                <div class="relative">
                                    <select name="Class_ref" class="block appearance-none w-full bg-white border border-gray-300 text-gray-700 py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-blue-500" required>
                                        <option value="">Select Class</option>
                                        <?php
                                        $select_class = mysqli_query($conn, "SELECT * FROM school_classes
                                        LEFT JOIN school_levels ON school_classes.class_level = school_levels.level_id WHERE class_school='$school_ref'");    
                                        while($find_class = mysqli_fetch_array($select_class)) {
                                            echo '<option value="' . htmlspecialchars($find_class['class_id']) . '">' . 
                                                 htmlspecialchars($find_class['level_name']) . ' / ' . 
                                                 htmlspecialchars($find_class['class_name']) . '</option>';
                                        }
                                        ?>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-6 text-center">
                                <a href="download_excel_sample.php" class="inline-block bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded transition duration-200">
                                    Download Sample Excel File
                                </a>
                            </div>

                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2" for="file">Excel File</label>
                                <input name="file" type="file" accept=".xlsx,.xls" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                                <p class="text-xs text-gray-500 mt-1">Accepted formats: .xlsx, .xls</p>
                            </div>

                            <div class="mt-6">
                                <button type="submit" name="upload" class="w-full bg-green-500 hover:bg-green-600 text-white font-medium py-3 px-4 rounded transition duration-200">
                                    Upload Students List
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--/Main-->
</div>

</html>
<?php ob_end_flush(); ?>