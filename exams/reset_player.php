<?php
/**
 * AJAX endpoint: reset a single player's attempt on an exam so they can
 * retake it — used by the teacher's per-row "🔄 Reset" button on the
 * exam report.
 *
 * Clears that player's `answers` rows, zeros their `score`, flips
 * `is_completed` back to 0. The student's browser still holds the
 * "exam_done_<id>" cookie pointing at this player_id, but join_exam.php
 * rechecks `is_completed` server-side, so once we flip it the next
 * re-entry is allowed.
 *
 * ACL: same-school colleagues OK (collaborate tier).
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

$exam_id   = (int)($_POST['exam_id']   ?? 0);
$player_id = (int)($_POST['player_id'] ?? 0);

if (!$exam_id || !$player_id) {
    echo json_encode(['success' => false, 'error' => 'exam_id and player_id required']);
    exit;
}

if (!exam_acl_can_collaborate($conn, $exam_id)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Not your school']);
    exit;
}

ensure_exam_setting_columns($conn);

// Verify the player actually belongs to this exam — prevents cross-exam
// reset attempts via crafted POSTs.
$vchk = $conn->prepare("SELECT 1 FROM players WHERE player_id = ? AND exam_id = ?");
$vchk->bind_param('ii', $player_id, $exam_id);
$vchk->execute();
if (!$vchk->get_result()->fetch_row()) {
    $vchk->close();
    echo json_encode(['success' => false, 'error' => 'Player not in this exam']);
    exit;
}
$vchk->close();

// 1. Clear the player's answers
$del = $conn->prepare("DELETE FROM answers WHERE player_id = ? AND exam_id = ?");
$del->bind_param('ii', $player_id, $exam_id);
$del->execute();
$cleared = $del->affected_rows;
$del->close();

// 2. Zero the score, unset completion. Keep the player row itself so the
//    cookie -> player_id link remains stable and the student lands back
//    on the same record when they retake.
$upd = $conn->prepare("UPDATE players SET score = 0, is_completed = 0 WHERE player_id = ?");
$upd->bind_param('i', $player_id);
$upd->execute();
$upd->close();

// 3. If this player is part of a group, the group propagation we wrote
//    earlier means the score is mirrored across teammates. Knock the
//    group's score back to MAX of remaining submitted players (or 0).
$gn_stmt = $conn->prepare("SELECT group_nbr FROM players WHERE player_id = ? LIMIT 1");
$gn_stmt->bind_param('i', $player_id);
$gn_stmt->execute();
$gn = (int)($gn_stmt->get_result()->fetch_assoc()['group_nbr'] ?? 0);
$gn_stmt->close();

if ($gn > 0) {
    // Recompute the surviving group score: highest SUM(points_earned)
    // among teammates that still have answer rows.
    $rec = $conn->prepare(
        "SELECT COALESCE(MAX(sub.total), 0) AS m
           FROM (
                SELECT SUM(a.points_earned) AS total
                  FROM players p
             LEFT JOIN answers a ON a.player_id = p.player_id AND a.exam_id = p.exam_id
                 WHERE p.exam_id = ? AND p.group_nbr = ?
              GROUP BY p.player_id
           ) AS sub"
    );
    $rec->bind_param('ii', $exam_id, $gn);
    $rec->execute();
    $new_team_score = (int)($rec->get_result()->fetch_assoc()['m'] ?? 0);
    $rec->close();

    $g_upd = $conn->prepare("UPDATE players SET score = ? WHERE exam_id = ? AND group_nbr = ?");
    $g_upd->bind_param('iii', $new_team_score, $exam_id, $gn);
    $g_upd->execute();
    $g_upd->close();
}

echo json_encode([
    'success' => true,
    'cleared_answers' => $cleared,
    'message' => 'Student attempt cleared — they can retake the exam.',
]);
