<?php
session_start();
include('../db.php');
header('Content-Type: application/json');
 
$exam_id = $_SESSION['exam_id'] ?? 0;
 
$stmt = $conn->prepare("SELECT status FROM exams WHERE exam_id = ?");
$stmt->bind_param("i", $exam_id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
 
echo json_encode(['status' => $row['status'] ?? 'waiting']);
?>