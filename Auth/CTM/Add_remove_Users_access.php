<?php 
include('header.php'); 

// Secure the ID from SQL injection
$ID = mysqli_real_escape_string($conn, $_GET['ID']);

// Fetch user roles
$select_user_role = mysqli_query($conn, "SELECT * FROM users WHERE user_id = '$ID'");
$user_roles = mysqli_num_rows($select_user_role);


?>

<!--/Header-->

<div class="flex flex-1">
    <!--Sidebar-->
    <?php include('dynamic_side_bar.php'); ?>
    <!--/Sidebar-->

    <!--Main-->
    <main class="bg-white-500 flex-1 p-3 overflow-hidden">
        <div class="flex flex-col">

            <!-- Card Section -->
            <div class="flex flex-1 flex-col md:flex-row lg:flex-row mx-2"></div>

            <div class="flex flex-1 flex-col md:flex-row lg:flex-row mx-2">
                <div class="mb-2 border-solid border-gray-300 rounded border shadow-sm w-full">
                    <div class="bg-gray-200 px-2 py-3 border-solid border-gray-200 border-b">
                        <?php
                        


                        if($user_roles>0){
                         $user_details_data = mysqli_fetch_array($select_user_role); 
                         ?>  <big><strong><?php echo $user_details_data['firstname'] . "&nbsp;" . $user_details_data['lastname']; ?> </strong></big> &nbsp;&nbsp;<strong> Access Levels</strong>&nbsp;<?php
                            
                        }
                        else{
                           ?>  <big><strong>this ID:: <?php echo $ID; ?> Don't have  coresponding User I the system </strong></big> Access Levels&nbsp;<?php   
                        }
                        
                        
                        ?>
                       

                        <?php if ($user_roles >= $setting_maxrole_no): ?>
                            <!-- Max roles reached, no Add New button -->
                        <?php else: ?>
                            <a href="Add_role?ID=<?php echo $ID; ?>" class="bg-red-500 cursor-pointer rounded p-1 mx-1 text-white">
                                <i class="fas fa-plus"></i> Add New Access
                            </a>
                        <?php endif; ?>
                    </div>

                    <div class="p-3">
                        <table class="table-responsive w-full rounded">
                            <thead>
                                <tr>
                                    <th class="border w-1/12 px-4 py-2">ID</th>
                                    <th class="border w-1/8 px-4 py-2">Role</th> 
                                    <th class="border w-1/7 px-4 py-2">Status</th>
                                    <th class="border w-1/7 px-4 py-2">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $select_users = mysqli_query($conn, "SELECT * FROM active_user_permission  
                                    LEFT JOIN user_permission ON active_user_permission.active_permission = user_permission.permissio_id 
                                    LEFT JOIN users ON active_user_permission.Active_user_ref = users.user_id 
                                    WHERE Active_user_ref = '$ID'");
                                
                                while ($users_roles_detail = mysqli_fetch_array($select_users)):
                                    $active_permission = $users_roles_detail['active_permission'];
                                ?>
                                <tr>
                                    <td class="border px-4 py-2"><?php echo $users_roles_detail['permissio_id']; ?></td>
                                    <td class="border px-4 py-2"><?php echo $users_roles_detail['permission']; ?></td>
                                    <td class="border px-4 py-2">
                                        <?php if ($users_roles_detail['permission_status'] == "Active"): ?>
                                            <button class='bg-green-500 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded'>
                                                <?php echo $users_roles_detail['permission_status']; ?>
                                            </button>
                                        <?php else: ?>
                                            <button class='bg-red-500 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'>
                                                Inactive
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                    <td class="border px-4 py-2">
                                        <?php if ($users_roles_detail['permission_status'] == "Active" || $users_roles_detail['active_permission'] > $uper_level): ?>
                                            <span class="bg-green-500 cursor-pointer rounded p-1 mx-1 text-white inline-block">
                                                <i class="fas fa-minus"></i> You Can't Remove
                                            </span>
                                        <?php else: ?>
                                            <a href="Delete_role?USER=<?php echo $ID; ?>&ID=<?php echo $users_roles_detail['active_permission_id']; ?>" class="bg-red-500 cursor-pointer rounded p-1 mx-1 text-white">
                                                <i class="fas fa-minus"></i> Remove
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!--/Grid Form-->
        </div>
    </main>
    <!--/Main-->
</div>

<!--Footer-->
<?php include('footer.php'); ?>
<!--/Footer-->

<script src="../../main.js"></script>

</body>
</html>
