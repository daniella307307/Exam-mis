<?php
/**
 * Per-exam permission/setting helpers.
 *
 * Lazy-migrates the columns the rest of the feature relies on, so
 * upgrading to this feature requires no manual SQL — the first hit on
 * any page that includes this file ensures the columns exist.
 *
 * Columns added (idempotently):
 *   exams.allow_replay              TINYINT(1) DEFAULT 1
 *   exams.show_answers_to_student   TINYINT(1) DEFAULT 1
 *   players.is_completed            TINYINT(1) DEFAULT 0
 *
 * Public helpers:
 *   ensure_exam_setting_columns($conn)
 *   exam_get_setting($conn, $exam_id, $key, $default)
 *   exam_set_setting($conn, $exam_id, $key, $value)        — uses prepared statements
 *   exam_settings_allowed_keys()                            — list of toggle keys
 */

if (!defined('EXAM_SETTINGS_ALLOWED')) {
    define('EXAM_SETTINGS_ALLOWED', ['allow_replay', 'show_answers_to_student']);
}

function ensure_exam_setting_columns(mysqli $conn): void {
    static $done = false;
    if ($done) return;

    $migrations = [
        ['exams',   'allow_replay',            "TINYINT(1) NOT NULL DEFAULT 1"],
        ['exams',   'show_answers_to_student', "TINYINT(1) NOT NULL DEFAULT 1"],
        ['players', 'is_completed',            "TINYINT(1) NOT NULL DEFAULT 0"],
    ];

    foreach ($migrations as [$table, $col, $def]) {
        // SHOW COLUMNS LIKE is portable across MySQL/MariaDB versions and
        // doesn't need ADD COLUMN IF NOT EXISTS (MySQL 8.0.29+ only).
        $res = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$col'");
        if ($res && $res->num_rows === 0) {
            // Column name and table name are hardcoded constants above —
            // not user input — so direct interpolation is safe.
            @$conn->query("ALTER TABLE `$table` ADD COLUMN `$col` $def");
        }
    }
    $done = true;
}

function exam_settings_allowed_keys(): array {
    return EXAM_SETTINGS_ALLOWED;
}

function exam_get_setting(mysqli $conn, int $exam_id, string $key, $default = null) {
    ensure_exam_setting_columns($conn);
    if (!in_array($key, EXAM_SETTINGS_ALLOWED, true)) return $default;

    $stmt = $conn->prepare("SELECT `$key` AS v FROM exams WHERE exam_id = ? LIMIT 1");
    $stmt->bind_param('i', $exam_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ? (int)$row['v'] : $default;
}

function exam_set_setting(mysqli $conn, int $exam_id, string $key, $value): bool {
    ensure_exam_setting_columns($conn);
    if (!in_array($key, EXAM_SETTINGS_ALLOWED, true)) return false;

    $val = (int)((bool)$value);
    $stmt = $conn->prepare("UPDATE exams SET `$key` = ? WHERE exam_id = ?");
    $stmt->bind_param('ii', $val, $exam_id);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}
