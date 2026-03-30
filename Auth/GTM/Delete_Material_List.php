<?php
$CURRENT =$_GET['CURRENT'];
$STATUS =$_GET['STATUS'];
$ID =$_GET['ID'];
include('../../db.php');
include('../../session.php');
$update_delete = mysqli_query($conn,"DELETE FROM laboratory_equipments WHERE  equipments_id =$ID");

 header('location:Robotics_Material_List');


?>