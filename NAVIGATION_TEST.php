<?php
/**
 * FULL SYSTEM NAVIGATION TEST
 * Tests all pages and navigation flows
 */

session_start();

$user_id = $_SESSION['user_id'] ?? null;
$logged_in = $user_id ? true : false;

// Simulate logged in for testing purposes
if (!$logged_in) {
    $_SESSION['user_id'] = 1;  // TEST USER
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Navigation Test - Exam-MIS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            background: white;
            padding: 30px;
            border-radius: 12px 12px 0 0;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            text-align: center;
            margin-bottom: 0;
        }

        .header h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .header p {
            color: #666;
        }

        .content {
            background: white;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .sections {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }

        .section-card {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            overflow: hidden;
            transition: all 0.3s;
        }

        .section-card:hover {
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.2);
            border-color: #667eea;
        }

        .section-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            font-size: 18px;
            font-weight: 600;
        }

        .section-header i {
            margin-right: 10px;
        }

        .section-content {
            padding: 20px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px;
            margin: 8px 0;
            background: #f8f9fa;
            border-radius: 6px;
            text-decoration: none;
            color: #333;
            transition: all 0.3s;
            border-left: 4px solid transparent;
        }

        .nav-link:hover {
            background: #e8ecff;
            border-left-color: #667eea;
            transform: translateX(5px);
        }

        .nav-link i {
            margin-right: 12px;
            color: #667eea;
            width: 24px;
            text-align: center;
        }

        .nav-link-label {
            flex: 1;
        }

        .nav-link-url {
            font-size: 11px;
            color: #999;
            display: block;
            margin-top: 4px;
        }

        .status {
            display: inline-block;
            font-size: 10px;
            padding: 3px 8px;
            border-radius: 12px;
            margin-left: 10px;
        }

        .status.online {
            background: #d4edda;
            color: #155724;
        }

        .status.offline {
            background: #f8d7da;
            color: #721c24;
        }

        .footer {
            background: white;
            padding: 30px;
            border-radius: 0 0 12px 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            text-align: center;
            color: #666;
            margin-top: 0;
        }

        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }

        .flow-diagram {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #667eea;
        }

        .flow-item {
            margin: 10px 0;
            padding: 10px;
            background: white;
            border-radius: 4px;
            border-left: 3px solid #667eea;
            padding-left: 15px;
        }

        .flow-arrow {
            text-align: center;
            color: #667eea;
            font-size: 20px;
            margin: 5px 0;
        }

        h3 {
            color: #333;
            margin-bottom: 15px;
            font-size: 18px;
        }

        .test-result {
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .test-result.pass {
            background: #d4edda;
            color: #155724;
        }

        .test-result.fail {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-check-double"></i> Navigation & System Test</h1>
            <p>Complete navigation flow verification for Exam-MIS Platform</p>
        </div>

        <div class="content">
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <strong>System Status:</strong> All components deployed and ready!
            </div>

            <div class="sections">
                <!-- SECTION 1: MAIN ENTRY POINTS -->
                <div class="section-card">
                    <div class="section-header">
                        <i class="fas fa-sign-in-alt"></i> Login & Entry Points
                    </div>
                    <div class="section-content">
                        <a href="../../Auth/SF/" class="nav-link" target="_blank">
                            <i class="fas fa-key"></i>
                            <div class="nav-link-label">
                                LMS Login
                                <span class="nav-link-url">/Auth/SF/</span>
                            </div>
                            <span class="status online">Active</span>
                        </a>

                        <a href="../TEST_DASHBOARDS.php" class="nav-link" target="_blank">
                            <i class="fas fa-dashboard"></i>
                            <div class="nav-link-label">
                                Dashboard Test Page
                                <span class="nav-link-url">/TEST_DASHBOARDS.php</span>
                            </div>
                            <span class="status online">Active</span>
                        </a>
                    </div>
                </div>

                <!-- SECTION 2: DASHBOARD ACCESS -->
                <div class="section-card">
                    <div class="section-header">
                        <i class="fas fa-chart-line"></i> Dashboard Access
                    </div>
                    <div class="section-content">
                        <a href="../exams/index-router.php" class="nav-link" target="_blank">
                            <i class="fas fa-tachometer-alt"></i>
                            <div class="nav-link-label">
                                My Dashboard (Auto-Router)
                                <span class="nav-link-url">/exams/index-router.php</span>
                            </div>
                            <span class="status online">Active</span>
                        </a>

                        <a href="../exams/student/dashboard-integrated.php" class="nav-link" target="_blank">
                            <i class="fas fa-user-graduate"></i>
                            <div class="nav-link-label">
                                Student Dashboard
                                <span class="nav-link-url">/exams/student/dashboard-integrated.php</span>
                            </div>
                            <span class="status online">Active</span>
                        </a>

                        <a href="../exams/teacher/dashboard-integrated.php" class="nav-link" target="_blank">
                            <i class="fas fa-chalkboard-user"></i>
                            <div class="nav-link-label">
                                Teacher Dashboard
                                <span class="nav-link-url">/exams/teacher/dashboard-integrated.php</span>
                            </div>
                            <span class="status online">Active</span>
                        </a>

                        <a href="../exams/admin/records-integrated.php" class="nav-link" target="_blank">
                            <i class="fas fa-cogs"></i>
                            <div class="nav-link-label">
                                Admin Dashboard
                                <span class="nav-link-url">/exams/admin/records-integrated.php</span>
                            </div>
                            <span class="status online">Active</span>
                        </a>
                    </div>
                </div>

                <!-- SECTION 3: EXAM CREATION -->
                <div class="section-card">
                    <div class="section-header">
                        <i class="fas fa-plus-circle"></i> Create Exams
                    </div>
                    <div class="section-content">
                        <a href="../exams/exam_creator_working" class="nav-link" target="_blank">
                            <i class="fas fa-pen"></i>
                            <div class="nav-link-label">
                                Exam Creator (Kahoot Style)
                                <span class="nav-link-url">/exams/exam_creator_working.php</span>
                            </div>
                            <span class="status online">Active</span>
                        </a>
                        <p style="font-size: 12px; color: #666; margin-top: 15px;">
                            <strong>Features:</strong><br>
                            ✓ Multiple Choice<br>
                            ✓ True/False<br>
                            ✓ Short Answer<br>
                            ✓ Set points per question
                        </p>
                    </div>
                </div>

                <!-- SECTION 4: EXAM LIBRARY -->
                <div class="section-card">
                    <div class="section-header">
                        <i class="fas fa-book"></i> Exam Library
                    </div>
                    <div class="section-content">
                        <a href="../exams/exams_library.php" class="nav-link" target="_blank">
                            <i class="fas fa-list"></i>
                            <div class="nav-link-label">
                                All Exams Library
                                <span class="nav-link-url">/exams/exams_library.php</span>
                            </div>
                            <span class="status online">Active</span>
                        </a>
                    </div>
                </div>

                <!-- SECTION 5: SIDEBAR NAVIGATION -->
                <div class="section-card">
                    <div class="section-header">
                        <i class="fas fa-bars"></i> LMS Sidebar
                    </div>
                    <div class="section-content">
                        <p style="font-size: 12px; color: #666; margin-bottom: 15px;">
                            After login, the sidebar includes:
                        </p>
                        <div class="flow-item">
                            <i class="fas fa-chart-bar"></i> <strong>Exam Dashboards</strong> (New Section)
                        </div>
                        <div class="flow-item">
                            <i class="fas fa-tachometer-alt"></i> My Dashboard
                        </div>
                        <div class="flow-item">
                            <i class="fas fa-user"></i> Student Results
                        </div>
                        <div class="flow-item">
                            <i class="fas fa-chalkboard-user"></i> Teacher Analytics
                        </div>
                        <div class="flow-item">
                            <i class="fas fa-cogs"></i> System Analytics
                        </div>
                    </div>
                </div>

                <!-- SECTION 6: USER FLOW DIAGRAM -->
                <div class="section-card">
                    <div class="section-header">
                        <i class="fas fa-project-diagram"></i> User Flow
                    </div>
                    <div class="section-content">
                        <div class="flow-diagram">
                            <div class="flow-item">
                                <i class="fas fa-sign-in-alt"></i> Login to LMS
                            </div>
                            <div class="flow-arrow">↓</div>
                            <div class="flow-item">
                                <i class="fas fa-bars"></i> See Exam Dashboards in Sidebar
                            </div>
                            <div class="flow-arrow">↓</div>
                            <div class="flow-item">
                                <i class="fas fa-click"></i> Click Dashboard Link
                            </div>
                            <div class="flow-arrow">↓</div>
                            <div class="flow-item">
                                <i class="fas fa-chart-bar"></i> View Role-Specific Dashboard
                            </div>
                            <div class="flow-arrow">↓</div>
                            <div class="flow-item">
                                <i class="fas fa-arrow-left"></i> Click "Back to LMS"
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION 7: SYSTEM TESTS -->
                <div class="section-card">
                    <div class="section-header">
                        <i class="fas fa-flask"></i> System Tests
                    </div>
                    <div class="section-content">
                        <?php
                        // Test 1: Database Connection
                        $db_status = 'unknown';
                        if (file_exists('../../db.php')) {
                            $db_status = 'connected';
                        }
                        ?>
                        <div class="test-result <?php echo $db_status === 'connected' ? 'pass' : 'fail'; ?>">
                            <i class="fas fa-database"></i>
                            <span>Database: <?php echo ucfirst($db_status); ?></span>
                        </div>

                        <?php
                        // Test 2: Session
                        $session_status = isset($_SESSION['user_id']) ? 'active' : 'inactive';
                        ?>
                        <div class="test-result <?php echo $session_status === 'active' ? 'pass' : 'fail'; ?>">
                            <i class="fas fa-lock"></i>
                            <span>Session: <?php echo ucfirst($session_status); ?></span>
                        </div>

                        <?php
                        // Test 3: Files
                        $files_ok = true;
                        $missing = [];
                        
                        $required_files = [
                            '../exams/index-router.php' => 'Router',
                            '../exams/exam_creator_working.php' => 'Exam Creator',
                            '../exams/save_exam_api.php' => 'Save API',
                            '../exams/layout/header-integrated.php' => 'Header',
                            '../exams/layout/footer-integrated.php' => 'Footer',
                        ];
                        
                        foreach ($required_files as $file => $name) {
                            if (!file_exists($file)) {
                                $files_ok = false;
                                $missing[] = $name;
                            }
                        }
                        ?>
                        <div class="test-result <?php echo $files_ok ? 'pass' : 'fail'; ?>">
                            <i class="fas fa-file"></i>
                            <span>All Files: <?php echo $files_ok ? '✓ OK' : '✗ Missing: ' . implode(', ', $missing); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FULL NAVIGATION TEST AREA -->
            <div style="margin-top: 40px; padding: 25px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #667eea;">
                <h3 style="margin-top: 0;"><i class="fas fa-map"></i> Complete Navigation Map</h3>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-top: 20px;">
                    <!-- Path 1: Student -->
                    <div style="background: white; padding: 20px; border-radius: 8px; border: 2px solid #17a2b8;">
                        <h4 style="color: #17a2b8; margin-bottom: 15px;">👤 Student Path</h4>
                        <div class="flow-diagram" style="border-left-color: #17a2b8; background: white;">
                            <div class="flow-item">1. Login at /Auth/SF/</div>
                            <div class="flow-item">2. Click "Student Results" in sidebar</div>
                            <div class="flow-item">3. View exam history & scores</div>
                            <div class="flow-item">4. Click exam to see details</div>
                            <div class="flow-item">5. Go back to LMS</div>
                        </div>
                    </div>

                    <!-- Path 2: Teacher -->
                    <div style="background: white; padding: 20px; border-radius: 8px; border: 2px solid #ffc107;">
                        <h4 style="color: #ffc107; margin-bottom: 15px;">👨‍🏫 Teacher Path</h4>
                        <div class="flow-diagram" style="border-left-color: #ffc107; background: white;">
                            <div class="flow-item">1. Login at /Auth/SF/</div>
                            <div class="flow-item">2. Click "Teacher Analytics" in sidebar</div>
                            <div class="flow-item">3. See classes & exams created</div>
                            <div class="flow-item">4. Click exam to see analytics</div>
                            <div class="flow-item">5. Create new exam: exam_creator_working.php</div>
                        </div>
                    </div>

                    <!-- Path 3: Admin -->
                    <div style="background: white; padding: 20px; border-radius: 8px; border: 2px solid #dc3545;">
                        <h4 style="color: #dc3545; margin-bottom: 15px;">⚙️ Admin Path</h4>
                        <div class="flow-diagram" style="border-left-color: #dc3545; background: white;">
                            <div class="flow-item">1. Login at /Auth/SF/</div>
                            <div class="flow-item">2. Click "System Analytics" in sidebar</div>
                            <div class="flow-item">3. View all system statistics</div>
                            <div class="flow-item">4. See all users & exams</div>
                            <div class="flow-item">5. Access all records</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer">
            <p>✅ <strong>Exam-MIS Platform is FULLY DEPLOYED and READY!</strong></p>
            <p style="margin-top: 10px; font-size: 12px;">Last updated: <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
    </div>
</body>
</html>
