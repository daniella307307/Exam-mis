<?php
ob_start(); 
include('header.php');

// Validate and sanitize input parameters
$CURRENT = isset($_GET['CURRENT']) ? $_GET['CURRENT'] : '';
$STATUS = isset($_GET['STATUS']) ? $_GET['STATUS'] : '';
$ID = isset($_GET['ID']) ? intval($_GET['ID']) : 0;

// Check if ID is valid
if($ID <= 0) {
    die("Invalid item ID");
}

$select_item = mysqli_query($conn,"SELECT * FROM laboratory_equipments
LEFT JOIN Equipment_categories ON laboratory_equipments.equipments_category = Equipment_categories.category_id
LEFT JOIN Equipment_sub_categories ON laboratory_equipments.equipments_subcategory = Equipment_sub_categories.subcategory_id WHERE equipments_id='$ID'");

if(!$select_item) {
    die("Database error: " . mysqli_error($conn));
}

$Find_details = mysqli_fetch_array($select_item);

// Check if item exists
if(!$Find_details) {
    die("Item not found");
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
            <form action="" method="POST" class="max-w-xl m-4 p-10 bg-white rounded shadow-xl" enctype="multipart/form-data">
                <p class="text-gray-800 font-medium">Update Robotics Materials Name &nbsp;<strong> </strong> </p>
       		
				<div class="mb-3">
				   <label class="block text-sm text-gray-600" for="equipments_name">Material name</label>
				   <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="equipments_name" name="equipments_name" value="<?php echo htmlspecialchars($Find_details['equipments_name']); ?>" type="text" placeholder="Material name" aria-label="Name" required>
                </div>
                	
                <div class="mb-3">
				   <label class="block text-sm text-gray-600" for="equipments_ModelNo">Model No</label>
				   <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="equipments_ModelNo" name="equipments_ModelNo" value="<?php echo htmlspecialchars($Find_details['equipments_ModelNo']); ?>" type="text" placeholder="Model No" aria-label="Name" required>
                </div>
                
                <div class="mb-3">
				   <label class="block text-sm text-gray-600" for="equipments_description">Description</label>
				   <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="equipments_description" name="equipments_description" value="<?php echo htmlspecialchars($Find_details['equipments_description']); ?>" type="text" placeholder="Description" aria-label="Name" required>
                </div>
				     
                <div class="flex flex-wrap -mx-3 mb-2">
                    <div class="w-full md:w-1/1 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="equipments_category">
                          Category
                        </label>
                        <div class="relative">
                            <select name="equipments_category" required
                                class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey"
                                id="equipments_category">	
							  <option value="">Select Category</option> 
							  <?php
				 			  $select_cat = mysqli_query($conn,"SELECT * FROM Equipment_categories WHERE category_status='Active'");
							  while($categories = mysqli_fetch_array($select_cat)){
								  $selected = ($categories['category_id'] == $Find_details['equipments_category']) ? 'selected' : '';
							  ?>
							  <option value="<?php echo $categories['category_id']; ?>" <?php echo $selected; ?>>
								  <?php echo htmlspecialchars($categories['category_name']); ?>
							  </option>
							  <?php      
							  }
							  ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="w-full md:w-1/1 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="equipments_subcategory">
                          Sub category
                        </label>
                        <div class="relative">
                            <select name="equipments_subcategory" required
                                class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey"
                                id="equipments_subcategory">
								<option value="">Select Sub Category</option>
								<?php
								// Pre-load subcategories for the current category
								$current_category_id = $Find_details['equipments_category'];
								$subcat_query = mysqli_query($conn, "SELECT * FROM Equipment_sub_categories WHERE category_ref = '$current_category_id' AND subcategory_status = 'Active'");
								while($subcat = mysqli_fetch_array($subcat_query)) {
									$selected = ($subcat['subcategory_id'] == $Find_details['equipments_subcategory']) ? 'selected' : '';
								?>
								<option value="<?php echo $subcat['subcategory_id']; ?>" <?php echo $selected; ?>>
									<?php echo htmlspecialchars($subcat['subcategory_name']); ?>
								</option>
								<?php } ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="w-full md:w-1/1 px-3 mb-6 md:mb-0">
                        <label class="block text-sm text-gray-600" for="equipments_picture">Image</label>
						<?php if(!empty($Find_details['equipments_picture'])): ?>
						<div class="mb-2">
							<label class="block text-sm text-gray-600">Current Image:</label>
							<img src="uploads/<?php echo htmlspecialchars($Find_details['equipments_picture']); ?>" alt="Current image" class="h-20 w-20 object-cover rounded">
						</div>
						<?php endif; ?>
				        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="equipments_picture" name="equipments_picture" type="file" aria-label="Image">
						<small class="text-gray-500">Leave empty to keep current image</small>
                    </div>
                    
                    <div class="w-full md:w-1/1 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="equipments_status">
                          Status
                        </label>
                        <div class="relative">
                            <select name="equipments_status" required
                                class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey"
                                id="equipments_status">
								<option value="Active" <?php echo ($Find_details['equipments_status'] == 'Active') ? 'selected' : ''; ?>>Active</option>
								<option value="Inactive" <?php echo ($Find_details['equipments_status'] == 'Inactive') ? 'selected' : ''; ?>>Inactive</option>  
                            </select>
                        </div>
                    </div>
                </div>

<?php
if(isset($_POST['Update'])){      
    $equipments_name = mysqli_real_escape_string($conn,$_POST['equipments_name']);
    $equipments_ModelNo = mysqli_real_escape_string($conn,$_POST['equipments_ModelNo']);
    $equipments_description = mysqli_real_escape_string($conn,$_POST['equipments_description']);
    $equipments_category = mysqli_real_escape_string($conn,$_POST['equipments_category']);
    $equipments_subcategory = mysqli_real_escape_string($conn,$_POST['equipments_subcategory']);
    $equipments_status = mysqli_real_escape_string($conn,$_POST['equipments_status']);

    // File upload handling
    $equipments_picture = $Find_details['equipments_picture']; // Keep current picture by default
    
    if(isset($_FILES['equipments_picture']) && $_FILES['equipments_picture']['error'] == 0) {
        $target_dir = "uploads/";
        if(!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_name = time() . '_' . basename($_FILES["equipments_picture"]["name"]);
        $target_file = $target_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
        
        // Check if image file is actual image
        $check = getimagesize($_FILES["equipments_picture"]["tmp_name"]);
        if($check !== false) {
            if(move_uploaded_file($_FILES["equipments_picture"]["tmp_name"], $target_file)) {
                $equipments_picture = $file_name;
                // Optional: Delete old image file
                if(!empty($Find_details['equipments_picture']) && file_exists($target_dir . $Find_details['equipments_picture'])) {
                    unlink($target_dir . $Find_details['equipments_picture']);
                }
            }
        }
    }

    // Check for duplicates excluding current item
    $select = mysqli_num_rows(mysqli_query($conn,"SELECT * FROM laboratory_equipments WHERE equipments_id != '$ID' AND (equipments_name = '$equipments_name' OR equipments_ModelNo = '$equipments_ModelNo')")); 
    
    if($select > 0){
        echo '<div class="bg-red-300 mb-2 border border-red-300 text-red-dark px-4 py-3 rounded relative" role="alert">
            <strong class="font-bold">Oops!</strong>
            <span class="block sm:inline">This Robotic Item: <strong class="font-bold">'.htmlspecialchars($equipments_name).'</strong> or Model No: <strong>'.htmlspecialchars($equipments_ModelNo).'</strong> already exists in the system!!!</span>
        </div>';	
    } else {
        // Build update query
        $update_query = "UPDATE laboratory_equipments SET 
            equipments_name = '$equipments_name', 
            equipments_ModelNo = '$equipments_ModelNo', 
            equipments_description = '$equipments_description', 
            equipments_category = '$equipments_category', 
            equipments_subcategory = '$equipments_subcategory', 
            equipments_status = '$equipments_status'";
        
        // Only update picture if a new one was uploaded
        if(!empty($equipments_picture) && $equipments_picture != $Find_details['equipments_picture']) {
            $update_query .= ", equipments_picture = '$equipments_picture'";
        }
        
        $update_query .= " WHERE equipments_id = '$ID'";
        
        $update_data = mysqli_query($conn, $update_query);
        
        if($update_data){
            echo '<div class="bg-green-300 mb-2 border border-green-300 text-green-dark px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Success!</strong>
                <span class="block sm:inline">Item updated successfully! Redirecting...</span>
            </div>';
            echo "<script>setTimeout(function(){ window.location.href = 'Robotics_Material_List'; }, 2000);</script>";
        } else {
            echo '<div class="bg-red-300 mb-2 border border-red-300 text-red-dark px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Oops!</strong>
                <span class="block sm:inline">Something went wrong. Please try again!!! Error: ' . mysqli_error($conn) . '</span>
            </div>'; 
        }	
    } 
}
?>

                <div class="mt-4">
                    <center>
                        <button type="submit" name="Update" class="px-4 py-1 text-white font-light tracking-wider bg-green-500 rounded hover:bg-green-600">
                            Update Material
                        </button>
                        <a href="Robotics_Material_List" class="px-4 py-1 text-white font-light tracking-wider bg-gray-500 rounded hover:bg-gray-600 ml-2">
                            Cancel
                        </a>
                    </center>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript for Dependent Dropdown -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // When category changes
    $('#equipments_category').change(function() {
        var category_id = $(this).val();
        var current_subcategory = '<?php echo $Find_details["equipments_subcategory"]; ?>';
        
        // Clear subcategory dropdown
        $('#equipments_subcategory').html('<option value="">Select Sub Category</option>');
        
        if(category_id != '') {
            // AJAX request to get subcategories
            $.ajax({
                url: 'get_subcategories.php',
                type: 'POST',
                data: {category_id: category_id},
                dataType: 'json',
                success: function(response) {
                    if(response.length > 0) {
                        $.each(response, function(index, subcategory) {
                            var selected = (subcategory.subcategory_id == current_subcategory) ? 'selected' : '';
                            $('#equipments_subcategory').append('<option value="'+subcategory.subcategory_id+'" '+selected+'>'+subcategory.subcategory_name+'</option>');
                        });
                    } else {
                        $('#equipments_subcategory').html('<option value="">No subcategories found</option>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading subcategories:', error);
                    $('#equipments_subcategory').html('<option value="">Error loading subcategories</option>');
                }
            });
        }
    });
});
</script>

</body>
</html>