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
                <p class="text-gray-800 font-medium">Add New Robotics Materials Name &nbsp;<strong> </strong> </p>
       		
				<div class="">
				   <label class="block text-sm text-gray-600" for="equipments_name">Material name</label>
				   <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="equipments_name"  name="equipments_name"   type="text"  placeholder="Material name" aria-label="Name" required>
                </div>
                	
                <div class="">
				   <label class="block text-sm text-gray-600" for="equipments_ModelNo">Model No</label>
				   <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="equipments_ModelNo"  name="equipments_ModelNo"   type="text"  placeholder="Model No" aria-label="Name" required>
                </div>
                
                <div class="">
				   <label class="block text-sm text-gray-600" for="equipments_description">Description</label>
				   <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="equipments_description"  name="equipments_description"   type="text"  placeholder="Description" aria-label="Name" required>
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
							  <option value="">============ Select Category ========</option>	   
							  <?php
				 			  $select_cat = mysqli_query($conn,"SELECT * FROM Equipment_categories WHERE category_status='Active'");
				while($categories = mysqli_fetch_array($select_cat)){
				?><option value="<?php echo $categories['category_id'];?>"><?php echo $categories['category_name']?></option><?php      
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
								  <option value="">============= Select Sub Category ========</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="w-full md:w-1/1 px-3 mb-6 md:mb-0">
                        <label class="block text-sm text-gray-600" for="equipments_picture">Image</label>
				        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="equipments_picture"  name="equipments_picture"   type="file"  placeholder="Image" aria-label="Image">
                    </div>
                    
                    <div class="w-full md:w-1/1 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="equipments_status">
                          Status
                        </label>
                        <div class="relative">
                            <select name="equipments_status" required
                                class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey"
                                id="equipments_status">
								<option value="Active">Active</option>
								<option value="Inactive">Inactive</option>  
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
    $equipments_picture = "";
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
            }
        }
    }

    $select = mysqli_num_rows(mysqli_query($conn,"SELECT * FROM laboratory_equipments WHERE equipments_name='$equipments_name' OR equipments_ModelNo='$equipments_ModelNo'")); 
    
    if($select > 0){
        ?><div class="bg-red-300 mb-2 border border-red-300 text-red-dark px-4 py-3 rounded relative" role="alert">
            <strong class="font-bold">Oops!</strong>
            <span class="block sm:inline">This Robotic Item: <strong class="font-bold"><?php echo $equipments_name;?></strong> or Model No: <strong><?php echo $equipments_ModelNo;?></strong> already exists in the system!!!</span>
        </div><?php 	
    } else {
        $Add_data = mysqli_query($conn,"INSERT INTO laboratory_equipments (equipments_name, equipments_ModelNo, equipments_description, equipments_category, equipments_subcategory, equipments_picture, equipments_status) VALUES 
                                                                   ('$equipments_name', '$equipments_ModelNo', '$equipments_description', '$equipments_category', '$equipments_subcategory', '$equipments_picture', '$equipments_status')");
        if($Add_data){
            header('location:Robotics_Material_List'); 
            ?><div class="bg-green-300 mb-2 border border-green-300 text-green-dark px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Success!</strong>
                <span class="block sm:inline">Item added successfully! Redirecting...</span>
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
                    <center><button type="submit" name="Update" class="px-4 py-1 text-white font-light tracking-wider bg-green-500 rounded">Add New Material</button></center>
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