<?php
$CURRENT =$_GET['CURRENT'];
$STATUS =$_GET['STATUS'];
$ID =$_GET['ID'];
$CATEGORY = $_GET['CATEGORY'];
include('../../db.php');
include('../../session.php');
$update_delete = mysqli_query($conn,"DELETE FROM Equipment_sub_categories WHERE  subcategory_id =$ID");
header('location:Robotics_Sub_Categories_List?CATEGORY='.$CATEGORY.'');


?>

Robotics_Sub_Categories_List?CATEGORY=1&STATUS=Inactive