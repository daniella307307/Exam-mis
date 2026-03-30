<?php
include('session.php');
?>
 <!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="keywords" content="tailwind,tailwindcss,tailwind css,css,starter template,free template,admin templates, admin template, admin dashboard, free tailwind templates, tailwind example">
    <!-- Css -->
    <link rel="stylesheet" href="../../dist/styles.css">
    <link rel="stylesheet" href="../../dist/all.css">
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
                    <h1 class="text-white p-2">BLIS LMS    | &nbsp School Name: <?php echo  $school_name;?></h1>
                </div>
                <div class="p-1 flex flex-row items-center">
                    <a href="User_profile" class="text-white p-2 mr-2 no-underline hidden md:block lg:block"><?php echo  $user_data['permission']; ?></a>


                    <img onclick="profileToggle()" class="inline-block h-8 w-8 rounded-full" src="../<?php echo$user_data['user_image'];?>" alt="">
                    <a href="Roles_per_User"   class="text-white p-2 no-underline hidden md:block lg:block"><?php echo  $user_data['firstname']."".$user_data['lastname']; ?></a>
                    
                </div>   
            </div>
        </header>
        
 
       
        
        
        
        
        
        
        
        
        
        
        