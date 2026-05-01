<?php
/**
 * Exam Dashboard Router
 * Redirects users to their appropriate dashboard based on role.
 * Students have no dashboard — they go straight to the join-exam page.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

require_once(__DIR__ . '/../db.php');

$user_id = $_SESSION['user_id'];
$user_sql = "SELECT up.permission FROM users u
    LEFT JOIN user_permission up ON u.access_level = up.permissio_id
    WHERE u.user_id = '$user_id'
    LIMIT 1";

$result = mysqli_query($conn, $user_sql);

if (!$result || mysqli_num_rows($result) == 0) {
    session_destroy();
    header("Location: ../index.php");
    exit;
}

$user_data = mysqli_fetch_array($result);
$user_role = strtolower($user_data['permission'] ?? 'student');

if (strpos($user_role, 'admin') !== false) {
    header("Location: admin/records.php");
} elseif (strpos($user_role, 'teacher') !== false || strpos($user_role, 'facilitator') !== false) {
    header("Location: teacher/dashboard.php");
} else {
    header("Location: join_exam.php");
}
exit;
?>
