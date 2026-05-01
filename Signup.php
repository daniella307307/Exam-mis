<?php
/**
 * Self-service registration. Creates a user with status='Pending' so
 * an administrator must approve the account before it can log in.
 *
 * Schools dropdown is populated server-side from the schools table.
 * The chosen school's country / region are denormalised onto the user
 * row so existing reports keep working without a JOIN.
 */
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/Auth/auth_helpers.php';
require_once __DIR__ . '/mailer.php';

$flash = null;
$values = [
    'firstname'     => '',
    'lastname'      => '',
    'email_address' => '',
    'phone_number'  => '',
    'school_ref'    => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    if (!auth_csrf_check($_POST['csrf_token'] ?? null)) {
        $flash = ['type' => 'error', 'msg' => 'Your session expired. Please try again.'];
    } else {
        $values['firstname']     = trim((string)($_POST['firstname']     ?? ''));
        $values['lastname']      = trim((string)($_POST['lastname']      ?? ''));
        $values['email_address'] = trim((string)($_POST['email_address'] ?? ''));
        $values['phone_number']  = trim((string)($_POST['phone_number']  ?? ''));
        $values['school_ref']    = trim((string)($_POST['school_ref']    ?? ''));
        $password                = (string)($_POST['password']         ?? '');
        $password_confirm        = (string)($_POST['password_confirm'] ?? '');

        if ($values['firstname'] === '' || $values['lastname'] === '') {
            $flash = ['type' => 'error', 'msg' => 'First name and last name are required.'];
        } elseif (!filter_var($values['email_address'], FILTER_VALIDATE_EMAIL)) {
            $flash = ['type' => 'error', 'msg' => 'Please enter a valid email address.'];
        } elseif ($values['phone_number'] === '') {
            $flash = ['type' => 'error', 'msg' => 'Phone number is required.'];
        } elseif ($values['school_ref'] === '' || !ctype_digit($values['school_ref'])) {
            $flash = ['type' => 'error', 'msg' => 'Please choose your school.'];
        } elseif (strlen($password) < 8) {
            $flash = ['type' => 'error', 'msg' => 'Password must be at least 8 characters.'];
        } elseif ($password !== $password_confirm) {
            $flash = ['type' => 'error', 'msg' => 'Passwords do not match.'];
        } else {
            try {
                $stmt = $conn->prepare("SELECT user_id FROM users WHERE email_address = ? OR phone_number = ? LIMIT 1");
                $stmt->bind_param('ss', $values['email_address'], $values['phone_number']);
                $stmt->execute();
                $exists = $stmt->get_result()->fetch_assoc();
                $stmt->close();
            } catch (Throwable $e) {
                error_log('signup duplicate check failed: ' . $e->getMessage());
                $exists = null;
                $flash = ['type' => 'error', 'msg' => 'Internal error. Please try again later.'];
            }

            if ($flash === null && $exists) {
                $flash = ['type' => 'error', 'msg' => 'An account with that email or phone number already exists.'];
            }

            $school_country = null;
            $school_region  = null;
            if ($flash === null) {
                try {
                    $sid = (int)$values['school_ref'];
                    $s = $conn->prepare("SELECT country_ref, school_region FROM schools WHERE school_id = ? AND school_status = 'Active' LIMIT 1");
                    $s->bind_param('i', $sid);
                    $s->execute();
                    $school = $s->get_result()->fetch_assoc();
                    $s->close();
                    if (!$school) {
                        $flash = ['type' => 'error', 'msg' => 'Selected school is not available.'];
                    } else {
                        $school_country = (string)($school['country_ref']  ?? '');
                        $school_region  = (string)($school['school_region'] ?? '');
                    }
                } catch (Throwable $e) {
                    error_log('signup school lookup failed: ' . $e->getMessage());
                    $flash = ['type' => 'error', 'msg' => 'Internal error. Please try again later.'];
                }
            }

            $access_level = 0;
            if ($flash === null) {
                try {
                    $p = $conn->prepare("SELECT permissio_id FROM user_permission WHERE permission = 'School Facilitator' LIMIT 1");
                    $p->execute();
                    $perm = $p->get_result()->fetch_assoc();
                    $p->close();
                    $access_level = (int)($perm['permissio_id'] ?? 0);
                } catch (Throwable $e) {
                    error_log('signup permission lookup failed: ' . $e->getMessage());
                }
            }

            if ($flash === null) {
                $hash = auth_hash_password($password);
                if ($hash === null) {
                    $flash = ['type' => 'error', 'msg' => 'Could not secure password. Please try again.'];
                } else {
                    try {
                        $status = 'Pending';
                        $sid    = (int)$values['school_ref'];
                        $ins = $conn->prepare(
                            "INSERT INTO users
                                (firstname, lastname, email_address, phone_number, password,
                                 status, access_level, school_ref, user_country, user_region)
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
                        );
                        $ins->bind_param(
                            'ssssssiiss',
                            $values['firstname'],
                            $values['lastname'],
                            $values['email_address'],
                            $values['phone_number'],
                            $hash,
                            $status,
                            $access_level,
                            $sid,
                            $school_country,
                            $school_region
                        );
                        $ins->execute();
                        $new_user_id = $conn->insert_id;
                        $ins->close();
                    } catch (Throwable $e) {
                        error_log('signup insert failed: ' . $e->getMessage());
                        $new_user_id = 0;
                        $flash = ['type' => 'error', 'msg' => 'Could not create account. Please try again.'];
                    }

                    if ($flash === null && $new_user_id > 0) {
                        $subject = 'BLIS LMS — registration received';
                        $body  = "Dear " . $values['firstname'] . " " . $values['lastname'] . ",\n\n";
                        $body .= "Thank you for registering with BLIS LMS.\n";
                        $body .= "Your account is pending administrator approval. ";
                        $body .= "You will be notified by email once it has been activated.\n\n";
                        $body .= "If you did not create this account, please ignore this email.\n";
                        send_mail($values['email_address'], $subject, $body, false);

                        $flash = [
                            'type' => 'success',
                            'msg'  => 'Account created. It is pending administrator approval — you will receive an email once it is active.',
                        ];
                        $values = ['firstname' => '', 'lastname' => '', 'email_address' => '', 'phone_number' => '', 'school_ref' => ''];
                    }
                }
            }
        }
    }
}

$schools = [];
try {
    $sq = $conn->query("SELECT school_id, school_name FROM schools WHERE school_status = 'Active' ORDER BY school_name ASC");
    if ($sq) {
        while ($row = $sq->fetch_assoc()) {
            $schools[] = $row;
        }
    }
} catch (Throwable $e) {
    error_log('signup schools list failed: ' . $e->getMessage());
}

$csrf = auth_csrf_token();
$registered = ($flash !== null && $flash['type'] === 'success');
?>
<!doctype html>
<html lang="en">
<head>
  <title>Register | BLIS MIS</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="stylesheet" href="./dist/styles.css">
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.13/css/all.css">
  <style>.login{background:url('./dist/images/Microprocessor.jpg')}</style>
</head>
<body class="font-sans login bg-cover min-h-screen">

<?php if ($registered): ?>
  <div class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(0,0,0,.55)">
    <div class="bg-white rounded-lg shadow-2xl max-w-md w-11/12 p-8 text-center">
      <div class="mx-auto mb-4 flex items-center justify-center rounded-full bg-green-100" style="width:72px;height:72px">
        <svg class="text-green-600" xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
      </div>
      <h2 class="text-2xl font-bold text-gray-800 mb-2">Registration received</h2>
      <p class="text-gray-700 mb-2">
        Please wait for the administrator to approve you as a Facilitator.
      </p>
      <p class="text-gray-700 mb-6">
        Once approved, you will receive a confirmation email in your inbox and you'll be able to sign in.
      </p>
      <a href="login.php" class="inline-block px-8 py-2 text-white font-medium tracking-wider bg-blue-900 rounded hover:bg-blue-800">Go to Login</a>
      <div class="mt-3">
        <a href="index.php" class="text-sm text-blue-500 hover:text-blue-800">← Back to home</a>
      </div>
    </div>
  </div>
<?php endif; ?>

<div class="container mx-auto py-8 flex flex-1 justify-center items-center">
  <div class="w-full max-w-lg">
    <div class="leading-loose">
      <form class="max-w-xl m-4 p-10 bg-white rounded shadow-xl" action="" method="POST" autocomplete="on">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
        <a class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800" href="index.php">← Back | Home</a>
        <p class="text-gray-800 font-medium text-center text-lg font-bold">Register as New Facilitator</p>

        <?php if ($flash && !$registered): ?>
          <div class="bg-red-300 border border-red-300 text-red-dark mb-2 px-4 py-3 rounded" role="alert">
            <?= htmlspecialchars($flash['msg'], ENT_QUOTES, 'UTF-8') ?>
          </div>
        <?php endif; ?>

        <div class="mt-2">
          <label class="block text-sm text-gray-600" for="firstname">First name</label>
          <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="firstname" name="firstname" type="text" required maxlength="60"
                 value="<?= htmlspecialchars($values['firstname'], ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="mt-2">
          <label class="block text-sm text-gray-600" for="lastname">Last name</label>
          <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="lastname" name="lastname" type="text" required maxlength="60"
                 value="<?= htmlspecialchars($values['lastname'], ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="mt-2">
          <label class="block text-sm text-gray-600" for="email_address">Email</label>
          <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="email_address" name="email_address" type="email" required maxlength="120" autocomplete="email"
                 value="<?= htmlspecialchars($values['email_address'], ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="mt-2">
          <label class="block text-sm text-gray-600" for="phone_number">Phone number</label>
          <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="phone_number" name="phone_number" type="text" required maxlength="20" autocomplete="tel"
                 value="<?= htmlspecialchars($values['phone_number'], ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="mt-2">
          <label class="block text-sm text-gray-600" for="school_ref">School</label>
          <select class="w-full px-5 py-2 text-gray-700 bg-gray-200 rounded" id="school_ref" name="school_ref" required>
            <option value="">— Select your school —</option>
            <?php foreach ($schools as $s): ?>
              <option value="<?= (int)$s['school_id'] ?>" <?= ((string)$s['school_id'] === $values['school_ref']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($s['school_name'], ENT_QUOTES, 'UTF-8') ?>
              </option>
            <?php endforeach; ?>
          </select>
          <?php if (!$schools): ?>
            <p class="text-xs text-red-700 mt-1">No active schools found. Contact the administrator.</p>
          <?php endif; ?>
        </div>
        <div class="mt-2">
          <label class="block text-sm text-gray-600" for="password">Password (min 8 characters)</label>
          <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="password" name="password" type="password" required minlength="8" autocomplete="new-password">
        </div>
        <div class="mt-2">
          <label class="block text-sm text-gray-600" for="password_confirm">Confirm password</label>
          <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="password_confirm" name="password_confirm" type="password" required minlength="8" autocomplete="new-password">
        </div>

        <div class="mt-4 items-center justify-between">
          <button class="px-12 py-2 text-white font-light tracking-wider bg-blue-900 rounded" name="register" type="submit">Register</button>
          &nbsp;<a class="inline-block right-0 align-baseline font-bold text-sm text-blue-500 hover:text-blue-800" href="login.php">Already have an account? Sign in</a>
        </div>
      </form>
    </div>
  </div>
</div>
</body>
</html>
