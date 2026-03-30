<?php
ob_start(); 
include('header.php');
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
            <form  action ="" method= "POST" class="max-w-xl m-4 p-10 bg-white rounded shadow-xl" enctype="multipart/form-data">
                <p class="text-gray-800 font-medium">Add New Robotics Robotics Category &nbsp;<strong> </strong> </p>
       		
				<div class="">     
				   <label class="block text-sm text-gray-600" for="category_name">category name</label>
				   <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="category_name"  name="category_name"   type="text"  placeholder="Material name" aria-label="Name" required>
                </div>
                 
				     
                <div class="flex flex-wrap -mx-3 mb-2">
                    <div class="w-full md:w-1/1 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="equipments_category">
                          category status
                        </label>
                        <div class="relative">
                            <select name="category_status" required
                                class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey"
                                id="category_status">
							  <option value="">============ Select category status ========</option>
							  <option value="Active">Active</option>
							  <option value="Inactive">Inactive</option>
							  
                            </select>
                        </div>
                    </div>
                    
                     
                </div>

<?php
if(isset($_POST['Update'])){ 
    
    
    
    $category_name  = mysqli_real_escape_string($conn,$_POST['category_name']);
    $category_status = mysqli_real_escape_string($conn,$_POST['category_status']);
    
     

    $select = mysqli_num_rows(mysqli_query($conn,"SELECT * FROM Equipment_categories WHERE category_name='$category_name'")); 
    
    if($select > 0){
        ?><div class="bg-red-300 mb-2 border border-red-300 text-red-dark px-4 py-3 rounded relative" role="alert">
            <strong class="font-bold">Oops!</strong>
            <span class="block sm:inline">This Category: <strong class="font-bold"><?php echo $category_name;?></strong>   already exists in the system!!!</span>
        </div><?php 	
    } else {
        $Add_data = mysqli_query($conn,"INSERT INTO Equipment_categories (category_id,category_name,category_status) VALUES 
                                                                         (NULL, '$category_name', '$category_status')");
        if($Add_data){
            header('location:Robotics_Categories_List'); 
            ?><div class="bg-green-300 mb-2 border border-green-300 text-green-dark px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Success!</strong>
                <span class="block sm:inline">Category added successfully! Redirecting...</span>
            </div><?php 	
        } else {
            ?><div class="bg-red-300 mb-2 border border-red-300 text-red-dark px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Oops!</strong>
                <span class="block sm:inline">Something went wrong. Please try again!!!</span>
            </div><?php 
        }	
    } 
}
?>

                <div class="mt-4">
                    <center><button type="submit" name="Update" class="px-4 py-1 text-white font-light tracking-wider bg-green-500 rounded">Add New Category </button></center>
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
        
        // Clear subcategory dropdown
        $('#equipments_subcategory').html('<option value="">============= Select Sub Category ========</option>');
        
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
                            $('#equipments_subcategory').append('<option value="'+subcategory.subcategory_id+'">'+subcategory.subcategory_name+'</option>');
                        });
                    } else {
                        $('#equipments_subcategory').html('<option value="">No subcategories found</option>');
                    }
                },
                error: function() {
                    alert('Error loading subcategories');
                }
            });
        }
    });
});
</script>

</body>
</html>