<?php
/**
 * Resolves the URL prefix where this project is being served from.
 *
 * Same code works in both deploys:
 *   - Local  XAMPP/Apache:  /var/www/html/Exam-mis/  → APP_BASE_URL = '/Exam-mis'
 *   - Hostinger production: /home/.../public_html/   → APP_BASE_URL = ''
 *
 * Detection: compare the project's filesystem root against the web server's
 * DOCUMENT_ROOT. The leftover prefix is the URL the project lives at.
 *
 * Use everywhere instead of hard-coding '/Exam-mis/':
 *   <link rel="stylesheet" href="<?= APP_BASE_URL ?>/exams/assets/x.css">
 *   header('Location: ' . APP_BASE_URL . '/Administrator_login.php');
 *   fetch('<?= APP_BASE_URL ?>/exams/api/get_stats.php')
 */
// Pin PHP to East Africa Time. Hostinger's default is UTC, so without this a
// teacher who types "May 18 19:00" in the Live-Class form has it interpreted
// as 19:00 UTC, then the page later prints "19:00" while the browser's
// countdown is computed against the real (UTC+3-offset) instant. Match the
// teacher's clock and everything lines up. (Set MYSQL session tz too — see
// db_connection.php — so any DATE_ADD / NOW() comparisons agree.)
if (!ini_get('date.timezone') || date_default_timezone_get() === 'UTC') {
    date_default_timezone_set('Africa/Dar_es_Salaam');
}

if (!defined('APP_BASE_URL')) {
    $proj = realpath(__DIR__);
    $proj = $proj ? str_replace('\\', '/', $proj) : __DIR__;

    $doc = $_SERVER['DOCUMENT_ROOT'] ?? '';
    $doc = $doc ? (realpath($doc) ?: $doc) : '';
    $doc = str_replace('\\', '/', $doc);

    $base = '';
    if ($doc !== '' && strpos($proj, $doc) === 0) {
        $base = substr($proj, strlen($doc));
        $base = '/' . trim($base, '/');
        if ($base === '/') {
            $base = '';
        }
    }
    define('APP_BASE_URL', $base);
}
