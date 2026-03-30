<?php
session_start();
include('../db.php');
header('Content-Type: application/json');
 
$exam_id = $_SESSION['exam_id'] ?? 0;
 
$stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM players WHERE exam_id = ?");
$stmt->bind_param("i", $exam_id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
 
echo json_encode(['count' => (int)$row['cnt']]);
?>