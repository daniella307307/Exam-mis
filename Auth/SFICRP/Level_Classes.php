<?php
ob_start();
include('header.php');

// Set the number of records to display per page
$records_per_page = 30;

// Get the current page or set a default
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;

// Calculate the offset for the query
$offset = ($current_page - 1) * $records_per_page;

// Initialize variables
$search_term = '';
$search_query = '1'; // Default to true condition for easier concatenation

// Handle STATUS
if (isset($_GET['STATUS'])) {
    $STATUS = $_GET['STATUS'];
    if ($STATUS === "Active") {
        $action = "level_status = 'Active'";
    } else {
        $action = "level_status != 'Active' OR level_status = 'Inactive'";
    }
} else {
    $STATUS = "Active";
    $action = "level_status = 'Active'";
}

// Handle search
if (isset($_GET['search']) && !empty($_GET['search_term'])) {
    $search_term = mysqli_real_escape_string($conn, $_GET['search_term']);
    $search_query = "(level_name LIKE '%$search_term%'  OR level_id LIKE '%$search_term%')";
}

// Count total records (respecting filters)
$count_query = "
    SELECT COUNT(*) 
    FROM school_levels 
    WHERE $search_query AND $action
";
$count_result = mysqli_query($conn, $count_query);
$total_records = mysqli_fetch_array($count_result)[0];

// Fetch paginated records
$select_user = mysqli_query($conn, "
    SELECT * 
    FROM school_levels 
    WHERE $search_query AND $action
    ORDER BY level_id ASC
    LIMIT $offset, $records_per_page
");
?>

<!--/Header-->

<div class="flex flex-1">
    <!--Sidebar-->
    <?php include('dynamic_side_bar.php'); ?>
    <!--/Sidebar-->

    <!--Main-->
    <main class="bg-white-500 flex-1 p-3 overflow-hidden">
        <div class="flex flex-col">

            <!-- Header Buttons -->
            <div class="flex justify-between items-center mb-4">
                <div>
                    <a href="Students_perschool?STATUS=Active">
                        <button class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Active</button>
                    </a>
                    <a href="Students_perschool?STATUS=Inactive">
                        <button class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Inactive</button>
                    </a>
                    <a href="Add_New_Class_inschool">
                        <button class="bg-blue-800 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            <i class="fas fa-plus"></i>&nbsp;Add New
                        </button>
                    </a>
                </div>
            </div>

            <!-- Search Form -->
            <form method="GET" action="" class="flex mb-4">
                <input type="hidden" name="STATUS" value="<?php echo $STATUS; ?>">
                <input type="text" name="search_term" placeholder="Search by level name or class"
                       value="<?php echo htmlspecialchars($search_term); ?>"
                       class="px-4 py-2 border rounded-l focus:outline-none focus:ring-2 focus:ring-blue-500 w-1/2">
                <button type="submit" name="search"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-r">
                    <i class="fas fa-search"></i> Search
                </button>
                <?php if (!empty($search_term)): ?>
                    <a href="Students_perschool?STATUS=<?php echo $STATUS; ?>"
                       class="ml-2 bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Clear
                    </a>
                <?php endif; ?>
            </form>

            <!-- Data Table -->
            <div class="p-3 border rounded shadow-sm">
                <table class="table-auto w-full border-collapse">
                    <thead>
                        <tr class="bg-gray-100 text-left">
                            <th class="border px-4 py-2">ID</th> 
                            <th class="border px-4 py-2">Level Name</th>
                            <th class="border px-4 py-2">Status</th> 
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($select_user) > 0): ?>
                            <?php while ($row = mysqli_fetch_array($select_user)): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="border px-4 py-2"><?php echo $row['level_id']; ?></td>
                                    <td class="border px-4 py-2"><?php echo $row['level_name']; ?></td> 
                                    <td class="border px-4 py-2">
                                       
                                       
                                       
                                        <?php
                                        $status_icon = [
                                            'Active' => '<i class="fas fa-check text-green-500"></i>',
                                            'Inactive' => '<i class="fas fa-times text-red-500"></i>',
                                            'Deleted' => '<i class="fas fa-trash text-gray-500"></i>',
                                            'Burned' => '<i class="fas fa-fire text-orange-500"></i>',
                                            'Suspended' => '<i class="fas fa-pause text-yellow-500"></i>'
                                        ];
                                        echo $status_icon[$row['level_status']] ?? '<i class="fas fa-question text-gray-400"></i>';
                                        ?>
                                    </td>
                                   
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="border px-4 py-2 text-center text-gray-500">
                                    No records found
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="flex justify-center items-center mt-4">
                <?php
                $total_pages = ceil($total_records / $records_per_page);
                if ($total_pages > 1):
                    // Previous button
                    if ($current_page > 1) {
                        echo "<a href='?page=".($current_page-1)."&STATUS=$STATUS&search_term=$search_term&search=1' class='mx-1 px-3 py-2 bg-gray-200 rounded hover:bg-blue-500 hover:text-white'>Prev</a>";
                    }

                    // Page numbers
                    for ($i = 1; $i <= $total_pages; $i++) {
                        $active = $i == $current_page ? 'bg-blue-500 text-white' : 'bg-gray-200 hover:bg-gray-300';
                        echo "<a href='?page=$i&STATUS=$STATUS&search_term=$search_term&search=1' class='mx-1 px-3 py-2 rounded $active'>$i</a>";
                    }

                    // Next button
                    if ($current_page < $total_pages) {
                        echo "<a href='?page=".($current_page+1)."&STATUS=$STATUS&search_term=$search_term&search=1' class='mx-1 px-3 py-2 bg-gray-200 rounded hover:bg-blue-500 hover:text-white'>Next</a>";
                    }

                    echo "<div class='ml-4 text-gray-600'>Showing ".($offset + 1)." to ".min($offset + $records_per_page, $total_records)." of $total_records records</div>";
                endif;
                ?>
            </div>
        </div>
    </main>
</div>

<?php include('footer.php'); ?>
<script src="../../main.js"></script>
</body>
</html>
