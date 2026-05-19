<?php
/**
 * AJAX endpoint: toggle a per-exam permission setting.
 *
 * POST exam_id, key, value (0 or 1). Same-school colleagues OK (uses
 * can_collaborate so co-teachers like Shafii + Daniella can both manage
 * the exam's permissions, not just the original creator).
 */
require_once('../db_connection.php');
require_once(__DIR__ . '/lib/exam_acl.php');
require_once(__DIR__ . '/lib/exam_settings.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'POST only']);
    exit;
}

$exam_id = (int)($_POST['exam_id'] ?? 0);
$key     = trim((string)($_POST['key'] ?? ''));
$value   = (int)($_POST['value'] ?? 0);

if (!$exam_id || !$key) {
    echo json_encode(['success' => false, 'error' => 'exam_id and key required']);
    exit;
}

if (!in_array($key, exam_settings_allowed_keys(), true)) {
    echo json_encode(['success' => false, 'error' => 'Unknown setting']);
    exit;
}

if (!exam_acl_can_collaborate($conn, $exam_id)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Not your school']);
    exit;
}

if (exam_set_setting($conn, $exam_id, $key, $value)) {
    echo json_encode(['success' => true, 'key' => $key, 'value' => $value ? 1 : 0]);
} else {
    echo json_encode(['success' => false, 'error' => 'Save failed']);
}
