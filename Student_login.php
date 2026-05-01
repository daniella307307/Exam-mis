<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/Auth/auth_helpers.php';

$flash = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    if (!auth_csrf_check($_POST['csrf_token'] ?? null)) {
        $flash = ['type' => 'error', 'msg' => 'Your session expired. Please try again.'];
    } else {
        $regno    = trim((string)($_POST['student_regno'] ?? ''));
        $password = (string)($_POST['student_password'] ?? '');

        if ($regno === '' || $password === '') {
            $flash = ['type' => 'error', 'msg' => 'Registration number and password are required.'];
        } else {
            try {
                $stmt = $conn->prepare(
                    "SELECT student_id, student_first_name, student_last_name, student_regno,
                            student_password, student_status
                       FROM student_list
                      WHERE student_regno = ?
                      LIMIT 1"
                );
                $stmt->bind_param('s', $regno);
                $stmt->execute();
                $row = $stmt->get_result()->fetch_assoc();
                $stmt->close();
            } catch (Throwable $e) {
                error_log('student login lookup failed: ' . $e->getMessage());
                $row   = null;
                $flash = ['type' => 'error', 'msg' => 'Internal error. Please try again later.'];
            }

            if ($flash === null) {
                $verify = $row ? auth_verify_password($password, $row['student_password']) : ['ok' => false, 'needs_rehash' => false];

                if ($row && $verify['ok']) {
                    if (strcasecmp((string)$row['student_status'], 'Active') !== 0) {
                        $flash = ['type' => 'error', 'msg' => 'Your account is not active. Contact your school.'];
                    } else {
                        if ($verify['needs_rehash']) {
                            $newhash = auth_hash_password($password);
                            if ($newhash !== null) {
                                try {
                                    $upd = $conn->prepare("UPDATE student_list SET student_password = ? WHERE student_id = ?");
                                    $upd->bind_param('si', $newhash, $row['student_id']);
                                    $upd->execute();
                                    $upd->close();
                                } catch (Throwable $e) {
                                    error_log('student password rehash failed: ' . $e->getMessage());
                                }
                            }
                        }
                        auth_session_init((int)$row['student_id'], [
                            'student_id'    => (int)$row['student_id'],
                            'firstname'     => $row['student_first_name'],
                            'lastname'      => $row['student_last_name'],
                            'student_regno' => $row['student_regno'],
                            'role'          => 'student',
                        ]);
                        header('Location: Auth/Students/index.php');
                        exit;
                    }
                } else {
                    $flash = ['type' => 'error', 'msg' => 'Invalid registration number or password.'];
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
  <title>Student Login | BLIS MIS</title>
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
      <form class="max-w-xl m-4 p-10 bg-white rounded shadow-xl" action="" method="POST" autocomplete="on">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
        <a class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800" href="index.php">← Back | Home</a>
        <p class="text-gray-800 font-medium text-center text-lg font-bold">Student Login</p>

        <?php if ($flash): ?>
          <div class="<?= $flash['type'] === 'error' ? 'bg-red-300 border border-red-300 text-red-dark' : 'bg-green-500 text-white' ?> mb-2 px-4 py-3 rounded" role="alert">
            <?= htmlspecialchars($flash['msg'], ENT_QUOTES, 'UTF-8') ?>
          </div>
        <?php endif; ?>

        <div>
          <label class="block text-sm text-gray-600" for="student_regno">Registration No</label>
          <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="student_regno" name="student_regno" type="text" required placeholder="REG-..." autocomplete="username"
                 value="<?= htmlspecialchars((string)($_POST['student_regno'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="mt-2">
          <label class="block text-sm text-gray-600" for="student_password">Password</label>
          <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="student_password" name="student_password" type="password" required placeholder="*******" autocomplete="current-password">
        </div>
        <div class="mt-4 items-center justify-between">
          <button class="px-12 py-1 text-white font-light tracking-wider bg-green-600 rounded" name="login" type="submit">Login</button>
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
