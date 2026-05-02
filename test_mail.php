<?php
/**
 * SMTP diagnostic page.
 *
 * Disposable. Hit /test_mail.php, type any address, click Send. The page
 * shows the actual reason send_mail() succeeded or failed (auth rejected,
 * host unreachable, TLS issue, etc.) so we can fix deliverability without
 * digging through the server's PHP error log.
 *
 * After SMTP is confirmed working, DELETE this file from production.
 */
require_once __DIR__ . '/mailer.php';

$result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $to = trim((string)($_POST['to'] ?? ''));
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        $result = ['ok' => false, 'msg' => 'Enter a valid email address.'];
    } else {
        $ok = send_mail(
            $to,
            'BLIS LMS — SMTP test',
            "This is a test message from BLIS LMS.\nIf you can read it, SMTP is working."
        );
        if ($ok) {
            $result = ['ok' => true, 'msg' => "Sent to $to. Check inbox AND spam folder."];
        } else {
            $err = $GLOBALS['send_mail_last_error'] ?? 'unknown error';
            $result = ['ok' => false, 'msg' => "send_mail failed: $err"];
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>SMTP Test</title>
  <style>
    body{font-family:system-ui,sans-serif;max-width:560px;margin:60px auto;padding:0 20px;color:#222}
    h1{font-size:20px}
    label{display:block;font-size:13px;color:#555;margin-bottom:4px}
    input[type=email]{width:100%;padding:10px;border:1px solid #ccc;border-radius:6px;font-size:14px}
    button{margin-top:12px;padding:10px 18px;background:#1f6feb;color:#fff;border:none;border-radius:6px;font-size:14px;cursor:pointer}
    .ok{background:#d4edda;color:#155724;padding:12px;border-radius:6px;margin-top:18px;border:1px solid #b7dfc1}
    .err{background:#f8d7da;color:#721c24;padding:12px;border-radius:6px;margin-top:18px;border:1px solid #f1b0b7;word-break:break-word}
    code{background:#f4f4f4;padding:2px 4px;border-radius:3px}
    .note{color:#666;font-size:13px;margin-top:24px;line-height:1.5}
  </style>
</head>
<body>
  <h1>SMTP test</h1>
  <p style="color:#555">Sends a test message via the SMTP credentials in <code>mail_config.php</code> and shows the actual server response.</p>

  <form method="POST">
    <label for="to">Send a test message to:</label>
    <input id="to" name="to" type="email" placeholder="you@example.com" required value="<?= htmlspecialchars($_POST['to'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
    <button type="submit">Send test</button>
  </form>

  <?php if ($result): ?>
    <div class="<?= $result['ok'] ? 'ok' : 'err' ?>">
      <?= htmlspecialchars($result['msg'], ENT_QUOTES, 'UTF-8') ?>
    </div>
  <?php endif; ?>

  <div class="note">
    <strong>Common failures:</strong><br>
    <code>AUTH password rejected</code> → SMTP user/pass in <code>mail_config.php</code> is wrong or the mailbox is dead.<br>
    <code>connect ssl://... failed</code> → host unreachable from this server (firewall / wrong host / wrong port).<br>
    <code>TLS handshake failed</code> → SSL/TLS negotiation problem (try port 587 with STARTTLS instead of 465 SSL).<br>
    <code>RCPT TO rejected</code> → server refused the recipient (often: From-domain doesn't have proper SPF/DKIM).<br>
    <strong>If "Sent" but no email arrives:</strong> check the SPAM folder. From-address mismatch with the sending domain is the #1 cause.
  </div>

  <p class="note"><em>Delete this file from production after debugging — it lets anyone hit the SMTP relay.</em></p>
</body>
</html>
