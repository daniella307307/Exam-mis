<?php
header("Content-Type: application/json");
include('../db.php');

// Get request data
$input = json_decode(file_get_contents("php://input"), true);
$exam_id = $input['exam_id'] ?? null;
$action = $input['action'] ?? 'activate'; // 'activate' or 'deactivate'
$start_now = $input['start_now'] ?? true; // Start immediately or schedule

if (!$exam_id) {
    echo json_encode([
        "success" => false,
        "error" => "exam_id required"
    ]);
    exit;
}

// Get exam details first
$check = $conn->prepare("SELECT exam_id, status, start_time FROM exams WHERE exam_id = ?");
$check->bind_param("i", $exam_id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        "success" => false,
        "error" => "Exam not found"
    ]);
    exit;
}

$exam = $result->fetch_assoc();

if ($action === 'activate') {
    // Set status to active
    $new_status = 'active';
    
    // Determine start time
    if ($start_now) {
        // Start immediately
        $start_time = date('Y-m-d H:i:s');
    } else {
        // Use existing start_time or schedule for 1 hour from now
        $start_time = $exam['start_time'] ?: date('Y-m-d H:i:s', strtotime('+1 hour'));
    }
    
    // Update exam status
    $update = $conn->prepare("UPDATE exams SET status = ?, start_time = ?, is_active = 1 WHERE exam_id = ?");
    $update->bind_param("ssi", $new_status, $start_time, $exam_id);
    
    if ($update->execute()) {
        echo json_encode([
            "success" => true,
            "message" => "Exam activated successfully",
            "status" => "active",
            "start_time" => $start_time,
            "exam_id" => $exam_id
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "error" => "Failed to activate exam: " . $update->error
        ]);
    }
} 
elseif ($action === 'deactivate') {
    // Set status back to draft
    $new_status = 'draft';
    
    $update = $conn->prepare("UPDATE exams SET status = ?, is_active = 0 WHERE exam_id = ?");
    $update->bind_param("si", $new_status, $exam_id);
    
    if ($update->execute()) {
        echo json_encode([
            "success" => true,
            "message" => "Exam deactivated successfully",
            "status" => "draft",
            "exam_id" => $exam_id
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "error" => "Failed to deactivate exam: " . $update->error
        ]);
    }
}
else {
    echo json_encode([
        "success" => false,
        "error" => "Invalid action. Use 'activate' or 'deactivate'"
    ]);
}
?>

<?php $conn->close(); ?>
