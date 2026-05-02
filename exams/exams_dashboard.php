<?php
require_once('../db_connection.php');

// Get all exams (sorted by created_at, newest first)
$query = "SELECT exam_id, exam_code, title, topic, grade, status, created_at, start_time, is_active 
          FROM exams 
          ORDER BY created_at DESC 
          LIMIT 100";

$result = $conn->query($query);
$exams = [];

while ($row = $result->fetch_assoc()) {
    $exams[] = $row;
}
// Connection closed by db_connection.php shutdown handler
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Management Dashboard</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
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
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background: white;
            padding: 20px 30px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
        }

        .header h1 {
            color: #333;
            font-size: 28px;
        }

        .header a {
            background: #667eea;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s;
            cursor: pointer;
        }

        .header a:hover {
            background: #764ba2;
            transform: translateY(-2px);
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-card h3 {
            color: #666;
            font-size: 14px;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .stat-card .number {
            font-size: 36px;
            font-weight: bold;
            color: #667eea;
        }

        .exams-list {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            overflow: hidden;
        }

        .exam-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 30px;
            border-bottom: 1px solid #eee;
            transition: background 0.3s;
        }

        .exam-item:hover {
            background: #f8f9ff;
        }

        .exam-info h3 {
            margin-bottom: 8px;
            color: #333;
        }

        .exam-info p {
            font-size: 13px;
            color: #999;
            margin: 5px 0;
        }

        .exam-meta {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge.draft {
            background: #fff3cd;
            color: #856404;
        }

        .badge.active {
            background: #d4edda;
            color: #155724;
        }

        .badge.ended {
            background: #f8d7da;
            color: #721c24;
        }

        .exam-code {
            font-family: 'Courier New', monospace;
            background: #f0f0f0;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
            color: #333;
        }

        .actions {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 10px 18px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-activate {
            background: #28a745;
            color: white;
        }

        .btn-activate:hover {
            background: #218838;
            transform: translateY(-2px);
        }

        .btn-deactivate {
            background: #dc3545;
            color: white;
        }

        .btn-deactivate:hover {
            background: #c82333;
            transform: translateY(-2px);
        }

        .btn-view {
            background: #667eea;
            color: white;
            text-decoration: none;
        }

        .btn-view:hover {
            background: #764ba2;
        }

        .empty {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .empty h2 {
            margin-bottom: 10px;
            color: #666;
        }

        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #333;
            color: white;
            padding: 16px 24px;
            border-radius: 8px;
            opacity: 0;
            transition: opacity 0.3s;
            z-index: 1000;
        }

        .toast.show {
            opacity: 1;
        }

        .toast.success {
            background: #28a745;
        }

        .toast.error {
            background: #dc3545;
        }

        .time-info {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
    </style>
    <link rel="stylesheet" href="<?= APP_BASE_URL ?>/exams/assets/exam-theme.css">
</head>
<body class="exam-dark">
    <div class="container">
        <a href="<?= APP_BASE_URL ?>/Auth/SF/index.php"
           style="display:inline-flex;align-items:center;gap:8px;margin-bottom:14px;padding:10px 18px;background:rgba(255,255,255,.08);color:#fff;font-weight:700;font-size:13px;border-radius:8px;text-decoration:none;border:1px solid rgba(168,85,247,.3)">
            ← Back to LMS Home
        </a>
        <div class="header">
            <h1 style="color:#fff">📚 Exam Management Dashboard</h1>
            <a href="exam_creator_working.php" class="create-btn" style="color:#fff;font-weight:800;background:linear-gradient(135deg,#7c3aed,#a855f7);padding:12px 24px;border-radius:8px;text-decoration:none;box-shadow:0 8px 24px rgba(124,58,237,.4)">+ Create New Exam</a>
        </div>

        <div class="stats">
            <?php
            $draft_count = count(array_filter($exams, fn($e) => $e['status'] === 'draft'));
            $active_count = count(array_filter($exams, fn($e) => $e['status'] === 'active'));
            $ended_count = count(array_filter($exams, fn($e) => $e['status'] === 'ended'));
            $total_count = count($exams);
            ?>
            <div class="stat-card">
                <h3>Total Exams</h3>
                <div class="number"><?= $total_count ?></div>
            </div>
            <div class="stat-card">
                <h3>Draft</h3>
                <div class="number" style="color: #ffc107"><?= $draft_count ?></div>
            </div>
            <div class="stat-card">
                <h3>Active</h3>
                <div class="number" style="color: #28a745"><?= $active_count ?></div>
            </div>
            <div class="stat-card">
                <h3>Ended</h3>
                <div class="number" style="color: #dc3545"><?= $ended_count ?></div>
            </div>
        </div>

        <div class="exams-list">
            <?php if (empty($exams)): ?>
                <div class="empty">
                    <h2>No Exams Yet</h2>
                    <p>Create your first exam to get started</p>
                </div>
            <?php else: ?>
                <?php foreach ($exams as $exam): ?>
                    <div class="exam-item">
                        <div class="exam-info">
                            <h3><?= htmlspecialchars($exam['title']) ?></h3>
                            <p>📝 Topic: <strong><?= htmlspecialchars($exam['topic']) ?></strong> | 👥 Grade: <strong><?= htmlspecialchars($exam['grade']) ?></strong></p>
                            <p>📅 Created: <?= date('M d, Y H:i', strtotime($exam['created_at'])) ?></p>
                            <?php if ($exam['status'] === 'active'): ?>
                                <div class="time-info">⏱️ Start Time: <?= date('M d, Y H:i', strtotime($exam['start_time'])) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="exam-meta">
                            <span class="exam-code">Code: <?= $exam['exam_code'] ?></span>
                            <span class="badge <?= strtolower($exam['status']) ?>"><?= ucfirst($exam['status']) ?></span>
                            <div class="actions">
                                <?php if ($exam['status'] === 'draft'): ?>
                                    <button class="btn btn-activate" onclick="activateExam(<?= $exam['exam_id'] ?>)">Activate</button>
                                <?php elseif ($exam['status'] === 'active'): ?>
                                    <button class="btn btn-deactivate" onclick="deactivateExam(<?= $exam['exam_id'] ?>)">Deactivate</button>
                                <?php endif; ?>
                                <a href="view_exam.php?exam_id=<?= $exam['exam_id'] ?>" class="btn btn-view">📋 View</a>
                                <a href="exam_creator_working.php?exam_id=<?= $exam['exam_id'] ?>" class="btn btn-view" style="background:#28a745;">✏️ Edit</a>
                                <a href="assignment_submissions.php?exam_id=<?= $exam['exam_id'] ?>" class="btn btn-view" style="background:#7c3aed;">📎 Submissions</a>
                                <button class="btn btn-view" style="background:#f59e0b;" onclick="republishExam(<?= $exam['exam_id'] ?>, '<?= htmlspecialchars(addslashes($exam['title'])) ?>')">🔄 Re-Publish</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="toast" id="toast"></div>

    <script>
        async function activateExam(examId) {
            if (!confirm('Activate this exam and start it immediately?')) return;
            
            try {
                const response = await fetch('activate_exam.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        exam_id: examId,
                        action: 'activate',
                        start_now: true
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    showToast('✓ Exam activated!', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast('❌ ' + data.error, 'error');
                }
            } catch (err) {
                showToast('❌ Error: ' + err.message, 'error');
            }
        }

        async function deactivateExam(examId) {
            if (!confirm('Deactivate this exam?')) return;
            
            try {
                const response = await fetch('activate_exam.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        exam_id: examId,
                        action: 'deactivate'
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    showToast('✓ Exam deactivated!', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast('❌ ' + data.error, 'error');
                }
            } catch (err) {
                showToast('❌ Error: ' + err.message, 'error');
            }
        }

        function showToast(msg, type = 'info') {
            const toast = document.getElementById('toast');
            toast.textContent = msg;
            toast.className = 'toast ' + type;
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 3000);
        }
        async function republishExam(examId, examTitle) {
    const label = prompt(
        `📚 Re-publish "${examTitle}"\n\n` +
        `Enter a label for this new session (e.g. "Class 6A", "Class 6B"):`
    );
    
    if (!label || !label.trim()) return;

    try {
        const formData = new FormData();
        formData.append('exam_id', examId);
        formData.append('label', label.trim());

        const response = await fetch('republish_exam.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            alert(
                `✅ New session created for "${examTitle}"!\n\n` +
                `📋 Session: ${data.label}\n` +
                `🔑 New Code: ${data.code}\n` +
                `🔐 Pin: ${data.pin}\n\n` +
                `Share the code with your students!`
            );
            location.reload();
        } else {
            alert('❌ Error: ' + data.error);
        }
    } catch (err) {
        alert('❌ Error: ' + err.message);
    }
}
    </script>
</body>
</html>
