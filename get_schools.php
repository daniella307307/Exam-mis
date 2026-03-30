<?php
include('db.php');

if(isset($_POST['country_id'])) {
    $countryId = intval($_POST['country_id']);
    $query = mysqli_query($conn, "SELECT * FROM schools 
                                WHERE country_ref = $countryId 
                                AND   chool_status='Active'");
    
    echo '<option value="">======== SELECT SCHOOL ========</option>';
    while($row = mysqli_fetch_array($query)) {
        echo '<option value="'.$row['school_id'].'">'.$row['school_name'].'</option>';
    }
}
?>