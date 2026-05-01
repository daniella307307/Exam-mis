<?php
session_start();
include("../db.php");
header('Content-Type: application/json');

$exam_id = (int)($_POST['exam_id'] ?? 0);
$label   = trim($_POST['label'] ?? 'Class Session');
$user_id = (int)($_SESSION['user_id'] ?? 0);

if (!$exam_id) {
    echo json_encode(['success' => false, 'error' => 'Exam ID required']);
    exit;
}

// Generate new unique code
do {
    $new_code = rand(100000, 999999);
    $check = $conn->query("SELECT 1 FROM exam_sessions WHERE session_code = $new_code");
} while ($check && $check->num_rows > 0);

$new_pin = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);

// Deactivate previous sessions for this exam (optional - keeps old ones for records)
$conn->query("UPDATE exam_sessions SET is_active = 0 WHERE exam_id = $exam_id");

// Create new session
$stmt = $conn->prepare("
    INSERT INTO exam_sessions (exam_id, session_code, session_pin, session_label, created_by)
    VALUES (?, ?, ?, ?, ?)
");
$stmt->bind_param("iissi", $exam_id, $new_code, $new_pin, $label, $user_id);

if ($stmt->execute()) {
    $session_id = $stmt->insert_id;
    
    // Also update the main exam's code (so join_exam.php still works)
    $conn->query("UPDATE exams SET exam_code = $new_code, pin = '$new_pin', is_active = 1, status = 'active' WHERE exam_id = $exam_id");
    
    echo json_encode([
        'success' => true, 
        'session_id' => $session_id,
        'code' => $new_code, 
        'pin' => $new_pin,
        'label' => $label
    ]);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}

if (isset($conn) && $conn instanceof mysqli) { $conn->close(); }
