<?php
/**
 * Step 2 of password reset: user clicks the email link with ?token=XXX,
 * we verify the token (matched by SHA-256, must not be expired or used),
 * and let them choose a new password. On submit we hash with bcrypt,
 * write the new password to the right table, and mark the token used.
 */
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/Auth/auth_helpers.php';

$flash       = null;
$token       = trim((string)($_GET['token'] ?? $_POST['token'] ?? ''));
$token_valid = false;
$reset_ctx   = null; // ['user_kind' => 'user'|'student', 'user_ref_id' => int, 'email' => string]

if ($token === '' || strlen($token) < 16) {
    $flash = ['type' => 'error', 'msg' => 'Invalid or missing reset token.'];
} else {
    $token_hash = hash('sha256', $token);
    $row = null;
    try {
        $stmt = $conn->prepare(
            "SELECT id, user_kind, user_ref_id, email, expires_at, used_at
               FROM password_reset_tokens
              WHERE token_hash = ?
              LIMIT 1"
        );
        $stmt->bind_param('s', $token_hash);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    } catch (Throwable $e) {
        error_log('reset token lookup failed: ' . $e->getMessage());
        $flash = ['type' => 'error', 'msg' => 'Internal error. Please try again later.'];
    }

    if ($flash === null) {
        if (!$row) {
            $flash = ['type' => 'error', 'msg' => 'This reset link is not valid.'];
        } elseif ($row['used_at'] !== null) {
            $flash = ['type' => 'error', 'msg' => 'This reset link has already been used. Please request a new one.'];
        } elseif (strtotime($row['expires_at']) < time()) {
            $flash = ['type' => 'error', 'msg' => 'This reset link has expired. Please request a new one.'];
        } else {
            $token_valid = true;
            $reset_ctx = [
                'id'           => (int)$row['id'],
                'user_kind'    => (string)$row['user_kind'],
                'user_ref_id'  => (int)$row['user_ref_id'],
                'email'        => (string)$row['email'],
            ];
        }
    }
}

if ($token_valid && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_password'])) {
    if (!auth_csrf_check($_POST['csrf_token'] ?? null)) {
        $flash = ['type' => 'error', 'msg' => 'Your session expired. Please try again.'];
    } else {
        $new1 = (string)($_POST['new_password'] ?? '');
        $new2 = (string)($_POST['new_password_confirm'] ?? '');

        if (strlen($new1) < 8) {
            $flash = ['type' => 'error', 'msg' => 'Password must be at least 8 characters.'];
        } elseif ($new1 !== $new2) {
            $flash = ['type' => 'error', 'msg' => 'Passwords do not match.'];
        } else {
            $hash = auth_hash_password($new1);
            if ($hash === null) {
                $flash = ['type' => 'error', 'msg' => 'Could not hash password. Please try again.'];
            } else {
                $conn->begin_transaction();
                try {
                    if ($reset_ctx['user_kind'] === 'user') {
                        $upd = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                    } else {
                        $upd = $conn->prepare("UPDATE student_list SET student_password = ? WHERE student_id = ?");
                    }
                    if ($upd === false) {
                        throw new RuntimeException('prepare failed');
                    }
                    $upd->bind_param('si', $hash, $reset_ctx['user_ref_id']);
                    $upd->execute();
                    $upd->close();

                    $mark = $conn->prepare("UPDATE password_reset_tokens SET used_at = NOW() WHERE id = ?");
                    if ($mark === false) {
                        throw new RuntimeException('prepare failed');
                    }
                    $mark->bind_param('i', $reset_ctx['id']);
                    $mark->execute();
                    $mark->close();

                    // Invalidate any other outstanding tokens for the same user.
                    $kill = $conn->prepare(
                        "UPDATE password_reset_tokens
                            SET used_at = NOW()
                          WHERE user_kind = ? AND user_ref_id = ? AND used_at IS NULL"
                    );
                    if ($kill !== false) {
                        $kill->bind_param('si', $reset_ctx['user_kind'], $reset_ctx['user_ref_id']);
                        $kill->execute();
                        $kill->close();
                    }

                    $conn->commit();
                    $token_valid = false; // hide the form on success
                    $flash = [
                        'type' => 'success',
                        'msg'  => 'Your password has been updated. You can now log in with your new password.',
                    ];
                } catch (Throwable $e) {
                    $conn->rollback();
                    $flash = ['type' => 'error', 'msg' => 'Could not update your password. Please try again.'];
                }
            }
        }
    }
}

$csrf = auth_csrf_token();
?>
<!doctype html>
<html lang="en">
<head>
  <title>Choose new password | BLIS MIS</title>
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
        <input type="hidden" name="token"      value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
        <p class="text-gray-800 font-medium text-center text-lg font-bold">Choose a new password</p>

        <?php if ($flash): ?>
          <div class="<?= $flash['type'] === 'error' ? 'bg-red-300 border border-red-300 text-red-dark' : 'bg-green-500 text-white' ?> mb-2 px-4 py-3 rounded" role="alert">
            <?= htmlspecialchars($flash['msg'], ENT_QUOTES, 'UTF-8') ?>
          </div>
        <?php endif; ?>

        <?php if ($token_valid): ?>
          <div>
            <label class="block text-sm text-gray-600" for="new_password">New password (min 8 chars)</label>
            <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="new_password" name="new_password" type="password" minlength="8" required autocomplete="new-password">
          </div>
          <div class="mt-2">
            <label class="block text-sm text-gray-600" for="new_password_confirm">Confirm new password</label>
            <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="new_password_confirm" name="new_password_confirm" type="password" minlength="8" required autocomplete="new-password">
          </div>
          <div class="mt-6 text-center">
            <button class="px-12 py-2 text-white font-light tracking-wider bg-gray-900 rounded" name="set_password" type="submit">Update password</button>
          </div>
        <?php else: ?>
          <div class="text-center mt-4">
            <a class="inline-block right-0 align-baseline font-bold text-sm text-blue-500 hover:text-blue-800" href="login.php">Go to login</a>
            &nbsp;·&nbsp;
            <a class="inline-block right-0 align-baseline font-bold text-sm text-blue-500 hover:text-blue-800" href="Reset_password.php">Request a new link</a>
          </div>
        <?php endif; ?>
      </form>
    </div>
  </div>
</div>
</body>
</html>
