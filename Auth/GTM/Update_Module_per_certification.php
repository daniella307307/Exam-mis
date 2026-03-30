<?php
ob_start(); 
include('header.php');
$ID = $_GET['ID'];
$CERTIFICATE = $_GET['CERTIFICATE'];
$STATUS = $_GET['STATUS'];

// Fetch module details for editing
$module_details = mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM certification_courses WHERE course_id='$ID'"));
$course_code = $module_details['course_code'];
$course_name = $module_details['course_name'];
$course_french = $module_details['course_french'];
$course_status = $module_details['course_status'];

// Fetch certification details
$cert_details = mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM certifications WHERE certification_id='$CERTIFICATE'"));
$certification_name = $cert_details['certification_name'];
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
            <form action="" method="POST" class="max-w-xl m-4 p-10 bg-white rounded shadow-xl">
                <p class="text-gray-800 font-medium">Update Module<br> 
                    <strong><?php echo $certification_name;?> Certificate</strong>
                </p>
                <div class="mb-4">
                    <label class="block text-sm text-gray-600" for="course_code">Module Code</label>
                    <input class="w-full px-5 py-2 text-gray-700 bg-gray-100 rounded border border-gray-300" 
                           id="course_code" name="course_code" value="<?php echo htmlspecialchars($course_code); ?>" 
                           type="text" placeholder="Module Code" aria-label="Module Code" required>
                </div>
				
				<div class="mb-4">
                    <label class="block text-sm text-gray-600" for="course_name">Module Name (English)</label>
                    <input class="w-full px-5 py-2 text-gray-700 bg-gray-100 rounded border border-gray-300" 
                           id="course_name" name="course_name" value="<?php echo htmlspecialchars($course_name); ?>" 
                           type="text" placeholder="Module Name" aria-label="Module Name" required>
                </div>
				 
				<div class="mb-4">
                    <label class="block text-sm text-gray-600" for="course_french">Module Name (French)</label>
                    <input class="w-full px-5 py-2 text-gray-700 bg-gray-100 rounded border border-gray-300" 
                           id="course_french" name="course_french" value="<?php echo htmlspecialchars($course_french); ?>" 
                           type="text" placeholder="Module Name in French" aria-label="Module Name French" required>
                </div>
                
                <div class="mb-6">
                    <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-2" for="course_status">
                        Status
                    </label>
                    <div class="relative">
                        <select name="course_status"
                            class="block appearance-none w-full bg-gray-100 border border-gray-300 text-gray-700 py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                            id="course_status" required>
                            <option value="">Select Status</option>
                            <option value="Active" <?php echo ($course_status == 'Active') ? 'selected' : ''; ?>>Active</option>
                            <option value="Inactive" <?php echo ($course_status == 'Inactive') ? 'selected' : ''; ?>>Inactive</option>  
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/>
                            </svg>
                        </div>
                    </div>
                </div>

<?php
if(isset($_POST['Update'])){
    $course_code = mysqli_real_escape_string($conn, $_POST['course_code']);
    $course_name = mysqli_real_escape_string($conn, $_POST['course_name']);
    $course_french = mysqli_real_escape_string($conn, $_POST['course_french']);
    $course_status = mysqli_real_escape_string($conn, $_POST['course_status']);
    
    // Check if the course name or code already exists in OTHER modules (excluding current one)
    $check_sql = "SELECT * FROM certification_courses 
                  WHERE (course_name='$course_name' OR course_code='$course_code') 
                  AND course_id != '$ID'";
    $select = mysqli_num_rows(mysqli_query($conn, $check_sql));
    
    if($select > 0){
        ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline">Module with name <strong><?php echo $course_name; ?></strong> or code <strong><?php echo $course_code; ?></strong> already exists in the system.</span>
            <button type="button" class="absolute top-0 right-0 px-4 py-3" onclick="this.parentElement.style.display='none'">
                <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                    <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
                </svg>
            </button>
        </div>
        <?php 	
    } else {
        // Update the module
        $update_sql = "UPDATE certification_courses 
                      SET course_code = '$course_code', 
                          course_name = '$course_name', 
                          course_french = '$course_french', 
                          course_status = '$course_status' 
                      WHERE course_id = '$ID'";
        $update_data = mysqli_query($conn, $update_sql);
        
        if($update_data){
            ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Success!</strong>
                <span class="block sm:inline">Module updated successfully! Redirecting...</span>
            </div>
            <?php
            echo "<script>
                    setTimeout(function() {
                        window.location.href = 'Modules_per_Certification?CERTIFICATE=$CERTIFICATE&STATUS=$STATUS';
                    }, 2000);
                  </script>";
        } else {
            ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Error!</strong>
                <span class="block sm:inline">Something went wrong. Please try again.</span>
                <span class="block sm:inline">Error: <?php echo mysqli_error($conn); ?></span>
            </div>
            <?php 
        }	
    } 
}
?>

                <div class="mt-6 flex space-x-4">
                    <button type="submit" name="Update" 
                            class="px-6 py-2 text-white font-medium bg-blue-500 hover:bg-blue-600 rounded transition duration-200 flex-1">
                        Update Module Details
                    </button>
                    <a href="Modules_per_Certification?CERTIFICATE=<?php echo $CERTIFICATE; ?>&STATUS=<?php echo $STATUS; ?>" 
                       class="px-6 py-2 text-gray-700 font-medium bg-gray-200 hover:bg-gray-300 rounded transition duration-200 flex-1 text-center">
                        Cancel
                    </a>
                </div>
                
            </form>
        </div>
    </div>
</div>

</body>
</html>