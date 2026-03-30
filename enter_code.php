<?php
session_start();
include('db.php');

$error = '';
$success = '';
function console_log($msg) {
    echo "<script>console.log(" . json_encode($msg) . ");</script>";
}

if (isset($_POST['join_exam'])) {
    $exam_code = strtoupper(trim(mysqli_real_escape_string($conn, $_POST['exam_code'])));
    console_log("Received exam code: " . $exam_code);
    if (empty($exam_code)) {
        $error = 'Please enter an exam code.';
    } elseif (strlen($exam_code) < 4) {
        $error = 'Exam code must be at least 4 characters.';
    } else {
        $stmt = $conn->prepare("SELECT * FROM exams WHERE exam_code = ? AND status = 'active'");
        $stmt->bind_param("s", $exam_code);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && mysqli_num_rows($result) > 0) {
            $exam = mysqli_fetch_assoc($result);
            $_SESSION['exam_code']  = $exam_code;
            $_SESSION['exam_id']    = $exam['exam_id'];
            $_SESSION['exam_title'] = $exam['title'];
            $success = 'Code verified! Redirecting you now...';
            echo "<script>
console.log('Redirecting to waiting room...');
setTimeout(() => {
    window.location.href = 'waiting_room.php';
}, 10000);
</script>";
exit();
        } else {
            $error = 'Invalid or expired exam code. Please try again.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <title>Join Exam | BLIS MIS</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="stylesheet" href="./dist/styles.css">
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.13/css/all.css"
        integrity="sha384-DNOHZ68U8hZfKXOrtjWvjxusGo9WQnrNx2sqG0tfsghAvtVlRW3tvkXWZh58N9jp"
        crossorigin="anonymous">
  <style>
    /* Background – same as login page */
    .login { background: url("../dist/images/Microprocessor.jpg") center center / cover no-repeat; }

    /* Dark overlay for depth */
    .overlay {
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,.45);
      z-index: 0;
    }

    /* Card slide-up entrance */
    .card {
      position: relative;
      z-index: 1;
      animation: slideUp .55s cubic-bezier(.22,.68,0,1.2) both;
    }
    @keyframes slideUp {
      from { opacity:0; transform:translateY(36px); }
      to   { opacity:1; transform:translateY(0);    }
    }

    /* Large centred code input */
    .code-input {
      letter-spacing: .35em;
      font-size: 1.7rem;
      font-weight: 700;
      text-align: center;
      text-transform: uppercase;
      caret-color: #16a34a;
      transition: border-color .2s, box-shadow .2s;
    }
    .code-input::placeholder { letter-spacing:.15em; font-weight:400; color:#9ca3af; }
    .code-input:focus {
      outline: none;
      border-color: #16a34a;
      box-shadow: 0 0 0 3px rgba(22,163,74,.25);
    }

    /* Pulsing green icon ring */
    .icon-ring {
      width:72px; height:72px; border-radius:50%;
      background: linear-gradient(135deg,#16a34a 0%,#4ade80 100%);
      display:flex; align-items:center; justify-content:center;
      margin:0 auto 1.25rem;
      box-shadow: 0 0 0 0 rgba(22,163,74,.55);
      animation: pulse 2.2s ease-in-out infinite;
    }
    @keyframes pulse {
      0%   { box-shadow:0 0 0 0   rgba(22,163,74,.55); }
      60%  { box-shadow:0 0 0 16px rgba(22,163,74,0);  }
      100% { box-shadow:0 0 0 0   rgba(22,163,74,0);   }
    }

    /* Animated progress dots below input */
    .digit-dots { display:flex; justify-content:center; gap:.5rem; margin-top:.6rem; }
    .digit-dots span {
      width:10px; height:10px; border-radius:50%;
      background:#e5e7eb;
      transition: background .15s, transform .15s;
    }
    .digit-dots span.active {
      background:#16a34a;
      transform:scale(1.25);
    }

    /* Submit button */
    .btn-join { transition: background .2s, transform .15s, box-shadow .2s; }
    .btn-join:hover {
      background:#15803d;
      transform:translateY(-1px);
      box-shadow:0 6px 18px rgba(22,163,74,.35);
    }
    .btn-join:active { transform:translateY(0); }

    /* Button spinner */
    .spinner {
      display:none;
      width:18px; height:18px;
      border:2.5px solid rgba(255,255,255,.35);
      border-top-color:#fff;
      border-radius:50%;
      animation:spin .7s linear infinite;
      margin:0 auto;
    }
    @keyframes spin { to { transform:rotate(360deg); } }

    /* Alert fade-in */
    .alert-enter { animation: fadeIn .3s ease both; }
    @keyframes fadeIn {
      from { opacity:0; transform:translateY(-8px); }
      to   { opacity:1; transform:translateY(0);    }
    }

    /* Divider with text */
    .divider {
      display:flex; align-items:center; gap:.75rem;
      color:#9ca3af; font-size:.8rem;
    }
    .divider::before,.divider::after {
      content:''; flex:1; height:1px; background:#e5e7eb;
    }

    /* How-it-works steps */
    .step { display:flex; align-items:flex-start; gap:.75rem; font-size:.82rem; color:#6b7280; }
    .step-num {
      width:22px; height:22px; border-radius:50%;
      background:#dcfce7; color:#16a34a;
      font-weight:700; font-size:.75rem;
      display:flex; align-items:center; justify-content:center;
      flex-shrink:0;
    }
  </style>
</head>

<body class="h-screen font-sans login bg-cover">

  <div class="overlay"></div>

  <div class="container mx-auto h-full flex flex-1 justify-center items-center"
       style="position:relative;z-index:1;">
    <div class="w-full max-w-lg card">
      <div class="leading-loose">

        <div class="max-w-xl m-4 p-10 bg-white rounded shadow-xl">

          <!-- Back link -->
          <a class="inline-block align-baseline font-bold text-sm text-gray-500 hover:text-green-700 mb-4 transition-colors"
             href="index">
            <i class="fas fa-arrow-left mr-1"></i> Back | Home
          </a>

          <!-- Icon + heading -->
          <div class="text-center mb-6">
            <div class="icon-ring">
              <i class="fas fa-key fa-lg" style="color:#fff;"></i>
            </div>
            <h1 class="text-gray-800 font-bold text-xl">Join an Exam</h1>
            <p class="text-gray-500 text-sm mt-1">Enter the code provided by your instructor</p>
          </div>

          <!-- Alerts -->
          <?php if ($error): ?>
          <div class="flex items-center mb-4 bg-red-100 border border-red-300 text-red-700 text-sm font-semibold px-4 py-3 rounded alert-enter" role="alert">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <?= htmlspecialchars($error) ?>
          </div>
          <?php endif; ?>

          <?php if ($success): ?>
          <div class="flex items-center mb-4 bg-green-500 text-white text-sm font-bold px-4 py-3 rounded alert-enter" role="alert">
            <svg class="fill-current w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
              <path d="M12.432 0c1.34 0 2.01.912 2.01 1.957 0 1.305-1.164 2.512-2.679 2.512-1.269 0-2.009-.75-1.974-1.99C9.789 1.436 10.67 0 12.432 0zM8.309 20c-1.058 0-1.833-.652-1.093-3.524l1.214-5.092c.211-.814.246-1.141 0-1.141-.317 0-1.689.562-2.502 1.117l-.528-.88c2.572-2.186 5.531-3.467 6.801-3.467 1.057 0 1.233 1.273.705 3.23l-1.391 5.352c-.246.945-.141 1.271.106 1.271.317 0 1.357-.392 2.379-1.207l.6.814C12.098 19.02 9.365 20 8.309 20z"/>
            </svg>
            <?= htmlspecialchars($success) ?>
          </div>
          <?php endif; ?>

          <!-- Form -->
          <form method="POST" action="" id="codeForm">

            <label class="block text-sm font-semibold text-gray-700 mb-1 text-center"
                   for="exam_code">
              Exam Code
            </label>

            <input
              id="exam_code"
              name="exam_code"
              type="text"
              maxlength="12"
              autocomplete="off"
              spellcheck="false"
              placeholder="ABC123"
              class="code-input w-full px-5 py-3 text-gray-800 bg-gray-100 border-2 border-gray-200 rounded-lg"
              value="<?= isset($_POST['exam_code']) ? htmlspecialchars(strtoupper(trim($_POST['exam_code']))) : '' ?>"
              required
            >

            <!-- Animated progress dots -->
            <div class="digit-dots" id="digitDots">
              <span></span><span></span><span></span>
              <span></span><span></span><span></span>
            </div>

            <button
              type="submit"
              name="join_exam"
              id="joinBtn"
              class="btn-join mt-6 w-full py-2 text-white font-semibold tracking-wide bg-green-600 rounded-lg">
              <span id="btnText"><i class="fas fa-sign-in-alt mr-2"></i>Join Exam</span>
              <div class="spinner" id="btnSpinner"></div>
            </button>

          </form>

          <!-- Divider -->
          <div class="divider my-6">How it works</div>

          <!-- Steps -->
          <div class="space-y-3">
            <div class="step">
              <div class="step-num">1</div>
              <span>Get the <strong class="text-gray-700">exam code</strong> from your instructor or the on-screen display.</span>
            </div>
            <div class="step">
              <div class="step-num">2</div>
              <span>Type the code above and click <strong class="text-gray-700">Join Exam</strong>.</span>
            </div>
            <div class="step">
              <div class="step-num">3</div>
              <span>Choose your participation type and wait in the lobby until the exam begins.</span>
            </div>
          </div>

          <!-- Footer -->
          <p class="text-center text-xs text-gray-400 mt-8">
            Having trouble?
            <a href="mailto:info@blisglobal.tech"
               class="text-green-600 hover:underline font-semibold">
              Contact support
            </a>
          </p>

        </div><!-- /card -->
      </div>
    </div>
  </div>

  <script>
    const input   = document.getElementById('exam_code');
    const dots    = document.querySelectorAll('#digitDots span');
    const form    = document.getElementById('codeForm');
    const btnText = document.getElementById('btnText');
    const spinner = document.getElementById('btnSpinner');

    // Strip non-alphanumeric and uppercase as user types
    input.addEventListener('input', function () {
      this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
      updateDots();
    });

    function updateDots() {
      const len = input.value.length;
      dots.forEach(function (d, i) {
        d.classList.toggle('active', i < len);
      });
    }

    // Show spinner on submit
    form.addEventListener('submit', function () {
      btnText.style.display = 'none';
      spinner.style.display = 'block';
    });

    // Initialise dots for repopulated values & auto-focus
    updateDots();
    input.focus();
  </script>

</body>
</html>