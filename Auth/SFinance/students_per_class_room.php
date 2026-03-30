<?php
ob_start();
include('header.php');

// Set the number of records to display per page
$records_per_page = 35;

// Validate and sanitize input
$CLASS = isset($_GET['CLASS']) ? intval($_GET['CLASS']) : 0;

// Get class details using prepared statement
$stmt = $conn->prepare("SELECT * FROM school_classes 
                       LEFT JOIN school_levels ON school_classes.class_level = school_levels.level_id
                       WHERE class_school = ? AND class_id = ?");
$stmt->bind_param("si", $school_ref, $CLASS);
$stmt->execute();
$class_details = $stmt->get_result()->fetch_assoc();

if (!$class_details) {
    die("Invalid class selected");
}

// Get the current page or set a default
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $records_per_page;

// Initialize search variables
$search_query = '';
$search_term = '';
$STATUS = "Active"; // Default status

if (isset($_GET['STATUS'])) {
    $STATUS = $_GET['STATUS'] === "Active" ? "Active" : "Inactive";
}

// Set status filter condition
$status_condition = $STATUS === "Active" 
    ? "student_status = 'Active'" 
    : "(student_status != 'Active' OR student_status = 'Inactive')";

// Handle search
if (isset($_GET['search']) && !empty($_GET['search_term'])) {
    $search_term = trim($_GET['search_term']);
    $search_term_escaped = $conn->real_escape_string($search_term);
    $search_query = " AND (student_first_name LIKE '%$search_term_escaped%' 
                      OR student_last_name LIKE '%$search_term_escaped%'
                      OR student_regno LIKE '%$search_term_escaped%'
                      OR student_contact LIKE '%$search_term_escaped%')";
}

// Count total records with prepared statement
$count_query = "SELECT COUNT(*) FROM student_list 
                WHERE student_class = ? AND student_school = ? AND $status_condition $search_query";
$stmt = $conn->prepare($count_query);
$stmt->bind_param("is", $CLASS, $school_ref);
$stmt->execute();
$total_records = $stmt->get_result()->fetch_row()[0];

// Fetch records with prepared statement
$query = "SELECT * FROM student_list 
          WHERE student_class = ? AND student_school = ? AND $status_condition $search_query
          ORDER BY student_regno DESC 
          LIMIT ?, ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("isii", $CLASS, $school_ref, $offset, $records_per_page);
$stmt->execute();
$select_user = $stmt->get_result();
?>

<!--/Header-->

<div class="flex flex-1">
    <!--Sidebar-->
    <?php include('dynamic_side_bar.php'); ?>
    <!--/Sidebar-->
    <!--Main-->
    <main class="bg-white-500 flex-1 p-3 overflow-hidden">
        <div class="flex flex-col">
            <!-- Card Section Starts Here -->
            <div class="flex flex-1 flex-col md:flex-row lg:flex-row mx-2">
                <!-- Action buttons -->
                <div class="mb-2 border-solid border-gray-300 rounded border shadow-sm w-full">
                    <div class="bg-gray-200 px-2 py-3 border-solid border-gray-200 border-b flex flex-wrap justify-between items-center gap-2">
                        
                    </div>

                    <!-- Search form -->
                    <div class="p-3">
                        <form method="GET" action="" class="flex items-center gap-2">
                            <input type="hidden" name="CLASS" value="<?= $CLASS ?>">
                            <input type="hidden" name="STATUS" value="<?= htmlspecialchars($STATUS) ?>">
                            <input type="text" name="search_term" placeholder="Search by name, reg no, or contact" 
                                   value="<?= htmlspecialchars($search_term) ?>" 
                                   class="flex-1 px-4 py-2 border rounded-l focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <button type="submit" name="search" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-r">
                                <i class="fas fa-search"></i> Search
                            </button>
                            <?php if (!empty($search_term)): ?>
                                <a href="Students_perschool?STATUS=<?= $STATUS ?>&CLASS=<?= $CLASS ?>" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                    Clear
                                </a>
                            <?php endif; ?>
                        </form>
                    </div>

                    <!-- Student list table -->
                    <div class="p-3">
                        <h1 class="text-center text-xl font-bold">
                            <span class="text-lg">List of students in:</span> 
                            <span class="text-2xl"><?= htmlspecialchars($class_details['class_name']) ?></span>
                        </h1>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full table-auto border-collapse">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="border px-4 py-2">REG No</th>
                                        <th class="border px-4 py-2">Names</th>
                                        <th class="border px-4 py-2">Gender</th>
                                        <th class="border px-4 py-2">Contact</th>
                                        <th class="border px-4 py-2">Status</th>
                                        <th class="border px-4 py-2">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($select_user->num_rows > 0): ?>
                                        <?php while($user = $select_user->fetch_assoc()): ?>
                                            <tr class="hover:bg-gray-50">
                                                <td class="border px-4 py-2"><?= htmlspecialchars($user['student_regno']) ?></td>
                                                <td class="border px-4 py-2">
                                                    <?= htmlspecialchars($user['student_first_name'] . ' ' . $user['student_last_name']) ?>
                                                </td>
                                                <td class="border px-4 py-2"><?= htmlspecialchars($user['student_gender']) ?></td>
                                                <td class="border px-4 py-2"><?= htmlspecialchars($user['student_contact']) ?></td>
                                                <td class="border px-4 py-2 text-center">
                                                    <?php switch($user['student_status']):
                                                        case 'Active': ?>
                                                            <i class="fas fa-check text-green-500" title="Active"></i>
                                                            <?php break;
                                                        case 'Inactive': ?>
                                                            <i class="fas fa-times text-red-500" title="Inactive"></i>
                                                            <?php break;
                                                        case 'Deleted': ?>
                                                            <i class="fas fa-trash text-red-500" title="Deleted"></i>
                                                            <?php break;
                                                        case 'Burned': ?>
                                                            <i class="fas fa-globe text-red-500" title="Burned"></i>
                                                            <?php break;
                                                        case 'Suspended': ?>
                                                            <i class="fas fa-pause text-red-500" title="Suspended"></i>
                                                            <?php break;
                                                    endswitch; ?>
                                                </td>
                                                <td class="border px-4 py-2 text-center">
                                                    <a href="Student_details?ID=<?= $user['student_id'] ?>" 
                                                       class="inline-block bg-blue-500 hover:bg-blue-700 text-white p-1 mx-1 rounded" 
                                                       title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="Update_Student_details?ID=<?= $user['student_id'] ?>" 
                                                       class="inline-block bg-green-600 hover:bg-green-700 text-white p-1 mx-1 rounded" 
                                                       title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="border px-4 py-2 text-center">No students found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Enhanced Pagination -->
            <?php if ($total_records > $records_per_page): ?>
                <div class="flex flex-col md:flex-row justify-between items-center mt-4 gap-4">
                    <div class="text-gray-600">
                        Showing <?= $offset + 1 ?> to <?= min($offset + $records_per_page, $total_records) ?> of <?= $total_records ?> records
                    </div>
                    
                    <div class="flex flex-wrap justify-center gap-1">
                        <?php
                        $total_pages = ceil($total_records / $records_per_page);
                        $query_params = [
                            'CLASS' => $CLASS,
                            'STATUS' => $STATUS,
                            'search_term' => $search_term,
                            'search' => !empty($search_term) ? 1 : null
                        ];
                        
                        // Previous button
                        if ($current_page > 1) {
                            $query_params['page'] = $current_page - 1;
                            echo "<a href='Students_perschool.php?" . http_build_query($query_params) . "' class='btn-pagination'>
                                    <i class='fas fa-chevron-left'></i> Previous
                                  </a>";
                        }
                        
                        // Page numbers
                        $start_page = max(1, $current_page - 2);
                        $end_page = min($total_pages, $current_page + 2);
                        
                        if ($start_page > 1) {
                            $query_params['page'] = 1;
                            echo "<a href='Students_perschool.php?" . http_build_query($query_params) . "' class='btn-pagination'>1</a>";
                            if ($start_page > 2) echo "<span class='px-2 py-2'>...</span>";
                        }
                        
                        for ($i = $start_page; $i <= $end_page; $i++) {
                            $query_params['page'] = $i;
                            $active_class = $i == $current_page ? 'bg-blue-500 text-white' : 'bg-gray-200 hover:bg-gray-300';
                            echo "<a href='Students_perschool.php?" . http_build_query($query_params) . "' class='btn-pagination $active_class'>$i</a>";
                        }
                        
                        if ($end_page < $total_pages) {
                            if ($end_page < $total_pages - 1) echo "<span class='px-2 py-2'>...</span>";
                            $query_params['page'] = $total_pages;
                            echo "<a href='Students_perschool.php?" . http_build_query($query_params) . "' class='btn-pagination'>$total_pages</a>";
                        }
                        
                        // Next button
                        if ($current_page < $total_pages) {
                            $query_params['page'] = $current_page + 1;
                            echo "<a href='Students_perschool.php?" . http_build_query($query_params) . "' class='btn-pagination'>
                                    Next <i class='fas fa-chevron-right'></i>
                                  </a>";
                        }
                        ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
    <!--/Main-->
</div>

<!--Footer-->
<?php include('footer.php') ?>
<!--/footer-->

<style>
    .btn {
        @apply text-white font-bold py-2 px-4 rounded transition-colors duration-200;
    }
    .btn-pagination {
        @apply mx-1 px-4 py-2 rounded-lg transition-colors duration-200;
    }
    .table-responsive {
        @apply w-full overflow-x-auto;
    }
    .min-w-full {
        min-width: 100%;
    }
</style>

<script src="../../main.js"></script>
</body>
</html>