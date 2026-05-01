# 📚 Curriculum Integration Checklist

## Files Created ✅

| File | Location | Purpose |
|------|----------|---------|
| `curriculum_viewer.php` | `/Exam-mis/` | Student view - browse topics & videos |
| `curriculum_manager.php` | `/Exam-mis/` | Admin panel - manage curriculum |
| `CURRICULUM_GUIDE.md` | `/Exam-mis/` | Full technical documentation |
| `CURRICULUM_QUICKSTART.md` | `/Exam-mis/` | Quick reference guide |

---

## Database - Already Ready ✅

Your database **already has** everything needed:

```
Tables Available:
├── learning_topics          ← Main content (videos, files)
├── learning_weeks          ← Organize by week
├── certification_courses   ← Link to courses
└── school_programs        ← For different programs
```

**No database changes needed!** Just add data using the curriculum_manager.php

---

## Where to Edit for Integration

### 1. Add Navigation Link (Your Dashboard)

**File to edit:** Your main dashboard/navigation file

**Find:** The navigation/menu section

**Add this link:**
```html
<!-- Student View -->
<a href="curriculum_viewer.php" class="menu-item">
    <i class="fas fa-book"></i>
    📚 Curriculum
</a>

<!-- Admin Panel (teachers/admins only) -->
<?php if ($user_role == 'admin' || $user_role == 'teacher'): ?>
    <a href="curriculum_manager.php" class="menu-item">
        <i class="fas fa-cog"></i>
        ⚙️ Manage Curriculum
    </a>
<?php endif; ?>
```

---

### 2. Add Authentication (Optional but Recommended)

**File to edit:** `curriculum_manager.php`

**Find:** Line 10 (top of file after session_start)

**Change this:**
```php
// TODO: Add authentication check
// if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'teacher'])) {
//     header('Location: login.php');
//     exit;
// }
```

**To this:**
```php
// Add authentication check
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'teacher'])) {
    header('Location: login.php');
    exit;
}
```

---

### 3. Create Upload Directory

**Run this command:**
```bash
mkdir -p /var/www/html/Exam-mis/uploads/curriculum
chmod 755 /var/www/html/Exam-mis/uploads/curriculum
```

---

### 4. Create Directories for Each Course (Optional)

```bash
mkdir -p /var/www/html/Exam-mis/uploads/curriculum/electronics
mkdir -p /var/www/html/Exam-mis/uploads/curriculum/robotics
mkdir -p /var/www/html/Exam-mis/uploads/curriculum/microcontrollers
```

---

## Testing Checklist

### Test 1: View Curriculum
- [ ] Go to `http://localhost/Exam-mis/curriculum_viewer.php`
- [ ] See list of courses/weeks
- [ ] Sidebar loads correctly

### Test 2: Add Content
- [ ] Go to `http://localhost/Exam-mis/curriculum_manager.php`
- [ ] Fill in form:
  - Course: Select one
  - Week: Select 1
  - Title: "Test Lesson"
  - Video URL: `https://youtube.com/embed/dQw4w9WgXcQ`
  - Document: leave blank
- [ ] Click "Add Topic"
- [ ] See success message

### Test 3: View Added Content
- [ ] Go back to `curriculum_viewer.php`
- [ ] See your new topic
- [ ] Video thumbnail shows
- [ ] Click "Full Screen" - video plays

### Test 4: Download Files
- [ ] Add a topic with document URL: `/Exam-mis/CURRICULUM_GUIDE.md`
- [ ] Go to viewer
- [ ] Click "Download" button
- [ ] File downloads successfully

### Test 5: Mobile Responsive
- [ ] Open curriculum_viewer.php on phone/tablet
- [ ] Layout adapts properly
- [ ] Video still plays
- [ ] Download button works

---

## Data Entry Examples

### Add Electronics Course Content

**Topic 1:**
```
Course: Electronics
Week: 1
Title: Introduction to Circuits
Video: https://youtube.com/embed/Bkp2MKjHdJI
Document: /Exam-mis/uploads/curriculum/electronics/circuits_101.pdf
```

**Topic 2:**
```
Course: Electronics
Week: 2
Title: Understanding Resistors
Video: https://youtube.com/embed/3uBqNEBT0qM
Document: /Exam-mis/uploads/curriculum/electronics/resistors.pdf
```

---

## SQL Insert Examples

### Add topics directly (Alternative to UI):

```sql
-- Electronics Course, Week 1
INSERT INTO learning_topics 
(topic_course, topic_week, topic_title, topic_video, topic_document, topic_status, topic_visibility)
VALUES 
(1, 1, 'Introduction to Circuits', 'https://youtube.com/embed/Bkp2MKjHdJI', '/Exam-mis/uploads/curriculum/circuits_101.pdf', 'Active', 'Public');

-- Robotics Course, Week 1
INSERT INTO learning_topics 
(topic_course, topic_week, topic_title, topic_video, topic_document, topic_status, topic_visibility)
VALUES 
(2, 1, 'Robot Basics', 'https://youtube.com/embed/3uBqNEBT0qM', '/Exam-mis/uploads/curriculum/robot_basics.pdf', 'Active', 'Public');
```

---

## File Upload Instructions

### Upload files via SFTP/FTP:

1. Open SFTP client (FileZilla, WinSCP, etc.)
2. Navigate to: `/var/www/html/Exam-mis/uploads/curriculum/`
3. Upload your PDF files
4. Note the path: `/Exam-mis/uploads/curriculum/filename.pdf`
5. Use path in curriculum_manager.php

---

## Error Troubleshooting

### "Course not found"
- Check course_id in URL
- Make sure certification_courses table has data
- Verify course status = 'Active'

### "No topics available"
- Add topics via curriculum_manager.php
- Check topic_status = 'Active'
- Check topic_visibility = 'Public'

### Videos not playing
- Double-check YouTube embed URL
- Format: `https://youtube.com/embed/VIDEO_ID`
- Not: `https://www.youtube.com/watch?v=VIDEO_ID`

### Files not downloading
- Verify file exists at path
- Check file permissions: `chmod 644 /path/to/file`
- Use full URLs if local files don't work

---

## Performance Tips

### Optimize Database:
```sql
-- Add indexes for faster queries
ALTER TABLE learning_topics ADD INDEX (topic_course);
ALTER TABLE learning_topics ADD INDEX (topic_week);
ALTER TABLE learning_topics ADD INDEX (topic_status);
```

### Optimize Images:
- Compress video thumbnails
- Use WebP format for images
- Cache video previews

---

## Future Enhancements

### Phase 2 (Soon):
- [ ] File upload form
- [ ] Edit topic modal
- [ ] Delete confirmation
- [ ] Bulk import (CSV)
- [ ] Course templates

### Phase 3 (Later):
- [ ] Progress tracking (mark as watched)
- [ ] Student notes/comments
- [ ] Quiz per topic
- [ ] Certificate on completion
- [ ] Recommendation system

---

## Current Database Structure Used

```sql
-- Fields actually used from learning_topics:
SELECT 
    topic_id,           -- Unique ID
    topic_course,       -- Which course (links to certification_courses)
    topic_week,         -- Which week (links to learning_weeks)
    topic_title,        -- Lesson name
    topic_video,        -- Video URL
    topic_document,     -- PDF/file URL
    topic_status,       -- Active/Inactive
    topic_visibility    -- Public/Private
FROM learning_topics;
```

**Not used (ignored):**
- `topic_french` - French translation
- `topic_document_french` - French PDF
- `topic_certification` - Alternative link

---

## Quick Reference URLs

| What | URL |
|------|-----|
| View Curriculum | `http://localhost/Exam-mis/curriculum_viewer.php?course_id=1` |
| Manage Topics | `http://localhost/Exam-mis/curriculum_manager.php` |
| Add Navigation | Edit your main dashboard file |
| Upload Files | `/var/www/html/Exam-mis/uploads/curriculum/` |
| Database | Remote MySQL (use phpmyadmin) |

---

## Support & Questions

- **Viewer not loading?** Check if db_connection.php is available
- **Manager not working?** Check database permissions
- **Videos not showing?** Check YouTube URL format
- **Files not downloading?** Check file paths and permissions

**All set! Start managing your curriculum! 🎓**
