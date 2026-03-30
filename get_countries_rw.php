
<?php
include('db.php');

if (isset($_POST['region_id'])) {
    $regionId = intval($_POST['region_id']);
    
    $query = mysqli_query($conn, "SELECT * FROM countries 
                                  WHERE Country_region = $regionId 
                                  AND Country_status = 'Active'
                                  AND Country_name='Rwanda'");

    echo '<option value="">======== SELECT COUNTRY ========</option>';
    
    while ($row = mysqli_fetch_array($query)) {
        echo '<option value="' . $row['id'] . '" data-phonecode="' . $row['Country_phonecode'] . '">'
             . $row['Country_name'] . '</option>';
    }
}
?>
