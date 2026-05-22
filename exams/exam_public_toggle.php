<?php
/**
 * AJAX endpoint: flip exams.is_public for a single exam.
 *
 * Only the owner (or a Developer) may change this — same-school colleagues
 * deliberately cannot, because publishing someone else's exam to the public
 * library is the owner's call, not the school's.
 *
 * POST: exam_id (int), value (0|1)
 * Response: { success: bool, value?: 0|1, error?: string }
 */
require_once('../db_connection.php');
require_once(__DIR__ . '/lib/exam_acl.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'POST only']);
    exit;
}

$acl = exam_acl_context($conn);
if (!$acl['user_id']) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$exam_id = (int)($_POST['exam_id'] ?? 0);
$value   = (int)!!($_POST['value'] ?? 0);   // coerce to 0/1

if ($exam_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'exam_id required']);
    exit;
}

// Owner-only: posting a public listing is the original creator's decision.
if (!exam_acl_owns($conn, $exam_id)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Only the exam owner can change this']);
    exit;
}

$upd = $conn->prepare("UPDATE exams SET is_public = ? WHERE exam_id = ?");
$upd->bind_param('ii', $value, $exam_id);
$ok = $upd->execute();
$upd->close();

if (!$ok) {
    echo json_encode(['success' => false, 'error' => 'Database update failed']);
    exit;
}

echo json_encode(['success' => true, 'value' => $value]);
