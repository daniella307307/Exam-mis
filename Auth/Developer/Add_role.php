<?php
ob_start(); 
include('header.php');

if (isset($_GET['ID'])) {
    $ID = intval($_GET['ID']); // Prevent SQL injection
}

$details_user = mysqli_fetch_array(mysqli_query($conn, "
    SELECT * FROM users  
    LEFT JOIN user_permission ON users.access_level = user_permission.permissio_id
    LEFT JOIN schools ON users.school_ref = schools.school_id 
    WHERE user_id = $ID
"));

$current_pass = $details_user['password'];
?>

<!-- /Header -->
<div class="flex flex-1">
    <!-- Sidebar -->
    <?php include('dynamic_side_bar.php'); ?>
    <!-- /Sidebar -->

    <!-- Main -->
    <body class="h-screen font-sans login bg-cover">
    <div class="container mx-auto h-full flex flex-1 justify-center items-center">
        <div class="w-full max-w-lg">
            <div class="leading-loose">
                <form action="" method="POST" class="max-w-xl m-4 p-10 bg-white rounded shadow-xl">
                    <p class="text-gray-800 font-medium">
                        Add Role to <strong><?php echo $details_user['firstname'] . " " . $details_user['lastname']; ?></strong>
                    </p>

                    <div class="flex flex-wrap -mx-3 mb-2">
                        <div class="w-full px-3 mb-6">
                            <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="grid-state">
                                User Role
                            </label>
                            <div class="relative">
                                <select name="access_level"
                                    class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey"
                                    id="grid-state">
                                    <?php
                                    $select_role = mysqli_query($conn, "SELECT * FROM user_permission WHERE per_status = 'Active'");
                                    while ($find_role = mysqli_fetch_array($select_role)) {
                                        echo '<option value="' . $find_role['permissio_id'] . '">' . $find_role['permission'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <?php
                    if (isset($_POST['Update'])) {
                        $access_level = $_POST['access_level'];  
                        $select_num = mysqli_num_rows(mysqli_query($conn, "
                            SELECT * FROM active_user_permission 
                            WHERE Active_user_ref = '$ID' AND active_permission = '$access_level'
                        "));

                        if ($select_num > 0) {
                            echo '<div class="bg-red-300 mb-2 border border-red-300 text-red-dark px-4 py-3 rounded relative" role="alert">
                                <strong class="font-bold">Duplicate Error</strong>
                                <span class="block sm:inline">This Role has already been assigned to ' . $details_user['firstname'] . " " . $details_user['lastname'] . '. Try a different role.</span>
                            </div>';
                        } else {
                            $find_reset = mysqli_query($conn, "
                                UPDATE active_user_permission 
                                SET permission_status = '' 
                                WHERE Active_user_ref = '$ID' AND active_permission != '$access_level'
                            ");

                            $insert = mysqli_query($conn, "
                                INSERT INTO active_user_permission (active_permission_id, active_permission, Active_user_ref, permission_status) 
                                VALUES (NULL, '$access_level', '$ID', 'Active')
                            ");

                            $update = mysqli_query($conn, "
                                UPDATE users SET access_level = '$access_level' WHERE user_id = $ID
                            ");

                            if ($insert && $update && $find_reset) {
                                header('Location: Add_remove_Users_access?ID=' . $ID);
                                exit();
                            } else {
                                echo '<div class="bg-red-300 mb-2 border border-red-300 text-red-dark px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">Oops!</strong>
                                    <span class="block sm:inline">Something went wrong. Try again!</span>
                                </div>';
                            }
                        }
                    }
                    ?>

                    <div class="mt-4">
                        <center>
                            <button type="submit" name="Update" class="px-4 py-1 text-white font-light tracking-wider bg-green-500 rounded">
                                Update User Details
                            </button>
                        </center>
                    </div>
                </form>
            </div>
        </div>
    </div>
    </body>
</div>
</html>
