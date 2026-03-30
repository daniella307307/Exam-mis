<?php 
include('header.php');

// Handle search and pagination
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$results_per_page = 10;
$offset = ($page - 1) * $results_per_page;

// Validate and set status
$STATUS = isset($_GET['STATUS']) && in_array($_GET['STATUS'], ['Generated', 'Aprouved']) 
            ? $_GET['STATUS'] 
            : "Generated";
$status_condition = "received_status='$STATUS'";

// Base query with joins
$base_query = "SELECT tr.*, tt.ticket_type, u.lastname 
              FROM ticket_received tr
              LEFT JOIN tickets_types tt ON tr.received_type = tt.ticket_id
              LEFT JOIN users u ON tr.received_user = u.user_id
              WHERE tr.received_school = '$school_ref'
                AND $status_condition";

// Add search filter
if(!empty($search)) {
    $base_query .= " AND (tr.received_number LIKE '%".$search."%' 
                      OR tt.ticket_type LIKE '%".$search."%')";
}

// Get paginated results
$query = $base_query . " LIMIT $offset, $results_per_page";
$result = mysqli_query($conn, $query);

if(!$result) {
    die("Query failed: " . mysqli_error($conn));
}

// Count total records
$count_query = str_replace('SELECT tr.*, tt.ticket_type, u.lastname', 'SELECT COUNT(*) as total', $base_query);
$count_result = mysqli_query($conn, $count_query);
$total_data = mysqli_fetch_assoc($count_result);
$total_rows = $total_data['total'];
$total_pages = ceil($total_rows / $results_per_page);
?>

<div class="flex flex-1">
    <?php include('dynamic_side_bar.php');?>

    <main class="bg-white-500 flex-1 p-3 overflow-hidden">
        <div class="flex flex-col">
            <div class="flex flex-1 flex-col md:flex-row lg:flex-row mx-2">
                <div class="mb-2 border-solid border-gray-300 rounded border shadow-sm w-full">
                    <div class="bg-gray-200 px-2 py-3 border-solid border-gray-200 border-b">
                        <!-- Search Form -->
                        <form method="GET" class="inline-block">
                            <input type="text" name="search" placeholder="Search tickets..." 
                                   value="<?php echo htmlspecialchars($search); ?>"
                                   class="px-4 py-2 border rounded-l bg-gray-200">
                            <input type="hidden" name="STATUS" value="<?php echo $STATUS; ?>">
                            <button type="submit" 
                                    class="px-4 py-2 bg-blue-500 text-white rounded-r hover:bg-blue-600">
                                Search
                            </button>
                        </form>

                        <!-- Status Buttons -->
                        <div class="inline-block ml-4">
                            <?php 
                            $status_buttons = [
                                'Generated' => ['color' => 'green', 'label' => 'Created'],
                                'Aprouved' => ['color' => 'red', 'label' => 'Approved']
                            ];
                            foreach ($status_buttons as $status => $info): 
                                $active = $STATUS === $status;
                            ?>
                                <a href="?STATUS=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>" 
                                   class="<?php echo $active ? 'bg-'.$info['color'].'-500' : 'bg-gray-500'; ?> 
                                   hover:bg-blue-800 text-white font-bold py-2 px-4 rounded ml-2">
                                    <?php echo $info['label']; ?>
                                </a>
                            <?php endforeach; ?>
                            
                            <a href="Add_new_ticket" 
                               class="bg-blue-500 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded ml-2">
                                <i class="fas fa-plus text-white-500 mx-2"></i> Add New
                            </a>
                        </div>
                    </div>

                    <div class="p-3">
                        <table class="table-responsive w-full rounded">
                            <thead>
                                <tr>
                                    <th class="border w-1/12 px-4 py-2">#</th>
                                    <th class="border w-1/8 px-4 py-2">Ticket Number</th>
                                    <th class="border w-1/8 px-4 py-2">Ticket Type</th>
                                    <th class="border w-1/9 px-4 py-2">Amount</th>
                                    <th class="border w-1/8 px-4 py-2">Date</th>
                                    <th class="border w-1/10 px-4 py-2">Created by</th>
                                    <th class="border w-1/10 px-4 py-2">Status</th>
                                    <th class="border w-1/10 px-4 py-2">Approved by</th>
                                    <th class="border w-1/8 px-4 py-2">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(mysqli_num_rows($result) > 0): ?>
                                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                                        <tr>
                                            <td class="border px-4 py-1"><?php echo $row['received_id']; ?></td>
                                            <td class="border px-4 py-2"><?php echo $row['received_number']; ?></td>
                                            <td class="border px-4 py-2"><?php echo $row['ticket_type']; ?></td>
                                            <td class="border px-4 py-2"><?php echo $row['received_amount']; ?></td>
                                            <td class="border px-4 py-2"><?php echo $row['received_date']; ?></td>
                                            <td class="border px-4 py-2"><?php echo $row['lastname']; ?></td>
                                            <td class="border px-4 py-2">
                                                <span class="px-2 py-1 text-sm rounded <?php echo $row['received_status'] === 'Generated' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                                                    <?php echo $row['received_status']; ?>
                                                </span>
                                            </td>
                                            <td class="border px-4 py-2"><?php echo $row['received_aprouved_by']; ?></td>
                                            <td class="border px-4 py-2">
                                                <a href="Update_Countries?CURRENT=<?php echo $STATUS; ?>&ID=<?php echo $row['received_id']; ?>" 
                                                   class="bg-teal-300 cursor-pointer rounded p-1 mx-1 text-white">
                                                    <i class="fas fa-lock text-red-500 mx-2"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            No tickets found
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        <?php if($total_pages > 1): ?>
                            <div class="mt-4 flex justify-center items-center">
                                <?php if($page > 1): ?>
                                    <a href="?STATUS=<?php echo $STATUS; ?>&search=<?php echo urlencode($search); ?>&page=<?php echo $page - 1; ?>" 
                                       class="px-4 py-2 bg-blue-500 text-white rounded-l hover:bg-blue-600">
                                        Previous
                                    </a>
                                <?php endif; ?>

                                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                    <a href="?STATUS=<?php echo $STATUS; ?>&search=<?php echo urlencode($search); ?>&page=<?php echo $i; ?>" 
                                       class="px-4 py-2 border-t border-b <?php echo $i === $page ? 'bg-blue-500 text-white' : 'bg-gray-200 hover:bg-gray-300' ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>

                                <?php if($page < $total_pages): ?>
                                    <a href="?STATUS=<?php echo $STATUS; ?>&search=<?php echo urlencode($search); ?>&page=<?php echo $page + 1; ?>" 
                                       class="px-4 py-2 bg-blue-500 text-white rounded-r hover:bg-blue-600">
                                        Next
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include('footer.php'); ?>