<?php ob_start(); 
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
                            <p class="text-gray-800 font-medium">Upload Students List in <?php echo $school_name; ?></p>

                            <?php
                            require_once '../../vendor/autoload.php';
                            use PhpOffice\PhpSpreadsheet\IOFactory;

                            if (isset($_POST['upload'])) {
                                $Class_ref = $_POST['Class_ref'];
                                $find_level = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM school_classes WHERE class_id='$Class_ref'"));
                                $class_level = $find_level['class_level'];
                                $file = $_FILES['file']['tmp_name'];

                                $spreadsheet = IOFactory::load($file);
                                $sheet = $spreadsheet->getActiveSheet();
                                $data = $sheet->toArray();

                                $totalRows = count($data);
                                $uploadedCount = 0;
                                $duplicateCount = 0;
                                $errorCount = 0;

                                foreach ($data as $row) {
                                    // Skip empty rows
                                    if (empty(array_filter($row))) {
                                        $errorCount++;
                                        continue;
                                    }

                                    $student_id = 'NULL';
                                    $student_first_name = $row[0];
                                    $student_last_name = $row[1];
                                    $internal_regno = $row[2];
                                    $student_dob = date('Y-m-d');
                                    $student_gender = 'Male';
                                    $student_class = $Class_ref;
                                    $student_level = $class_level;
                                    $student_school = $school_ref;
                                    $student_country = $user_country;
                                    $student_region = $user_region;  
                                    $student_contact = '';
                                    $student_status = 'Active';
                                    $student_profile = '';
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
                                    $checkDuplicate->bind_param("i", $student_id);
                                    $checkDuplicate->execute();
                                    $checkDuplicate->bind_result($count);
                                    $checkDuplicate->fetch();
                                    $checkDuplicate->close();

                                    if ($count == 0) {
                                        $stmt = $conn->prepare("INSERT INTO student_list (student_id, student_first_name, student_last_name, student_dob, student_gender, student_class, student_level, student_school, student_country, student_region, student_contact, student_status, student_profile, student_regno,internal_regno, student_password) VALUES (?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                                        $stmt->bind_param("isssssssssssssss", $student_id, $student_first_name, $student_last_name, $student_dob, $student_gender, $student_class, $student_level, $student_school, $student_country, $student_region, $student_contact, $student_status, $student_profile, $student_regno,$internal_regno, $student_password);
                                        if ($stmt->execute()) {
                                            $uploadedCount++;
                                        } else {
                                            $errorCount++;
                                        }
                                        $stmt->close();
                                    } else {
                                        $duplicateCount++;
                                    }
                                }

                                $conn->close();

                                echo '<div class="bg-green-300 mb-2 border border-green-300 text-white px-4 py-3 rounded relative" role="alert">
                                        <strong class="font-bold">Processing Complete!</strong>
                                        <span class="block sm:inline">Total rows processed: ' . $totalRows . '</span>
                                        <span class="block sm:inline">Successfully inserted: ' . $uploadedCount . ' students</span>
                                        <span class="block sm:inline">Duplicate records: ' . $duplicateCount . '</span>
                                        <span class="block sm:inline">Errors/empty rows: ' . $errorCount . '</span>
                                        <span class="absolute top-0 top-0 right-0 px-4 py-3">
                                            <svg class="fill-current h-6 w-6 text-red" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                                        </span>
                                    </div>';

                                echo '<script>window.setTimeout(function(){
                                        window.location.href = "Students_perschool";
                                    }, 2500);</script>';
                            }
                            ?>

                            <div class="relative">
                                <select name="Class_ref" class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey" id="countries" required>
                                    <option value="">===========Select Class ========</option>
                                    <?php
                                    $select_class = mysqli_query($conn, "SELECT * FROM school_classes
                                    LEFT JOIN school_levels ON school_classes.class_level = school_levels.level_id WHERE class_school='$school_ref'");    
                                    while($find_class = mysqli_fetch_array($select_class)) {
                                        ?><option value="<?php echo $find_class['class_id']; ?>"><?php echo $find_class['level_name']; ?>/<?php echo $find_class['class_name']; ?></option><?php	
                                    }
                                    ?>
                                </select>
                            </div>
                            <br>
                            <center><a href="student details_sample_file.xlsx" class="w-full text-black-700 bg-red-500 rounded">Download Sample Excel File Here</a></center>
                            <div class="flex flex-wrap -mx-3 mb-2">
                                <div class="mt-2">
                                    <label class="block text-sm text-gray-600" for="file">Excel file</label>
                                    <input name="file" type="file" class="w-full px-5 py-4 text-gray-700 bg-gray-200 rounded" id="file" required="">
                                </div>
                            </div>

                            <div class="mt-4">
                                <center><button type="submit" name="upload" class="px-4 py-1 text-white font-light tracking-wider bg-green-500 rounded">Upload Students List</button></center>
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