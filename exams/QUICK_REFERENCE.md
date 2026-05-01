# Quick Reference: Question & Answer Flow

## Question Creation Flow

```
FRONTEND (exam_creator_working.php)
├─ AI Mode / Manual Mode / Upload Mode
├─ Questions built as JavaScript objects:
│  {
│    type: "multiple|true-false|short-answer"
│    text: "Question?"
│    points: 10
│    options: ["A", "B", "C", "D"]  ← Only for MCQ
│    correctAnswer: 0|true|"text"
│  }
│
└─ PUBLISH BUTTON
   └─ fetch('publish_exam.php', JSON with all questions)
       │
       └─ BACKEND (publish_exam.php)
           ├─ INSERT INTO exams (title, code, duration, ...)
           │  → exam_id = 15
           │
           ├─ FOR EACH QUESTION:
           │  ├─ INSERT INTO questions (exam_id, text, type, marks)
           │  │  → question_id = 94
           │  │
           │  └─ IF MCQ:
           │     └─ FOR EACH OPTION:
           │        └─ INSERT INTO options (question_id, text, is_correct)
           │           → option_id = 336
           │
           │  └─ IF TRUE_FALSE:
           │     ├─ INSERT INTO options (question_id, "True", 1/0)
           │     └─ INSERT INTO options (question_id, "False", 0/1)
           │
           │  └─ IF ESSAY:
           │     └─ (NO OPTIONS SAVED)
           │
           └─ RETURN: {success: true, exam_id: 15, exam_code: 12345}
```

---

## Student Taking Exam Flow

```
FRONTEND (start_exam.php)
├─ GET questions WHERE exam_id = 15
├─ GET options WHERE question_id IN (94, 95, 96)
│
├─ FOR EACH QUESTION:
│  ├─ IF MCQ:
│  │  ├─ Show 4 buttons with option_text
│  │  └─ Click button → answers[94] = 336 (option_id)
│  │
│  ├─ IF TRUE_FALSE:
│  │  ├─ Show 2 buttons: "True", "False"
│  │  └─ Click button → answers[92] = 334 (option_id for "True")
│  │
│  └─ IF ESSAY:
│     ├─ Show textarea
│     └─ Type answer → answers[93] = "My answer text"
│
├─ SUBMIT BUTTON
│  └─ Create form with hidden inputs:
│     <input name="q94" value="336">     ← option_id
│     <input name="q92" value="334">     ← option_id
│     <input name="q93" value="My...">   ← text
│
└─ Submit form to submit_exam.php
```

---

## Answer Validation & Scoring Flow

```
BACKEND (submit_exam.php)
├─ FOR EACH QUESTION:
│  │
│  ├─ IF MCQ (type = 'mcq'):
│  │  ├─ option_id = POST["q94"] = 336
│  │  ├─ SELECT option_text, is_correct FROM options 
│  │  │  WHERE option_id = 336 AND question_id = 94
│  │  │  → option_text = "Option A", is_correct = 1
│  │  │
│  │  ├─ chosen = "Option A"
│  │  ├─ is_correct = 1
│  │  └─ points = 1 × marks(10) = 10
│  │
│  ├─ IF TRUE_FALSE (type = 'true_false'):
│  │  ├─ option_id = POST["q92"] = 334
│  │  ├─ SELECT option_text, is_correct FROM options 
│  │  │  WHERE option_id = 334 AND question_id = 92
│  │  │  → option_text = "True", is_correct = 1
│  │  │
│  │  ├─ chosen = "True"
│  │  ├─ is_correct = 1
│  │  └─ points = 1 × marks(5) = 5
│  │
│  └─ IF ESSAY (type = 'essay'):
│     ├─ chosen = POST["q93"] = "My answer text"
│     ├─ is_correct = 0 (essays not auto-graded!)
│     ├─ points = 0  (essays awarded by teacher)
│     │
│
├─ SAVE ALL ANSWERS:
│  ├─ INSERT INTO answers (player_id, exam_id, q_id, chosen, is_correct, points)
│  │  VALUES (1, 15, 94, "Option A", 1, 10)
│  │  VALUES (1, 15, 92, "True", 1, 5)
│  │  VALUES (1, 15, 93, "My answer", 0, 0)
│  │
│  ├─ total_score = 10 + 5 + 0 = 15
│  └─ UPDATE players SET score = 15 WHERE player_id = 1
│
└─ REDIRECT to leaderboard.php
```

---

## Database After Complete Flow

```
EXAMS TABLE:
exam_id | title            | exam_code | duration
15      | "Biology Final"  | 12345     | 60

QUESTIONS TABLE:
q_id | exam_id | question_text          | type       | marks
94   | 15      | "What is A?"           | mcq        | 10
92   | 15      | "Is A = A?"            | true_false | 5
93   | 15      | "Explain A"            | essay      | 10

OPTIONS TABLE (MCQ only):
opt_id | q_id | option_text     | is_correct
336    | 94   | "Option A"      | 1          ← Correct
337    | 94   | "Option B"      | 0
338    | 94   | "Option C"      | 0
339    | 94   | "Option D"      | 0
334    | 92   | "True"          | 1          ← Correct
335    | 92   | "False"         | 0

OPTIONS TABLE (No entries for essays!)

ANSWERS TABLE:
ans_id | player_id | exam_id | q_id | chosen_answer | is_correct | points
1      | 1         | 15      | 94   | "Option A"    | 1          | 10     ✓
2      | 1         | 15      | 92   | "True"        | 1          | 5      ✓
3      | 1         | 15      | 93   | "My answer"   | 0          | 0      (needs grading)

PLAYERS TABLE:
player_id | exam_id | score
1         | 15      | 15    ← Total: 10 + 5 + 0
```

---

## Key Differences Between Question Types

```
┌─────────────┬─────────────────────┬──────────────┬──────────────────┐
│ Type        │ Options Table Usage │ Auto-Graded? │ Points Earning   │
├─────────────┼─────────────────────┼──────────────┼──────────────────┤
│ MCQ         │ ✓ YES (4 entries)   │ ✓ YES        │ Full if correct  │
├─────────────┼─────────────────────┼──────────────┼──────────────────┤
│ True/False  │ ✓ YES (2 entries)   │ ✓ YES        │ Full if correct  │
├─────────────┼─────────────────────┼──────────────┼──────────────────┤
│ Essay       │ ✗ NO (0 entries)    │ ✗ NO         │ Teacher awards   │
└─────────────┴─────────────────────┴──────────────┴──────────────────┘
```

---

## The Fix (What Changed)

### BEFORE (Broken)
```php
// submit_exam.php tried MCQ lookup for ALL types
$selected_option_id = (int) ($_POST["q{$qid}"] ?? 0);
$check = $conn->prepare("SELECT option_text, is_correct 
                         FROM options 
                         WHERE option_id = ? AND question_id = ?");
// This FAILED for essays because they have no options!
```

### AFTER (Fixed)
```php
// Now checks type first
$qtype = $q['question_type'];  // Get the type

if ($qtype === 'mcq' || $qtype === 'true_false') {
    // MCQ/T-F: Lookup option_id in options table ✓
    $option_id = (int) $_POST["q{$qid}"];
    $check = $conn->prepare("SELECT option_text, is_correct FROM options ...");
}
else if ($qtype === 'essay') {
    // Essay: Just store the text, don't lookup ✓
    $chosen = $_POST["q{$qid}"];
    $is_correct = 0;  // Not auto-graded
}
```

---

## Test Checklist

```
✓ Create exam with 1 MCQ + 1 T/F + 1 Essay
✓ Publish and get exam code
✓ Join exam as student
✓ Answer MCQ (click button)
✓ Answer T/F (click True/False)
✓ Answer Essay (type text)
✓ Submit exam
✓ Check leaderboard shows correct score
  (Should be: MCQ_points + T/F_points + 0 for essay)
✓ Run database query:
  SELECT * FROM answers WHERE exam_id = <code>;
✓ Verify:
  - All 3 answers saved
  - MCQ & T/F have is_correct=1 (if correct)
  - Essay has is_correct=0
  - Points match expected values
```

---

## Common Issues & Fixes

### Issue: "Essays cause errors"
**Fix**: Now handled properly (no option lookup)

### Issue: "MCQ answers marked wrong"
**Fix**: Ensure option with is_correct=1 is clicked

### Issue: "Score is 0 for everything"
**Fix**: Check options table has is_correct=1 for correct answer

### Issue: "Leaderboard shows wrong score"
**Fix**: Run: `SELECT * FROM answers WHERE exam_id = <id>;`

---

## For Support

**Check these in order:**
1. Database: `SELECT COUNT(*) FROM questions WHERE exam_id = ?;`
2. Options: `SELECT * FROM options WHERE question_id = ?;`
3. Answers: `SELECT * FROM answers WHERE exam_id = ?;`
4. Score: `SELECT score FROM players WHERE player_id = ?;`
