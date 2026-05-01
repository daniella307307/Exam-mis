<?php
/**
 * SMTP credentials for outgoing mail.
 *
 * Kept separate from db.php / config.php so including it does not open
 * a database connection. Loaded by mailer.php.
 *
 * To switch providers (Gmail, Hostinger, etc.) change the values below.
 */
$smtpHost     = 'blisglobal.gloryacademy.rw';
$smtpUser     = 'noreply@blisglobal.gloryacademy.rw';
$smtpPass     = 'ah_YZJ+W5bcd';
$smtpPort     = 465;
$mailFromName = 'BLIS LMS';
