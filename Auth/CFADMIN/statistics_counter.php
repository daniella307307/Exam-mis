<?php
  // Query to count regions
$regionsResult = mysqli_query($conn, "SELECT COUNT(*) AS regions FROM regions_table");
$regionsRow = mysqli_fetch_assoc($regionsResult);
$regions = $regionsRow['regions'];

// Query to count countries with 'Active' status
$countriesResult = mysqli_query($conn, "SELECT COUNT(*) AS countries FROM countries WHERE Country_status='Active'");
$countriesRow = mysqli_fetch_assoc($countriesResult);
$countries = $countriesRow['countries'];
//Query to count Schools with 'Active' status
$schoolsResult = mysqli_query($conn, "SELECT COUNT(*)  AS schools FROM schools WHERE school_status='Active'");
$schoolsRow = mysqli_fetch_assoc($schoolsResult);
$schools = $schoolsRow['schools'];
//Query to count Students with 'Active' status
$StudentsResult = mysqli_query($conn, "SELECT COUNT(*) AS Students  FROM student_list WHERE student_status='Active'");
$StudentsRow = mysqli_fetch_assoc($StudentsResult);
$Students = $StudentsRow['Students'];
?>