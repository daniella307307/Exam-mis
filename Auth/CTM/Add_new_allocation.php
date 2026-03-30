<?php
ob_start(); 
include('header.php');

// Validate and sanitize user input
$USER = isset($_GET['USER']) ? intval($_GET['USER']) : 0;

if($USER == 0) {
    die("Invalid user ID");
}

// Use prepared statement for security
$select_user = mysqli_prepare($conn, "SELECT * FROM users  
    LEFT JOIN user_permission ON users.access_level = user_permission.permissio_id
    LEFT JOIN schools ON users.school_ref = schools.school_id 
    WHERE user_id = ?");
mysqli_stmt_bind_param($select_user, "i", $USER);
mysqli_stmt_execute($select_user);
$user_result = mysqli_stmt_get_result($select_user);
$find_user = mysqli_fetch_array($user_result);

if(!$find_user) {
    die("User not found");
}
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
                        <p class="text-gray-800 font-medium">Assign New School to: 
                            <strong><?php echo htmlspecialchars($find_user['firstname'] . ' ' . $find_user['lastname']); ?></strong>
                        </p>
                        
                        <div class="mt-2">  
                            <label class="block text-sm text-gray-600" for="Allocation_school">School Allocation</label>	  
                            <select name="Allocation_school" class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey" id="grid-state" required>
                                <option value="">============ Select School ==========</option>  
                                <?php
                                // Use prepared statement for security
                                $select_school = mysqli_prepare($conn, "SELECT * FROM schools WHERE country_ref = ?");
                                mysqli_stmt_bind_param($select_school, "s", $user_country);
                                mysqli_stmt_execute($select_school);
                                $school_result = mysqli_stmt_get_result($select_school);
                                
                                while($find_school = mysqli_fetch_array($school_result)){
                                    $school_id22 = $find_school['school_id'];
                                    
                                    // Check if school is already allocated
                                    $find_allocation = mysqli_prepare($conn, "SELECT * FROM allocation_schools WHERE allocation_school = ? AND allocation_teacher = ?");
                                    mysqli_stmt_bind_param($find_allocation, "ii", $school_id22, $USER);
                                    mysqli_stmt_execute($find_allocation);
                                    mysqli_stmt_store_result($find_allocation);
                                    $nums = mysqli_stmt_num_rows($find_allocation);
                                    
                                    if($nums == 0){
                                        echo '<option value="' . $find_school['school_id'] . '">' . htmlspecialchars($find_school['school_name']) . '</option>';
                                    }
                                    mysqli_stmt_close($find_allocation);
                                }
                                mysqli_stmt_close($select_school);
                                ?>
                            </select>
                        </div>
                        
                        <div class="mt-4">
                            <label class="block text-sm text-gray-600" for="working_days">Weekly working days</label>
                            <div class="bg-gray-200 rounded p-4">
                                <div class="flex flex-wrap gap-4 mb-3" id="dayCheckboxes">
                                    <?php
                                    $days = [
                                        'Monday' => 'Mon',
                                        'Tuesday' => 'Tue', 
                                        'Wednesday' => 'Wed',
                                        'Thursday' => 'Thu',
                                        'Friday' => 'Fri',
                                        'Saturday' => 'Sat',
                                        'Sunday' => 'Sun'
                                    ];
                                    
                                    foreach($days as $fullDay => $abbr) {
                                        echo '
                                        <label class="inline-flex items-center">
                                            <input type="checkbox" name="working_days[]" value="' . $fullDay . '" class="rounded text-gray-700">
                                            <span class="ml-2 text-sm">' . $abbr . '</span>
                                        </label>';
                                    }
                                    ?>
                                </div>
                                <div id="selectedDaysBasket" class="min-h-10 p-2 bg-white rounded border border-gray-300">
                                    <span class="text-gray-400 text-sm">Selected days will appear here...</span>
                                </div>
                                <input type="hidden" name="workin_days_at_school" id="hiddenDaysField">
                            </div>
                        </div>

                        <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const checkboxes = document.querySelectorAll('input[name="working_days[]"]');
                            const basket = document.getElementById('selectedDaysBasket');
                            const hiddenField = document.getElementById('hiddenDaysField');
                            
                            checkboxes.forEach(checkbox => {
                                checkbox.addEventListener('change', updateSelectedDays);
                            });
                            
                            function updateSelectedDays() {
                                const selectedDays = Array.from(checkboxes)
                                    .filter(cb => cb.checked)
                                    .map(cb => cb.value);
                                
                                // Update the basket display
                                if (selectedDays.length === 0) {
                                    basket.innerHTML = '<span class="text-gray-400 text-sm">Selected days will appear here...</span>';
                                } else {
                                    basket.innerHTML = selectedDays.map(day => 
                                        `<span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded mr-2 mb-2">${day}</span>`
                                    ).join('');
                                }
                                
                                // Update the hidden field with comma-separated values for database
                                hiddenField.value = selectedDays.join(',');
                            }
                        });
                        </script>
                         
                        <?php
                        if(isset($_POST['Add_school'])){
                            // Validate and sanitize inputs
                            $Allocation_school = isset($_POST['Allocation_school']) ? intval($_POST['Allocation_school']) : 0;
                            $working_days = isset($_POST['workin_days_at_school']) ? mysqli_real_escape_string($conn, trim($_POST['workin_days_at_school'])) : '';
                            
                            if($Allocation_school == 0) {
                                echo '<div class="bg-red-300 mb-2 border border-red-300 text-red-dark px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">Error!</strong>
                                    <span class="block sm:inline">Please select a school.</span>
                                </div>';
                            } elseif(empty($working_days)) {
                                echo '<div class="bg-red-300 mb-2 border border-red-300 text-red-dark px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">Error!</strong>
                                    <span class="block sm:inline">Please select at least one working day.</span>
                                </div>';
                            } else {
                                // Check if allocation already exists using prepared statement
                                $check_stmt = mysqli_prepare($conn, "SELECT * FROM allocation_schools WHERE allocation_teacher = ? AND allocation_school = ?");
                                mysqli_stmt_bind_param($check_stmt, "ii", $USER, $Allocation_school);
                                mysqli_stmt_execute($check_stmt);
                                mysqli_stmt_store_result($check_stmt);
                                $count = mysqli_stmt_num_rows($check_stmt);
                                mysqli_stmt_close($check_stmt);

                                if($count > 0){
                                    echo '<div class="bg-yellow-300 mb-2 border border-yellow-300 text-yellow-dark px-4 py-3 rounded relative" role="alert">
                                        <strong class="font-bold">Notice!</strong>
                                        <span class="block sm:inline">This school has already been assigned to this teacher.</span>
                                    </div>';
                                } else {
                                    // Insert new allocation using prepared statement
                                    $insert_stmt = mysqli_prepare($conn, "INSERT INTO allocation_schools (allocation_teacher, allocation_school, allocation_day) VALUES (?, ?, ?)");
                                    mysqli_stmt_bind_param($insert_stmt, "iis", $USER, $Allocation_school, $working_days);
                                    
                                    if(mysqli_stmt_execute($insert_stmt)){
                                        mysqli_stmt_close($insert_stmt);
                                        header('Location: Users_School_allocations?USER='.$USER.'&STATUS=Active');
                                        exit();
                                    } else {
                                        echo '<div class="bg-red-300 mb-2 border border-red-300 text-red-dark px-4 py-3 rounded relative" role="alert">
                                            <strong class="font-bold">Error!</strong>
                                            <span class="block sm:inline">Something went wrong. Please try again.</span>
                                        </div>';
                                    }
                                    mysqli_stmt_close($insert_stmt);
                                }
                            }
                        }
                        ?>
                        
                        <div class="mt-4">
                            <center>
                                <button type="submit" name="Add_school" class="px-4 py-1 text-white font-light tracking-wider bg-green-500 rounded hover:bg-green-600 transition duration-200">
                                    Add New School
                                </button>
                            </center>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>