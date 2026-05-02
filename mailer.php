<?php
/**
 * Minimal SMTP mailer. No external dependencies.
 *
 * Replaces previous @mail() calls that silently swallowed failures.
 * Real errors are written to error_log so we can debug deliverability.
 *
 * The most recent failure reason is also stashed in
 * $GLOBALS['send_mail_last_error'] so a debug page (test_mail.php) can
 * surface it inline without needing access to the server log files.
 *
 * Usage:
 *   require_once __DIR__ . '/mailer.php';
 *   $ok = send_mail('to@x.com', 'Subject', "Body...", $isHtml = false);
 *   if (!$ok) { echo $GLOBALS['send_mail_last_error']; }
 */

if (!function_exists('send_mail')) {

function _smtp_fail(string $reason, $sock = null): bool {
    $GLOBALS['send_mail_last_error'] = $reason;
    error_log('send_mail: ' . $reason);
    if ($sock) { @fclose($sock); }
    return false;
}

function _smtp_read($sock): string {
    $data = '';
    while (!feof($sock)) {
        $line = fgets($sock, 1024);
        if ($line === false) break;
        $data .= $line;
        if (preg_match('/^\d{3} /', $line)) break;
    }
    return $data;
}

function _smtp_cmd($sock, string $cmd, string $expectPrefix): array {
    fwrite($sock, $cmd . "\r\n");
    $resp = _smtp_read($sock);
    return [strpos($resp, $expectPrefix) === 0, $resp];
}

function send_mail(string $to, string $subject, string $body, bool $isHtml = false): bool {
    $GLOBALS['send_mail_last_error'] = null;
    require __DIR__ . '/mail_config.php';

    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return _smtp_fail("invalid recipient '$to'");
    }

    $isSsl = ((int)$smtpPort === 465);
    $url   = ($isSsl ? 'ssl://' : 'tcp://') . $smtpHost . ':' . (int)$smtpPort;

    $sock = @stream_socket_client($url, $errno, $errstr, 20, STREAM_CLIENT_CONNECT);
    if (!$sock) {
        return _smtp_fail("connect $url failed: $errstr ($errno)");
    }
    stream_set_timeout($sock, 20);

    $banner = _smtp_read($sock);
    if (strpos($banner, '220') !== 0) {
        return _smtp_fail('bad banner: ' . trim($banner), $sock);
    }

    $hostname = $_SERVER['SERVER_NAME'] ?? gethostname() ?: 'localhost';

    [$ok, $r] = _smtp_cmd($sock, "EHLO $hostname", '250');
    if (!$ok) { return _smtp_fail("EHLO rejected: " . trim($r), $sock); }

    if (!$isSsl) {
        [$ok, $r] = _smtp_cmd($sock, 'STARTTLS', '220');
        if (!$ok) { return _smtp_fail("STARTTLS rejected: " . trim($r), $sock); }
        if (!stream_socket_enable_crypto($sock, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            return _smtp_fail('TLS handshake failed', $sock);
        }
        [$ok, $r] = _smtp_cmd($sock, "EHLO $hostname", '250');
        if (!$ok) { return _smtp_fail("post-TLS EHLO rejected: " . trim($r), $sock); }
    }

    [$ok, $r] = _smtp_cmd($sock, 'AUTH LOGIN', '334');
    if (!$ok) { return _smtp_fail("AUTH LOGIN start rejected: " . trim($r), $sock); }
    [$ok, $r] = _smtp_cmd($sock, base64_encode($smtpUser), '334');
    if (!$ok) { return _smtp_fail("AUTH user rejected: " . trim($r), $sock); }
    [$ok, $r] = _smtp_cmd($sock, base64_encode($smtpPass), '235');
    if (!$ok) { return _smtp_fail("AUTH password rejected: " . trim($r), $sock); }

    [$ok, $r] = _smtp_cmd($sock, "MAIL FROM:<{$smtpUser}>", '250');
    if (!$ok) { return _smtp_fail("MAIL FROM rejected: " . trim($r), $sock); }

    [$ok, $r] = _smtp_cmd($sock, "RCPT TO:<{$to}>", '250');
    if (!$ok) { return _smtp_fail("RCPT TO rejected: " . trim($r), $sock); }

    [$ok, $r] = _smtp_cmd($sock, 'DATA', '354');
    if (!$ok) { return _smtp_fail("DATA rejected: " . trim($r), $sock); }

    $contentType = $isHtml ? 'text/html; charset=UTF-8' : 'text/plain; charset=UTF-8';
    $messageId   = bin2hex(random_bytes(8)) . '@' . $smtpHost;

    $headers = [
        "From: =?UTF-8?B?" . base64_encode($mailFromName) . "?= <{$smtpUser}>",
        "To: <{$to}>",
        "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=",
        "Date: " . date('r'),
        "Message-ID: <{$messageId}>",
        "MIME-Version: 1.0",
        "Content-Type: {$contentType}",
        "Content-Transfer-Encoding: 8bit",
    ];

    $body = str_replace(["\r\n", "\r"], "\n", $body);
    $body = str_replace("\n", "\r\n", $body);
    $body = preg_replace('/^\./m', '..', $body); // dot-stuffing per RFC 5321

    $payload = implode("\r\n", $headers) . "\r\n\r\n" . $body . "\r\n.";
    [$ok, $r] = _smtp_cmd($sock, $payload, '250');
    if (!$ok) { return _smtp_fail("end-of-DATA rejected: " . trim($r), $sock); }

    @fwrite($sock, "QUIT\r\n");
    fclose($sock);
    return true;
}

}
