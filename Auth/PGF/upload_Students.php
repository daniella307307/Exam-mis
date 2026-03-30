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
                            <p class="text-gray-800 font-medium">Upload Students List in <?php echo $school_name; ?></p>

                            <?php
                            // Include necessary PhpSpreadsheet files
                            require_once '../../vendor/autoload.php'; // Adjust the path accordingly

                            use PhpOffice\PhpSpreadsheet\IOFactory;

                            if (isset($_POST['upload'])) {
                                // Database connection
                                $Class_ref = $_POST['Class_ref'];
                                $find_level = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM school_classes WHERE class_id='$Class_ref'"));
                                $class_level = $find_level['class_level'];

                                // File upload
                                $file = $_FILES['file']['tmp_name'];

                                // Read the Excel file
                                $spreadsheet = IOFactory::load($file);
                                $sheet = $spreadsheet->getActiveSheet();
                                $data = $sheet->toArray();

                                foreach ($data as $row) {
                                    $student_id = $row[0];
                                    $student_first_name = $row[1];
                                    $student_last_name = $row[2];
                                    $student_dob = date('Y-m-d', strtotime($row[3]));
                                    $student_gender = $row[4];
                                    $student_class = $Class_ref;
                                    $student_level = $class_level;
                                    $student_school = $school_ref;
                                    $student_country = $user_country;
                                    $student_region = $user_region;  
                                    $student_contact = $row[5];
                                    $student_status = $row[6];
                                    $student_profile = $row[7];
                                    $student_password = md5("123456"); // Default password

                                    $currentYear = date('Y');
                                    $regPrefix = "BG/$currentYear/";

                                    // Get the last inserted registration number for the current year
                                    $result = $conn->query("SELECT student_regno FROM student_list WHERE student_regno LIKE '$regPrefix%' ORDER BY student_regno DESC LIMIT 1");
                                    $lastRegNo = $result->fetch_assoc();

                                    if ($lastRegNo) {
                                        $lastNumber = intval(substr($lastRegNo['student_regno'], strrpos($lastRegNo['student_regno'], '/') + 1));
                                        $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
                                    } else {
                                        $newNumber = '00001';
                                    }

                                    $student_regno = $regPrefix . $newNumber;

                                    // Check for duplicate based on First_Name
                                    $checkDuplicate = $conn->prepare("SELECT COUNT(*) FROM student_list WHERE student_first_name = ?");
                                    $checkDuplicate->bind_param("s", $student_first_name);
                                    $checkDuplicate->execute();
                                    $checkDuplicate->bind_result($count);
                                    $checkDuplicate->fetch();
                                    $checkDuplicate->close();

                                    if ($count == 0) {
                                        // Insert the data into the database
                                        $stmt = $conn->prepare("INSERT INTO student_list (student_id, student_first_name, student_last_name, student_dob, student_gender, student_class, student_level, student_school, student_country, student_region, student_contact, student_status, student_profile, student_regno, student_password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                                        $stmt->bind_param("issssssssssssss", $student_id, $student_first_name, $student_last_name, $student_dob, $student_gender, $student_class, $student_level, $student_school, $student_country, $student_region, $student_contact, $student_status, $student_profile, $student_regno, $student_password);
                                        $stmt->execute();
                                        $stmt->close();
                                    }
                                }

                                $conn->close();

                                echo '<div class="bg-green-300 mb-2 border border-green-300 text-white px-4 py-3 rounded relative" role="alert">
                                        <strong class="font-bold">Success!</strong>
                                        <span class="block sm:inline">Students list uploaded successfully!!!.</span>
                                        <span class="absolute top-0 top-0 right-0 px-4 py-3">
                                            <svg class="fill-current h-6 w-6 text-red" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                                        </span>
                                    </div>';

                                echo '<script>window.setTimeout(function(){
                                        // Move to a new location or you can do something else
                                        window.location.href = "Students_perschool";
                                    }, 1000);</script>';
                            }
                            ?>

                            <div class="relative">
                                <select name="Class_ref" class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey" id="countries" required>
                                    <option value="">===========Select Class ========</option>
                                    <?php
                                    $select_class = mysqli_query($conn, "SELECT * FROM school_classes LEFT JOIN school_levels ON school_classes.class_level = school_levels.level_id WHERE 1");
                                    while ($find_class = mysqli_fetch_array($select_class)) {
                                        ?>
                                        <option value="<?php echo $find_class['class_id']; ?>"><?php echo $find_class['level_name']; ?>/<?php echo $find_class['class_name']; ?></option>
                                        <?php
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
