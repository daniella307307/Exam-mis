<?php
/**
 * Admin Exam Dashboard
 * Shows system-wide analytics and all records
 * Integrates with existing BLIS LMS session
 */

// Use integrated header with existing session
include(__DIR__ . '/../layout/header-integrated.php');

// Verify user is an admin
if ($user_role !== 'admin') {
    echo '<div style="padding: 2rem; color: #ef4444; text-align: center;">';
    echo '<p>You do not have access to this page.</p>';
    echo '<a href="../../Auth/SF/Auth_welcome.php" style="color: #3b82f6;">Back to Dashboard</a>';
    echo '</div>';
    include(__DIR__ . '/../layout/footer-integrated.php');
    exit;
}

// Get system-wide statistics
$stats_sql = "SELECT 
    (SELECT COUNT(DISTINCT user_id) FROM users) as total_users,
    (SELECT COUNT(*) FROM exams) as total_exams,
    (SELECT COUNT(*) FROM student_exam_attempts) as total_attempts,
    (SELECT COUNT(DISTINCT student_id) FROM student_exam_attempts) as students_with_attempts,
    (SELECT AVG(score) FROM student_exam_attempts) as system_avg_score
";

$stats_result = mysqli_query($conn, $stats_sql);
$stats = mysqli_fetch_array($stats_result);

$total_users = $stats['total_users'] ?? 0;
$total_exams = $stats['total_exams'] ?? 0;
$total_attempts = $stats['total_attempts'] ?? 0;
$students_with_attempts = $stats['students_with_attempts'] ?? 0;
$system_avg_score = round($stats['system_avg_score'] ?? 0, 2);

// Get all exam attempts
$attempts_sql = "SELECT 
    sea.id,
    u.firstname,
    u.lastname,
    e.title,
    sea.score,
    sea.attempted_at
FROM student_exam_attempts sea
LEFT JOIN users u ON sea.student_id = u.user_id
LEFT JOIN exams e ON sea.exam_id = e.id
ORDER BY sea.attempted_at DESC
LIMIT 20";

$attempts_result = mysqli_query($conn, $attempts_sql);
$attempts_count = mysqli_num_rows($attempts_result);

// Get exams by teacher
$exams_sql = "SELECT 
    e.id,
    e.title,
    u.firstname,
    u.lastname,
    e.duration,
    (SELECT COUNT(*) FROM exam_questions WHERE exam_id = e.id) as questions_count,
    (SELECT COUNT(*) FROM student_exam_attempts WHERE exam_id = e.id) as attempts_count,
    (SELECT AVG(score) FROM student_exam_attempts WHERE exam_id = e.id) as avg_score
FROM exams e
LEFT JOIN users u ON e.created_by = u.user_id
ORDER BY e.created_at DESC
LIMIT 15";

$exams_result = mysqli_query($conn, $exams_sql);
$exams_count = mysqli_num_rows($exams_result);
?>

    <!-- Statistics Row -->
    <div class="dashboard-container">
        <div class="stat-card">
            <div class="stat-label">
                <i class="fas fa-users"></i> Total Users
            </div>
            <div class="stat-value"><?php echo $total_users; ?></div>
            <div class="stat-change">Registered users</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">
                <i class="fas fa-file-alt"></i> Total Exams
            </div>
            <div class="stat-value"><?php echo $total_exams; ?></div>
            <div class="stat-change">Created exams</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">
                <i class="fas fa-check"></i> Total Attempts
            </div>
            <div class="stat-value"><?php echo $total_attempts; ?></div>
            <div class="stat-change">Exam submissions</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">
                <i class="fas fa-chart-line"></i> System Average
            </div>
            <div class="stat-value"><?php echo $system_avg_score; ?></div>
            <div class="stat-change">Overall score</div>
        </div>
    </div>

    <!-- Recent Attempts -->
    <div style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 2rem;">
        <h2 style="margin-bottom: 1.5rem; color: #1a1a1a;">
            <i class="fas fa-clock"></i> Recent Exam Attempts
        </h2>

        <?php if ($attempts_count > 0) { ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f9fafb; border-bottom: 2px solid #e5e7eb;">
                            <th style="padding: 1rem; text-align: left; color: #6b7280; font-weight: 600;">Student</th>
                            <th style="padding: 1rem; text-align: left; color: #6b7280; font-weight: 600;">Exam</th>
                            <th style="padding: 1rem; text-align: center; color: #6b7280; font-weight: 600;">Score</th>
                            <th style="padding: 1rem; text-align: left; color: #6b7280; font-weight: 600;">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($attempt = mysqli_fetch_array($attempts_result)) { ?>
                            <tr style="border-bottom: 1px solid #e5e7eb;">
                                <td style="padding: 1rem;">
                                    <strong><?php echo htmlspecialchars(($attempt['firstname'] ?? 'Student') . ' ' . ($attempt['lastname'] ?? '')); ?></strong>
                                </td>
                                <td style="padding: 1rem;">
                                    <?php echo htmlspecialchars($attempt['title'] ?? 'Exam'); ?>
                                </td>
                                <td style="padding: 1rem; text-align: center;">
                                    <span style="background: #e0e7ff; color: #3b82f6; padding: 0.5rem 1rem; border-radius: 4px; font-weight: 600;">
                                        <?php echo $attempt['score'] ?? 0; ?>
                                    </span>
                                </td>
                                <td style="padding: 1rem; color: #6b7280;">
                                    <?php echo date('M d, Y H:i', strtotime($attempt['attempted_at'] ?? date('Y-m-d'))); ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } else { ?>
            <p style="color: #6b7280;">No attempts yet.</p>
        <?php } ?>
    </div>

    <!-- All Exams -->
    <div style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <h2 style="margin-bottom: 1.5rem; color: #1a1a1a;">
            <i class="fas fa-list"></i> All Exams
        </h2>

        <?php if ($exams_count > 0) { ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f9fafb; border-bottom: 2px solid #e5e7eb;">
                            <th style="padding: 1rem; text-align: left; color: #6b7280; font-weight: 600;">Exam Name</th>
                            <th style="padding: 1rem; text-align: left; color: #6b7280; font-weight: 600;">Created By</th>
                            <th style="padding: 1rem; text-align: center; color: #6b7280; font-weight: 600;">Questions</th>
                            <th style="padding: 1rem; text-align: center; color: #6b7280; font-weight: 600;">Attempts</th>
                            <th style="padding: 1rem; text-align: center; color: #6b7280; font-weight: 600;">Avg Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($exam = mysqli_fetch_array($exams_result)) { ?>
                            <tr style="border-bottom: 1px solid #e5e7eb;">
                                <td style="padding: 1rem;">
                                    <strong><?php echo htmlspecialchars($exam['title'] ?? 'Exam'); ?></strong>
                                </td>
                                <td style="padding: 1rem; color: #6b7280;">
                                    <?php echo htmlspecialchars(($exam['firstname'] ?? 'Unknown') . ' ' . ($exam['lastname'] ?? '')); ?>
                                </td>
                                <td style="padding: 1rem; text-align: center;">
                                    <?php echo $exam['questions_count'] ?? 0; ?>
                                </td>
                                <td style="padding: 1rem; text-align: center;">
                                    <span style="background: #f0fdf4; color: #10b981; padding: 0.5rem 1rem; border-radius: 4px;">
                                        <?php echo $exam['attempts_count'] ?? 0; ?>
                                    </span>
                                </td>
                                <td style="padding: 1rem; text-align: center;">
                                    <?php echo round($exam['avg_score'] ?? 0, 2); ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } else { ?>
            <p style="color: #6b7280;">No exams yet.</p>
        <?php } ?>
    </div>

<?php
// Use integrated footer
include(__DIR__ . '/../layout/footer-integrated.php');
?>
