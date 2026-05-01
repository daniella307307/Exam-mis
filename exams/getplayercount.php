<?php
session_start();
include('../db.php');
header('Content-Type: application/json');

$exam_id    = (int)($_GET['eid'] ?? $_SESSION['exam_id'] ?? 0);
$session_id = $_SESSION['session_id'] ?? null;

if (!$exam_id) {
    echo json_encode(['count' => 0]);
    exit();
}

if ($session_id) {
    $stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM players WHERE exam_id = ? AND session_id = ?");
    $stmt->bind_param("ii", $exam_id, $session_id);
} else {
    $stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM players WHERE exam_id = ?");
    $stmt->bind_param("i", $exam_id);
}
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

echo json_encode(['count' => (int)$row['cnt']]);

if (isset($conn) && $conn instanceof mysqli) { $conn->close(); }
