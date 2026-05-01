<?php
/**
 * Teacher Exam Dashboard
 * Shows teacher's classes, student performance, and analytics
 * Integrates with existing BLIS LMS session
 */

// Use integrated header with existing session
include(__DIR__ . '/../layout/header-integrated.php');

// Verify user is a teacher
if ($user_role !== 'teacher') {
    echo '<div style="padding: 2rem; color: #ef4444; text-align: center;">';
    echo '<p>You do not have access to this page.</p>';
    echo '<a href="../../Auth/SF/Auth_welcome.php" style="color: #3b82f6;">Back to Dashboard</a>';
    echo '</div>';
    include(__DIR__ . '/../layout/footer-integrated.php');
    exit;
}

// Get teacher's classes from database
$teacher_id = $user_id;
$classes_sql = "SELECT * FROM classes
WHERE teacher_id = '$teacher_id' OR created_by = '$teacher_id'
ORDER BY class_name ASC";

$classes_result = mysqli_query($conn, $classes_sql);
$classes_count = mysqli_num_rows($classes_result);

// Get teacher's created exams
$exams_sql = "SELECT 
    e.id,
    e.title,
    e.duration,
    (SELECT COUNT(*) FROM exam_questions WHERE exam_id = e.id) as questions_count,
    (SELECT COUNT(*) FROM student_exam_attempts WHERE exam_id = e.id) as attempts_count,
    (SELECT AVG(score) FROM student_exam_attempts WHERE exam_id = e.id) as avg_score
FROM exams e
WHERE e.created_by = '$teacher_id'
ORDER BY e.created_at DESC
LIMIT 10";

$exams_result = mysqli_query($conn, $exams_sql);
$exams_count = mysqli_num_rows($exams_result);

// Get class statistics
$stats_sql = "SELECT 
    COUNT(DISTINCT c.class_id) as total_classes,
    COUNT(DISTINCT sea.student_id) as total_students,
    AVG(sea.score) as class_avg_score
FROM classes c
LEFT JOIN student_exam_attempts sea ON c.class_id = sea.exam_id
WHERE c.teacher_id = '$teacher_id' OR c.created_by = '$teacher_id'";

$stats_result = mysqli_query($conn, $stats_sql);
$stats = mysqli_fetch_array($stats_result);

$total_classes = $stats['total_classes'] ?? 0;
$total_students = $stats['total_students'] ?? 0;
$class_avg_score = round($stats['class_avg_score'] ?? 0, 2);
?>

    <!-- Statistics Row -->
    <div class="dashboard-container">
        <div class="stat-card">
            <div class="stat-label">
                <i class="fas fa-school"></i> Total Classes
            </div>
            <div class="stat-value"><?php echo $total_classes; ?></div>
            <div class="stat-change">Classes under your care</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">
                <i class="fas fa-users"></i> Total Students
            </div>
            <div class="stat-value"><?php echo $total_students; ?></div>
            <div class="stat-change">Across all classes</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">
                <i class="fas fa-file-alt"></i> Exams Created
            </div>
            <div class="stat-value"><?php echo $exams_count; ?></div>
            <div class="stat-change">Total exams</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">
                <i class="fas fa-chart-line"></i> Class Average
            </div>
            <div class="stat-value"><?php echo $class_avg_score; ?></div>
            <div class="stat-change">Overall performance</div>
        </div>
    </div>

    <!-- Classes Section -->
    <div style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 2rem;">
        <h2 style="margin-bottom: 1.5rem; color: #1a1a1a;">
            <i class="fas fa-chalkboard"></i> Your Classes
        </h2>

        <?php if ($classes_count > 0) { ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem;">
                <?php while ($class = mysqli_fetch_array($classes_result)) { ?>
                    <div style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 1.5rem; background: #f9fafb;">
                        <h3 style="color: #1a1a1a; margin-bottom: 0.5rem;">
                            <?php echo htmlspecialchars($class['class_name'] ?? 'Class'); ?>
                        </h3>
                        <p style="color: #6b7280; font-size: 0.9rem; margin-bottom: 1rem;">
                            Grade: <?php echo htmlspecialchars($class['grade'] ?? 'N/A'); ?>
                        </p>
                        <a href="class_report.php?class_id=<?php echo $class['class_id']; ?>" 
                           style="display: inline-block; background: #667eea; color: white; padding: 0.5rem 1rem; border-radius: 4px; text-decoration: none; font-size: 0.9rem;">
                            <i class="fas fa-chart-bar"></i> View Report
                        </a>
                    </div>
                <?php } ?>
            </div>
        <?php } else { ?>
            <p style="color: #6b7280;">No classes assigned yet.</p>
        <?php } ?>
    </div>

    <!-- Exams Section -->
    <div style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <h2 style="margin-bottom: 1.5rem; color: #1a1a1a;">
            <i class="fas fa-file-alt"></i> Your Exams
        </h2>

        <?php if ($exams_count > 0) { ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f9fafb; border-bottom: 2px solid #e5e7eb;">
                            <th style="padding: 1rem; text-align: left; color: #6b7280; font-weight: 600;">Exam Name</th>
                            <th style="padding: 1rem; text-align: center; color: #6b7280; font-weight: 600;">Questions</th>
                            <th style="padding: 1rem; text-align: center; color: #6b7280; font-weight: 600;">Duration</th>
                            <th style="padding: 1rem; text-align: center; color: #6b7280; font-weight: 600;">Attempts</th>
                            <th style="padding: 1rem; text-align: center; color: #6b7280; font-weight: 600;">Avg Score</th>
                            <th style="padding: 1rem; text-align: center; color: #6b7280; font-weight: 600;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($exam = mysqli_fetch_array($exams_result)) { ?>
                            <tr style="border-bottom: 1px solid #e5e7eb;">
                                <td style="padding: 1rem;">
                                    <strong><?php echo htmlspecialchars($exam['title'] ?? 'Exam'); ?></strong>
                                </td>
                                <td style="padding: 1rem; text-align: center; color: #6b7280;">
                                    <?php echo $exam['questions_count'] ?? 0; ?>
                                </td>
                                <td style="padding: 1rem; text-align: center; color: #6b7280;">
                                    <?php echo $exam['duration'] ?? 0; ?> min
                                </td>
                                <td style="padding: 1rem; text-align: center;">
                                    <span style="background: #f0fdf4; color: #10b981; padding: 0.5rem 1rem; border-radius: 4px;">
                                        <?php echo $exam['attempts_count'] ?? 0; ?>
                                    </span>
                                </td>
                                <td style="padding: 1rem; text-align: center;">
                                    <?php echo round($exam['avg_score'] ?? 0, 2); ?>
                                </td>
                                <td style="padding: 1rem; text-align: center;">
                                    <a href="student_record.php?exam_id=<?php echo $exam['id']; ?>" 
                                       style="color: #3b82f6; text-decoration: none; font-weight: 500;">
                                        <i class="fas fa-users"></i> Results
                                    </a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } else { ?>
            <div style="text-align: center; padding: 3rem; color: #6b7280;">
                <i class="fas fa-inbox" style="font-size: 3rem; opacity: 0.5; display: block; margin-bottom: 1rem;"></i>
                <p>No exams created yet.</p>
                <p style="font-size: 0.9rem;">
                    Visit the <a href="../exams_library.php" style="color: #3b82f6;">Exam Library</a> to create exams.
                </p>
            </div>
        <?php } ?>
    </div>

<?php
// Use integrated footer
include(__DIR__ . '/../layout/footer-integrated.php');
?>
