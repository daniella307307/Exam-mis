<?php
/**
 * CURRICULUM VIEWER - Student View
 * Displays topics, videos, and downloadable files
 */

require_once('db_connection.php');

$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 1;
$week_id = isset($_GET['week_id']) ? intval($_GET['week_id']) : 0;

// Fetch course info
$courseStmt = $conn->prepare("SELECT * FROM certification_courses WHERE course_id = ? LIMIT 1");
$courseStmt->bind_param("i", $course_id);
$courseStmt->execute();
$courseResult = $courseStmt->get_result();
$course = $courseResult->fetch_assoc();
$courseStmt->close();

if (!$course) {
    die("Course not found");
}

// Fetch all weeks for this course
$weeksStmt = $conn->prepare("
    SELECT DISTINCT lw.* FROM learning_weeks lw
    JOIN learning_topics lt ON lw.week_id = lt.topic_week
    WHERE lt.topic_course = ? AND lt.topic_status = 'Active'
    ORDER BY lw.week_id ASC
");
$weeksStmt->bind_param("i", $course_id);
$weeksStmt->execute();
$weeksResult = $weeksStmt->get_result();
$weeks = $weeksResult->fetch_all(MYSQLI_ASSOC);
$weeksStmt->close();

// Fetch topics for selected week (or all if no week selected)
$topicsQuery = "
    SELECT * FROM learning_topics 
    WHERE topic_course = ? AND topic_status = 'Active' AND topic_visibility = 'Public'
";
$topicsParams = [$course_id];
$topicsTypes = "i";

if ($week_id > 0) {
    $topicsQuery .= " AND topic_week = ?";
    $topicsParams[] = $week_id;
    $topicsTypes .= "i";
}

$topicsQuery .= " ORDER BY topic_week ASC, topic_id ASC";

$topicsStmt = $conn->prepare($topicsQuery);
$topicsStmt->bind_param($topicsTypes, ...$topicsParams);
$topicsStmt->execute();
$topicsResult = $topicsStmt->get_result();
$topics = $topicsResult->fetch_all(MYSQLI_ASSOC);
$topicsStmt->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Curriculum - <?= htmlspecialchars($course['course_name'] ?? 'Course') ?></title>
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
            max-width: 1400px; 
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
            font-size: 36px; 
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .header p { 
            font-size: 16px;
            opacity: 0.95;
        }
        
        .main-layout {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 25px;
        }
        
        .sidebar { 
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            height: fit-content;
            position: sticky;
            top: 20px;
        }
        
        .sidebar h3 { 
            margin-bottom: 15px;
            color: #333;
            border-bottom: 3px solid #667eea;
            padding-bottom: 12px;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .week-btn { 
            display: block; 
            width: 100%; 
            padding: 12px 15px;
            margin-bottom: 8px; 
            background: #f8f9fa;
            border: 2px solid transparent;
            border-left: 4px solid transparent;
            cursor: pointer; 
            text-align: left; 
            border-radius: 6px;
            transition: all 0.3s ease;
            font-size: 14px;
            font-weight: 500;
        }
        
        .week-btn:hover { 
            background: #e9ecef;
            border-left-color: #667eea;
        }
        
        .week-btn.active { 
            background: #667eea;
            color: white;
            border-left-color: #764ba2;
            border: 2px solid #667eea;
        }
        
        .content {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .topics-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 20px;
        }
        
        .topic-card { 
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }
        
        .topic-card:hover { 
            transform: translateY(-8px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
        }
        
        .topic-video { 
            width: 100%;
            height: 200px;
            background: #000;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        
        .topic-video iframe { 
            width: 100%;
            height: 100%;
            border: none;
        }
        
        .no-video {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 48px;
        }
        
        .topic-content { 
            padding: 20px;
        }
        
        .topic-week { 
            font-size: 12px;
            color: #999;
            display: inline-block;
            background: #f0f0f0;
            padding: 4px 12px;
            border-radius: 20px;
            margin-bottom: 10px;
        }
        
        .topic-title { 
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 12px;
            color: #333;
            line-height: 1.4;
        }
        
        .topic-actions { 
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .btn {
            flex: 1;
            min-width: 120px;
            padding: 12px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        
        .btn-download { 
            background: #28a745;
            color: white;
        }
        
        .btn-download:hover {
            background: #218838;
            transform: scale(1.02);
        }
        
        .btn-expand { 
            background: #667eea;
            color: white;
        }
        
        .btn-expand:hover {
            background: #5568d3;
            transform: scale(1.02);
        }
        
        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .empty-state {
            background: white;
            padding: 60px 20px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .empty-state i {
            font-size: 48px;
            color: #ccc;
            margin-bottom: 16px;
        }
        
        .empty-state h3 {
            color: #999;
            margin-bottom: 8px;
        }
        
        .modal { 
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }
        
        .modal.active {
            display: flex;
        }
        
        .modal-content { 
            background: white;
            border-radius: 12px;
            max-width: 900px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }
        
        .modal-header { 
            padding: 24px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-header h2 {
            font-size: 24px;
            color: #333;
        }
        
        .modal-body { 
            padding: 24px;
        }
        
        .modal-video { 
            width: 100%;
            height: 550px;
            border-radius: 8px;
        }
        
        .close-btn { 
            background: none;
            border: none;
            font-size: 28px;
            cursor: pointer;
            color: #999;
            transition: all 0.3s;
        }
        
        .close-btn:hover {
            color: #333;
        }
        
        @media (max-width: 900px) {
            .main-layout {
                grid-template-columns: 1fr;
            }
            
            .sidebar {
                position: static;
            }
            
            .topics-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>
                <i class="fas fa-book"></i>
                Curriculum
            </h1>
            <p><?= htmlspecialchars($course['course_name'] ?? 'Learning Path') ?></p>
        </div>
        
        <!-- Main Layout -->
        <div class="main-layout">
            <!-- Sidebar -->
            <div class="sidebar">
                <h3><i class="fas fa-calendar"></i> Weeks</h3>
                <button class="week-btn <?= $week_id === 0 ? 'active' : '' ?>" onclick="location.href='?course_id=<?= $course_id ?>&week_id=0'">
                    <i class="fas fa-list"></i> All Topics
                </button>
                <?php foreach ($weeks as $w): ?>
                    <button 
                        class="week-btn <?= $week_id === intval($w['week_id']) ? 'active' : '' ?>" 
                        onclick="location.href='?course_id=<?= $course_id ?>&week_id=<?= $w['week_id'] ?>'"
                    >
                        <i class="fas fa-check-circle"></i> Week <?= $w['week_id'] ?>
                    </button>
                <?php endforeach; ?>
            </div>
            
            <!-- Topics Grid -->
            <div class="content">
                <?php if (count($topics) > 0): ?>
                    <div class="topics-grid">
                        <?php foreach ($topics as $topic): ?>
                            <div class="topic-card">
                                <!-- Video Preview -->
                                <div class="topic-video">
                                    <?php if (!empty($topic['topic_video'])): ?>
                                        <iframe src="<?= htmlspecialchars($topic['topic_video']) ?>" allowfullscreen></iframe>
                                    <?php else: ?>
                                        <div class="no-video">
                                            <i class="fas fa-play"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Content -->
                                <div class="topic-content">
                                    <span class="topic-week">📅 Week <?= $topic['topic_week'] ?></span>
                                    <h3 class="topic-title"><?= htmlspecialchars($topic['topic_title']) ?></h3>
                                    
                                    <!-- Actions -->
                                    <div class="topic-actions">
                                        <?php if (!empty($topic['topic_document'])): ?>
                                            <a href="<?= htmlspecialchars($topic['topic_document']) ?>" download class="btn btn-download">
                                                <i class="fas fa-download"></i> Download
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-download" disabled>
                                                <i class="fas fa-download"></i> No File
                                            </button>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($topic['topic_video'])): ?>
                                            <button class="btn btn-expand" onclick="openVideo(<?= htmlspecialchars(json_encode($topic)) ?>)">
                                                <i class="fas fa-expand"></i> Full Screen
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-expand" disabled>
                                                <i class="fas fa-video"></i> No Video
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h3>No topics available</h3>
                        <p>Check back soon for new content</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Video Modal -->
    <div class="modal" id="videoModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="videoTitle">Video</h2>
                <button class="close-btn" onclick="closeVideo()" title="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <iframe id="videoPlayer" class="modal-video" allowfullscreen></iframe>
            </div>
        </div>
    </div>
    
    <script>
        function openVideo(topic) {
            document.getElementById('videoTitle').textContent = topic.topic_title;
            document.getElementById('videoPlayer').src = topic.topic_video;
            document.getElementById('videoModal').classList.add('active');
        }
        
        function closeVideo() {
            document.getElementById('videoModal').classList.remove('active');
            document.getElementById('videoPlayer').src = '';
        }
        
        // Close on ESC key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeVideo();
            }
        });
    </script>
</body>
</html>
