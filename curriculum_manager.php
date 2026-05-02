<?php
/**
 * CURRICULUM MANAGER - Admin/Teacher Panel
 * Add, edit, and delete course topics with videos and documents
 */

session_start();
require_once('db_connection.php');

// TODO: Add authentication check
// if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'teacher'])) {
//     header('Location: login.php');
//     exit;
// }

// Handle form submission
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $course_id = intval($_POST['course_id'] ?? 0);
        $week_id = intval($_POST['week_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $video_url = trim($_POST['video_url'] ?? '');
        $document_url = trim($_POST['document_url'] ?? '');
        
        if (!$course_id || !$week_id || !$title) {
            $message = 'Please fill in all required fields';
            $messageType = 'error';
        } else {
            $stmt = $conn->prepare("
                INSERT INTO learning_topics 
                (topic_course, topic_week, topic_title, topic_video, topic_document, topic_status, topic_visibility)
                VALUES (?, ?, ?, ?, ?, 'Active', 'Public')
            ");
            
            if ($stmt) {
                $stmt->bind_param("iisss", $course_id, $week_id, $title, $video_url, $document_url);
                
                if ($stmt->execute()) {
                    $message = '✅ Topic added successfully!';
                    $messageType = 'success';
                } else {
                    $message = '❌ Error adding topic: ' . $stmt->error;
                    $messageType = 'error';
                }
                $stmt->close();
            }
        }
    } elseif ($action === 'delete') {
        $topic_id = intval($_POST['topic_id'] ?? 0);
        
        if ($topic_id) {
            $stmt = $conn->prepare("DELETE FROM learning_topics WHERE topic_id = ?");
            
            if ($stmt) {
                $stmt->bind_param("i", $topic_id);
                
                if ($stmt->execute()) {
                    $message = '✅ Topic deleted successfully!';
                    $messageType = 'success';
                } else {
                    $message = '❌ Error deleting topic: ' . $stmt->error;
                    $messageType = 'error';
                }
                $stmt->close();
            }
        }
    } elseif ($action === 'update') {
        $topic_id = intval($_POST['topic_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $video_url = trim($_POST['video_url'] ?? '');
        $document_url = trim($_POST['document_url'] ?? '');
        
        if ($topic_id && $title) {
            $stmt = $conn->prepare("
                UPDATE learning_topics 
                SET topic_title = ?, topic_video = ?, topic_document = ?
                WHERE topic_id = ?
            ");
            
            if ($stmt) {
                $stmt->bind_param("sssi", $title, $video_url, $document_url, $topic_id);
                
                if ($stmt->execute()) {
                    $message = '✅ Topic updated successfully!';
                    $messageType = 'success';
                } else {
                    $message = '❌ Error updating topic: ' . $stmt->error;
                    $messageType = 'error';
                }
                $stmt->close();
            }
        }
    }
}

// Fetch courses
$coursesResult = $conn->query("SELECT course_id, course_name FROM certification_courses WHERE status = 'Active' ORDER BY course_name");
$courses = $coursesResult ? $coursesResult->fetch_all(MYSQLI_ASSOC) : [];

// Fetch weeks
$weeksResult = $conn->query("SELECT week_id, week_description FROM learning_weeks WHERE week_status = 'Active' ORDER BY week_id");
$weeks = $weeksResult ? $weeksResult->fetch_all(MYSQLI_ASSOC) : [];

// Fetch all topics with course names
$topicsResult = $conn->query("
    SELECT lt.*, cc.course_name 
    FROM learning_topics lt
    JOIN certification_courses cc ON lt.topic_course = cc.course_id
    ORDER BY lt.topic_course, lt.topic_week, lt.topic_id DESC
");
$topics = $topicsResult ? $topicsResult->fetch_all(MYSQLI_ASSOC) : [];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Curriculum Manager</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
        }
        
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            color: #333;
        }
        
        .container { 
            max-width: 1200px; 
            margin: 0 auto; 
            padding: 20px; 
        }
        
        .header { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        
        .header h1 { 
            font-size: 32px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .header p { 
            font-size: 15px;
            opacity: 0.95;
        }
        
        .alert {
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideIn 0.3s ease;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        @keyframes slideIn {
            from { 
                opacity: 0;
                transform: translateY(-10px);
            }
            to { 
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .form-section {
            background: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .form-section h2 { 
            margin-bottom: 24px;
            color: #333;
            border-bottom: 3px solid #667eea;
            padding-bottom: 12px;
            font-size: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-grid { 
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group { 
            display: flex;
            flex-direction: column;
        }
        
        .form-group label { 
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }
        
        .form-group .required {
            color: #dc3545;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea { 
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: Arial, sans-serif;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .form-group textarea { 
            resize: vertical;
            min-height: 80px;
        }
        
        .form-full { 
            grid-column: 1 / -1;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            font-size: 15px;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary { 
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-success:hover {
            background: #218838;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c82333;
        }
        
        .topics-section {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .topics-section h2 { 
            margin-bottom: 24px;
            color: #333;
            border-bottom: 3px solid #667eea;
            padding-bottom: 12px;
            font-size: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .topics-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .topics-table thead {
            background: #f8f9fa;
        }
        
        .topics-table th {
            padding: 16px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #eee;
            font-size: 13px;
        }
        
        .topics-table td {
            padding: 16px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }
        
        .topics-table tbody tr:hover {
            background: #f8f9fa;
        }
        
        .topic-course {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .topic-meta {
            font-size: 12px;
            color: #999;
        }
        
        .topic-actions {
            display: flex;
            gap: 8px;
        }
        
        .btn-small {
            padding: 6px 12px;
            font-size: 12px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-edit {
            background: #007bff;
            color: white;
        }
        
        .btn-edit:hover {
            background: #0056b3;
        }
        
        .btn-delete {
            background: #dc3545;
            color: white;
        }
        
        .btn-delete:hover {
            background: #c82333;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
        
        .empty-state i {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.5;
        }
        
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .topics-table {
                font-size: 12px;
            }
            
            .topics-table th,
            .topics-table td {
                padding: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>
                <i class="fas fa-cog"></i>
                Curriculum Manager
            </h1>
            <p>Manage course topics, videos, and learning materials</p>
        </div>
        
        <!-- Message Alert -->
        <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?>">
                <i class="fas fa-<?= $messageType === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        
        <!-- Add Topic Form -->
        <div class="form-section">
            <h2>
                <i class="fas fa-plus-circle"></i>
                Add New Topic
            </h2>
            
            <form method="POST" action="">
                <input type="hidden" name="action" value="add">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label>Course <span class="required">*</span></label>
                        <select name="course_id" required>
                            <option value="">Select course...</option>
                            <?php foreach ($courses as $c): ?>
                                <option value="<?= $c['course_id'] ?>">
                                    <?= htmlspecialchars($c['course_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Week <span class="required">*</span></label>
                        <select name="week_id" required>
                            <option value="">Select week...</option>
                            <?php foreach ($weeks as $w): ?>
                                <option value="<?= $w['week_id'] ?>">
                                    Week <?= $w['week_id'] ?> - <?= htmlspecialchars($w['week_description']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Topic Title <span class="required">*</span></label>
                        <input type="text" name="title" placeholder="e.g., Introduction to Electronics" required>
                    </div>
                    
                    <div class="form-group form-full">
                        <label>Video URL (YouTube, Vimeo, etc.)</label>
                        <input type="url" name="video_url" placeholder="https://youtube.com/embed/VIDEO_ID">
                    </div>
                    
                    <div class="form-group form-full">
                        <label>Document/File URL</label>
                        <input type="url" name="document_url" placeholder="<?= APP_BASE_URL ?>/uploads/curriculum/lesson.pdf">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Add Topic
                </button>
            </form>
        </div>
        
        <!-- Topics List -->
        <div class="topics-section">
            <h2>
                <i class="fas fa-list"></i>
                Existing Topics (<?= count($topics) ?>)
            </h2>
            
            <?php if (count($topics) > 0): ?>
                <table class="topics-table">
                    <thead>
                        <tr>
                            <th>Course</th>
                            <th>Week</th>
                            <th>Title</th>
                            <th>Resources</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($topics as $t): ?>
                            <tr>
                                <td>
                                    <span class="topic-course">
                                        <?= htmlspecialchars($t['course_name']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="topic-meta">Week <?= $t['topic_week'] ?></span>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($t['topic_title']) ?></strong>
                                </td>
                                <td>
                                    <span class="topic-meta">
                                        <?php if (!empty($t['topic_video'])): ?>
                                            <i class="fas fa-video"></i> Video
                                        <?php endif; ?>
                                        <?php if (!empty($t['topic_document'])): ?>
                                            <?php if (!empty($t['topic_video'])): ?>&nbsp;•&nbsp;<?php endif; ?>
                                            <i class="fas fa-file"></i> File
                                        <?php endif; ?>
                                        <?php if (empty($t['topic_video']) && empty($t['topic_document'])): ?>
                                            <em>No resources</em>
                                        <?php endif; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="topic-actions">
                                        <button 
                                            class="btn-small btn-edit" 
                                            onclick="editTopic(<?= $t['topic_id'] ?>, <?= htmlspecialchars(json_encode($t)) ?>)"
                                        >
                                            ✏️ Edit
                                        </button>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this topic?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="topic_id" value="<?= $t['topic_id'] ?>">
                                            <button type="submit" class="btn-small btn-delete">
                                                🗑️ Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3>No topics yet</h3>
                    <p>Start by adding your first topic above</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        function editTopic(topicId, topic) {
            // TODO: Implement edit modal
            alert('Edit functionality coming soon!\n\nTopic ID: ' + topicId + '\nTitle: ' + topic.topic_title);
        }
    </script>
</body>
</html>
