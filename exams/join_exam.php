<?php
session_start();
include("../db.php");

// Clear old exam session data when entering join_exam
unset($_SESSION['exam_id']);
unset($_SESSION['exam_title']);
unset($_SESSION['player_id']);
unset($_SESSION['nickname']);

$error = '';
$exam_code = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $exam_code = trim($_POST['exam_code'] ?? '');
    
    if (empty($exam_code)) {
        $error = 'Please enter an exam code';
    } else {
        // First check exam_sessions (for republish codes)
$sess_stmt = $conn->prepare("
    SELECT es.session_id, es.exam_id, es.session_label, e.title, e.status 
    FROM exam_sessions es 
    JOIN exams e ON es.exam_id = e.exam_id 
    WHERE es.session_code = ? AND es.is_active = 1
");
$sess_stmt->bind_param("i", $exam_code);
$sess_stmt->execute();
$sess_result = $sess_stmt->get_result();

if ($sess_result->num_rows > 0) {
    $session = $sess_result->fetch_assoc();
    $_SESSION['exam_id'] = $session['exam_id'];
    $_SESSION['session_id'] = $session['session_id'];
    $_SESSION['session_label'] = $session['session_label'];
    $_SESSION['exam_title'] = $session['title'];

    $placeholder = '';
    $exam_id_val = (int) $session['exam_id'];
    $session_id_val = (int) $session['session_id'];
    $ins = $conn->prepare("INSERT INTO players (exam_id, nickname, session_id) VALUES (?, ?, ?)");
    $ins->bind_param("isi", $exam_id_val, $placeholder, $session_id_val);
    $ins->execute();
    $_SESSION['player_id'] = $conn->insert_id;

    $conn->close();
    header("Location: add_name.php");
    exit();
}

// Fallback: check main exams table (for exams without republish)
$stmt = $conn->prepare("SELECT exam_id, title, status FROM exams WHERE exam_code = ?");
$stmt->bind_param("i", $exam_code);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $error = 'Invalid exam code. Please check and try again.';
} else {
    $exam = $result->fetch_assoc();

    if ($exam['status'] !== 'active') {
        $error = 'This exam is not currently available.';
    } else {
        $_SESSION['exam_id'] = $exam['exam_id'];
        $_SESSION['session_id'] = null;
        $_SESSION['exam_title'] = $exam['title'];

        $placeholder = '';
        $exam_id_val = (int) $exam['exam_id'];
        $ins = $conn->prepare("INSERT INTO players (exam_id, nickname) VALUES (?, ?)");
        $ins->bind_param("is", $exam_id_val, $placeholder);
        $ins->execute();
        $_SESSION['player_id'] = $conn->insert_id;

        $conn->close();
        header("Location: add_name.php");
        exit();
    }
}
        $stmt->close();
        $conn->close();
    }
}

// Close connection for GET requests
if (isset($conn) && $conn instanceof mysqli) { $conn->close(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join Exam</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Nunito', 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #0d0d2b, #1e1b4b);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: #f1f5f9;
            position: relative;
            overflow: hidden;
        }
        body::before, body::after {
            content: ''; position: fixed; border-radius: 50%;
            filter: blur(100px); opacity: .25; pointer-events: none;
        }
        body::before { width: 500px; height: 500px; background: #7c3aed; top: -150px; left: -120px; }
        body::after  { width: 420px; height: 420px; background: #06b6d4; bottom: -150px; right: -120px; }

        .container {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(168,85,247,0.3);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 20px;
            box-shadow: 0 25px 80px rgba(0,0,0,0.5);
            max-width: 500px;
            width: 100%;
            padding: 44px 40px;
            position: relative;
            z-index: 1;
        }

        .header { text-align: center; margin-bottom: 32px; }

        .header h1 {
            font-size: 30px;
            font-weight: 900;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #facc15, #f97316);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .header p { color: #94a3b8; font-size: 14px; }

        .form-group { margin-bottom: 20px; }

        label {
            display: block;
            color: #cbd5e1;
            font-weight: 700;
            margin-bottom: 10px;
            font-size: 11px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }

        input[type="text"] {
            width: 100%;
            padding: 14px 18px;
            background: rgba(15,15,40,.6);
            border: 1.5px solid rgba(168,85,247,.25);
            border-radius: 12px;
            font-size: 18px;
            font-weight: 700;
            color: #f1f5f9;
            font-family: 'Nunito', sans-serif;
            letter-spacing: 4px;
            text-align: center;
            outline: none;
            transition: border-color .2s, background .2s, box-shadow .2s;
        }

        input[type="text"]:focus {
            border-color: #a855f7;
            background: rgba(15,15,40,.85);
            box-shadow: 0 0 0 4px rgba(168,85,247,.18);
        }

        input[type="text"]::placeholder { color: #64748b; letter-spacing: 1px; }

        .error {
            background: rgba(239,68,68,.12);
            color: #fca5a5;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            border: 1px solid rgba(239,68,68,.4);
            text-align: center;
        }

        button {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #7c3aed, #a855f7);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 17px;
            font-weight: 900;
            font-family: 'Nunito', sans-serif;
            cursor: pointer;
            transition: transform .15s, box-shadow .15s;
            box-shadow: 0 8px 24px rgba(124,58,237,.4);
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(124,58,237,.5);
        }

        button:active { transform: translateY(0); }

        .info {
            background: rgba(124,58,237,.12);
            padding: 16px 18px;
            border-radius: 12px;
            margin-top: 24px;
            font-size: 13px;
            color: #cbd5e1;
            border: 1px solid rgba(168,85,247,.25);
            line-height: 1.6;
        }
        .info strong { color: #facc15; }

        .back-link { text-align: center; margin-top: 22px; }

        .back-link a {
            color: #a855f7;
            text-decoration: none;
            font-size: 14px;
            font-weight: 700;
            transition: color .15s;
        }

        .back-link a:hover { color: #c084fc; text-decoration: underline; }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;900&display=swap" rel="stylesheet">
</head>
<body>
    <?php $back_to = 'index.php'; $back_label = 'Home'; include('nav_back.php'); ?>
    <div class="container">
        <div class="header">
            <h1>📝 Join Exam</h1>
            <p>Enter your exam code to get started</p>
        </div>
        
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="exam_code">Exam Code</label>
                <input 
                    type="text" 
                    id="exam_code" 
                    name="exam_code" 
                    placeholder="Enter 6-digit exam code"
                    value="<?= htmlspecialchars($exam_code) ?>"
                    maxlength="10"
                    autofocus
                    required
                >
            </div>
            
            <button type="submit">Enter Exam</button>
        </form>
        
        <div class="info">
            <strong>💡 Tip:</strong> Your instructor will provide you with a 6-digit exam code. Enter it above to begin the exam.
        </div>
        
        <div class="back-link">
            <a href="index.php">← Back to Home</a>
        </div>
    </div>
</body>
</html>
