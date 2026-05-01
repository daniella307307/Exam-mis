<?php
// layout/header.php - Common navigation for all pages
// Detects current page and user role from session/URL

$current_page = basename($_SERVER['PHP_SELF']);
$base_url = '/Exam-mis/exams';

// Detect user role from session or URL parameter
$user_role = $_SESSION['user_role'] ?? $_GET['role'] ?? 'student';
if (!in_array($user_role, ['student', 'teacher', 'admin'])) {
    $user_role = 'student';
}

// Color scheme by role
$colors = [
    'student' => ['bg' => '#667eea', 'accent' => '#764ba2'],
    'teacher' => ['bg' => '#28a745', 'accent' => '#20c997'],
    'admin' => ['bg' => '#fd7e14', 'accent' => '#ff6b6b']
];

$color = $colors[$user_role];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Management System</title>
    <link rel="stylesheet" href="/Exam-mis/exams/assets/exam-theme.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --primary: <?= $color['bg'] ?>;
            --accent: <?= $color['accent'] ?>;
            --bg: transparent;
            --surface: rgba(255,255,255,.05);
            --text: #f1f5f9;
            --muted: #94a3b8;
            --border: rgba(168,85,247,.3);
            --radius: 16px;
        }

        body {
            font-family: 'Nunito', 'Segoe UI', sans-serif;
            color: var(--text);
        }

        /* NAVIGATION HEADER */
        nav.main-nav {
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            padding: 0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 24px;
            height: 70px;
        }

        .nav-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 24px;
            font-weight: 800;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
        }

        .nav-logo:hover {
            transform: scale(1.05);
        }

        .nav-logo-icon {
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.2);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .nav-links {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .nav-link {
            color: white;
            text-decoration: none;
            padding: 10px 18px;
            border-radius: 6px;
            transition: all 0.3s;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .nav-link:hover {
            background: rgba(255,255,255,0.2);
            transform: translateY(-2px);
        }

        .nav-link.active {
            background: rgba(255,255,255,0.3);
            border-bottom: 3px solid white;
        }

        .nav-divider {
            width: 1px;
            height: 30px;
            background: rgba(255,255,255,0.2);
            margin: 0 8px;
        }

        .nav-role {
            display: inline-block;
            background: rgba(255,255,255,0.15);
            color: white;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            margin-right: 12px;
        }

        /* BREADCRUMB */
        .breadcrumb {
            background: rgba(255,255,255,.04);
            padding: 12px 24px;
            border-bottom: 1px solid var(--border);
            font-size: 13px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .breadcrumb a {
            color: var(--primary);
            text-decoration: none;
            margin: 0 4px;
            transition: color 0.3s;
        }

        .breadcrumb a:hover {
            color: var(--accent);
            text-decoration: underline;
        }

        .breadcrumb span {
            color: var(--muted);
            margin: 0 4px;
        }

        /* MAIN CONTENT */
        .main-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px 24px;
            min-height: calc(100vh - 70px - 200px);
        }

        /* Mobile Menu Toggle */
        .menu-toggle {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .nav-links {
                display: none;
                position: absolute;
                top: 70px;
                left: 0;
                right: 0;
                background: var(--primary);
                flex-direction: column;
                padding: 12px;
            }

            .nav-links.active {
                display: flex;
            }

            .menu-toggle {
                display: block;
            }

            .nav-container {
                padding: 0 16px;
            }

            .main-content {
                padding: 20px 16px;
            }
        }
    </style>
</head>
<body class="exam-dark">

<!-- NAVIGATION HEADER -->
<nav class="main-nav">
    <div class="nav-container">
        <a href="<?= $base_url ?>/home.php" class="nav-logo">
            <div class="nav-logo-icon">📚</div>
            <span>Quizly</span>
        </a>

        <div class="nav-links" id="navLinks">
            <span class="nav-role">
                <?php 
                    $roles = ['student' => '👨‍🎓 Student', 'teacher' => '👨‍🏫 Teacher', 'admin' => '🔐 Admin'];
                    echo $roles[$user_role];
                ?>
            </span>
            <div class="nav-divider"></div>

            <?php if ($user_role === 'student'): ?>
                <a href="<?= $base_url ?>/home.php" class="nav-link <?= $current_page === 'home.php' ? 'active' : '' ?>">
                    🏠 Home
                </a>
                <a href="<?= $base_url ?>/join_exam.php" class="nav-link <?= $current_page === 'join_exam.php' ? 'active' : '' ?>">
                    ✏️ Take Exam
                </a>

            <?php elseif ($user_role === 'teacher'): ?>
                <a href="<?= $base_url ?>/home.php" class="nav-link <?= $current_page === 'home.php' ? 'active' : '' ?>">
                    🏠 Home
                </a>
                <a href="<?= $base_url ?>/exam_creator_working.php" class="nav-link <?= $current_page === 'exam_creator_working.php' ? 'active' : '' ?>">
                    ✏️ Create
                </a>
                <a href="<?= $base_url ?>/exams_dashboard.php" class="nav-link <?= $current_page === 'exams_dashboard.php' ? 'active' : '' ?>">
                    ⚙️ Manage
                </a>
                <a href="<?= $base_url ?>/teacher/dashboard.php" class="nav-link <?= strpos($current_page, 'teacher') !== false ? 'active' : '' ?>">
                    📈 Reports
                </a>

            <?php elseif ($user_role === 'admin'): ?>
                <a href="<?= $base_url ?>/home.php" class="nav-link <?= $current_page === 'home.php' ? 'active' : '' ?>">
                    🏠 Home
                </a>
                <a href="<?= $base_url ?>/admin/records.php" class="nav-link <?= strpos($current_page, 'admin') !== false ? 'active' : '' ?>">
                    📋 All Records
                </a>
                <a href="<?= $base_url ?>/exams_dashboard.php" class="nav-link">
                    ⚙️ Manage Exams
                </a>
            <?php endif; ?>
        </div>

        <button class="menu-toggle" id="menuToggle">☰</button>
    </div>
</nav>

<!-- BREADCRUMB - Will be filled by individual pages -->
<div class="breadcrumb" id="breadcrumb"></div>

<!-- MAIN CONTENT AREA -->
<div class="main-content">
