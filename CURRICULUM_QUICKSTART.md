# 🚀 CURRICULUM SYSTEM - Quick Start Guide

## ✅ What I Created For You

### 1. **CURRICULUM_GUIDE.md** 
   - Complete documentation of the curriculum structure
   - Database schema explanation
   - SQL query examples

### 2. **curriculum_viewer.php** ✨ (Student View)
   - Beautiful interface to browse topics by week
   - Video player with full-screen mode
   - Download files directly
   - Responsive design (works on mobile)

### 3. **curriculum_manager.php** ⚙️ (Admin Panel)
   - Add new topics with videos and files
   - View all topics in a table
   - Delete topics
   - Edit functionality (coming soon)

---

## 🎯 Quick Start - 3 Steps

### Step 1: Access the Curriculum Viewer
```
http://localhost/Exam-mis/curriculum_viewer.php?course_id=1
```

### Step 2: Add Content (Admin)
```
http://localhost/Exam-mis/curriculum_manager.php
```

Fill in:
- **Course**: Select which course
- **Week**: Select which week
- **Topic Title**: Name of the lesson
- **Video URL**: YouTube embed URL (optional)
- **Document URL**: Link to PDF file (optional)

### Step 3: Test It Out
1. Go to curriculum manager → Add a topic
2. Go to curriculum viewer → See your topic
3. Click "Download" to download files
4. Click "Full Screen" to watch videos

---

## 📝 How to Add Content

### Example 1: Adding a YouTube Video

**In curriculum_manager.php:**
- Course: "Electronics"
- Week: "1"
- Title: "Introduction to LEDs"
- Video URL: `https://youtube.com/embed/dQw4w9WgXcQ`
- Document URL: (leave blank)

### Example 2: Adding a PDF File

**In curriculum_manager.php:**
- Course: "Robotics"
- Week: "2"
- Title: "Motor Control Basics"
- Video URL: (leave blank)
- Document URL: `/Exam-mis/uploads/curriculum/motor_control.pdf`

### Example 3: Adding Both Video + PDF

**In curriculum_manager.php:**
- Course: "Microcontrollers"
- Week: "3"
- Title: "Arduino Programming"
- Video URL: `https://youtube.com/embed/VIDEO_ID`
- Document URL: `/Exam-mis/uploads/curriculum/arduino_guide.pdf`

---

## 📁 Where to Store Your Files

### Create Upload Directory:
```bash
mkdir -p /var/www/html/Exam-mis/uploads/curriculum
chmod 755 /var/www/html/Exam-mis/uploads/curriculum
```

### Upload Your Files:
1. Use FTP/SFTP to upload PDFs
2. Or use a file upload script (coming soon)
3. Store paths like: `/Exam-mis/uploads/curriculum/lesson_1.pdf`

---

## 🎥 Supported Video Platforms

### YouTube
```
https://youtube.com/embed/VIDEO_ID
```
Get VIDEO_ID from: https://www.youtube.com/watch?v=**VIDEO_ID**

### Vimeo
```
https://player.vimeo.com/video/VIDEO_ID
```

### Local Video File
```
https://yourdomain.com/videos/lesson.mp4
```

---

## 🗄️ Database Details

### Your Current Structure

**learning_topics table:**
- `topic_id` - Unique ID
- `topic_course` - Course ID
- `topic_week` - Week number
- `topic_title` - Lesson name
- `topic_video` - Video URL
- `topic_document` - PDF/File URL
- `topic_status` - Active/Inactive
- `topic_visibility` - Public/Private

### Direct SQL Example

Add a topic via SQL:
```sql
INSERT INTO learning_topics 
(topic_course, topic_week, topic_title, topic_video, topic_document, topic_status, topic_visibility)
VALUES 
(1, 1, 'Basic Electronics', 'https://youtube.com/embed/abc123', '/Exam-mis/uploads/electronics.pdf', 'Active', 'Public');
```

Update a topic:
```sql
UPDATE learning_topics 
SET topic_video = 'https://youtube.com/embed/newid'
WHERE topic_id = 5;
```

---

## 📊 Features Overview

### Curriculum Viewer (Student)
✅ View topics by week  
✅ Watch embedded videos  
✅ Download files  
✅ Full-screen video player  
✅ Beautiful responsive design  
✅ Search/filter by week  

### Curriculum Manager (Admin)
✅ Add new topics  
✅ View all topics  
✅ Delete topics  
✅ Bulk actions  
✅ Easy form interface  

---

## 🔐 Security Notes

### Currently:
- No authentication check (add later)
- All topics visible to everyone
- No file upload validation

### To Add Later:
```php
// Add at top of curriculum_manager.php
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Check if user is admin/teacher
if ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'teacher') {
    die('Access denied');
}
```

---

## 🐛 Troubleshooting

### Videos not showing?
- Check YouTube URL format: `https://youtube.com/embed/VIDEO_ID`
- Not `https://www.youtube.com/watch?v=VIDEO_ID`

### Files not downloading?
- Check file path exists
- Use absolute paths: `/Exam-mis/uploads/...`
- Not relative paths

### No topics showing?
- Check topics have `topic_status = 'Active'`
- Check `topic_visibility = 'Public'`
- Check correct `topic_course` ID

---

## 📋 Next Steps

1. **Add your first topic** via curriculum_manager.php
2. **View it** on curriculum_viewer.php
3. **Test on mobile** - make sure it works
4. **Add more courses** and topics
5. **Link from main dashboard** - add navigation button
6. **Enable file uploads** - create upload form
7. **Add edit functionality** - complete the edit modal

---

## 💬 Common Questions

**Q: Can I add videos from other platforms?**
A: Yes! Any platform with an embed URL works (Vimeo, Dailymotion, etc.)

**Q: How do I edit topics?**
A: Go to curriculum_manager.php and click the Edit button (feature coming soon)

**Q: Can students see all courses?**
A: Yes, all courses with `topic_status = 'Active'` and `topic_visibility = 'Public'`

**Q: Where are files stored?**
A: Create `/var/www/html/Exam-mis/uploads/curriculum/` directory

**Q: Can I upload via the web interface?**
A: File upload form coming soon! Currently use FTP or manual SQL

---

## 📞 Contact

Files deployed to:
- `curriculum_viewer.php` - Student view
- `curriculum_manager.php` - Admin panel  
- `CURRICULUM_GUIDE.md` - Full documentation

Location: `/var/www/html/Exam-mis/`

All set! Start adding your curriculum content! 🎓
