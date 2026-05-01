<?php
session_start();
$_SESSION['user_role'] = $_GET['role'] ?? $_SESSION['user_role'] ?? 'student';
$user_role = $_SESSION['user_role'];

include('layout/header.php');
?>

<style>
    .hero {
        background: linear-gradient(135deg, rgba(102,126,234,0.1) 0%, rgba(118,75,162,0.1) 100%);
        padding: 60px 24px;
        text-align: center;
        border-radius: 20px;
        margin-bottom: 50px;
    }

    .hero h1 {
        font-size: 48px;
        margin-bottom: 16px;
        color: var(--text);
        font-weight: 800;
    }

    .hero p {
        font-size: 18px;
        color: var(--muted);
        margin-bottom: 30px;
    }

    .role-selector {
        display: flex;
        gap: 12px;
        justify-content: center;
        margin-top: 30px;
        flex-wrap: wrap;
    }

    .role-btn {
        padding: 12px 24px;
        border: 2px solid var(--border);
        background: white;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.3s;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .role-btn:hover {
        border-color: var(--primary);
        color: var(--primary);
        transform: translateY(-2px);
    }

    .role-btn.active {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
    }

    .paths-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 24px;
        margin-bottom: 50px;
    }

    .path-card {
        background: white;
        border-radius: 15px;
        padding: 40px 24px;
        text-align: center;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        transition: all 0.3s;
        border: 2px solid transparent;
    }

    .path-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        border-color: var(--primary);
    }

    .path-icon {
        font-size: 64px;
        margin-bottom: 20px;
    }

    .path-card h2 {
        font-size: 28px;
        margin-bottom: 12px;
        color: var(--text);
    }

    .path-card p {
        color: var(--muted);
        margin-bottom: 24px;
        line-height: 1.6;
    }

    .path-features {
        text-align: left;
        margin-bottom: 24px;
        background: var(--bg);
        padding: 16px;
        border-radius: 8px;
    }

    .path-features li {
        padding: 8px 0;
        font-size: 14px;
        color: var(--muted);
        list-style: none;
    }

    .path-features li:before {
        content: "✓ ";
        color: var(--primary);
        font-weight: bold;
        margin-right: 8px;
    }

    .path-btn {
        display: inline-block;
        background: var(--primary);
        color: white;
        padding: 14px 32px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s;
        border: none;
        cursor: pointer;
        font-size: 14px;
    }

    .path-btn:hover {
        background: var(--accent);
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.2);
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-top: 50px;
    }

    .stat-card {
        background: white;
        padding: 24px;
        border-radius: 12px;
        text-align: center;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    }

    .stat-number {
        font-size: 36px;
        font-weight: bold;
        color: var(--primary);
        margin-bottom: 8px;
    }

    .stat-label {
        font-size: 13px;
        color: var(--muted);
        text-transform: uppercase;
    }

    .highlight {
        color: var(--primary);
        font-weight: 600;
    }

    @media (max-width: 768px) {
        .hero h1 {
            font-size: 32px;
        }

        .hero p {
            font-size: 16px;
        }

        .paths-grid {
            grid-template-columns: 1fr;
        }

        .role-selector {
            gap: 8px;
        }

        .role-btn {
            padding: 10px 16px;
            font-size: 12px;
        }
    }
</style>

<!-- HERO SECTION -->
<div class="hero">
    <h1>📚 Welcome to Quizly</h1>
    <p>Modern Exam Management System for Teachers & Students</p>
    
    <div class="role-selector">
        <a href="?role=student" class="role-btn <?= $user_role === 'student' ? 'active' : '' ?>">👨‍🎓 Student</a>
        <a href="?role=teacher" class="role-btn <?= $user_role === 'teacher' ? 'active' : '' ?>">👨‍🏫 Teacher</a>
        <a href="?role=admin" class="role-btn <?= $user_role === 'admin' ? 'active' : '' ?>">🔐 Admin</a>
    </div>
</div>

<!-- PATH CARDS -->
<div class="paths-grid">
    <?php if ($user_role === 'student'): ?>
        
        <!-- STUDENT: Join Exam -->
        <div class="path-card">
            <div class="path-icon">✏️</div>
            <h2>Take an Exam</h2>
            <p>Join an exam using your 6-digit code and test your knowledge</p>
            <ul class="path-features">
                <li>Enter exam code</li>
                <li>Answer questions</li>
                <li>Get instant results</li>
                <li>View score & percentage</li>
            </ul>
            <a href="join_exam.php" class="path-btn">Start Exam →</a>
        </div>

        <!-- STUDENT: Help -->
        <div class="path-card">
            <div class="path-icon">❓</div>
            <h2>Need Help?</h2>
            <p>Get answers to common questions about taking exams</p>
            <ul class="path-features">
                <li>How to join exam</li>
                <li>Exam time limits</li>
                <li>Scoring explained</li>
                <li>Contact support</li>
            </ul>
            <button class="path-btn" onclick="alert('Support coming soon!')">Learn More →</button>
        </div>

    <?php elseif ($user_role === 'teacher'): ?>

        <!-- TEACHER: Create Exam -->
        <div class="path-card">
            <div class="path-icon">✏️</div>
            <h2>Create Exam</h2>
            <p>Generate exams with AI or create manually with multiple question types</p>
            <ul class="path-features">
                <li>AI-powered generation</li>
                <li>Multiple question types</li>
                <li>Auto-grading setup</li>
                <li>Instant deployment</li>
            </ul>
            <a href="exam_creator_working.php" class="path-btn">Create Now →</a>
        </div>

        <!-- TEACHER: Manage Exams -->
        <div class="path-card">
            <div class="path-icon">⚙️</div>
            <h2>Manage Exams</h2>
            <p>Activate exams, manage settings, and control who can access them</p>
            <ul class="path-features">
                <li>Activate/Deactivate</li>
                <li>Set time limits</li>
                <li>View participants</li>
                <li>Edit exam details</li>
            </ul>
            <a href="exams_dashboard.php" class="path-btn">Manage →</a>
        </div>

        <!-- TEACHER: View Reports -->
        <div class="path-card">
            <div class="path-icon">📈</div>
            <h2>Class Reports</h2>
            <p>View detailed reports by class, grade, and individual students</p>
            <ul class="path-features">
                <li>Filter by grade/class</li>
                <li>Student performance</li>
                <li>Pass/fail analytics</li>
                <li>Export to Excel</li>
            </ul>
            <a href="teacher/dashboard.php" class="path-btn">View Reports →</a>
        </div>

        <!-- TEACHER: Student Records -->
        <div class="path-card">
            <div class="path-icon">👤</div>
            <h2>Student Records</h2>
            <p>Track individual student performance across all exams</p>
            <ul class="path-features">
                <li>Search by name</li>
                <li>View exam history</li>
                <li>See detailed scores</li>
                <li>Print report card</li>
            </ul>
            <a href="teacher/student_record.php" class="path-btn">View Records →</a>
        </div>

    <?php elseif ($user_role === 'admin'): ?>

        <!-- ADMIN: All Records -->
        <div class="path-card">
            <div class="path-icon">📋</div>
            <h2>Complete Records</h2>
            <p>View all students, exams, and results in the system</p>
            <ul class="path-features">
                <li>All students database</li>
                <li>All exams created</li>
                <li>Complete results</li>
                <li>Export everything</li>
            </ul>
            <a href="admin/records.php" class="path-btn">View All →</a>
        </div>

        <!-- ADMIN: Manage Exams -->
        <div class="path-card">
            <div class="path-icon">⚙️</div>
            <h2>Manage System</h2>
            <p>Admin controls for exams, users, and system settings</p>
            <ul class="path-features">
                <li>Exam management</li>
                <li>Teacher controls</li>
                <li>Student management</li>
                <li>System config</li>
            </ul>
            <a href="exams_dashboard.php" class="path-btn">Manage →</a>
        </div>

        <!-- ADMIN: Analytics -->
        <div class="path-card">
            <div class="path-icon">📊</div>
            <h2>Analytics</h2>
            <p>System-wide analytics and performance metrics</p>
            <ul class="path-features">
                <li>Total exams</li>
                <li>Total students</li>
                <li>Success rates</li>
                <li>Trending data</li>
            </ul>
            <button class="path-btn" onclick="alert('Analytics dashboard coming soon!')">View Analytics →</button>
        </div>

    <?php endif; ?>
</div>

<!-- STATS SECTION -->
<div style="background: white; padding: 40px 24px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.08);">
    <h2 style="text-align: center; margin-bottom: 30px; color: var(--text);">System Overview</h2>
    <div class="stats-grid" id="statsGrid">
        <div class="stat-card">
            <div class="stat-number" id="totalExams">—</div>
            <div class="stat-label">Total Exams</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" id="totalStudents">—</div>
            <div class="stat-label">Students Participated</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" id="activeExams">—</div>
            <div class="stat-label">Active Exams</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" id="totalAnswers">—</div>
            <div class="stat-label">Responses Submitted</div>
        </div>
    </div>
</div>

<script>
    // Fetch and display statistics
    async function loadStats() {
        try {
            const response = await fetch('/Exam-mis/exams/api/get_stats.php');
            const data = await response.json();
            
            if (data.success) {
                document.getElementById('totalExams').textContent = data.total_exams;
                document.getElementById('totalStudents').textContent = data.total_students;
                document.getElementById('activeExams').textContent = data.active_exams;
                document.getElementById('totalAnswers').textContent = data.total_answers;
            }
        } catch (err) {
            console.error('Error loading stats:', err);
        }
    }

    // Load stats when page loads
    document.addEventListener('DOMContentLoaded', loadStats);
</script>

<?php include('layout/footer.php'); ?>
