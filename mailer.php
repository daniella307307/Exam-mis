<?php
/**
 * Minimal SMTP mailer. No external dependencies.
 *
 * Replaces previous @mail() calls that silently swallowed failures.
 * Real errors are written to error_log so we can debug deliverability.
 *
 * Usage:
 *   require_once __DIR__ . '/mailer.php';
 *   $ok = send_mail('to@x.com', 'Subject', "Body...", $isHtml = false);
 */

if (!function_exists('send_mail')) {

function _smtp_read($sock): string {
    $data = '';
    while (!feof($sock)) {
        $line = fgets($sock, 1024);
        if ($line === false) break;
        $data .= $line;
        // last line of a multi-line response is "NNN <space>..."; continuations use "NNN-..."
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
    require __DIR__ . '/mail_config.php';

    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        error_log("send_mail: invalid recipient '$to'");
        return false;
    }

    $isSsl = ((int)$smtpPort === 465);
    $url   = ($isSsl ? 'ssl://' : 'tcp://') . $smtpHost . ':' . (int)$smtpPort;

    $sock = @stream_socket_client($url, $errno, $errstr, 20, STREAM_CLIENT_CONNECT);
    if (!$sock) {
        error_log("send_mail: connect $url failed: $errstr ($errno)");
        return false;
    }
    stream_set_timeout($sock, 20);

    $banner = _smtp_read($sock);
    if (strpos($banner, '220') !== 0) {
        error_log("send_mail: bad banner: " . trim($banner));
        fclose($sock);
        return false;
    }

    $hostname = $_SERVER['SERVER_NAME'] ?? gethostname() ?: 'localhost';

    [$ok, $r] = _smtp_cmd($sock, "EHLO $hostname", '250');
    if (!$ok) { error_log("send_mail EHLO: $r"); fclose($sock); return false; }

    if (!$isSsl) {
        [$ok, $r] = _smtp_cmd($sock, 'STARTTLS', '220');
        if (!$ok) { error_log("send_mail STARTTLS: $r"); fclose($sock); return false; }
        if (!stream_socket_enable_crypto($sock, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            error_log("send_mail: TLS handshake failed");
            fclose($sock);
            return false;
        }
        [$ok, $r] = _smtp_cmd($sock, "EHLO $hostname", '250');
        if (!$ok) { error_log("send_mail post-TLS EHLO: $r"); fclose($sock); return false; }
    }

    [$ok, $r] = _smtp_cmd($sock, 'AUTH LOGIN', '334');
    if (!$ok) { error_log("send_mail AUTH start: $r"); fclose($sock); return false; }
    [$ok, $r] = _smtp_cmd($sock, base64_encode($smtpUser), '334');
    if (!$ok) { error_log("send_mail AUTH user: $r"); fclose($sock); return false; }
    [$ok, $r] = _smtp_cmd($sock, base64_encode($smtpPass), '235');
    if (!$ok) { error_log("send_mail AUTH password rejected: $r"); fclose($sock); return false; }

    [$ok, $r] = _smtp_cmd($sock, "MAIL FROM:<{$smtpUser}>", '250');
    if (!$ok) { error_log("send_mail MAIL FROM: $r"); fclose($sock); return false; }

    [$ok, $r] = _smtp_cmd($sock, "RCPT TO:<{$to}>", '250');
    if (!$ok) { error_log("send_mail RCPT TO: $r"); fclose($sock); return false; }

    [$ok, $r] = _smtp_cmd($sock, 'DATA', '354');
    if (!$ok) { error_log("send_mail DATA: $r"); fclose($sock); return false; }

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
    if (!$ok) { error_log("send_mail end-of-DATA: $r"); fclose($sock); return false; }

    @fwrite($sock, "QUIT\r\n");
    fclose($sock);
    return true;
}

}
