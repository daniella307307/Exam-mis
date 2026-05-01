<?php
/**
 * Step 1 of password reset: user enters their email. We look up the
 * account, generate a secure one-time token, store its SHA-256 hash
 * with a 1-hour expiry, and email the user a reset link.
 *
 * To avoid disclosing which emails are registered, we always show the
 * same success message regardless of whether the address exists.
 */
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/Auth/auth_helpers.php';
require_once __DIR__ . '/mailer.php';

$flash = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['Reset'])) {
    if (!auth_csrf_check($_POST['csrf_token'] ?? null)) {
        $flash = ['type' => 'error', 'msg' => 'Your session expired. Please try again.'];
    } else {
        $email = trim((string)($_POST['email_address'] ?? ''));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $flash = ['type' => 'error', 'msg' => 'Please enter a valid email address.'];
        } else {
            $found_kind   = null;
            $found_id     = null;
            $found_first  = '';
            $found_last   = '';

            try {
                $stmt = $conn->prepare("SELECT user_id, firstname, lastname FROM users WHERE email_address = ? LIMIT 1");
                $stmt->bind_param('s', $email);
                $stmt->execute();
                if ($u = $stmt->get_result()->fetch_assoc()) {
                    $found_kind  = 'user';
                    $found_id    = (int)$u['user_id'];
                    $found_first = (string)$u['firstname'];
                    $found_last  = (string)$u['lastname'];
                }
                $stmt->close();
            } catch (Throwable $e) {
                error_log('reset users lookup failed: ' . $e->getMessage());
            }

            if ($found_kind === null) {
                try {
                    $stmt = $conn->prepare("SELECT student_id, student_first_name, student_last_name FROM student_list WHERE student_contact = ? LIMIT 1");
                    $stmt->bind_param('s', $email);
                    $stmt->execute();
                    if ($s = $stmt->get_result()->fetch_assoc()) {
                        $found_kind  = 'student';
                        $found_id    = (int)$s['student_id'];
                        $found_first = (string)$s['student_first_name'];
                        $found_last  = (string)$s['student_last_name'];
                    }
                    $stmt->close();
                } catch (Throwable $e) {
                    error_log('reset students lookup failed: ' . $e->getMessage());
                }
            }

            if ($found_kind !== null) {
                $token        = auth_random_token(32);
                $token_hash   = hash('sha256', $token);
                $expires_at   = (new DateTimeImmutable('+1 hour'))->format('Y-m-d H:i:s');
                $requester_ip = (string)($_SERVER['REMOTE_ADDR'] ?? '');

                $token_saved = false;
                try {
                    $ins = $conn->prepare(
                        "INSERT INTO password_reset_tokens
                            (user_kind, user_ref_id, email, token_hash, expires_at, requester_ip)
                         VALUES (?, ?, ?, ?, ?, ?)"
                    );
                    $ins->bind_param('sissss', $found_kind, $found_id, $email, $token_hash, $expires_at, $requester_ip);
                    $ins->execute();
                    $ins->close();
                    $token_saved = true;
                } catch (Throwable $e) {
                    error_log('reset token insert failed: ' . $e->getMessage());
                }

                if ($token_saved) {

                    // Build the absolute reset URL.
                    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                    $host   = (string)($_SERVER['HTTP_HOST'] ?? 'localhost');
                    $script = (string)($_SERVER['SCRIPT_NAME'] ?? '');
                    $base   = rtrim(str_replace('Reset_password.php', '', $script), '/');
                    $reset_url = $scheme . '://' . $host . $base . '/Reset_password_confirm.php?token=' . urlencode($token);

                    $subject = 'BLIS LMS — password reset link';
                    $body  = "Dear " . $found_first . " " . $found_last . ",\n\n";
                    $body .= "We received a request to reset the password for your BLIS LMS account.\n";
                    $body .= "Click the link below to choose a new password (it expires in 1 hour):\n\n";
                    $body .= $reset_url . "\n\n";
                    $body .= "If you did not request this, you can safely ignore this email.\n";

                    // send_mail() logs the real failure reason to error_log;
                    // we still show the same generic message to the user to
                    // avoid leaking whether the address is registered.
                    send_mail($email, $subject, $body, false);
                }
            }

            // Always show the same response to prevent email enumeration.
            $flash = [
                'type' => 'success',
                'msg'  => 'If that email is registered, a password reset link has been sent. Please check your inbox (and spam folder). The link expires in 1 hour.',
            ];
        }
    }
}

$csrf = auth_csrf_token();
?>
<!doctype html>
<html lang="en">
<head>
  <title>Reset Password | BLIS MIS</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="stylesheet" href="./dist/styles.css">
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.13/css/all.css">
  <style>.login{background:url('./dist/images/Microprocessor.jpg')}</style>
</head>
<body class="h-screen font-sans login bg-cover">
<div class="container mx-auto h-full flex flex-1 justify-center items-center">
  <div class="w-full max-w-lg">
    <div class="leading-loose">
      <form class="max-w-xl m-4 p-10 bg-white rounded shadow-xl" action="" method="POST">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
        <p class="text-gray-800 font-medium text-center text-lg font-bold">Password Reset</p>

        <?php if ($flash): ?>
          <div class="<?= $flash['type'] === 'error' ? 'bg-red-300 border border-red-300 text-red-dark' : 'bg-green-500 text-white' ?> mb-2 px-4 py-3 rounded" role="alert">
            <?= htmlspecialchars($flash['msg'], ENT_QUOTES, 'UTF-8') ?>
          </div>
        <?php endif; ?>

        <div>
          <label class="block text-sm text-gray-600" for="email_address">Account email</label>
          <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="email_address" name="email_address" type="email" required placeholder="you@example.com" autocomplete="username">
        </div>
        <div class="mt-8 items-center justify-between text-center">
          <button class="px-24 py-2 text-white font-light tracking-wider bg-gray-900 rounded" name="Reset" type="submit">Send reset link</button>
        </div>
        <a class="inline-block right-0 align-baseline font-bold text-sm text-blue-500 hover:text-blue-800 mt-2" href="login.php">← Back to login</a>
      </form>
    </div>
  </div>
</div>
</body>
</html>
