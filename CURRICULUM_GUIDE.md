# 📚 Curriculum Structure Guide - Videos & Files

## Current Database Structure

Your system **already has** a curriculum structure in place! Here's what exists:

### Database Tables

#### 1. **learning_topics** (Main Content Table)
```
Column                  Type        Purpose
─────────────────────────────────────────────────────────
topic_id               INT         Unique ID for each topic
topic_course           INT         Links to course (course_id)
topic_week             INT         Which week this topic is in (week_id)
topic_title            TEXT        Title of the topic
topic_french           TEXT        French description
topic_document         TEXT        URL/path to PDF or document file
topic_document_french  TEXT        French document URL
topic_video            TEXT        URL to video (YouTube, MP4, etc.)
topic_certification    INT         Links to certification
topic_status           VARCHAR     Active/Inactive
topic_visibility       VARCHAR     Public/Private
```

#### 2. **learning_weeks** (Organize by Week)
```
Column               Type        Purpose
─────────────────────────────────────────────────────
week_id              INT         Unique week ID
week_description     VARCHAR     Week name/description
week_status          VARCHAR     Active/Inactive
```

#### 3. **certification_courses** (Course Level)
```
Links to courses that contain topics
```

---

## 🎯 How to Add Content

### Option 1: Using phpMyAdmin or MySQL CLI

#### Add a new topic with video and file:

```sql
INSERT INTO learning_topics 
(topic_course, topic_week, topic_title, topic_french, topic_video, topic_document, topic_status, topic_visibility)
VALUES 
(
    1,                                                    -- course_id
    1,                                                    -- week_id
    'Introduction to Electronics',                        -- topic_title
    'Introduction à l\'électronique',                     -- french title
    'https://youtube.com/embed/VIDEO_ID',               -- video URL (YouTube embed)
    '/Exam-mis/uploads/electronics_intro.pdf',          -- document URL (file path)
    'Active',                                             -- status
    'Public'                                              -- visibility
);
```

#### Update existing topic:

```sql
UPDATE learning_topics 
SET 
    topic_video = 'https://youtube.com/embed/NEW_VIDEO_ID',
    topic_document = '/Exam-mis/uploads/new_file.pdf'
WHERE topic_id = 5;
```

---

## 📁 File Structure for Videos & Documents

### Recommended File Organization:

```
/var/www/html/Exam-mis/
├── uploads/
│   ├── curriculum/
│   │   ├── course_1/
│   │   │   ├── week_1/
│   │   │   │   ├── lesson_1.pdf
│   │   │   │   ├── lesson_2.pdf
│   │   │   │   └── resources.zip
│   │   │   ├── week_2/
│   │   │   │   └── ...
│   │   │
│   │   └── course_2/
│   │       └── ...
```

### File Paths in Database:
- **Local files**: `/Exam-mis/uploads/curriculum/course_1/week_1/lesson_1.pdf`
- **YouTube**: `https://youtube.com/embed/VIDEO_ID` (use embed, not watch)
- **Vimeo**: `https://player.vimeo.com/video/VIDEO_ID`
- **External**: `https://example.com/path/to/file.pdf`

---

## 💻 Create a Curriculum Viewer (Web Interface)

### File to Create: `/var/www/html/Exam-mis/curriculum_viewer.php`

**Purpose:** Display all topics for a course with videos and downloadable files

```php
<?php
require_once('db_connection.php');

$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 1;
$week_id = isset($_GET['week_id']) ? intval($_GET['week_id']) : 0;

// Fetch course info
$courseStmt = $conn->prepare("SELECT * FROM certification_courses WHERE course_id = ? LIMIT 1");
$courseStmt->bind_param("i", $course_id);
$courseStmt->execute();
$course = $courseStmt->get_result()->fetch_assoc();
$courseStmt->close();

// Fetch all weeks for this course
$weeksStmt = $conn->prepare("
    SELECT DISTINCT lw.* FROM learning_weeks lw
    JOIN learning_topics lt ON lw.week_id = lt.topic_week
    WHERE lt.topic_course = ? AND lt.topic_status = 'Active'
    ORDER BY lw.week_id ASC
");
$weeksStmt->bind_param("i", $course_id);
$weeksStmt->execute();
$weeks = $weeksStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$weeksStmt->close();

// Fetch topics for selected week (or all if no week selected)
$topicsQuery = "
    SELECT * FROM learning_topics 
    WHERE topic_course = ? AND topic_status = 'Active' AND topic_visibility = 'Public'
";
$topicsParams = [$course_id];

if ($week_id > 0) {
    $topicsQuery .= " AND topic_week = ?";
    $topicsParams[] = $week_id;
}

$topicsQuery .= " ORDER BY topic_week ASC, topic_id ASC";

$topicsStmt = $conn->prepare($topicsQuery);
$topicsStmt->bind_param(str_repeat('i', count($topicsParams)), ...$topicsParams);
$topicsStmt->execute();
$topics = $topicsStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$topicsStmt->close();

?>
<!DOCTYPE html>
<html>
<head>
    <title>Curriculum - <?= htmlspecialchars($course['course_name'] ?? 'Course') ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 8px; margin-bottom: 30px; }
        .header h1 { font-size: 32px; margin-bottom: 10px; }
        
        .sidebar { display: grid; grid-template-columns: 250px 1fr; gap: 20px; }
        
        .weeks-sidebar { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .weeks-sidebar h3 { margin-bottom: 15px; color: #333; border-bottom: 2px solid #667eea; padding-bottom: 10px; }
        
        .week-btn { 
            display: block; width: 100%; padding: 12px; margin-bottom: 8px; 
            background: #f0f0f0; border: none; border-left: 4px solid transparent; 
            cursor: pointer; text-align: left; border-radius: 4px; transition: all 0.3s;
        }
        .week-btn:hover { background: #e0e0e0; }
        .week-btn.active { background: #667eea; color: white; border-left-color: #764ba2; }
        
        .topics-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 20px; }
        
        .topic-card { 
            background: white; border-radius: 8px; overflow: hidden; 
            box-shadow: 0 2px 8px rgba(0,0,0,0.1); transition: transform 0.3s, box-shadow 0.3s;
        }
        .topic-card:hover { transform: translateY(-5px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
        
        .topic-video { 
            width: 100%; height: 200px; background: #000; 
            overflow: hidden; display: flex; align-items: center; justify-content: center;
        }
        .topic-video iframe { width: 100%; height: 100%; border: none; }
        
        .topic-content { padding: 20px; }
        .topic-title { font-size: 18px; font-weight: 600; margin-bottom: 10px; color: #333; }
        .topic-week { font-size: 12px; color: #999; margin-bottom: 10px; }
        
        .topic-actions { display: flex; gap: 10px; }
        .btn-download, .btn-expand { 
            flex: 1; padding: 10px; border: none; border-radius: 4px; 
            cursor: pointer; font-size: 14px; font-weight: 600; transition: all 0.3s;
        }
        .btn-download { background: #28a745; color: white; }
        .btn-download:hover { background: #218838; }
        .btn-expand { background: #667eea; color: white; }
        .btn-expand:hover { background: #5568d3; }
        
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); align-items: center; justify-content: center; z-index: 1000; }
        .modal.active { display: flex; }
        .modal-content { background: white; border-radius: 8px; max-width: 800px; width: 90%; max-height: 90vh; overflow-y: auto; }
        .modal-header { padding: 20px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
        .modal-body { padding: 20px; }
        .modal-video { width: 100%; height: 500px; }
        .close-btn { background: none; border: none; font-size: 24px; cursor: pointer; color: #999; }
        .close-btn:hover { color: #333; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📚 Curriculum</h1>
            <p><?= htmlspecialchars($course['course_name'] ?? 'Learning Path') ?></p>
        </div>
        
        <div class="sidebar">
            <!-- Weeks Sidebar -->
            <div class="weeks-sidebar">
                <h3>📅 Weeks</h3>
                <button class="week-btn <?= $week_id === 0 ? 'active' : '' ?>" onclick="location.href='?course_id=<?= $course_id ?>&week_id=0'">All Topics</button>
                <?php foreach ($weeks as $w): ?>
                    <button class="week-btn <?= $week_id === intval($w['week_id']) ? 'active' : '' ?>" onclick="location.href='?course_id=<?= $course_id ?>&week_id=<?= $w['week_id'] ?>'">
                        Week <?= $w['week_id'] ?>: <?= htmlspecialchars($w['week_description']) ?>
                    </button>
                <?php endforeach; ?>
            </div>
            
            <!-- Topics Grid -->
            <div>
                <div class="topics-grid">
                    <?php foreach ($topics as $topic): ?>
                        <div class="topic-card">
                            <!-- Video Preview -->
                            <div class="topic-video">
                                <?php if (!empty($topic['topic_video'])): ?>
                                    <iframe src="<?= htmlspecialchars($topic['topic_video']) ?>" allowfullscreen></iframe>
                                <?php else: ?>
                                    <p style="color: #ccc; text-align: center;">No video</p>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Content -->
                            <div class="topic-content">
                                <div class="topic-week">Week <?= $topic['topic_week'] ?></div>
                                <div class="topic-title"><?= htmlspecialchars($topic['topic_title']) ?></div>
                                
                                <!-- Actions -->
                                <div class="topic-actions" style="margin-top: 15px;">
                                    <?php if (!empty($topic['topic_document'])): ?>
                                        <a href="<?= htmlspecialchars($topic['topic_document']) ?>" download class="btn-download">📥 Download</a>
                                    <?php endif; ?>
                                    <?php if (!empty($topic['topic_video'])): ?>
                                        <button class="btn-expand" onclick="openVideo(<?= htmlspecialchars(json_encode($topic)) ?>)">🎬 Full Screen</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Video Modal -->
    <div class="modal" id="videoModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="videoTitle"></h2>
                <button class="close-btn" onclick="closeVideo()">✕</button>
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
    </script>
</body>
</html>
```

---

## 📝 Create an Admin Panel to Manage Curriculum

### File to Create: `/var/www/html/Exam-mis/curriculum_manager.php`

**Purpose:** Allow teachers/admins to add, edit, delete topics with video & file uploads

```php
<?php
require_once('db_connection.php');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $course_id = intval($_POST['course_id']);
        $week_id = intval($_POST['week_id']);
        $title = $_POST['title'];
        $video_url = $_POST['video_url'];
        $document_url = $_POST['document_url'];
        
        $stmt = $conn->prepare("
            INSERT INTO learning_topics 
            (topic_course, topic_week, topic_title, topic_video, topic_document, topic_status, topic_visibility)
            VALUES (?, ?, ?, ?, ?, 'Active', 'Public')
        ");
        $stmt->bind_param("iisss", $course_id, $week_id, $title, $video_url, $document_url);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Topic added successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => $stmt->error]);
        }
        $stmt->close();
        exit;
    }
}

// Fetch courses
$coursesResult = $conn->query("SELECT course_id, course_name FROM certification_courses WHERE status = 'Active'");
$courses = $coursesResult->fetch_all(MYSQLI_ASSOC);

// Fetch weeks
$weeksResult = $conn->query("SELECT week_id, week_description FROM learning_weeks WHERE week_status = 'Active'");
$weeks = $weeksResult->fetch_all(MYSQLI_ASSOC);

// Fetch all topics
$topicsResult = $conn->query("
    SELECT lt.*, cc.course_name 
    FROM learning_topics lt
    JOIN certification_courses cc ON lt.topic_course = cc.course_id
    ORDER BY lt.topic_course, lt.topic_week
");
$topics = $topicsResult->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Curriculum Manager</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        
        .header { background: #667eea; color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .header h1 { font-size: 28px; }
        
        .form-section { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .form-section h2 { margin-bottom: 20px; color: #333; border-bottom: 2px solid #667eea; padding-bottom: 10px; }
        
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin-bottom: 15px; }
        
        .form-group { display: flex; flex-direction: column; }
        .form-group label { margin-bottom: 5px; font-weight: 600; color: #333; }
        .form-group input, .form-group select, .form-group textarea { 
            padding: 10px; border: 1px solid #ddd; border-radius: 4px; 
            font-family: Arial; font-size: 14px;
        }
        .form-group textarea { resize: vertical; min-height: 80px; }
        
        .btn-add { 
            padding: 12px 24px; background: #28a745; color: white; border: none; 
            border-radius: 4px; font-weight: 600; cursor: pointer; margin-top: 10px;
        }
        .btn-add:hover { background: #218838; }
        
        .topics-list { background: white; padding: 20px; border-radius: 8px; }
        .topics-list h2 { margin-bottom: 20px; color: #333; }
        
        .topic-item { 
            background: #f9f9f9; padding: 15px; border-left: 4px solid #667eea; 
            margin-bottom: 10px; border-radius: 4px; display: flex; justify-content: space-between; align-items: center;
        }
        .topic-info { flex: 1; }
        .topic-course { font-size: 12px; color: #999; }
        .topic-title { font-weight: 600; color: #333; margin-top: 5px; }
        .topic-meta { font-size: 12px; color: #999; margin-top: 5px; }
        
        .topic-actions { display: flex; gap: 8px; }
        .btn-edit, .btn-delete { 
            padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: 600;
        }
        .btn-edit { background: #007bff; color: white; }
        .btn-edit:hover { background: #0056b3; }
        .btn-delete { background: #dc3545; color: white; }
        .btn-delete:hover { background: #c82333; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📚 Curriculum Manager</h1>
            <p>Add and manage course topics with videos and documents</p>
        </div>
        
        <!-- Add Topic Form -->
        <div class="form-section">
            <h2>➕ Add New Topic</h2>
            <form id="addTopicForm">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Course:</label>
                        <select name="course_id" required>
                            <option value="">Select course...</option>
                            <?php foreach ($courses as $c): ?>
                                <option value="<?= $c['course_id'] ?>"><?= htmlspecialchars($c['course_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Week:</label>
                        <select name="week_id" required>
                            <option value="">Select week...</option>
                            <?php foreach ($weeks as $w): ?>
                                <option value="<?= $w['week_id'] ?>">Week <?= $w['week_id'] ?> - <?= htmlspecialchars($w['week_description']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Topic Title:</label>
                        <input type="text" name="title" placeholder="e.g., Introduction to Circuits" required>
                    </div>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label>Video URL:</label>
                        <input type="url" name="video_url" placeholder="YouTube: https://youtube.com/embed/VIDEO_ID">
                    </div>
                    
                    <div class="form-group">
                        <label>Document URL:</label>
                        <input type="url" name="document_url" placeholder="/Exam-mis/uploads/curriculum/file.pdf">
                    </div>
                </div>
                
                <button type="submit" class="btn-add">✅ Add Topic</button>
            </form>
        </div>
        
        <!-- Topics List -->
        <div class="topics-list">
            <h2>📖 Existing Topics</h2>
            <?php foreach ($topics as $t): ?>
                <div class="topic-item">
                    <div class="topic-info">
                        <div class="topic-course"><?= htmlspecialchars($t['course_name']) ?></div>
                        <div class="topic-title"><?= htmlspecialchars($t['topic_title']) ?></div>
                        <div class="topic-meta">
                            Week <?= $t['topic_week'] ?>
                            <?php if (!empty($t['topic_video'])): ?> • 🎬 Video<?php endif; ?>
                            <?php if (!empty($t['topic_document'])): ?> • 📄 Document<?php endif; ?>
                        </div>
                    </div>
                    <div class="topic-actions">
                        <button class="btn-edit" onclick="editTopic(<?= $t['topic_id'] ?>)">✏️ Edit</button>
                        <button class="btn-delete" onclick="deleteTopic(<?= $t['topic_id'] ?>)">🗑️ Delete</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <script>
        document.getElementById('addTopicForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            formData.append('action', 'add');
            
            try {
                const response = await fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                
                if (result.success) {
                    alert('✅ Topic added successfully!');
                    location.reload();
                } else {
                    alert('❌ Error: ' + result.error);
                }
            } catch (err) {
                alert('❌ Network error: ' + err.message);
            }
        });
        
        function editTopic(topicId) {
            // TODO: Implement edit functionality
            alert('Edit functionality coming soon!');
        }
        
        function deleteTopic(topicId) {
            if (confirm('Are you sure you want to delete this topic?')) {
                // TODO: Implement delete functionality
                alert('Delete functionality coming soon!');
            }
        }
    </script>
</body>
</html>
```

---

## 🔗 Integration Steps

### Step 1: Add to Navigation
Update your main dashboard to include curriculum links:

```html
<a href="curriculum_viewer.php?course_id=1" class="nav-item">
    📚 Curriculum
</a>

<!-- For Admins/Teachers only -->
<?php if ($user_role === 'admin' || $user_role === 'teacher'): ?>
    <a href="curriculum_manager.php" class="nav-item">
        ⚙️ Manage Curriculum
    </a>
<?php endif; ?>
```

### Step 2: Permissions Check
Make sure users are authenticated:

```php
<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>
```

### Step 3: Upload Files
Create upload directory:

```bash
mkdir -p /var/www/html/Exam-mis/uploads/curriculum/course_1/week_1
chmod 755 /var/www/html/Exam-mis/uploads/curriculum
```

---

## 📊 SQL Queries to Manage Content

### Get all topics for a course:
```sql
SELECT * FROM learning_topics 
WHERE topic_course = 1 
AND topic_status = 'Active'
ORDER BY topic_week, topic_id;
```

### Get topics by week:
```sql
SELECT * FROM learning_topics 
WHERE topic_course = 1 AND topic_week = 2
AND topic_status = 'Active';
```

### Add a new week:
```sql
INSERT INTO learning_weeks (week_description, week_status) 
VALUES ('Week 5: Advanced Topics', 'Active');
```

### Delete a topic:
```sql
DELETE FROM learning_topics WHERE topic_id = 5;
```

---

## ✨ Next Steps

1. **Create the files:**
   - `curriculum_viewer.php` - Student view
   - `curriculum_manager.php` - Admin panel

2. **Add navigation:**
   - Link from dashboard

3. **Upload content:**
   - Add topics via the manager
   - Add video URLs (YouTube, Vimeo, etc.)
   - Upload PDF files to `/uploads/curriculum/`

4. **Test:**
   - View curriculum as student
   - Download files
   - Watch embedded videos

---

## 🎥 Video URL Examples

**YouTube (Embed Format):**
```
https://youtube.com/embed/dQw4w9WgXcQ
```

**Vimeo:**
```
https://player.vimeo.com/video/123456789
```

**Self-hosted MP4:**
```
https://yourdomain.com/videos/lesson.mp4
```

**Local File:**
```
/Exam-mis/uploads/curriculum/course_1/lesson.pdf
```

---

## 📞 Support

Need help?
- Check that videos/files URLs are correct
- Ensure database records have proper status = 'Active'
- Test on different browsers (Chrome, Firefox, Safari)
