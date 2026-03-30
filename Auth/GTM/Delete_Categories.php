<?php
$CURRENT =$_GET['CURRENT'];
$STATUS =$_GET['STATUS'];
$ID =$_GET['ID'];
include('../../db.php');
include('../../session.php');
$update_delete = mysqli_query($conn,"DELETE FROM Equipment_categories WHERE category_id =$ID");
header('location:Robotics_Categories_List');


?>