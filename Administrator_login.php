<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/Auth/auth_helpers.php';

$flash = null;
$redirect_url = null;

if ($_SERVER['REQUEST_METHOD'] === 'GET' && (string)($_GET['reason'] ?? '') === 'idle') {
    $flash = ['type' => 'error', 'msg' => 'You were signed out due to inactivity. Please log in again.'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!auth_csrf_check($_POST['csrf_token'] ?? null)) {
        $flash = ['type' => 'error', 'msg' => 'Your session expired. Please try again.'];
    } else {
        $email    = trim((string)($_POST['email_address'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            $flash = ['type' => 'error', 'msg' => 'Email and password are required.'];
        } else {
            $row = null;
            try {
                $stmt = $conn->prepare(
                    "SELECT u.user_id, u.firstname, u.lastname, u.email_address, u.password,
                            u.status, u.access_level, u.school_ref, u.user_group_ref,
                            up.permissio_location, up.permission
                       FROM users u
                  LEFT JOIN user_permission up ON u.access_level = up.permissio_id
                  LEFT JOIN schools s          ON u.school_ref   = s.school_id
                      WHERE u.email_address = ?
                      LIMIT 1"
                );
                $stmt->bind_param('s', $email);
                $stmt->execute();
                $row = $stmt->get_result()->fetch_assoc();
                $stmt->close();
            } catch (Throwable $e) {
                error_log('admin login lookup failed: ' . $e->getMessage());
                $flash = ['type' => 'error', 'msg' => 'Internal error. Please try again later.'];
            }

            if ($flash === null) {
                $verify = $row ? auth_verify_password($password, $row['password']) : ['ok' => false, 'needs_rehash' => false];

                if ($row && $verify['ok']) {
                    if (strcasecmp((string)$row['status'], 'Active') !== 0) {
                        $flash = ['type' => 'error', 'msg' => 'Your account is not active. Contact your administrator.'];
                    } else {
                        $access_level = (int)$row['access_level'];
                        $user_id      = (int)$row['user_id'];
                        $has_access   = false;
                        try {
                            $perm = $conn->prepare(
                                "SELECT 1
                                   FROM active_user_permission
                                  WHERE active_permission = ?
                                    AND Active_user_ref   = ?
                                    AND permission_status = 'Active'
                                  LIMIT 1"
                            );
                            $perm->bind_param('ii', $access_level, $user_id);
                            $perm->execute();
                            $has_access = (bool)$perm->get_result()->fetch_row();
                            $perm->close();
                        } catch (Throwable $e) {
                            error_log('admin permission lookup failed: ' . $e->getMessage());
                        }

                        if (!$has_access) {
                            $flash = [
                                'type' => 'error',
                                'msg'  => 'Dear ' . htmlspecialchars((string)$row['firstname'] . ' ' . (string)$row['lastname'], ENT_QUOTES, 'UTF-8')
                                          . ', you do not have any access in the system. Contact your admin.',
                            ];
                        } else {
                            if ($verify['needs_rehash']) {
                                $newhash = auth_hash_password($password);
                                if ($newhash !== null) {
                                    try {
                                        $upd = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                                        $upd->bind_param('si', $newhash, $user_id);
                                        $upd->execute();
                                        $upd->close();
                                    } catch (Throwable $e) {
                                        error_log('admin password rehash failed: ' . $e->getMessage());
                                    }
                                }
                            }
                            auth_session_init($user_id, [
                                'firstname'           => $row['firstname'],
                                'lastname'            => $row['lastname'],
                                'email_address'       => $row['email_address'],
                                'access_level'        => $access_level,
                                'school_ref'          => $row['school_ref'],
                                'user_group_ref'      => $row['user_group_ref'],
                                'permissio_location'  => $row['permissio_location'],
                                'permission'          => $row['permission'],
                            ]);
                            $location = (string)($row['permissio_location'] ?? '');
                            $location = ltrim($location, '/');
                            $redirect_url = 'Auth/' . $location;
                            $flash = ['type' => 'success', 'msg' => 'Login successful. Redirecting…'];
                        }
                    }
                } else {
                    $flash = ['type' => 'error', 'msg' => 'Invalid email or password.'];
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
  <title>Administrator Login | BLIS MIS</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="stylesheet" href="./dist/styles.css">
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.13/css/all.css">
  <style>.login{background:url('./dist/images/Microprocessor.jpg')}</style>
  <?php if ($redirect_url): ?>
  <meta http-equiv="refresh" content="1;url=<?= htmlspecialchars($redirect_url, ENT_QUOTES, 'UTF-8') ?>">
  <?php endif; ?>
</head>
<body class="h-screen font-sans login bg-cover">
<div class="container mx-auto h-full flex flex-1 justify-center items-center">
  <div class="w-full max-w-lg">
    <div class="leading-loose">
      <form class="max-w-xl m-4 p-10 bg-white rounded shadow-xl" action="" method="POST" autocomplete="on">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">

        <?php if ($flash): ?>
          <div class="<?= $flash['type'] === 'error' ? 'bg-red-300 border border-red-300 text-red-dark' : 'bg-green-500 text-white' ?> mb-2 px-4 py-3 rounded" role="alert">
            <?= $flash['msg'] /* may contain pre-escaped HTML for the access-denied case */ ?>
          </div>
        <?php endif; ?>

        <a class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800" href="index.php">← Back | Home</a>
        <p class="text-gray-800 font-medium text-center text-lg font-bold">Administrator Login</p>
        <a class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800" href="/Auth/programs/">Access School Program</a>

        <div>
          <label class="block text-sm text-gray-600" for="email_address">Your Email</label>
          <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="email_address" name="email_address" type="email" required placeholder="you@example.com" autocomplete="username"
                 value="<?= htmlspecialchars((string)($_POST['email_address'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="mt-2">
          <label class="block text-sm text-gray-600" for="password">Password</label>
          <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="password" name="password" type="password" required placeholder="*******" autocomplete="current-password">
        </div>
        <div class="mt-4 items-center justify-between">
          <button class="px-12 py-1 text-white font-light tracking-wider bg-gray-900 rounded" type="submit">Login</button>
          &nbsp;<a class="inline-block right-0 align-baseline font-bold text-sm text-blue-500 hover:text-blue-800" href="Reset_password.php">Forgot Password?</a>
        </div>
        <div class="mt-3 text-sm text-gray-600">
          New here? <a class="font-bold text-blue-500 hover:text-blue-800" href="Signup.php">Register a new account</a>
        </div>
      </form>
    </div>
  </div>
</div>
</body>
</html>
