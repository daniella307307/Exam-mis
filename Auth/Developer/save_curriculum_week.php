<?php
/**
 * Developer-only AJAX upsert for a curriculum week.
 *
 * session.php here enforces $permissio_location == 'Developer' (the parent
 * folder check), so this endpoint refuses anyone who isn't a Developer.
 *
 * POST: cert, term, month, week, title, notes, bunny_pdf_url, bunny_video_url
 * Response: { success: bool, error?: string }
 */
require_once(__DIR__ . '/session.php');           // bounces non-Developer + sets $conn, $session_id, $permissio_location
require_once(__DIR__ . '/../curriculum_helpers.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'POST only']);
    exit;
}

// Belt-and-braces role check — session.php should already have bounced
// anyone non-Developer to the login page, but if a hot session were
// somehow reused with a stale role, this stops the write.
if (strcasecmp((string)($permissio_location ?? ''), 'Developer') !== 0) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Developer access only']);
    exit;
}

curriculum_ensure_table($conn);

$user_id = (int)($session_id ?? 0);
if ($user_id <= 0) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$cert  = (int)($_POST['cert']  ?? 0);
$term  = (int)($_POST['term']  ?? 0);
$month = (int)($_POST['month'] ?? 0);
$week  = (int)($_POST['week']  ?? 0);

if ($cert <= 0 || !curriculum_valid_slot($term, $month, $week)) {
    echo json_encode(['success' => false, 'error' => 'Bad slot (cert/term/month/week)']);
    exit;
}

$title     = trim((string)($_POST['title']           ?? ''));
$notes     = trim((string)($_POST['notes']           ?? ''));
$pdf_url   = trim((string)($_POST['bunny_pdf_url']   ?? ''));
$video_url = trim((string)($_POST['bunny_video_url'] ?? ''));

foreach (['PDF' => $pdf_url, 'video' => $video_url] as $kind => $u) {
    if ($u !== '' && !preg_match('~^https?://~i', $u)) {
        echo json_encode(['success' => false, 'error' => "The $kind URL must start with http:// or https://"]);
        exit;
    }
}

// Confirm the certification actually exists — prevents accidental rows
// pointing at non-existent grades.
$ck = $conn->prepare("SELECT 1 FROM certifications WHERE certification_id = ? LIMIT 1");
$ck->bind_param('i', $cert);
$ck->execute();
$ok = (bool)$ck->get_result()->fetch_row();
$ck->close();
if (!$ok) {
    echo json_encode(['success' => false, 'error' => 'Unknown certification']);
    exit;
}

$stmt = $conn->prepare(
    "INSERT INTO curriculum_weeks
        (certification_id, term_number, month_number, week_number,
         title, notes, bunny_pdf_url, bunny_video_url, updated_by)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
     ON DUPLICATE KEY UPDATE
        title           = VALUES(title),
        notes           = VALUES(notes),
        bunny_pdf_url   = VALUES(bunny_pdf_url),
        bunny_video_url = VALUES(bunny_video_url),
        updated_by      = VALUES(updated_by)"
);
$stmt->bind_param(
    'iiiissssi',
    $cert, $term, $month, $week,
    $title, $notes, $pdf_url, $video_url, $user_id
);

if (!$stmt->execute()) {
    $err = $stmt->error;
    $stmt->close();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'DB error: ' . $err]);
    exit;
}
$stmt->close();

echo json_encode(['success' => true]);
