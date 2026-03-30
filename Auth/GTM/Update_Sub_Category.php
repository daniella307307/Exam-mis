<?php
ob_start(); 
include('header.php');
$STATUS = $_GET['CURRENT'];
$ID = $_GET['ID'];
$find_catdetail = mysqli_query($conn,"SELECT * FROM Equipment_sub_categories
LEFT JOIN Equipment_categories ON Equipment_sub_categories.category_ref =Equipment_categories.category_id WHERE subcategory_id='$ID'");
$subcatdetails = mysqli_fetch_array($find_catdetail);
 $category_ref =$subcatdetails['category_ref'];



 

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
                <p class="text-gray-800 font-medium">Update Robotics Sub Category &nbsp;<strong> </strong> </p>
       		
				<div class="">     
				   <label class="block text-sm text-gray-600" for="subcategory_name">Sub category name</label>
				   <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="subcategory_name"  name="subcategory_name"  value="<?php echo $subcatdetails['subcategory_name'];?>"  type="text"  placeholder="Material name" aria-label="Name" required>
                </div> 
                 
				   <div class="flex flex-wrap -mx-3 mb-2">
                    
                    
                      <div class="w-full md:w-1/1 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="equipments_category">
                          Current category 
                        </label>
                        <div class="relative">
                            <select name="category_ref" required
                                class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey"
                                id="category_ref">
                                 <option value="<?php echo $subcatdetails['category_ref'];?>"> <?php echo $subcatdetails['category_name'];?></option>
							   	   
							  <?php
				 			  $select_cat = mysqli_query($conn,"SELECT * FROM Equipment_categories WHERE category_status='Active'");
				while($categories = mysqli_fetch_array($select_cat)){
				?><option value="<?php echo $categories['category_id'];?>"><?php echo $categories['category_name']?></option><?php      
				}
							  ?>
                            </select>
                        </div>
                    </div> 
                     
                     
                </div>    
                <div class="flex flex-wrap -mx-3 mb-2">
                    <div class="w-full md:w-1/1 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="equipments_category">
                          Sub category status
                        </label>
                        <div class="relative">
                            <select name="subcategory_status" required
                                class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey"
                                id="subcategory_status">
							  <option value="<?php echo $subcatdetails['subcategory_status'];?>"> <?php echo $subcatdetails['subcategory_status'];?></option>
							  <option value="Active">Active</option>
							  <option value="Inactive">Inactive</option>
							  
                            </select>
                        </div>
                    </div>
                    
                     
                </div>

<?php
if(isset($_POST['Update'])){ 
    
    
    
    $subcategory_name  = mysqli_real_escape_string($conn,$_POST['subcategory_name']);
    $subcategory_status = mysqli_real_escape_string($conn,$_POST['subcategory_status']);
    $category_ref = mysqli_real_escape_string($conn,$_POST['category_ref']);
     $subcategory_status =  mysqli_real_escape_string($conn,$_POST['subcategory_status']);

    $select = mysqli_num_rows(mysqli_query($conn,"SELECT * FROM Equipment_sub_categories WHERE subcategory_name='$subcategory_name' AND category_ref='$category_ref' AND  subcategory_id !='$ID'")); 
    
    if($select > 0){
        ?><div class="bg-red-300 mb-2 border border-red-300 text-red-dark px-4 py-3 rounded relative" role="alert">
            <strong class="font-bold">Oops!</strong>
            <span class="block sm:inline">This Sub Category: <strong class="font-bold"><?php echo $category_name;?></strong>   already exists in the system!!!</span>
        </div><?php 	
    } else {
        $Add_data = mysqli_query($conn,"UPDATE Equipment_sub_categories SET 
        subcategory_name = '$subcategory_name',
        category_ref = '$category_ref', 
        subcategory_status = '$subcategory_status' WHERE  subcategory_id =$ID");
        if($Add_data){
            header('location:Robotics_Sub_Categories_List?CATEGORY='.$category_ref.'&STATUS='.$subcategory_status.''); 
            ?><div class="bg-green-300 mb-2 border border-green-300 text-green-dark px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Success!</strong>
                <span class="block sm:inline">Sub Category Updated successfully! Redirecting...</span>
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
                    <center><button type="submit" name="Update" class="px-4 py-1 text-white font-light tracking-wider bg-green-500 rounded">Update Sub Category </button></center>
                </div>
            </form>
        </div>
    </div>
</div>

 
</body>
</html>