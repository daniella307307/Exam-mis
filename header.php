<?php
 include('db.php');
 
// Determine the correct base path for assets
$base_path = '';
if (basename(dirname(__FILE__)) !== 'Exam-mis') {
    // We're in a subdirectory like /exams or /Auth/SF
    $depth = substr_count(str_replace('\\', '/', __FILE__), '/') - substr_count(str_replace('\\', '/', dirname($_SERVER['SCRIPT_FILENAME'])), '/');
    if (basename(dirname(__FILE__)) === 'exams') {
        $base_path = '../';
    } elseif (strpos(__FILE__, '/Auth/') !== false) {
        $base_path = '../../';
    }
}
?>
 <!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="keywords" content="tailwind,tailwindcss,tailwind css,css,starter template,free template,admin templates, admin template, admin dashboard, free tailwind templates, tailwind example">
    <!-- Css -->
    <link rel="stylesheet" href="<?php echo $base_path; ?>dist/styles.css">
    <link rel="stylesheet" href="<?php echo $base_path; ?>dist/all.css">
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,400i,600,600i,700,700i" rel="stylesheet">
    <title>Dashboard | BLIS LMS</title>
</head>

<body>
<!--Container -->
<div class="mx-auto bg-grey-400">
    <!--Screen-->
    <div class="min-h-screen flex flex-col">
        <!--Header Section Starts Here-->
        <header class="bg-nav">
            <div class="flex justify-between">
                <div class="p-1 mx-3 inline-flex items-center">
                    <i class="fas fa-bars pr-2 text-white" onclick="sidebarToggle()"></i>
                    <h1 class="text-white p-2">BLIS LMS</h1>
                </div>
                
            </div>
        </header>