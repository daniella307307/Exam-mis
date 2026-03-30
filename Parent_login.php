<?php 
session_start();
include('db.php');

$errors = [];
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize all inputs
    $parent_email = mysqli_real_escape_string($conn, $_POST['parent_email']);
    $parent_credential = mysqli_real_escape_string($conn, $_POST['parent_credential']);
    
    // Query database for parent
    $sql = "SELECT * FROM students_parent_details WHERE parent_email = '$parent_email'";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) > 0) {
        $user_details = mysqli_fetch_assoc($result);
        
        $parent_phone = $user_details['parent_phone'];
        $stored_password = $user_details['parent_password'];
        $login_method = $user_details['parent_login_chanel'];
        
        if ($login_method == "Password") {
            // Verify password
            if (md5($parent_credential) === $stored_password) {
                // Login successful
                $_SESSION['logged_in'] = true;
                $_SESSION['last_activity'] = time();
                $_SESSION['parent_id'] = $user_details['parent_id'];
                $_SESSION['parent_fname'] = $user_details['parent_fname'];
                $_SESSION['parent_lname'] = $user_details['parent_lname'];
                $_SESSION['permissio_location'] = "Auth/Parents/";
                $success = "login_success";
            } else {
                $errors[] = "Invalid password";
            }
        } else { // Phone login method
            if ($parent_credential === $parent_phone) {
                // Login successful
                $_SESSION['logged_in'] = true;
                $_SESSION['last_activity'] = time();
                $_SESSION['parent_id'] = $user_details['parent_id'];
                $_SESSION['parent_fname'] = $user_details['parent_fname'];
                $_SESSION['parent_lname'] = $user_details['parent_lname'];
                $_SESSION['permissio_location'] = "Auth/Parents/";
                $success = "login_success";
            } else {
                $errors[] = "Invalid phone number";
            }
        }
    } else {
        $errors[] = "No account found with that email address";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Parent Login | BLIS MIS</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary: #4361ee;
      --primary-dark: #3a56d4;
      --secondary: #4cc9f0;
      --success: #38b000;
      --danger: #e63946;
      --light: #f8f9fa;
      --dark: #212529;
      --gray: #6c757d;
      --light-gray: #e9ecef;
      --border: #ced4da;
      --shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }
    
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }
    
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: url('./dist/images/Microprocessor.jpg') no-repeat center center fixed;
      background-size: cover;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
      color: #333;
      line-height: 1.6;
      position: relative;
    }
    
    /* Overlay to improve readability */
    body::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.6);
      z-index: -1;
    }
    
    .login-container {
      background: rgba(255, 255, 255, 0.95);
      border-radius: 15px;
      box-shadow: var(--shadow);
      width: 100%;
      max-width: 500px;
      overflow: hidden;
      transform: translateY(0);
      transition: transform 0.3s ease;
    }
    
    .login-container:hover {
      transform: translateY(-5px);
    }
    
    .login-header {
      background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
      color: white;
      padding: 25px 30px;
      text-align: center;
      position: relative;
      border-bottom: 3px solid var(--secondary);
    }
    
    .login-header h2 {
      font-size: 1.8rem;
      margin-bottom: 5px;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 12px;
      font-weight: 600;
    }
    
    .login-header a {
      position: absolute;
      left: 25px;
      top: 50%;
      transform: translateY(-50%);
      color: white;
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 8px;
      transition: all 0.3s ease;
      font-weight: 500;
      padding: 6px 12px;
      border-radius: 30px;
      background: rgba(255, 255, 255, 0.15);
    }
    
    .login-header a:hover {
      transform: translateY(-50%) translateX(-5px);
      background: rgba(255, 255, 255, 0.25);
    }
    
    .form-content {
      padding: 30px;
    }
    
    .form-group {
      margin-bottom: 22px;
      position: relative;
    }
    
    .form-group label {
      display: block;
      margin-bottom: 10px;
      font-weight: 600;
      color: #444;
      font-size: 15px;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    
    .form-group label i {
      color: var(--primary);
      width: 20px;
      text-align: center;
    }
    
    .form-group input {
      width: 100%;
      padding: 15px 15px 15px 45px;
      border: 2px solid var(--border);
      border-radius: 10px;
      font-size: 16px;
      transition: all 0.3s ease;
      background: #f9f9f9;
      color: #333;
    }
    
    .form-group input:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.15);
      background: white;
    }
    
    .alert {
      padding: 18px 20px;
      margin-bottom: 25px;
      border-radius: 10px;
      font-size: 15px;
      display: flex;
      align-items: center;
      animation: fadeIn 0.5s ease;
    }
    
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    .alert-success {
      background-color: #d4edda;
      color: #155724;
      border-left: 5px solid #28a745;
    }
    
    .alert-danger {
      background-color: #f8d7da;
      color: #721c24;
      border-left: 5px solid #dc3545;
    }
    
    .alert-warning {
      background-color: #fff3cd;
      color: #856404;
      border-left: 5px solid #ffc107;
    }
    
    .alert-icon {
      margin-right: 15px;
      font-size: 24px;
      min-width: 30px;
      text-align: center;
    }
    
    .form-footer {
      padding: 22px 30px;
      background: #f8f9fa;
      border-top: 2px solid #eee;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 20px;
    }
    
    .btn {
      padding: 14px 30px;
      border-radius: 10px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      border: none;
      display: inline-flex;
      align-items: center;
      gap: 10px;
      font-size: 16px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }
    
    .btn-primary {
      background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
      color: white;
    }
    
    .btn-primary:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
      background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
    }
    
    .form-links {
      display: flex;
      gap: 20px;
      flex-wrap: wrap;
      justify-content: center;
      width: 100%;
      margin-top: 15px;
    }
    
    .form-links a {
      color: var(--primary);
      text-decoration: none;
      font-weight: 500;
      transition: all 0.2s ease;
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 6px 12px;
      border-radius: 6px;
    }
    
    .form-links a:hover {
      color: var(--primary-dark);
      background: rgba(67, 97, 238, 0.1);
      transform: translateY(-2px);
    }
    
    .required:after {
      content: " *";
      color: var(--danger);
    }
    
    .credential-toggle {
      position: absolute;
      right: 15px;
      top: 48px;
      cursor: pointer;
      color: var(--gray);
    }
    
    .login-options {
      display: flex;
      justify-content: center;
      gap: 15px;
      margin-top: 20px;
     
    }
    
    .option-card {
      background: #f0f5ff;
      border-radius: 10px;
      padding: 15px;
      text-align: center;
      height: ;: 50px;
      cursor: pointer;
      transition: all 0.3s ease;
      border: 2px solid transparent;
       width: 50%;
    }
    
    .option-card.active {
      border-color: var(--primary);
      background: #e1e9ff;
    }
    
    .option-card i {
      font-size: 24px;
      color: var(--primary);
      margin-bottom: 10px;
    }
    
    .option-card span {
      font-size: 14px;
      font-weight: 500;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
      .form-content {
        padding: 20px;
      }
      
      .login-header {
        padding: 20px 15px;
      }
      
      .login-header h2 {
        font-size: 1.5rem;
      }
      
      .form-links {
        gap: 10px;
        flex-direction: column;
      }
    }
    
    @media (max-width: 480px) {
      .form-footer {
        flex-direction: column;
        align-items: stretch;
        text-align: center;
      }
      
      .btn {
        width: 100%;
        justify-content: center;
      }
    }
    
    /* Animation for form elements */
    @keyframes slideIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    .animate-form {
      animation: slideIn 0.6s ease forwards;
    }
  </style>
</head>
<body>
<div class="login-container">
  <div class="login-header">
    <!--
    <a href="index">
      <i class="fas fa-arrow-left"></i> Back to Home
    </a>-->
    <h2>
     <i class="fas fa-sign-in-alt"></i> Parent Login   
    </h2>
  </div>
  
  <div class="form-content">
    <?php
    // Display success messages
    if ($success === "login_success") {
      echo '<div class="alert alert-success">
              <i class="fas fa-check-circle alert-icon"></i>
              <div>
                <p><strong>Login successful!</strong> Welcome back, ' . htmlspecialchars($_SESSION['parent_fname']) . '!</p>
                <p>You are being redirected to your dashboard...</p>
              </div>
            </div>
            <script>
              setTimeout(function() {
                window.location.href = "' . $_SESSION['permissio_location'] . '";
              }, 1500);
            </script>';
    }
    
    // Display errors
    if (!empty($errors)) {
      echo '<div class="alert alert-danger">
              <i class="fas fa-exclamation-circle alert-icon"></i>
              <div>
                <p><strong>Login failed:</strong></p>
                <ul>';
      foreach ($errors as $error) {
        echo '<li>' . htmlspecialchars($error) . '</li>';
      }
      echo '    </ul>
              </div>
            </div>';
    }
    ?>
  
    <form action="" method="POST">
      <div class="form-group animate-form">
        <label for="parent_email" class="required"><i class="fas fa-envelope"></i> Email Address</label>
        <input type="email" id="parent_email" name="parent_email" required 
               placeholder="your.email@example.com"
               value="<?php echo isset($_POST['parent_email']) ? htmlspecialchars($_POST['parent_email']) : ''; ?>">
        <i class="fas fa-envelope input-icon"></i>
      </div>
      
      <div class="form-group animate-form" style="animation-delay: 0.2s">
        <label for="parent_credential" class="required"><i class="fas fa-key"></i> Login Credential</label>
        <input type="text" id="parent_credential" name="parent_credential" required 
               placeholder="Enter phone number or password"
               value="<?php echo isset($_POST['parent_credential']) ? htmlspecialchars($_POST['parent_credential']) : ''; ?>">
        <span class="credential-toggle" id="credentialToggle">
          <i class="fas fa-eye"></i>
        </span>
      </div>
      
      <div class="form-group animate-form" style="animation-delay: 0.4s">
        <div class="login-options">
          <div class="option-card" data-type="phone">
            <i class="fas fa-phone"></i>
            <span>Phone Login</span>
          </div>
          <div class="option-card" data-type="password">
            <i class="fas fa-lock"></i>
            <span>Password Login</span>
          </div>
        </div>
      </div>
      
  </div>
  
  <div class="form-footer">
    <button type="submit" class="btn btn-primary">
      <i class="fas fa-sign-in-alt"></i> Login
    </button>
    
    <div class="form-links">
      <a href="Parent_Reset_password">
        <i class="fas fa-key"></i> Forgot Password?
      </a>
      <a href="Parent_registrations">
        <i class="fas fa-user-plus"></i> Create Account
      </a>
      <a href="index">
        <i class="fas fa-home"></i> Main Page
      </a>
    </div>
  </div>
  </form>
</div>

<script>
  // Toggle credential visibility
  const credentialToggle = document.getElementById('credentialToggle');
  const credentialInput = document.getElementById('parent_credential');
  
  credentialToggle.addEventListener('click', function() {
    const type = credentialInput.getAttribute('type') === 'password' ? 'text' : 'password';
    credentialInput.setAttribute('type', type);
    this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
  });
  
  // Set default credential input type
  credentialInput.setAttribute('type', 'text');
  
  // Login method selection
  const optionCards = document.querySelectorAll('.option-card');
  
  optionCards.forEach(card => {
    card.addEventListener('click', function() {
      // Remove active class from all cards
      optionCards.forEach(c => c.classList.remove('active'));
      
      // Add active class to clicked card
      this.classList.add('active');
      
      // Update input placeholder based on selection
      const type = this.getAttribute('data-type');
      if (type === 'phone') {
        credentialInput.placeholder = "Enter your phone number";
        credentialInput.setAttribute('type', 'text');
        credentialToggle.innerHTML = '<i class="fas fa-eye"></i>';
      } else {
        credentialInput.placeholder = "Enter your password";
        credentialInput.setAttribute('type', 'password');
        credentialToggle.innerHTML = '<i class="fas fa-eye-slash"></i>';
      }
    });
  });
  
  // Form animations
  document.addEventListener('DOMContentLoaded', function() {
    const formGroups = document.querySelectorAll('.form-group');
    
    formGroups.forEach((group, index) => {
      group.style.opacity = '0';
      group.style.transform = 'translateY(20px)';
      group.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
      
      setTimeout(() => {
        group.style.opacity = '1';
        group.style.transform = 'translateY(0)';
      }, 100 * index);
    });
  });
</script>
</body>
</html>