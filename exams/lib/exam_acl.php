<?php
/**
 * Multi-tenancy ACL for the exams module.
 *
 * Three tiers:
 *   OWN          — owner only (edit content / delete / answer-key preview)
 *   COLLABORATE  — owner OR same-school facilitator (republish, activate,
 *                  deactivate, grade submissions). Safe state changes that
 *                  don't rewrite the exam content or expose answer keys.
 *   VIEW         — owner OR same-school facilitator (dashboard listings,
 *                  leaderboard, reports, Excel download, per-question report)
 *
 *   $acl = exam_acl_context($conn);
 *
 *   $acl['user_id']       (int|null)   logged-in user, null if guest
 *   $acl['role']          (string)     permissio_location, e.g. 'Developer', 'SF'
 *   $acl['school_id']     (int)        users.school_ref (0 if none)
 *   $acl['is_developer']  (bool)       bypass all checks
 *
 *   exam_acl_owns($conn, $exam_id)                    (bool) OWN          — non-fatal
 *   exam_acl_require_owner($conn, $exam_id)                  OWN          — 403 + exit
 *   exam_acl_can_collaborate($conn, $exam_id)         (bool) COLLABORATE  — non-fatal
 *   exam_acl_require_collaborate($conn, $exam_id)            COLLABORATE  — 403 + exit
 *   exam_acl_can_view($conn, $exam_id)                (bool) VIEW         — non-fatal
 *   exam_acl_require_view($conn, $exam_id)                   VIEW         — 403 + exit
 *
 * Same-school rule: a user with school_ref > 0 may collaborate on / view any
 * exam whose school_id matches their own school_ref. school_ref = 0
 * (placeholder accounts) is treated as "no school" and does NOT pool with
 * other 0-school users.
 */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function exam_acl_context(mysqli $conn): array {
    static $cached = null;
    if ($cached !== null) return $cached;

    $uid = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
    if ($uid <= 0) {
        return $cached = ['user_id' => null, 'role' => '', 'is_developer' => false];
    }

    $stmt = $conn->prepare(
        "SELECT up.permissio_location, u.school_ref
         FROM users u
         LEFT JOIN user_permission up ON u.access_level = up.permissio_id
         WHERE u.user_id = ? LIMIT 1"
    );
    $stmt->bind_param('i', $uid);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $role = $row['permissio_location'] ?? '';
    return $cached = [
        'user_id'      => $uid,
        'role'         => $role,
        'school_id'    => (int)($row['school_ref'] ?? 0),
        'is_developer' => strcasecmp($role, 'Developer') === 0,
    ];
}

/** Non-fatal: did $exam_id originate from the current user (or are they dev)? */
function exam_acl_owns(mysqli $conn, int $exam_id): bool {
    $acl = exam_acl_context($conn);
    if ($acl['is_developer']) return true;
    if (!$acl['user_id'])     return false;

    $stmt = $conn->prepare("SELECT created_by FROM exams WHERE exam_id = ? LIMIT 1");
    $stmt->bind_param('i', $exam_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row && (int)$row['created_by'] === (int)$acl['user_id'];
}

/** Fatal: 403 + exit if the current user has no business touching $exam_id. */
function exam_acl_require_owner(mysqli $conn, int $exam_id): void {
    if (exam_acl_owns($conn, $exam_id)) return;
    http_response_code(403);
    if (PHP_SAPI !== 'cli') {
        header('Content-Type: text/html; charset=utf-8');
    }
    echo '<!doctype html><meta charset="utf-8"><title>403 Forbidden</title>'
       . '<div style="font-family:sans-serif;max-width:560px;margin:80px auto;text-align:center">'
       . '<h1 style="color:#dc2626">403 &mdash; Not your exam</h1>'
       . '<p style="color:#475569">This exam belongs to another teacher. Only the teacher who created it (or a Developer) can edit, delete, republish, or see its results.</p>'
       . '<p><a href="exams_dashboard.php" style="color:#2563eb">&larr; Back to my exams</a></p>'
       . '</div>';
    exit;
}

/**
 * Non-fatal: may the current user perform safe state changes (republish,
 * activate, deactivate, grade submissions) on $exam_id? Currently identical
 * to VIEW (same-school is enough), but expressed separately so we can tighten
 * it independently in future.
 */
function exam_acl_can_collaborate(mysqli $conn, int $exam_id): bool {
    return exam_acl_can_view($conn, $exam_id);
}

/** Fatal: 403 + exit if the current user can't collaborate on $exam_id. */
function exam_acl_require_collaborate(mysqli $conn, int $exam_id): void {
    if (exam_acl_can_collaborate($conn, $exam_id)) return;
    http_response_code(403);
    if (PHP_SAPI !== 'cli') {
        header('Content-Type: text/html; charset=utf-8');
    }
    echo '<!doctype html><meta charset="utf-8"><title>403 Forbidden</title>'
       . '<div style="font-family:sans-serif;max-width:560px;margin:80px auto;text-align:center">'
       . '<h1 style="color:#dc2626">403 &mdash; Not your school</h1>'
       . '<p style="color:#475569">This exam belongs to a teacher at a different school. Only the owner, a same-school facilitator, or a Developer can republish, activate, or grade it.</p>'
       . '<p><a href="exams_dashboard.php" style="color:#2563eb">&larr; Back to my exams</a></p>'
       . '</div>';
    exit;
}

/**
 * Non-fatal: may the current user VIEW (not modify) $exam_id?
 * True if: Developer, owner, or same school as the exam (exams.school_id matches
 * viewer's users.school_ref, both > 0).
 *
 * Note: the *exam's* school_id is the source of truth, not the creator's
 * school_ref. An admin (e.g. user 1) can publish an exam tagged for school X;
 * facilitators at school X must still be able to see it.
 */
function exam_acl_can_view(mysqli $conn, int $exam_id): bool {
    $acl = exam_acl_context($conn);
    if ($acl['is_developer']) return true;
    if (!$acl['user_id'])     return false;

    $stmt = $conn->prepare(
        "SELECT created_by, school_id FROM exams WHERE exam_id = ? LIMIT 1"
    );
    $stmt->bind_param('i', $exam_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$row) return false;

    if ((int)$row['created_by'] === (int)$acl['user_id']) return true;

    $exam_school   = (int)($row['school_id'] ?? 0);
    $viewer_school = (int)($acl['school_id'] ?? 0);
    return $exam_school > 0 && $viewer_school > 0 && $exam_school === $viewer_school;
}

/** Fatal: 403 + exit if the current user can't even VIEW $exam_id. */
function exam_acl_require_view(mysqli $conn, int $exam_id): void {
    if (exam_acl_can_view($conn, $exam_id)) return;
    http_response_code(403);
    if (PHP_SAPI !== 'cli') {
        header('Content-Type: text/html; charset=utf-8');
    }
    echo '<!doctype html><meta charset="utf-8"><title>403 Forbidden</title>'
       . '<div style="font-family:sans-serif;max-width:560px;margin:80px auto;text-align:center">'
       . '<h1 style="color:#dc2626">403 &mdash; Not visible to you</h1>'
       . '<p style="color:#475569">This exam belongs to a teacher at a different school. Only the owner, a same-school facilitator, or a Developer can see its results.</p>'
       . '<p><a href="exams_dashboard.php" style="color:#2563eb">&larr; Back to my exams</a></p>'
       . '</div>';
    exit;
}
