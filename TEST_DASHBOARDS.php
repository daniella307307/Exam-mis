<?php
/**
 * EXAM-MIS DASHBOARD TESTING SCRIPT
 * Simple script to test all dashboards after login
 */

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "
    <!DOCTYPE html>
    <html>
    <head>
        <title>Dashboard Test - Login Required</title>
        <style>
            body { font-family: Arial; margin: 40px; background: #f5f5f5; }
            .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; }
            h1 { color: #333; }
            p { color: #666; line-height: 1.6; }
            .warning { background: #fff3cd; border: 1px solid #ffc107; padding: 15px; border-radius: 4px; margin: 20px 0; }
            a { color: #007bff; text-decoration: none; }
            a:hover { text-decoration: underline; }
        </style>
    </head>
    <body>
        <div class='container'>
            <h1>📊 Exam-MIS Dashboard Test</h1>
            <div class='warning'>
                <strong>⚠️ Login Required</strong>
                <p>You must login to the LMS first before accessing dashboards.</p>
            </div>
            <p><strong>Steps:</strong></p>
            <ol>
                <li>Go to <a href='http://localhost/Exam-mis/Auth/SF/' target='_blank'>LMS Login</a></li>
                <li>Login with your credentials</li>
                <li>After login, come back to access dashboards</li>
            </ol>
            <p><strong>Dashboard Access:</strong></p>
            <ul>
                <li><a href='exams/index-router.php'>📈 Main Dashboard Router (Auto-detects your role)</a></li>
                <li><a href='exams/student/dashboard-integrated.php'>👤 Student Dashboard</a></li>
                <li><a href='exams/teacher/dashboard-integrated.php'>👨‍🏫 Teacher Dashboard</a></li>
                <li><a href='exams/admin/records-integrated.php'>⚙️ Admin Dashboard</a></li>
            </ul>
        </div>
    </body>
    </html>
    ";
    exit;
}

// User is logged in - show available dashboards
require_once('db.php');

$user_id = $_SESSION['user_id'];
$user_query = "SELECT u.*, up.permission, s.school_name 
    FROM users u 
    LEFT JOIN user_permission up ON u.access_level = up.permissio_id 
    LEFT JOIN schools s ON u.school_ref = s.school_id 
    WHERE u.user_id = '$user_id' LIMIT 1";

$result = mysqli_query($conn, $user_query);
$user = mysqli_fetch_assoc($result);

$user_role = 'unknown';
if (strpos($user['permission'], 'admin') !== false) {
    $user_role = 'admin';
} elseif (strpos($user['permission'], 'teacher') !== false) {
    $user_role = 'teacher';
} else {
    $user_role = 'student';
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam-MIS Dashboard Access</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container { 
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 800px;
            width: 100%;
            padding: 40px;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 3px solid #667eea;
            padding-bottom: 20px;
        }
        .header h1 {
            font-size: 32px;
            color: #333;
            margin-bottom: 10px;
        }
        .header p {
            color: #666;
            font-size: 14px;
        }
        .user-info {
            background: #f0f4ff;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 30px;
            border-left: 4px solid #667eea;
        }
        .user-info p {
            margin: 8px 0;
            font-size: 14px;
            color: #555;
        }
        .user-info strong {
            color: #333;
        }
        .role-badge {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .dashboards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        .dashboard-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 10px;
            text-decoration: none;
            transition: transform 0.3s, box-shadow 0.3s;
            text-align: center;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }
        .dashboard-card .icon {
            font-size: 40px;
            margin-bottom: 15px;
        }
        .dashboard-card h3 {
            font-size: 18px;
            margin-bottom: 10px;
        }
        .dashboard-card p {
            font-size: 12px;
            opacity: 0.9;
        }
        .back-link {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        .back-link a {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
        .router-button {
            background: #28a745;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 30px;
        }
        .router-button a {
            color: white;
            text-decoration: none;
            font-weight: bold;
            display: block;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📊 Exam-MIS Dashboard</h1>
            <p>Access your role-specific dashboard</p>
        </div>

        <div class="user-info">
            <p><strong>Welcome:</strong> <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p>
            <p><strong>School:</strong> <?php echo htmlspecialchars($user['school_name'] ?? 'N/A'); ?></p>
            <p><strong>Your Role:</strong> <span class="role-badge"><?php echo $user_role; ?></span></p>
        </div>

        <!-- Auto-Router Button -->
        <div class="router-button">
            <a href="exams/index-router.php">🚀 Go to My Dashboard (Auto-Detected)</a>
        </div>

        <!-- All Available Dashboards -->
        <div class="dashboards">
            <a href="exams/student/dashboard-integrated.php" class="dashboard-card">
                <div class="icon">👤</div>
                <h3>Student Dashboard</h3>
                <p>View your exam history, scores, and performance</p>
            </a>

            <a href="exams/teacher/dashboard-integrated.php" class="dashboard-card">
                <div class="icon">👨‍🏫</div>
                <h3>Teacher Dashboard</h3>
                <p>Manage your classes, exams, and student reports</p>
            </a>

            <a href="exams/admin/records-integrated.php" class="dashboard-card">
                <div class="icon">⚙️</div>
                <h3>Admin Dashboard</h3>
                <p>View system-wide analytics and all records</p>
            </a>
        </div>

        <div class="back-link">
            <a href="Auth/SF/Auth_welcome.php">← Back to LMS</a>
        </div>
    </div>
</body>
</html>
