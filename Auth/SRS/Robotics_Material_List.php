<?php include('header.php');

// Handle status filter
if(isset($_GET['STATUS'])){
    $STATUS = $_GET['STATUS'];
    if($STATUS == "Active"){
        $status = "Active"; 
    }
    else{
        $status = "Inactive";  
    }
    $action = "equipments_status ='$status'"; 
}
else{
    $STATUS = "Active";	
    $status = $STATUS; 
    $action = "equipments_status ='$STATUS'";
}

// Pagination setup
$results_per_page = 50;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page);
$start_from = ($page - 1) * $results_per_page;

// Search functionality
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// Build query with search
$where_conditions = ["equipments_status='$STATUS'"];
if(!empty($search)) {
    $where_conditions[] = "(equipments_name LIKE '%$search%' OR 
                          equipments_ModelNo LIKE '%$search%' OR 
                          equipments_description LIKE '%$search%' OR
                          category_name LIKE '%$search%' OR
                          subcategory_name LIKE '%$search%')";
}
$where_clause = implode(' AND ', $where_conditions);

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM laboratory_equipments
LEFT JOIN Equipment_categories ON laboratory_equipments.equipments_category = Equipment_categories.category_id
LEFT JOIN Equipment_sub_categories ON laboratory_equipments.equipments_subcategory = Equipment_sub_categories.subcategory_id
WHERE $where_clause";
$count_result = mysqli_query($conn, $count_query);
$total_rows = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_rows / $results_per_page);

// Get data with pagination and search
$select_countries = mysqli_query($conn, "SELECT * FROM laboratory_equipments
LEFT JOIN Equipment_categories ON laboratory_equipments.equipments_category = Equipment_categories.category_id
LEFT JOIN Equipment_sub_categories ON laboratory_equipments.equipments_subcategory = Equipment_sub_categories.subcategory_id
WHERE $where_clause 
ORDER BY equipments_id DESC 
LIMIT $start_from, $results_per_page");
?>
 
<!--/Header-->

<div class="flex flex-1">
    <!--Sidebar-->
    <?php include('Robotics_materials_side_bar.php');?>
    <!--/Sidebar-->
    <!--Main-->
    <main class="bg-white-500 flex-1 p-3 overflow-hidden">

        <div class="flex flex-col">
            <!-- Card Section Starts Here -->
            <div class="flex flex-1 flex-col md:flex-row lg:flex-row mx-2">
                <!--Horizontal form-->
                
                <!--/Horizontal form-->

                <!--Underline form-->
                 
                <!--/Underline form-->
            </div>
            <!-- /Cards Section Ends Here -->

            <!--Grid Form-->
            <div class="flex flex-1 flex-col md:flex-row lg:flex-row mx-2">
                <div class="mb-2 border-solid border-gray-300 rounded border shadow-sm w-full">
                    <div class="bg-gray-200 px-2 py-3 border-solid border-gray-200 border-b">
                        Robotics Materials List
                        <a href="Robotics_Material_List?STATUS=Active<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>"><button class='bg-green-500 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded'>Active</button></a>  
                        <a href="Robotics_Material_List?STATUS=Inactive<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>"><button class='bg-red-500 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'>Inactive</button></a>  
                       
                    </div>
                    
                    <!-- Search Box -->
                    <div class="bg-gray-100 px-4 py-3 border-b border-gray-300">
                        <form method="GET" action="" class="flex items-center">
                            <input type="hidden" name="STATUS" value="<?php echo $STATUS; ?>">
                            <input type="text" 
                                   name="search" 
                                   id="searchInput"
                                   value="<?php echo htmlspecialchars($search); ?>" 
                                   placeholder="Search by name, model, description, category..." 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   onkeyup="liveSearch()">
                            <button type="submit" class="ml-2 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                <i class="fas fa-search"></i>
                            </button>
                            <?php if(!empty($search)): ?>
                            <a href="Robotics_Material_List?STATUS=<?php echo $STATUS; ?>" class="ml-2 bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Clear
                            </a>
                            <?php endif; ?>
                        </form>
                    </div>

                    <div class="p-3">
                        <!-- Results Count -->
                        <div class="mb-3 text-sm text-gray-600">
                            <?php 
                            $start_item = $total_rows > 0 ? $start_from + 1 : 0;
                            $end_item = min($start_from + $results_per_page, $total_rows);
                            ?>
                            Showing <?php echo $start_item; ?> - <?php echo $end_item; ?> of <?php echo $total_rows; ?> items
                            <?php if(!empty($search)): ?>
                                for "<?php echo htmlspecialchars($search); ?>"
                            <?php endif; ?>
                        </div>

                        <table class="table-responsive w-full rounded" id="dataTable">
                            <thead>
                                <tr>
                                    <th class="border w-1/12 px-4 py-2">ID</th>
                                    <th class="border w-1/8 px-4 py-2">Name</th>
                                    <th class="border w-3/8 px-4 py-2">Model No</th>
                                    <th class="border w-1/8 px-4 py-2">Description</th>
                                    <th class="border w-1/12 px-4 py-2">Category</th>
                                    <th class="border w-2/12 px-4 py-2">Sub Category</th>
                                    <th class="border w-1/10 px-4 py-2">Status</th> 
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                                <?php
                                if(mysqli_num_rows($select_countries) > 0) {
                                    while($country_details = mysqli_fetch_array($select_countries)){
                                ?>
                                <tr class="searchable">
                                    <td class="border px-4 py-1"><?php echo $country_details['equipments_id'];?></td>
                                    <td class="border px-4 py-2"><?php echo $country_details['equipments_name'];?></td>
                                    <td class="border px-4 py-2"><?php echo $country_details['equipments_ModelNo'];?></td>
                                    <td class="border px-4 py-2"><?php echo $country_details['equipments_description'];?></td>
                                    <td class="border px-4 py-2"><?php echo $country_details['category_name'];?></td>
                                    <td class="border px-4 py-2"><?php echo $country_details['subcategory_name'];?></td>
                                    <td class="border px-4 py-2">
                                        <?php if($STATUS == "Active"){  
                                            ?> <a href="#"><i class="fas fa-unlock text-green-500 mx-2"></i></a><?php
                                        }else{
                                            ?> <a href="#"><i class="fas fa-lock text-red-500 mx-2"></i></a> <?php	
                                        }?>
                                    </td>
                                     
                                </tr>
                                <?php
                                    }
                                } else {
                                ?>
                                <tr>
                                    <td colspan="8" class="border px-4 py-4 text-center text-gray-500">
                                        <?php echo empty($search) ? 'No items found.' : 'No results found for "' . htmlspecialchars($search) . '"'; ?>
                                    </td>
                                </tr>
                                <?php
                                }
                                ?>
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        <?php if($total_pages > 1): ?>
                        <div class="mt-4 flex justify-center items-center space-x-2">
                            <!-- Previous Button -->
                            <?php if($page > 1): ?>
                                <a href="?STATUS=<?php echo $STATUS; ?>&page=<?php echo $page-1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                                   class="bg-blue-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                    &laquo; Previous
                                </a>&nbsp;
                            <?php endif; ?>

                            <!-- Page Numbers -->
                            <?php 
                            $start_page = max(1, $page - 2);
                            $end_page = min($total_pages, $page + 2);
                            
                            for($i = $start_page; $i <= $end_page; $i++): 
                            ?>
                                <a href="?STATUS=<?php echo $STATUS; ?>&page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                                   class="<?php echo $i == $page ? 'bg-blue-500 text-white' : 'bg-gray-300 text-gray-800'; ?> hover:bg-blue-400 font-bold py-2 px-4 rounded">
                                    <?php echo $i; ?>
                                </a>&nbsp;
                            <?php endfor; ?>

                            <!-- Next Button -->
                            <?php if($page < $total_pages): ?>
                                <a href="?STATUS=<?php echo $STATUS; ?>&page=<?php echo $page+1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                                   class="bg-blue-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                    Next &raquo;
                                </a> &nbsp;
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <!--/Grid Form-->
        </div>
    </main>
    <!--/Main-->
</div>
<!--Footer-->
<?php include('footer.php')?>
<!--/footer-->

</div>

</div>

<script src="../../main.js"></script>
<script>
// Live Search Functionality
function liveSearch() {
    const searchValue = document.getElementById('searchInput').value.toLowerCase();
    const rows = document.querySelectorAll('#tableBody tr.searchable');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if(text.includes(searchValue)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
    
    // Show/hide no results message
    const visibleRows = Array.from(rows).filter(row => row.style.display !== 'none');
    const noResultsRow = document.querySelector('#tableBody tr:not(.searchable)');
    
    if(visibleRows.length === 0 && noResultsRow) {
        noResultsRow.style.display = '';
    } else if(noResultsRow) {
        noResultsRow.style.display = 'none';
    }
}

// Initialize live search on page load
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    if(searchInput.value) {
        liveSearch();
    }
});
</script>

</body>
</html>