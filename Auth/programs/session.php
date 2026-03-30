<?php
include('../../db.php');
$this_year = DATE("Y");
$this_date = DATE("Y-m-d");
 

session_start();
$directory = __DIR__;
$current_folder = basename($directory);

if (!isset($_SESSION['school_id'])) {
    header("Location:programs");
    exit;
}
	
else{
	 $School =$_SESSION['school_id'];
    $school_name = $_SESSION['school_name'] ;
 $school_details = mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM schools
LEFT JOIN  countries ON schools.country_ref = countries.id
LEFT JOIN  regions_table ON schools.school_region = regions_table.region_id WHERE school_id='$School'"))  ; 

    
 	
}
 
?>