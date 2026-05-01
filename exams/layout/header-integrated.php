<?php
// Integrated Header - Works with existing BLIS LMS session
// This header bridges the new dashboard system with the existing LMS

// Check if session is already started (from LMS)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security check - user must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit;
}

// Include database if not already included
if (!isset($conn)) {
    require_once(__DIR__ . '/../../db.php');
}

// Get user data from existing session
$session_id = $_SESSION['user_id'];

// Query user info with role
$user_sql = "SELECT u.*, up.permission, s.school_name, s.school_id
    FROM users u
    LEFT JOIN user_permission up ON u.access_level = up.permissio_id
    LEFT JOIN schools s ON u.school_ref = s.school_id
    WHERE u.user_id = '$session_id'
    LIMIT 1";

$user_result = mysqli_query($conn, $user_sql);

if (!$user_result || mysqli_num_rows($user_result) == 0) {
    // User session invalid
    session_destroy();
    header("Location: ../../index.php");
    exit;
}

$user_data = mysqli_fetch_array($user_result);

// Extract user information
$user_id = $user_data['user_id'];
$user_firstname = $user_data['firstname'] ?? 'User';
$user_lastname = $user_data['lastname'] ?? '';
$user_fullname = trim($user_firstname . ' ' . $user_lastname);
$user_email = $user_data['user_email'] ?? 'user@example.com';
$user_image = $user_data['user_image'] ?? 'images/default-user.png';
$user_permission = strtolower($user_data['permission'] ?? 'student');
$school_name = $user_data['school_name'] ?? 'School';
$school_id = $user_data['school_id'] ?? 0;

// Determine user role for dashboard
if (strpos($user_permission, 'admin') !== false) {
    $user_role = 'admin';
    $role_display = 'Administrator';
    $role_color = '#fd7e14';
    $role_icon = 'fa-user-shield';
} elseif (strpos($user_permission, 'teacher') !== false || strpos($user_permission, 'facilitator') !== false) {
    $user_role = 'teacher';
    $role_display = 'Teacher';
    $role_color = '#28a745';
    $role_icon = 'fa-chalkboard-user';
} else {
    $user_role = 'student';
    $role_display = 'Student';
    $role_color = '#667eea';
    $role_icon = 'fa-graduation-cap';
}

// Set current page
$current_page = basename($_SERVER['PHP_SELF']);
$current_path = basename(dirname($_SERVER['PHP_SELF']));

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo $role_display; ?> Dashboard - BLIS LMS Exams</title>
    
    <!-- Link to existing LMS styles -->
    <link rel="stylesheet" href="../../dist/styles.css">
    <link rel="stylesheet" href="../../dist/all.css">
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,400i,600,600i,700,700i" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Dashboard styles -->
    <style>
        :root {
            --primary: <?php echo $role_color; ?>;
            --text-dark: #1a1a1a;
            --text-light: #6b7280;
            --bg-light: #f9fafb;
            --border: #e5e7eb;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --radius: 8px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Source Sans Pro', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-dark);
        }

        .dashboard-wrapper {
            display: flex;
            min-height: 100vh;
            flex-direction: column;
        }

        /* HEADER */
        .dashboard-header {
            background: white;
            border-bottom: 1px solid var(--border);
            padding: 1rem 2rem;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1400px;
            margin: 0 auto;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .header-title {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .header-title h1 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-dark);
        }

        .header-title .badge {
            background: var(--primary);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .school-name {
            color: var(--text-light);
            font-size: 0.9rem;
            margin-top: 0.25rem;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.1rem;
            overflow: hidden;
            border: 2px solid var(--border);
        }

        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .user-details h3 {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text-dark);
        }

        .user-details p {
            font-size: 0.8rem;
            color: var(--text-light);
        }

        .logout-btn {
            background: var(--danger);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: var(--radius);
            cursor: pointer;
            font-size: 0.9rem;
            transition: background 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logout-btn:hover {
            background: #dc2626;
            text-decoration: none;
        }

        /* NAVIGATION TABS */
        .dashboard-tabs {
            background: white;
            border-bottom: 2px solid var(--border);
            padding: 0 2rem;
        }

        .tabs-content {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            gap: 0;
        }

        .tab-link {
            padding: 1rem 1.5rem;
            border: none;
            background: none;
            color: var(--text-light);
            cursor: pointer;
            font-size: 0.95rem;
            font-weight: 500;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .tab-link:hover {
            color: var(--primary);
            background: var(--bg-light);
        }

        .tab-link.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }

        /* MAIN CONTENT */
        .dashboard-main {
            flex: 1;
            padding: 2rem;
            max-width: 1400px;
            width: 100%;
            margin: 0 auto;
        }

        .dashboard-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: var(--radius);
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border-left: 4px solid var(--primary);
        }

        .stat-label {
            color: var(--text-light);
            font-size: 0.85rem;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
        }

        .stat-change {
            font-size: 0.8rem;
            color: var(--text-light);
            margin-top: 0.5rem;
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 1rem;
            }

            .header-right {
                width: 100%;
                justify-content: space-between;
            }

            .dashboard-tabs {
                padding: 0;
                overflow-x: auto;
            }

            .tabs-content {
                padding: 0 1rem;
            }

            .dashboard-main {
                padding: 1rem;
            }

            .dashboard-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
    <link rel="stylesheet" href="/Exam-mis/exams/assets/exam-theme.css">
</head>
<body class="exam-dark">

<div class="dashboard-wrapper">
    
    <!-- HEADER -->
    <header class="dashboard-header">
        <div class="header-content">
            <div class="header-left">
                <div class="header-title">
                    <i class="fas fa-graduation-cap" style="color: var(--primary); font-size: 1.5rem;"></i>
                    <div>
                        <h1>BLIS LMS - Exam Dashboard</h1>
                        <p class="school-name"><?php echo $school_name; ?></p>
                    </div>
                </div>
            </div>
            
            <div class="header-right">
                <div class="user-info">
                    <div class="user-avatar">
                        <?php 
                        if (!empty($user_image) && file_exists('../../Auth/' . $user_image)) {
                            echo '<img src="../../Auth/' . $user_image . '" alt="' . $user_fullname . '">';
                        } else {
                            echo substr($user_firstname, 0, 1);
                        }
                        ?>
                    </div>
                    <div class="user-details">
                        <h3><?php echo $user_fullname; ?></h3>
                        <p>
                            <i class="fas <?php echo $role_icon; ?>" style="color: var(--primary);"></i>
                            <?php echo $role_display; ?>
                        </p>
                    </div>
                </div>
                
                <a href="../../Auth/SF/Logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </header>

    <!-- NAVIGATION TABS -->
    <nav class="dashboard-tabs">
        <div class="tabs-content">
            <?php
            // Show navigation based on role
            if ($user_role === 'student') {
                ?>
                <a href="../join_exam.php" class="tab-link <?php echo ($current_page === 'join_exam.php') ? 'active' : ''; ?>">
                    <i class="fas fa-key"></i> Join Exam
                </a>
                <?php
            } elseif ($user_role === 'teacher') {
                ?>
                <a href="../teacher/dashboard.php" class="tab-link <?php echo ($current_path === 'teacher' && $current_page === 'dashboard.php') ? 'active' : ''; ?>">
                    <i class="fas fa-chalkboard"></i> My Classes
                </a>
                <a href="../teacher/class_report.php" class="tab-link <?php echo ($current_page === 'class_report.php') ? 'active' : ''; ?>">
                    <i class="fas fa-chart-bar"></i> Reports
                </a>
                <a href="../teacher/student_record.php" class="tab-link <?php echo ($current_page === 'student_record.php') ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i> Student Records
                </a>
                <a href="../exams_library.php" class="tab-link <?php echo ($current_page === 'exams_library.php') ? 'active' : ''; ?>">
                    <i class="fas fa-book"></i> Exam Library
                </a>
                <?php
            } elseif ($user_role === 'admin') {
                ?>
                <a href="../admin/records.php" class="tab-link <?php echo ($current_path === 'admin' && $current_page === 'records.php') ? 'active' : ''; ?>">
                    <i class="fas fa-database"></i> All Records
                </a>
                <a href="../exams_library.php" class="tab-link <?php echo ($current_page === 'exams_library.php') ? 'active' : ''; ?>">
                    <i class="fas fa-book"></i> Exam Library
                </a>
                <a href="../api/get_stats.php" class="tab-link <?php echo ($current_page === 'get_stats.php') ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i> System Stats
                </a>
                <?php
            }
            ?>
            <a href="../../Auth/SF/Auth_welcome.php" class="tab-link">
                <i class="fas fa-arrow-left"></i> Back to LMS
            </a>
        </div>
    </nav>

    <!-- MAIN CONTENT AREA -->
    <main class="dashboard-main">
