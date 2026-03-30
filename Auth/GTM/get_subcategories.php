<?php
// Start session and include database connection
include('../../db.php');

// Set header to return JSON
header('Content-Type: application/json');

// Check if category_id is posted
if(isset($_POST['category_id']) && !empty($_POST['category_id'])) {
    $category_id = mysqli_real_escape_string($conn, $_POST['category_id']);
    
    // Query to get subcategories based on category_ref
    $query = "SELECT * FROM Equipment_sub_categories WHERE category_ref = '$category_id' AND subcategory_status = 'Active'";
    $result = mysqli_query($conn, $query);
    
    if($result) {
        $subcategories = array();
        while($row = mysqli_fetch_assoc($result)) {
            $subcategories[] = array(
                'subcategory_id' => $row['subcategory_id'],
                'subcategory_name' => $row['subcategory_name']
            );
        }
        
        // Return JSON response
        echo json_encode($subcategories);
    } else {
        // Return error if query fails
        echo json_encode(array('error' => 'Query failed: ' . mysqli_error($conn)));
    }
} else {
    // Return empty array if no category_id
    echo json_encode(array());
}

// Close connection
mysqli_close($conn);
?>