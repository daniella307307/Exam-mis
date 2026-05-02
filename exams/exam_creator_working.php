<?php
/**
 * EXAM CREATOR - WORKING VERSION
 * Create exams with:
 * - AI Mode (auto-generate)
 * - Manual Mode (Kahoot-style question builder)
 * - Upload Mode (Excel)
 */

require_once('../db_connection.php');

// Check if user is logged in
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Check if we're editing an existing exam
$editing_exam_id = isset($_GET['exam_id']) ? (int)$_GET['exam_id'] : 0;
$existing_exam = null;
$existing_questions = [];

if ($editing_exam_id) {
    $stmt = $conn->prepare("SELECT * FROM exams WHERE exam_id = ? LIMIT 1");
    $stmt->bind_param('i', $editing_exam_id);
    $stmt->execute();
    $existing_exam = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($existing_exam) {
        // Fetch all questions for this exam
        $qstmt = $conn->prepare("SELECT * FROM questions WHERE exam_id = ? ORDER BY question_id ASC");
        $qstmt->bind_param('i', $editing_exam_id);
        $qstmt->execute();
        $questions_result = $qstmt->get_result();
        
        while ($q = $questions_result->fetch_assoc()) {
            // Fetch options for this question
            $ostmt = $conn->prepare("SELECT * FROM options WHERE question_id = ? ORDER BY option_id ASC");
            $ostmt->bind_param('i', $q['question_id']);
            $ostmt->execute();
            $opts_result = $ostmt->get_result();
            $options = [];
            while ($opt = $opts_result->fetch_assoc()) {
                $options[] = $opt;
            }
            $ostmt->close();

            // Build question object for JavaScript
            $q_obj = [
                'id' => $q['question_id'],
                'text' => $q['question_text'],
                'type' => str_replace('_', '-', $q['question_type']),
                'points' => (int)$q['marks'],
            ];

            if ($q['question_type'] === 'mcq' || $q['question_type'] === 'multiple') {
                $q_obj['type'] = 'multiple';
                $q_obj['options'] = array_column($options, 'option_text');
                // Find which option is correct
                foreach ($options as $idx => $opt) {
                    if ($opt['is_correct']) {
                        $q_obj['correctAnswer'] = $idx;
                        break;
                    }
                }
            } elseif ($q['question_type'] === 'true_false') {
                $q_obj['type'] = 'true-false';
                $q_obj['correctAnswer'] = $options[0]['is_correct'] ? ($options[0]['option_text'] === 'True') : ($options[1]['option_text'] === 'True');
            } else {
                $q_obj['type'] = 'short-answer';
                $q_obj['correctAnswer'] = $options[0]['option_text'] ?? '';
            }

            $existing_questions[] = $q_obj;
        }
        $qstmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Exam - Exam-MIS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }

        .header p {
            opacity: 0.9;
        }

        .content {
            padding: 40px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
            font-family: inherit;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .mode-selection {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 25px 0;
        }

        .mode-card {
            padding: 20px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
        }

        .mode-card:hover {
            border-color: #667eea;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.2);
        }

        .mode-card.selected {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        .mode-card i {
            font-size: 32px;
            margin-bottom: 10px;
            display: block;
        }

        .mode-card h3 {
            font-size: 16px;
            margin-bottom: 5px;
        }

        .mode-card p {
            font-size: 12px;
            opacity: 0.7;
        }

        .hidden-section {
            display: none;
        }

        .hidden-section.active {
            display: block;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-block;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .btn-secondary {
            background: #f0f0f0;
            color: #333;
        }

        .btn-secondary:hover {
            background: #e0e0e0;
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .question-builder {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 8px;
            margin: 20px 0;
        }

        .question-type-selector {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .question-type-btn {
            padding: 10px 15px;
            border: 2px solid #e0e0e0;
            background: white;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 500;
        }

        .question-type-btn.active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        .question-type-btn:hover {
            border-color: #667eea;
        }

        .option-input {
            display: flex;
            gap: 10px;
            margin: 10px 0;
            align-items: center;
            padding: 6px 10px;
            border-radius: 10px;
            border: 2px solid transparent;
            transition: border-color .15s, background .15s;
        }
        .option-input.correct {
            border-color: rgba(34,197,94,.6) !important;
            background: rgba(34,197,94,.10) !important;
            box-shadow: 0 6px 20px rgba(34,197,94,.18);
        }
        .option-input.correct::after {
            content: '✓ Correct';
            display: inline-block;
            padding: 4px 10px;
            border-radius: 99px;
            background: rgba(34,197,94,.18);
            color: #16a34a;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
            border: 1px solid rgba(34,197,94,.4);
            margin-left: 4px;
        }
        body.exam-dark .option-input.correct::after {
            color: #86efac;
        }

        .option-input input {
            flex: 1;
        }

        .option-input button {
            padding: 8px 12px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .question-card {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
            border-left: 4px solid #667eea;
        }

.question-card.editing {
    border-left-color: #28a745;
    background: #fffef6;
    box-shadow: 0 6px 18px rgba(40,167,69,0.06);
}

        .question-card h4 {
            margin-bottom: 10px;
            color: #333;
        }

        .question-card p {
            color: #666;
            font-size: 14px;
            margin: 5px 0;
        }

        .actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
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

        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        #hiddenFileInput {
            display: none;
        }

        .questions-list {
            margin: 20px 0;
        }

        .question-preview {
            background: white;
            padding: 15px;
            border-radius: 6px;
            margin: 10px 0;
            border: 1px solid #e0e0e0;
        }

        .question-preview h5 {
            margin-bottom: 10px;
            color: #333;
        }

        .question-preview ul {
            margin-left: 20px;
            margin-top: 10px;
        }

        .question-preview li {
            margin: 5px 0;
            color: #666;
        }

        .success-message {
            text-align: center;
            padding: 40px 20px;
        }

        .success-message i {
            font-size: 64px;
            color: #28a745;
            margin-bottom: 20px;
        }

        .success-message h2 {
            color: #333;
            margin-bottom: 10px;
        }

        .back-link {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }

        .back-link a {
            color: #667eea;
            text-decoration: none;
        }

        .back-link a:hover {
            text-decoration: underline;
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
            <h1 style="color:#fff"><i class="fas fa-file-alt"></i> Create Exam</h1>
            <p style="color:#cbd5e1">Build your exam with questions</p>
        </div>

        <div class="content" id="mainContent">
            <!-- STEP 1: BASIC INFO -->
            <div id="step1" class="step-section active">
                <h2>Exam Details</h2>
                <div class="form-group">
                    <label for="examTitle">Exam Title / Subject *</label>
                    <input type="text" id="examTitle" placeholder="e.g., Robotics, CAD, Biology" required>
                </div>

                <div class="form-group">
                    <label for="examSchool">School *</label>
                    <select id="examSchool" required>
                        <option value="">Select Your School</option>
                    </select>
                    <small style="color: #666; margin-top: 5px; display: block;">Loading schools...</small>
                </div>

                <div class="form-group">
                    <label for="examTopic">Topic / Subject Area *</label>
                    <input type="text" id="examTopic" placeholder="e.g., Electronics, 3D Modeling, Photosynthesis" required>
                </div>

                <div class="form-group">
                    <label for="examGrade">Grade Level *</label>
                    <select id="examGrade" required>
                        <option value="">Select Grade Level</option>
                        <option value="Grade 1">Grade 1</option>
                        <option value="Grade 2">Grade 2</option>
                        <option value="Grade 3">Grade 3</option>
                        <option value="Grade 4">Grade 4</option>
                        <option value="Grade 5">Grade 5</option>
                        <option value="Grade 6">Grade 6</option>
                        <option value="Grade 7">Grade 7</option>
                        <option value="Grade 8">Grade 8</option>
                        <option value="Grade 9">Grade 9</option>
                        <option value="Grade 10">Grade 10</option>
                        <option value="Grade 11">Grade 11</option>
                        <option value="Grade 12">Grade 12</option>
                        <option value="Beginner">Beginner</option>
                        <option value="Intermediate">Intermediate</option>
                        <option value="Advanced">Advanced</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="examDescription">Description (Optional)</label>
                    <textarea id="examDescription" placeholder="Optional description" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label for="examDuration">Duration (minutes) *</label>
                    <input type="number" id="examDuration" value="60" min="5" max="300" required>
                </div>

                <div class="form-group">
                    <label for="examPassingScore">Passing Score (%) *</label>
                    <input type="number" id="examPassingScore" value="50" min="0" max="100" required>
                    <small style="color:#666;margin-top:4px;display:block;">Students who reach this score earn a certificate</small>
                </div>

                <div class="form-group">
                    <label for="examCertification">Certification Level (Optional)</label>
                    <select id="examCertification">
                        <option value="">No Specific Certification</option>
                    </select>
                    <small style="color:#666;margin-top:4px;display:block;">Certificate will be awarded with this certification level on pass</small>
                </div>

                <h3 style="margin-top: 30px; margin-bottom: 15px;">How do you want to create questions?</h3>
                <div class="mode-selection">
                    <div class="mode-card" onclick="selectMode(this, 'manual')">
                        <i class="fas fa-pen-fancy"></i>
                        <h3>Manual <span style="opacity:.7;font-weight:600">(Q&amp;A)</span></h3>
                        <p>Type each MCQ / True-False / Short Answer yourself</p>
                    </div>
                    <div class="mode-card" onclick="selectMode(this, 'practical')">
                        <i class="fas fa-file-pdf"></i>
                        <h3>Upload PDF Practical <span style="opacity:.7;font-weight:600">(Hands-on)</span></h3>
                        <p>Upload one PDF — students download &amp; submit a project link</p>
                    </div>
                    <div class="mode-card" onclick="selectMode(this, 'ai')">
                        <i class="fas fa-wand-magic-sparkles"></i>
                        <h3>Generate with AI <span style="opacity:.7;font-weight:600">(Auto)</span></h3>
                        <p>Auto-generate questions</p>
                    </div>
                </div>

                <button class="btn btn-primary" onclick="goToStep(2)" style="margin-top: 20px;">Next →</button>
            </div>

            <!-- STEP 2: MANUAL QUESTION BUILDER (KAHOOT STYLE) -->
            <div id="step2" class="step-section hidden-section">
                <h2>Add Questions</h2>

                <div class="question-builder">
                    <h3 style="margin-bottom: 15px;">Create Question</h3>

                    <div class="form-group">
                        <label>Question Text *</label>
                        <textarea id="questionText" placeholder="Enter your question here..." rows="3"></textarea>
                    </div>

                    <div class="form-group">
                        <label>Question Type *</label>
                        <div class="question-type-selector">
                            <button class="question-type-btn active" onclick="setQuestionType(this, 'multiple')">
                                <i class="fas fa-list"></i> Multiple Choice
                            </button>
                            <button class="question-type-btn" onclick="setQuestionType(this, 'true-false')">
                                <i class="fas fa-check-circle"></i> True/False
                            </button>
                            <button class="question-type-btn" onclick="setQuestionType(this, 'short-answer')">
                                <i class="fas fa-keyboard"></i> Short Answer
                            </button>
                        </div>
                    </div>

                    <!-- Multiple Choice Options -->
                    <div id="multipleChoiceSection" class="form-group">
                        <label>Options *</label>
                        <div id="optionsList"></div>
                        <button class="btn btn-secondary" onclick="addOption()">+ Add Option</button>

                        <div style="margin-top: 15px;">
                            <label>Correct Answer *</label>
                            <select id="correctAnswer" required>
                                <option value="">Select the correct answer</option>
                            </select>
                        </div>
                    </div>

                    <!-- True/False Section (Hidden by default) -->
                    <div id="trueFalseSection" class="form-group hidden-section">
                        <label>Correct Answer *</label>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                            <button class="btn btn-secondary" onclick="setTrueFalseAnswer(true)" id="btnTrue">True</button>
                            <button class="btn btn-secondary" onclick="setTrueFalseAnswer(false)" id="btnFalse">False</button>
                        </div>
                        <input type="hidden" id="trueFalseAnswer">
                    </div>

                    <!-- Short Answer Section (Hidden by default) -->
                    <div id="shortAnswerSection" class="form-group hidden-section">
                        <label>Sample Correct Answer(s) *</label>
                        <input type="text" id="shortAnswerText" placeholder="Enter the correct answer (you can add variations)">
                        <small style="color: #666;">Students' answers will be graded based on this</small>
                    </div>

                    <div class="form-group">
                        <label for="questionPoints">Points *</label>
                        <input type="number" id="questionPoints" value="1" min="1" max="100">
                    </div>

                    <div style="display:flex; gap:8px;">
                        <button id="addQuestionBtn" class="btn btn-primary" onclick="addQuestion()" style="flex:1;">
                            <i class="fas fa-plus"></i> Add Question to Exam
                        </button>
                        <button id="cancelEditBtn" class="btn btn-gray" onclick="cancelEdit()" style="display:none;">Cancel</button>
                    </div>
                </div>

                <h3 style="margin-top: 30px; margin-bottom: 15px;">Questions Added: <span id="questionCount">0</span></h3>
                <div id="questionsList" class="questions-list"></div>

                <div class="actions" style="margin-top: 30px;">
                    <button class="btn btn-secondary" onclick="goToStep(1)">← Back</button>
                    <button class="btn btn-primary" onclick="goToStep(3)">Review & Publish →</button>
                </div>
            </div>

            <!-- STEP 3: REVIEW & PUBLISH -->
            <div id="step3" class="step-section hidden-section">
                <h2>Review Exam</h2>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Review all questions before publishing
                </div>

                <div id="reviewQuestions"></div>

                <div class="actions">
                    <button class="btn btn-secondary" onclick="goToStep(2)">← Back to Edit</button>
                    <button class="btn btn-danger" onclick="clearAllQuestions()">Clear All</button>
                    <button class="btn btn-success" onclick="publishExam()" style="flex: 1;">
                        <i class="fas fa-paper-plane"></i> Publish Exam
                    </button>
                </div>
            </div>

            <!-- STEP 4: SUCCESS -->
            <div id="step4" class="step-section hidden-section">
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <h2>Exam Published Successfully! 🎉</h2>
                    <p style="color: #666; margin-bottom: 30px;">Your exam has been created and is ready for students</p>

                    <div class="alert alert-success">
                        <strong>Exam ID:</strong> <span id="examIdDisplay"></span>
                    </div>

                    <div class="actions">
                        <button class="btn btn-primary" onclick="viewExam()">View Exam</button>
                        <button class="btn btn-secondary" onclick="createAnother()">Create Another</button>
                        <a href="exams_dashboard.php" class="btn btn-secondary">Go to Dashboard</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="back-link">
            <a href="exams_dashboard.php">← Back to Dashboard</a>
        </div>
    </div>

    <!-- Hidden File Input -->
    <input type="file" id="hiddenFileInput" accept=".xlsx,.xls,.pdf" onchange="handleFileUpload(event)">

    <!-- SheetJS Library for Excel parsing -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

    <script>
        let currentMode = 'manual';
        let currentQuestionType = 'multiple';
        let currentExamOptions = [];
        let allQuestions = [];
    let trueFalseAnswerValue = null;
    // index of question being edited (-1 when adding new)
    let editingQuestionIndex = -1;
    // record last published exam id so "View" can open a friendly page
    let lastPublishedExamId = null;
    // Flag: are we editing an existing exam?
    let isEditingExam = <?= ($editing_exam_id ? 'true' : 'false') ?>;
    let editingExamId = <?= $editing_exam_id ?>;

    // Pre-populate questions if editing
    <?php if ($editing_exam_id && !empty($existing_questions)): ?>
        allQuestions = <?= json_encode($existing_questions) ?>;
    <?php endif; ?>

    // Pre-fill form fields if editing
    <?php if ($existing_exam): ?>
        document.addEventListener('DOMContentLoaded', function() {
            if (isEditingExam) {
                document.getElementById('examTitle').value = '<?= addslashes($existing_exam['title']) ?>';
                document.getElementById('examTopic').value = '<?= addslashes($existing_exam['topic']) ?>';
                document.getElementById('examGrade').value = '<?= addslashes($existing_exam['grade']) ?>';
                document.getElementById('examDuration').value = '<?= (int)$existing_exam['duration'] ?>';
                <?php if (!empty($existing_exam['school_id'])): ?>
                    document.getElementById('examSchool').value = '<?= (int)$existing_exam['school_id'] ?>';
                <?php endif; ?>
                // Go directly to step 2 (questions)
                setTimeout(() => {
                    goToStep(2);
                    displayQuestions();
                }, 100);
            }
        });
    <?php endif; ?>

    // Load schools and certifications on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadSchools();
        loadCertifications();
    });

    // ==================== CERTIFICATION LOADING ====================
    function loadCertifications() {
        const select = document.getElementById('examCertification');
        fetch('../Auth/GTM/get_certifications_list.php')
            .then(r => r.json())
            .then(data => {
                if (data.success && Array.isArray(data.certifications)) {
                    data.certifications.forEach(c => {
                        const opt = document.createElement('option');
                        opt.value = c.certification_id;
                        opt.textContent = c.certification_name;
                        select.appendChild(opt);
                    });
                }
            })
            .catch(() => {});
    }

    // ==================== SCHOOL LOADING ====================
    function loadSchools() {
        const schoolSelect = document.getElementById('examSchool');
        
        fetch('./get_schools_list.php')
            .then(response => response.json())
            .then(data => {
                if (data.success && Array.isArray(data.schools)) {
                    // Clear existing options except the first one
                    schoolSelect.innerHTML = '<option value="">Select Your School</option>';
                    
                    data.schools.forEach(school => {
                        const option = document.createElement('option');
                        option.value = school.school_id;
                        option.textContent = school.school_name;
                        schoolSelect.appendChild(option);
                    });
                } else {
                    schoolSelect.innerHTML = '<option value="">Error loading schools</option>';
                }
            })
            .catch(error => {
                console.error('Error loading schools:', error);
                schoolSelect.innerHTML = '<option value="">Error loading schools</option>';
            });
    }

        // ==================== STEP NAVIGATION ====================
        function goToStep(stepNumber) {
            if (stepNumber === 2 && currentMode === null) {
                alert('Please select a mode first!');
                return;
            }

            if (stepNumber === 3 && allQuestions.length === 0) {
                alert('Please add at least one question before proceeding!');
                return;
            }

            document.querySelectorAll('.step-section').forEach(el => {
                el.classList.remove('active');
                el.classList.add('hidden-section');
            });

            const stepEl = document.getElementById(`step${stepNumber}`);
            if (stepEl) {
                stepEl.classList.add('active');
                stepEl.classList.remove('hidden-section');

                if (stepNumber === 3) {
                    displayReviewQuestions();
                }
            }

            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // ==================== MODE SELECTION ====================
        function selectMode(element, mode) {
            document.querySelectorAll('.mode-card').forEach(el => {
                el.classList.remove('selected');
            });
            element.classList.add('selected');
            currentMode = mode;

            if (mode === 'practical') {
                // Show an immediate visual cue on the card — without this the
                // page looks frozen between click and the OS file picker.
                element.dataset.originalHtml = element.innerHTML;
                element.innerHTML = '<i class="fas fa-spinner fa-spin"></i><h3 style="margin-top:10px">Opening file picker…</h3><p>Choose your practical PDF</p>';
                // Restore the card label if the user dismisses the picker.
                const restore = () => {
                    if (element.dataset.originalHtml) {
                        element.innerHTML = element.dataset.originalHtml;
                        delete element.dataset.originalHtml;
                    }
                    window.removeEventListener('focus', restore);
                };
                window.addEventListener('focus', restore, { once: true });

                // Clear any previous selection so the change event always
                // fires — without this, re-clicking the card after a
                // failed upload silently does nothing because the browser
                // sees the same file already chosen.
                const fileInput = document.getElementById('hiddenFileInput');
                fileInput.value = '';
                fileInput.click();
            } else if (mode === 'ai') {
                showAIPromptDialog();
            }
        }

        // ==================== AI MODE FUNCTIONS ====================
        function showAIPromptDialog() {
            // Get exam details from Step 1
            const examTitle = document.getElementById('examTitle').value || 'Exam';
            const examTopic = document.getElementById('examTopic').value || 'General';
            const examGrade = document.getElementById('examGrade').value || 'Grade 10';

            const html = `
                <div style="background: white; padding: 20px; border-radius: 8px; max-width: 500px;">
                    <h3>Generate Questions with AI</h3>
                    
                    <div style="background: #f0f4f8; padding: 12px; border-radius: 6px; margin-bottom: 15px; border-left: 4px solid #667eea;">
                        <h4 style="margin-bottom: 8px; color: #667eea;">Exam Context:</h4>
                        <p style="margin: 4px 0; font-size: 13px; color: #333;"><strong>Subject:</strong> ${escapeHtml(examTitle)}</p>
                        <p style="margin: 4px 0; font-size: 13px; color: #333;"><strong>Topic:</strong> ${escapeHtml(examTopic)}</p>
                        <p style="margin: 4px 0; font-size: 13px; color: #333;"><strong>Level:</strong> ${escapeHtml(examGrade)}</p>
                        <p style="margin-top: 8px; font-size: 12px; color: #666; font-style: italic;">AI will generate questions matched to this level</p>
                    </div>

                    <div style="margin: 15px 0;">
                        <label style="display: block; margin-bottom: 5px; font-weight: 600;">Number of Questions *</label>
                        <input type="number" id="aiNumQuestions" min="1" max="50" value="10" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    </div>
                    <div style="margin: 15px 0;">
                        <label style="display: block; margin-bottom: 5px; font-weight: 600;">Difficulty Level</label>
                        <select id="aiDifficulty" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                            <option value="easy">Easy (Basic concepts)</option>
                            <option value="medium" selected>Medium (Standard level)</option>
                            <option value="hard">Hard (Advanced concepts)</option>
                        </select>
                    </div>
                    <div style="margin: 15px 0;">
                        <label style="display: block; margin-bottom: 5px; font-weight: 600;">Additional Instructions (Optional)</label>
                        <textarea id="aiInstructions" placeholder="e.g., Include practical examples, focus on calculations, add diagrams..." rows="3" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-family: inherit;"></textarea>
                    </div>
                    <div style="display: flex; gap: 10px; margin-top: 20px;">
                        <button onclick="closeDialog()" style="flex: 1; padding: 10px; border: 1px solid #ddd; background: white; border-radius: 4px; cursor: pointer;">Cancel</button>
                        <button onclick="generateAIQuestions()" style="flex: 1; padding: 10px; background: #667eea; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 600;">Generate</button>
                    </div>
                </div>
            `;
            showDialog(html);
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function showDialog(html) {
            let dialog = document.getElementById('customDialog');
            if (!dialog) {
                dialog = document.createElement('div');
                dialog.id = 'customDialog';
                document.body.appendChild(dialog);
            }
            dialog.innerHTML = `
                <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000;">
                    <div style="background: white; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto;">
                        ${html}
                    </div>
                </div>
            `;
        }

        function closeDialog() {
            const dialog = document.getElementById('customDialog');
            if (dialog) dialog.remove();
            // Reset mode card selection
            document.querySelectorAll('.mode-card').forEach(el => {
                el.classList.remove('selected');
            });
        }

        async function generateAIQuestions() {
            // Get elements with null checks
            const numQuestionsElem = document.getElementById('aiNumQuestions');
            const difficultyElem = document.getElementById('aiDifficulty');
            const instructionsElem = document.getElementById('aiInstructions');
            const examTitleElem = document.getElementById('examTitle');
            const examTopicElem = document.getElementById('examTopic');
            const examGradeElem = document.getElementById('examGrade');

            // Validate all elements exist
            if (!numQuestionsElem || !difficultyElem || !instructionsElem) {
                alert('❌ Error: Dialog elements not found. Please close and try again.');
                closeDialog();
                return;
            }

            const numQuestions = parseInt(numQuestionsElem.value) || 10;
            const difficulty = difficultyElem.value || 'medium';
            const instructions = instructionsElem.value || '';
            const examTitle = (examTitleElem ? examTitleElem.value : '') || 'Exam';
            const examTopic = (examTopicElem ? examTopicElem.value : '') || 'General Knowledge';
            const examGrade = (examGradeElem ? examGradeElem.value : '') || 'Grade 1';

            if (numQuestions < 1 || numQuestions > 50) {
                alert('Number of questions must be between 1 and 50');
                return;
            }

            const prompt = `IMPORTANT: Generate exactly ${numQuestions} quiz questions in JSON format.
These questions are for: ${examGrade} level students
Subject/Topic: ${examTopic}
Exam Title: ${examTitle}
Difficulty Level: ${difficulty.toUpperCase()}
${instructions ? `Special Instructions: ${instructions}` : ''}

CRITICAL REQUIREMENTS:
1. Questions MUST be appropriate for ${examGrade}
2. Complexity MUST match ${difficulty} difficulty
3. Use vocabulary suitable for ${examGrade}
4. Include a mix of: multiple choice, true/false, and short-answer questions
5. All questions must directly relate to: ${examTopic}

Return ONLY valid JSON array with this exact structure:
[
    {
        "text": "Question text here?",
        "type": "multiple|true-false|short-answer",
        "points": 10,
        "options": ["Option 1", "Option 2", "Option 3", "Option 4"] (for multiple choice only),
        "correctAnswer": 0 (for multiple: index 0-3, for true-false: true or false, for short-answer: string answer)
    }
]

Examples of difficulty levels:
- Easy: Basic definitions, recall questions, simple concepts
- Medium: Application of concepts, some reasoning required
- Hard: Complex analysis, synthesis, critical thinking required

Return ONLY the JSON array, no explanation, no markdown, no code blocks.`;

            // Show loading
            let dialog = document.getElementById('customDialog');
            dialog.innerHTML = `
                <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000;">
                    <div style="background: white; border-radius: 12px; padding: 40px; text-align: center;">
                        <div style="font-size: 48px; margin-bottom: 20px;">⏳</div>
                        <h3>Generating Questions...</h3>
                        <p>This may take a moment</p>
                    </div>
                </div>
            `;

            try {
                const response = await fetch('generateexams.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ prompt: prompt })
                });

                const data = await response.json();
                
                console.log('API Response:', data); // Debug log

                if (!data.success) {
                    throw new Error(data.error || 'API returned success: false');
                }
                
                if (!Array.isArray(data.data)) {
                    console.error('data.data is not an array:', data.data);
                    throw new Error('API did not return questions array');
                }
                
                if (data.data.length === 0) {
                    throw new Error('API returned empty questions array');
                }

                // Process questions and add to allQuestions
                let addedCount = 0;
                let skippedCount = 0;
                
                console.log('Questions received:', data.data); // Debug log
                
                data.data.forEach((q, idx) => {
                    // Validate question has required fields
                    if (!q.text || !q.type) {
                        console.warn(`Question ${idx} skipped - missing text or type:`, q);
                        skippedCount++;
                        return;
                    }

                    const question = {
                        id: Date.now() + Math.random(),
                        type: q.type.toLowerCase().trim(),
                        text: q.text,
                        points: q.points || 10,
                        correctAnswer: q.correctAnswer
                    };

                    if (q.type === 'multiple' && Array.isArray(q.options)) {
                        question.options = q.options;
                    }

                    allQuestions.push(question);
                    addedCount++;
                    console.log(`Added question ${addedCount}:`, question); // Debug log
                });

                console.log('Final totals - Added:', addedCount, 'Skipped:', skippedCount); // Debug log

                closeDialog();
                displayQuestions();
                goToStep(2);


                alert(`✅ Successfully added ${addedCount} AI-generated questions!`);

                if (data.warning) {
                    console.warn('AI Warning:', data.warning);
                }

            } catch (error) {
                console.error('AI Generation Error:', error);
                closeDialog();
                alert(`❌ Error generating questions: ${error.message}`);
                
                // Reset mode selection
                document.querySelectorAll('.mode-card').forEach(el => {
                    el.classList.remove('selected');
                });
                currentMode = null;
            }
        }

        // ==================== QUESTION TYPE SELECTION ====================
        function setQuestionType(element, type) {
            document.querySelectorAll('.question-type-btn').forEach(el => {
                el.classList.remove('active');
            });
            element.classList.add('active');
            currentQuestionType = type;

            // Show/hide relevant sections
            document.getElementById('multipleChoiceSection').classList.add('hidden-section');
            document.getElementById('trueFalseSection').classList.add('hidden-section');
            document.getElementById('shortAnswerSection').classList.add('hidden-section');

            if (type === 'multiple') {
                document.getElementById('multipleChoiceSection').classList.remove('hidden-section');
            } else if (type === 'true-false') {
                document.getElementById('trueFalseSection').classList.remove('hidden-section');
            } else if (type === 'short-answer') {
                document.getElementById('shortAnswerSection').classList.remove('hidden-section');
            }
        }

        // Programmatic helper to set question type when we don't have a button element
        function setQuestionTypeUI(type) {
            const buttons = document.querySelectorAll('.question-type-btn');
            buttons.forEach(btn => {
                const onclick = (btn.getAttribute('onclick') || '');
                if (onclick.indexOf("'" + type + "'") !== -1 || onclick.indexOf('"' + type + '"') !== -1) {
                    setQuestionType(btn, type);
                }
            });
        }

        // ==================== OPTIONS MANAGEMENT ====================
        function addOption() {
            const index = currentExamOptions.length;
            currentExamOptions.push('');

            const optionsList = document.getElementById('optionsList');
            const optionDiv = document.createElement('div');
            optionDiv.className = 'option-input';
            optionDiv.innerHTML = `
                <input type="text" placeholder="Option ${index + 1}" id="option${index}" onchange="updateOption(${index}, this.value)">
                <button type="button" onclick="removeOption(${index})">
                    <i class="fas fa-trash"></i> Delete
                </button>
            `;
            optionsList.appendChild(optionDiv);

            updateCorrectAnswerSelect();
        }

        function removeOption(index) {
            currentExamOptions.splice(index, 1);
            renderOptions();
            updateCorrectAnswerSelect();
        }

        function updateOption(index, value) {
            currentExamOptions[index] = value;
            updateCorrectAnswerSelect();
        }

        function renderOptions() {
            const optionsList = document.getElementById('optionsList');
            optionsList.innerHTML = '';
            currentExamOptions.forEach((option, index) => {
                const optionDiv = document.createElement('div');
                optionDiv.className = 'option-input';
                optionDiv.innerHTML = `
                    <input type="text" placeholder="Option ${index + 1}" value="${option}" onchange="updateOption(${index}, this.value)">
                    <button type="button" onclick="removeOption(${index})">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                `;
                optionsList.appendChild(optionDiv);
            });
        }

        function updateCorrectAnswerSelect() {
            const select = document.getElementById('correctAnswer');
            const previous = select.value;
            select.innerHTML = '<option value="">Select the correct answer</option>';
            currentExamOptions.forEach((option, index) => {
                const opt = document.createElement('option');
                opt.value = index;
                opt.textContent = option || `Option ${index + 1}`;
                select.appendChild(opt);
            });
            // Preserve the user's previous correct selection across re-renders.
            if (previous !== '' && Number(previous) < currentExamOptions.length) {
                select.value = previous;
            }
            highlightCorrectOption();
        }

        // Visually mark which option is the correct answer.
        function highlightCorrectOption() {
            const select = document.getElementById('correctAnswer');
            const idx = select.value;
            document.querySelectorAll('#optionsList .option-input').forEach((row, i) => {
                if (idx !== '' && Number(idx) === i) row.classList.add('correct');
                else row.classList.remove('correct');
            });
        }

        document.addEventListener('change', (e) => {
            if (e.target && e.target.id === 'correctAnswer') {
                highlightCorrectOption();
            }
        });

        // ==================== TRUE/FALSE HANDLING ====================
        function setTrueFalseAnswer(value) {
            trueFalseAnswerValue = value;
            document.getElementById('trueFalseAnswer').value = value;

            const t = document.getElementById('btnTrue');
            const f = document.getElementById('btnFalse');
            const correct = value ? t : f;
            const other   = value ? f : t;

            // Selected → green with check, unselected → muted glass with cross.
            const correctText = (value ? 'True' : 'False') + ' ✓ Correct';
            const otherText   = (value ? 'False' : 'True');
            correct.innerHTML = correctText;
            other.innerHTML   = otherText;

            correct.classList.add('active');
            correct.style.background    = 'linear-gradient(135deg,#16a34a,#22c55e)';
            correct.style.color         = '#ffffff';
            correct.style.fontWeight    = '800';
            correct.style.borderColor   = 'rgba(34,197,94,.6)';
            correct.style.boxShadow     = '0 6px 20px rgba(34,197,94,.4)';

            other.classList.remove('active');
            other.style.background    = 'rgba(15,15,40,.6)';
            other.style.color         = '#94a3b8';
            other.style.fontWeight    = '700';
            other.style.borderColor   = 'rgba(168,85,247,.25)';
            other.style.boxShadow     = 'none';
        }

        // ==================== ADD QUESTION ====================
        function addQuestion() {
            const text = document.getElementById('questionText').value.trim();
            const points = parseInt(document.getElementById('questionPoints').value) || 1;

            if (!text) {
                alert('Please enter a question!');
                return;
            }

            let question = {
            id: (editingQuestionIndex !== -1 ? allQuestions[editingQuestionIndex].id : Date.now()),
            type: currentQuestionType,
            text: text,
            points: points
};

            // Type-specific validation
            if (currentQuestionType === 'multiple') {
                const correctAnswerIndex = document.getElementById('correctAnswer').value;
                if (!correctAnswerIndex && correctAnswerIndex !== '0') {
                    alert('Please select the correct answer!');
                    return;
                }
                if (currentExamOptions.length < 2) {
                    alert('Please add at least 2 options!');
                    return;
                }

                question.options = [...currentExamOptions];
                question.correctAnswer = parseInt(correctAnswerIndex);

            } else if (currentQuestionType === 'true-false') {
                if (trueFalseAnswerValue === null) {
                    alert('Please select the correct answer (True or False)!');
                    return;
                }
                question.correctAnswer = trueFalseAnswerValue;

            } else if (currentQuestionType === 'short-answer') {
                const answer = document.getElementById('shortAnswerText').value.trim();
                if (!answer) {
                    alert('Please enter the correct answer!');
                    return;
                }
                question.correctAnswer = answer;
            }

            if (editingQuestionIndex !== -1) {
                // update existing
                allQuestions[editingQuestionIndex] = question;
                editingQuestionIndex = -1;
            } else {
                allQuestions.push(question);
            }
            displayQuestions();
            clearQuestionForm();
            currentExamOptions = [];
            trueFalseAnswerValue = null;
        }

        function clearQuestionForm() {
            document.getElementById('questionText').value = '';
            document.getElementById('questionPoints').value = '1';
            document.getElementById('shortAnswerText').value = '';
            document.getElementById('correctAnswer').value = '';
            currentExamOptions = [];
            renderOptions();
            trueFalseAnswerValue = null;
            editingQuestionIndex = -1;
            // restore add button text
            const addBtn = document.getElementById('addQuestionBtn');
            if (addBtn) addBtn.textContent = 'Add Question';
            const cancelBtn = document.getElementById('cancelEditBtn');
            if (cancelBtn) cancelBtn.style.display = 'none';
        }

        function displayQuestions() {
            const list = document.getElementById('questionsList');
            list.innerHTML = '';
            document.getElementById('questionCount').textContent = allQuestions.length;

            allQuestions.forEach((q, index) => {
                const div = document.createElement('div');
                div.className = 'question-card';
                if (typeof editingQuestionIndex !== 'undefined' && index === editingQuestionIndex) {
                    div.classList.add('editing');
                }
                let typeIcon = '📋';
                if (q.type === 'true-false') typeIcon = '✓✗';
                if (q.type === 'short-answer') typeIcon = '✍️';

                div.innerHTML = `
                    <div style="display: flex; justify-content: space-between; align-items: start;">
                        <div style="flex: 1; cursor: pointer;" onclick="editQuestion(${index})">
                            <h4>${typeIcon} Q${index + 1}: ${q.text}</h4>
                            <p style="margin-top:4px; font-size:12px; color:#888;">Click this area to edit the question</p>
                            <p><strong>Type:</strong> ${q.type}</p>
                            <p><strong>Points:</strong> ${q.points}</p>
                            ${q.type === 'multiple' ? `<p><strong>Options:</strong> ${q.options.join(', ')}</p>` : ''}
                            ${q.type === 'true-false' ? `<p><strong>Answer:</strong> ${q.correctAnswer ? 'True' : 'False'}</p>` : ''}
                            ${q.type === 'short-answer' ? `<p><strong>Answer:</strong> ${q.correctAnswer}</p>` : ''}
                        </div>
                        <div style="display:flex; gap:6px; margin-left:10px;">
                            <button class="btn btn-secondary" onclick="editQuestion(${index})" style="white-space: nowrap;">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn btn-danger" onclick="removeQuestion(${index})" style="white-space: nowrap;">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                `;
                list.appendChild(div);
            });
        }

        // Populate the question form with existing question data for editing
        function editQuestion(index) {
            const q = allQuestions[index];
            if (!q) return;
            editingQuestionIndex = index;
            // Populate fields
            document.getElementById('questionText').value = q.text || '';
            document.getElementById('questionPoints').value = q.points || 1;
            // set type and UI
            setQuestionTypeUI(q.type || 'multiple');
            currentQuestionType = q.type || 'multiple';
            if (q.type === 'multiple') {
                currentExamOptions = Array.isArray(q.options) ? [...q.options] : [];
                renderOptions();
                updateCorrectAnswerSelect(); // ← populate dropdown FIRST
                document.getElementById('correctAnswer').value = (typeof q.correctAnswer !== 'undefined') ? q.correctAnswer : '';
            } else if (q.type === 'true-false') {
                trueFalseAnswerValue = !!q.correctAnswer;
                setTrueFalseAnswer(trueFalseAnswerValue);
            } else if (q.type === 'short-answer') {
                document.getElementById('shortAnswerText').value = q.correctAnswer || '';
            }
            // change add button text and highlight editing question
            const addBtn = document.getElementById('addQuestionBtn');
            if (addBtn) addBtn.textContent = 'Save Changes';
            const cancelBtn = document.getElementById('cancelEditBtn');
            if (cancelBtn) cancelBtn.style.display = 'inline-block';
            // update question list highlighting and go to question step
            displayQuestions();
            goToStep(2);
        }

        function cancelEdit() {
            editingQuestionIndex = -1;
            clearQuestionForm();
            // refresh list and return UI focus to question list
            displayQuestions();
            const list = document.getElementById('questionsList');
            if (list) list.scrollIntoView({behavior: 'smooth', block: 'start'});
        }

        function removeQuestion(index) {
            allQuestions.splice(index, 1);
            displayQuestions();
        }

        function clearAllQuestions() {
            if (confirm('Are you sure you want to delete all questions?')) {
                allQuestions = [];
                displayQuestions();
            }
        }

        // ==================== REVIEW & PUBLISH ====================
        function displayReviewQuestions() {
            const reviewDiv = document.getElementById('reviewQuestions');
            reviewDiv.innerHTML = '';

            allQuestions.forEach((q, index) => {
                const div = document.createElement('div');
                div.className = 'question-preview';
                let typeIcon = '📋';
                if (q.type === 'true-false') typeIcon = '✓✗';
                if (q.type === 'short-answer') typeIcon = '✍️';

                let content = `
                    <h5>${typeIcon} Question ${index + 1} (${q.points} points)</h5>
                    <p><strong>${q.text}</strong></p>
                `;

                if (q.type === 'multiple') {
                    content += `<ul>`;
                    q.options.forEach((opt, i) => {
                        const isCorrect = i === q.correctAnswer ? ' ✓ (Correct)' : '';
                        content += `<li>${opt}${isCorrect}</li>`;
                    });
                    content += `</ul>`;
                } else if (q.type === 'true-false') {
                    content += `<p>A) True ${q.correctAnswer ? ' ✓ (Correct)' : ''}</p>`;
                    content += `<p>B) False ${!q.correctAnswer ? ' ✓ (Correct)' : ''}</p>`;
                } else if (q.type === 'short-answer') {
                    content += `<p>Expected Answer: <strong>${q.correctAnswer}</strong></p>`;
                }

                div.innerHTML = content;
                reviewDiv.appendChild(div);
            });
        }

        async function publishExam() {
            // Get values with fallbacks - they may not exist if called from Step 4
            const titleElem = document.getElementById('examTitle');
            const topicElem = document.getElementById('examTopic');
            const gradeElem = document.getElementById('examGrade');
            const durationElem = document.getElementById('examDuration');
            const schoolElem = document.getElementById('examSchool');
            const passingScoreElem = document.getElementById('examPassingScore');
            const certificationElem = document.getElementById('examCertification');

            const title = titleElem ? titleElem.value.trim() : 'Exam';
            const topic = topicElem ? topicElem.value.trim() : 'General';
            const grade = gradeElem ? gradeElem.value.trim() : '10';
            const duration = durationElem ? parseInt(durationElem.value) : 60;
            const school_id = schoolElem ? parseInt(schoolElem.value) : 0;
            const passing_score = passingScoreElem ? (parseInt(passingScoreElem.value) || 50) : 50;
            const exam_certification = certificationElem ? (parseInt(certificationElem.value) || 0) : 0;
            const status = 'active';

            if (!title || title === 'Exam') {
                alert('Please go back and enter an exam title!');
                return;
            }

            if (!school_id) {
                alert('Please select a school!');
                return;
            }

            if (allQuestions.length === 0) {
                alert('Please add at least one question!');
                return;
            }

            console.log('Publishing exam with data:', {
                title, topic, grade, duration, school_id, status,
                questionCount: allQuestions.length,
                questions: allQuestions,
                isEdit: isEditingExam,
                examId: editingExamId
            });

            try {
                const response = await fetch('publish_exam.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        exam_id: isEditingExam ? editingExamId : null,
                        title: title,
                        topic: topic,
                        grade: grade,
                        duration: duration,
                        school_id: school_id,
                        passing_score: passing_score,
                        exam_certification: exam_certification || null,
                        questions: allQuestions
                    })
                });

                console.log('Response status:', response.status);
                const responseText = await response.text();
                console.log('Response text:', responseText);
                
                let data;
                try {
                    data = JSON.parse(responseText);
                } catch (e) {
                    throw new Error('Server returned invalid JSON: ' + responseText.substring(0, 200));
                }

                if (data.success) {
                    document.getElementById('examIdDisplay').textContent = data.exam_code + ' (ID: ' + data.exam_id + ')';
                    // store last published id for quick view
                    lastPublishedExamId = data.exam_id;
                    goToStep(4);
                    const msg = isEditingExam ? '✅ Exam updated and published successfully!' : '✅ Exam published successfully!';
                    alert(msg);
                } else {
                    alert('❌ Error publishing exam: ' + data.error);
                    console.error('API Error:', data);
                }
            } catch (error) {
                console.error('Publish Error:', error);
                alert('❌ Error publishing exam. Check console for details.\n\n' + error.message);
            }
        }

        function viewExam() {
            // If we have the last published exam id, open the friendly viewer
            if (lastPublishedExamId) {
                window.location.href = `view_exam.php?exam_id=${lastPublishedExamId}`;
                return;
            }
            // Fallback
            window.location.href = 'exams_dashboard.php';
        }

        function createAnother() {
            allQuestions = [];
            currentExamOptions = [];
            trueFalseAnswerValue = null;
            document.getElementById('examTitle').value = '';
            document.getElementById('examDescription').value = '';
            document.getElementById('examDuration').value = '60';
            clearQuestionForm();
            displayQuestions();
            goToStep(1);
        }

        // ==================== FILE UPLOAD ====================
        function handleFileUpload(event) {
            const file = event.target.files[0];
            if (!file) return;

            // Check if it's a PDF (for practical exams)
            if (file.type === 'application/pdf' || file.name.endsWith('.pdf')) {
                uploadPracticalPDFToBunny(file);
            } else {
                // Handle Excel files (legacy Excel upload removed - now only PDF practical exams)
                alert('❌ Only PDF files are supported for practical exams.\n\nFor traditional Q&A exams, use Manual mode to add questions.');
                // Reset
                document.querySelectorAll('.mode-card').forEach(el => {
                    el.classList.remove('selected');
                });
                currentMode = null;
            }
        }

        // ==================== PRACTICAL PDF UPLOAD TO BUNNY ====================
        function uploadPracticalPDFToBunny(file) {
            const schoolId = document.getElementById('examSchool').value;
            const examTitle = (document.getElementById('examTitle').value || '').trim();
            if (!schoolId || !examTitle) {
                alert('❌ Please fill in the exam title and select a school before uploading the PDF.');
                document.querySelectorAll('.mode-card').forEach(el => {
                    el.classList.remove('selected');
                });
                currentMode = null;
                // Reset the file input so picking the same PDF again still fires change.
                document.getElementById('hiddenFileInput').value = '';
                return;
            }

            // Show progress
            const originalHtml = document.querySelector('.mode-card.selected').innerHTML;
            document.querySelector('.mode-card.selected').innerHTML = '<i class="fas fa-spinner fa-spin"></i><p style="margin-top: 10px;">Uploading to Bunny...</p>';

            const formData = new FormData();
            formData.append('pdf_file', file);
            formData.append('school_id', schoolId);

            fetch('./upload_to_bunny.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('PDF uploaded successfully:', data);
                    
                    // Create a practical exam question with the PDF URL
                    const practicalQuestion = {
                        id: Date.now(),
                        type: 'practical',
                        text: `Practical Exam - PDF Link: ${data.filename}`,
                        points: 100,
                        correctAnswer: data.url,  // Store the PDF URL
                        bunny_url: data.url,
                        pdf_filename: data.filename,
                        storage: data.storage,
                        options: [data.url]  // Store URL as option for consistency
                    };

                    allQuestions = [practicalQuestion];
                    displayQuestions();
                    goToStep(2);
                    
                    // Show appropriate message based on storage location
                    const storageType = data.storage === 'bunny' ? 'Bunny CDN' : 'Local Storage';
                    const message = data.storage === 'bunny' 
                        ? `✅ PDF uploaded to Bunny CDN successfully!\n\nFilename: ${data.filename}\nURL: ${data.url}`
                        : `✅ PDF uploaded successfully!\n\nStored in: ${storageType}\nFilename: ${data.filename}\nURL: ${data.url}\n\nNote: Using local storage (Bunny CDN not accessible from this network)`;
                    alert(message);
                } else {
                    alert(`❌ Upload failed: ${data.error}`);
                    document.querySelector('.mode-card.selected').innerHTML = originalHtml;
                    document.querySelectorAll('.mode-card').forEach(el => {
                        el.classList.remove('selected');
                    });
                    currentMode = null;
                    document.getElementById('hiddenFileInput').value = '';
                }
            })
            .catch(error => {
                alert(`❌ Upload error: ${error.message}`);
                document.querySelector('.mode-card.selected').innerHTML = originalHtml;
                document.querySelectorAll('.mode-card').forEach(el => {
                    el.classList.remove('selected');
                });
                currentMode = null;
                document.getElementById('hiddenFileInput').value = '';
            });
        }

        function parseExcelQuestion(row) {
            // Handle Excel with merged cells and complex layout
            // Look for questions that start with "Q1:", "Q2:", etc in the __empty or __EMPTY column
            
            const normalizedRow = {};
            for (let key in row) {
                normalizedRow[key.toLowerCase().trim()] = row[key];
            }

            console.log('Parsing row:', row); // DEBUG

            // Skip header/instruction rows
            if (!row || typeof row !== 'object') return null;
            
            // Look for question pattern "Q1: ...", "Q2: ..." etc in __empty, __EMPTY columns
            let text = null;
            let questionMatch = null;
            
            for (let key in row) {
                const val = (row[key] || '').toString().trim();
                // Match pattern like "Q1:", "Q2:", "Q3:" etc
                if (val.match(/^Q\d+:\s+.+/)) {
                    text = val;
                    break;
                }
            }

            if (!text) {
                console.log('No question pattern found');
                return null; // Skip this row
            }

            // Extract question number and clean text
            questionMatch = text.match(/^Q(\d+):\s+(.+)$/);
            if (!questionMatch) return null;
            
            const questionNum = parseInt(questionMatch[1]);
            text = questionMatch[2].trim();

            console.log('Found question:', text);

            // Default to multiple choice since your file is MCQ based
            const question = {
                id: Date.now() + Math.random(),
                type: 'multiple',
                text: text,
                points: 1
            };

            return question;
        }
        
        function parseExcelWithOptions(rows) {
            // Enhanced parser that groups options with questions
            const questions = [];
            let currentQuestion = null;
            let currentOptions = [];

            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const vals = Object.values(row);
                const rowText = vals.join(' ').trim();

                // Look for question pattern "Q1: ...", "Q2: ..." etc
                const questionMatch = rowText.match(/Q(\d+):\s+(.+)/);
                if (questionMatch) {
                    // Save previous question if exists
                    if (currentQuestion && currentOptions.length >= 2) {
                        currentQuestion.options = currentOptions.map(opt => opt.replace(/^[A-D]\.\s*/, '').trim());
                        currentQuestion.correctAnswer = 0; // Default to first option
                        questions.push(currentQuestion);
                    }

                    // Start new question
                    currentQuestion = {
                        id: Date.now() + Math.random() + i,
                        type: 'multiple',
                        text: questionMatch[2].trim(),
                        points: 1
                    };
                    currentOptions = [];
                } else if (currentQuestion && rowText.match(/^[A-D]\.\s+.+/)) {
                    // This is an option line
                    const optText = Object.values(row)[0] || rowText;
                    if (optText) {
                        currentOptions.push(optText.toString().trim());
                    }
                }
            }

            // Don't forget last question
            if (currentQuestion && currentOptions.length >= 2) {
                currentQuestion.options = currentOptions.map(opt => opt.replace(/^[A-D]\.\s*/, '').trim());
                currentQuestion.correctAnswer = 0;
                questions.push(currentQuestion);
            }

            return questions;
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            // Add initial option inputs for multiple choice
            addOption();
            addOption();
            // Ensure any existing questions are rendered and JS is loaded
            try {
                displayQuestions();
                console.log('Exam Creator: JS initialized and displayQuestions called');
            } catch (e) {
                console.warn('Exam Creator init: displayQuestions not available yet', e);
            }
        });
    </script>
</body>
</html>

<?php if(isset($conn)) $conn->close(); ?>
