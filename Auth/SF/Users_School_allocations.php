<?php 
include('header.php');

if(isset($session_id)){
    $USER = $session_id;
    // Sanitize inputs
     
    // Use prepared statement for user data
    $user_query = mysqli_prepare($conn, "SELECT * FROM users
        LEFT JOIN user_permission ON users.access_level = user_permission.permissio_id
        LEFT JOIN schools ON users.school_ref = schools.school_id
        LEFT JOIN countries ON users.user_country = countries.id
        LEFT JOIN regions_table ON users.user_region = regions_table.region_id
        WHERE user_id = ?");
    mysqli_stmt_bind_param($user_query, "i", $session_id);
    mysqli_stmt_execute($user_query);
    $user_result = mysqli_stmt_get_result($user_query);
    $find_user = mysqli_fetch_array($user_result);
   
    
    if(!$find_user) {
        die("User not found");
    }

    // Use prepared statement for allocations
    $allocations_query = mysqli_prepare($conn, "SELECT 
        allocation_schools.allocation_id,
        allocation_schools.allocation_school,
        allocation_schools.allocation_day,
        schools.school_id,
        schools.school_name,
         users.status,
         users.firstname,
        users.lastname
        FROM allocation_schools
        LEFT JOIN users ON allocation_schools.allocation_teacher = users.user_id
        LEFT JOIN schools ON allocation_schools.allocation_school = schools.school_id
        WHERE allocation_teacher = ?");
    mysqli_stmt_bind_param($allocations_query, "i", $USER);
    mysqli_stmt_execute($allocations_query);
    $select_countries = mysqli_stmt_get_result($allocations_query);
    
?>
 
<!--/Header-->

<div class="flex flex-1">
    <!--Sidebar-->
    <?php include('dynamic_side_bar.php');?>
    <!--/Sidebar-->
    
    <!--Main-->
    <main class="bg-gray-50 flex-1 p-6 overflow-auto">
        <div class="flex flex-col">
            <!-- Header Section -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                    <div class="mb-4 lg:mb-0">
                        <h1 class="text-2xl font-bold text-gray-800">Facilitator Allocations</h1>
                        <div class="mt-2">
                            <h2 class="text-lg text-gray-600">
                                <strong>Facilitator:</strong>
                                <span class="text-blue-600 font-semibold">
                                    <?php echo htmlspecialchars($find_user['firstname'] . " " . $find_user['lastname']); ?>
                                </span>
                            </h2>
                            <p class="text-sm text-gray-500 mt-1">
                                User ID: <?php echo $status; ?> | Status: 
                                <span class="font-semibold <?php echo $status == 'Active' ? 'text-green-600' : 'text-red-600'; ?>">
                                    <?php echo $status; ?>
                                </span>
                            </p>
                        </div>
                    </div>
                    
                </div>
            </div>

            <!-- Table Section -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 w-full">
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full table-auto">
                            <thead>
                                <tr class="bg-gray-50 border-b-2 border-gray-200">
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-16">ID</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-1/4">School Information</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-1/3">Working Schedule</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-48">Student Info</th> 
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php
                                if(mysqli_num_rows($select_countries) > 0) {
                                    while($country_details = mysqli_fetch_array($select_countries)) {
                                        $school = $country_details['school_id'];
                                        $STATUS =$country_details['status']; 
                                        
                                        // Get student count with prepared statement
                                        $student_query = mysqli_prepare($conn, "SELECT COUNT(student_id) AS Students FROM student_list WHERE student_school = ?");
                                        mysqli_stmt_bind_param($student_query, "i", $school);
                                        mysqli_stmt_execute($student_query);
                                        $student_result = mysqli_stmt_get_result($student_query);
                                        $find_students = mysqli_fetch_array($student_result);
                                        $Students = $find_students['Students'];
                                        mysqli_stmt_close($student_query);
                                        
                                        // Process allocation_day data
                                        $allocation_days = $country_details['allocation_day'];
                                        $days_array = [];
                                        
                                        if(!empty($allocation_days)) {
                                            $days_array = explode(',', $allocation_days);
                                        }
                                ?>
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <!-- ID Column -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm font-medium text-gray-900 bg-gray-100 px-2 py-1 rounded">
                                            #<?php echo htmlspecialchars($country_details['allocation_id']); ?>
                                        </span>
                                    </td>
                                    
                                    <!-- School Information Column -->
                                    <td class="px-6 py-4">
                                        <div class="flex items-start space-x-3">
                                            <div class="flex-shrink-0">
                                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                                    <i class="fas fa-school text-blue-600"></i>
                                                </div>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <h3 class="text-sm font-semibold text-gray-900 truncate">
                                                    <?php echo htmlspecialchars($country_details['school_name']); ?>
                                                </h3>
                                                <?php if(!empty($country_details['school_address'])): ?>
                                                    <p class="text-xs text-gray-500 mt-1 truncate">
                                                        <i class="fas fa-map-marker-alt mr-1"></i>
                                                        <?php echo htmlspecialchars($country_details['school_address']); ?>
                                                    </p>
                                                <?php endif; ?>
                                                <p class="text-xs text-gray-400 mt-1">
                                                    School ID: <?php echo htmlspecialchars($country_details['school_id']); ?>
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <!-- Working Schedule Column -->
                                    <td class="px-6 py-4">
                                        <?php if(!empty($days_array)): ?>
                                            <div class="mb-2">
                                                <div class="flex flex-wrap gap-1">
                                                    <?php 
                                                    $day_colors = [
                                                        'Monday' => 'bg-blue-100 text-blue-800 border-blue-200',
                                                        'Tuesday' => 'bg-green-100 text-green-800 border-green-200',
                                                        'Wednesday' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                                        'Thursday' => 'bg-purple-100 text-purple-800 border-purple-200',
                                                        'Friday' => 'bg-red-100 text-red-800 border-red-200',
                                                        'Saturday' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                                                        'Sunday' => 'bg-pink-100 text-pink-800 border-pink-200'
                                                    ];
                                                    
                                                    foreach($days_array as $day): 
                                                        $color_class = isset($day_colors[$day]) ? $day_colors[$day] : 'bg-gray-100 text-gray-800 border-gray-200';
                                                        $abbr = substr(trim($day), 0, 3);
                                                    ?>
                                                        <span class="inline-block <?php echo $color_class; ?> text-xs px-2 py-1 rounded border font-medium">
                                                            <?php echo htmlspecialchars($abbr); ?>
                                                        </span>&nbsp;&nbsp;
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                            <div class="text-xs text-gray-600 bg-gray-50 px-2 py-1 rounded">
                                                <i class="fas fa-calendar-week mr-1"></i>
                                                <?php echo htmlspecialchars($allocation_days); ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-red-500 text-sm italic bg-red-50 px-3 py-2 rounded-lg inline-flex items-center">
                                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                                No working days assigned
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <!-- Student Info Column -->
                                    <td class="px-6 py-4">
                                        <div class="text-center">
                                            <div class="inline-flex flex-col items-center">
                                                <div class="text-2xl font-bold <?php echo $Students > 0 ? 'text-green-600' : 'text-red-600'; ?>">
                                                    <?php echo $Students; ?>
                                                </div>
                                                <div class="text-xs text-gray-500 mt-1">Students</div>
                                            </div>
                                            <?php if($Students > 0): ?>
                                                <div class="mt-2">
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        <i class="fas fa-check-circle mr-1"></i>
                                                        Active
                                                    </span>
                                                </div>
                                            <?php else: ?>
                                                <div class="mt-2">
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                        <i class="fas fa-exclamation-circle mr-1"></i>
                                                        No Students
                                                    </span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                   
                                </tr>
                                <?php 
                                    }
                                } else {
                                ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center text-gray-500">
                                            <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
                                            <p class="text-xl font-semibold text-gray-600 mb-2">No Allocations Found</p>
                                            <p class="text-gray-500 mb-6 max-w-md">This facilitator doesn't have any school allocations yet. Start by adding their first allocation.</p>
                                            <?php if($STATUS == "Active"): ?>
                                                <a href="Add_new_allocation?USER=<?php echo $USER; ?>" 
                                                   class="bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-8 rounded-lg transition duration-200 inline-flex items-center shadow-md">
                                                    <i class="fas fa-plus-circle mr-2"></i>
                                                    Create First Allocation
                                                </a>
                                            <?php endif; ?>
                                        </div>
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
        </div>
    </main>
    <!--/Main-->
</div>

<!--Footer-->
<footer class="bg-grey-darkest text-white p-4">
    <div class="flex flex-1 mx-auto text-sm">&copy; <?php echo date('Y'); ?> My Design</div>
</footer>
<!--/footer-->

</div>

<script src="../../main.js"></script>

</body>
</html>
<?php 
} else {
    echo "<div class='min-h-screen flex items-center justify-center bg-gray-50'>
            <div class='bg-white rounded-lg shadow-sm p-8 max-w-md w-full mx-4'>
                <div class='text-center'>
                    <i class='fas fa-exclamation-triangle text-5xl text-red-400 mb-4'></i>
                    <h2 class='text-xl font-semibold text-gray-800 mb-2'>Invalid Request</h2>
                    <p class='text-gray-600 mb-4'>Missing required parameters. Please check the URL and try again.</p>
                    <a href='javascript:history.back()' class='bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition duration-200 inline-flex items-center'>
                        <i class='fas fa-arrow-left mr-2'></i>
                        Go Back
                    </a>
                </div>
            </div>
          </div>";
}
?>