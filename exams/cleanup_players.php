<?php
/**
 * Cleanup "abandoned" player rows for one exam.
 *
 * Removes rows that look like a student joined but never submitted — i.e. they
 * have ZERO rows in the `answers` table for this exam. Group-mode named-member
 * rows are PRESERVED because the group's placeholder is the one who actually
 * submitted (so the named members technically also have no answers).
 *
 * ACL: same-school colleagues OK (uses can_collaborate). Edit/delete on the
 * exam itself stays owner-only via the regular ACL paths — this only prunes
 * orphan player rows the join flow accumulated.
 */
require_once('../db_connection.php');
require_once(__DIR__ . '/lib/exam_acl.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'POST only']);
    exit;
}

$exam_id = (int)($_POST['exam_id'] ?? 0);
if (!$exam_id) {
    echo json_encode(['success' => false, 'error' => 'exam_id required']);
    exit;
}

if (!exam_acl_can_collaborate($conn, $exam_id)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Not your school']);
    exit;
}

// 1. Players who actually submitted at least one answer for this exam.
$sub_stmt = $conn->prepare(
    "SELECT DISTINCT player_id FROM answers WHERE exam_id = ?"
);
$sub_stmt->bind_param('i', $exam_id);
$sub_stmt->execute();
$submitted = [];
foreach ($sub_stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $r) {
    $submitted[(int)$r['player_id']] = true;
}
$sub_stmt->close();

// 2. Groups whose placeholder (or any member) did submit — keeps the named
//    teammates of real group attempts intact.
$grp_stmt = $conn->prepare(
    "SELECT DISTINCT p.group_nbr
       FROM players p
       INNER JOIN answers a
               ON a.player_id = p.player_id AND a.exam_id = p.exam_id
      WHERE p.exam_id = ?
        AND p.group_nbr IS NOT NULL
        AND p.group_nbr > 0"
);
$grp_stmt->bind_param('i', $exam_id);
$grp_stmt->execute();
$active_groups = [];
foreach ($grp_stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $r) {
    $active_groups[(int)$r['group_nbr']] = true;
}
$grp_stmt->close();

// 3. Walk all rows; decide who to delete.
$all_stmt = $conn->prepare(
    "SELECT player_id, group_nbr FROM players WHERE exam_id = ?"
);
$all_stmt->bind_param('i', $exam_id);
$all_stmt->execute();
$all_rows = $all_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$all_stmt->close();

$to_delete = [];
$preview   = [];
foreach ($all_rows as $row) {
    $pid = (int)$row['player_id'];
    if (isset($submitted[$pid])) continue;                                  // they submitted
    $gn = (int)($row['group_nbr'] ?? 0);
    if ($gn > 0 && isset($active_groups[$gn])) continue;                    // their group submitted
    $to_delete[] = $pid;
}

if (empty($to_delete)) {
    echo json_encode(['success' => true, 'removed' => 0, 'message' => 'No abandoned entries to clean.']);
    exit;
}

// 4. Delete in one shot. Each pid was already filtered to belong to this exam.
$placeholders = implode(',', array_fill(0, count($to_delete), '?'));
$types        = str_repeat('i', count($to_delete));
$del = $conn->prepare("DELETE FROM players WHERE player_id IN ($placeholders)");
$del->bind_param($types, ...$to_delete);
$del->execute();
$removed = $del->affected_rows;
$del->close();

echo json_encode([
    'success' => true,
    'removed' => $removed,
    'message' => "Removed $removed unfinished entries.",
]);
