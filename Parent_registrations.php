<?php 
session_start();
include('db.php');

$errors = [];
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize all inputs
    $parent_school = mysqli_real_escape_string($conn, $_POST['parent_school']);
    $parent_fname = mysqli_real_escape_string($conn, $_POST['parent_fname']);
    $parent_lname = mysqli_real_escape_string($conn, $_POST['parent_lname']);
    $parent_gender = mysqli_real_escape_string($conn, $_POST['parent_gender']);
    $parent_phone = mysqli_real_escape_string($conn, $_POST['parent_phone']);
    $parent_email = mysqli_real_escape_string($conn, $_POST['parent_email']);
    $parent_profession = mysqli_real_escape_string($conn, $_POST['parent_profession']);
    $parent_work_place = mysqli_real_escape_string($conn, $_POST['parent_work_place']);
    $parent_password = mysqli_real_escape_string($conn, $_POST['parent_password']);
    $parent_login_chanel = mysqli_real_escape_string($conn, $_POST['parent_login_chanel']);
    $parent_status = "Active";
    
    $hashed_password = md5($parent_password);
    
    // Validation
    $required_fields = [
        'parent_fname' => 'First Name',
        'parent_lname' => 'Last Name',
        'parent_gender' => 'Gender',
        'parent_phone' => 'Phone Number',
        'parent_email' => 'Email Address',
        'parent_password' => 'Password',
        'parent_login_chanel' => 'Login Method',
        'parent_school' => 'School'
    ];
    
    foreach ($required_fields as $field => $name) {
        if (empty($$field)) {
            $errors[] = "$name is required";
        }
    }
    
    if (!filter_var($parent_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (!preg_match('/^\+?\d{7,15}$/', $parent_phone)) {
        $errors[] = "Phone number must be 7-15 digits, with optional '+' prefix";
    }
    
    if (empty($errors)) {
        // Check if user exists
        $sql = "SELECT * FROM students_parent_details 
                WHERE parent_phone='$parent_phone' OR parent_email='$parent_email'";
        
        $result = mysqli_query($conn, $sql);
        
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $parent_login_chanel_db = $row['parent_login_chanel'];
            $parent_email_db = $row['parent_email'];
            $parent_phone_db = $row['parent_phone'];
            
            if ($parent_login_chanel_db == "Password") {
                if ($hashed_password == $row['parent_password']) {
                    // Login successful
                    $_SESSION['logged_in'] = true;
                    $_SESSION['last_activity'] = time();
                    $_SESSION['parent_id'] = $row['parent_id'];
                    $_SESSION['parent_fname'] = $row['parent_fname'];
                    $_SESSION['parent_lname'] = $row['parent_lname'];
                    $_SESSION['permissio_location'] = "Auth/Parents/";
                    $success = "login_success";
                } else {
                    $errors[] = "Invalid password";
                }
            } else {
                if ($parent_email_db == $parent_email && $parent_phone_db == $parent_phone) {
                    // Login successful
                    $_SESSION['logged_in'] = true;
                    $_SESSION['last_activity'] = time();
                    $_SESSION['parent_id'] = $row['parent_id'];
                    $_SESSION['parent_fname'] = $row['parent_fname'];
                    $_SESSION['parent_lname'] = $row['parent_lname'];
                    $_SESSION['permissio_location'] = "Auth/Parents/";
                    $success = "login_success";
                } else {
                    $errors[] = "Invalid credentials for selected login method";
                }
            }
        } else {
            // Register new user
            $insert_sql = "INSERT INTO students_parent_details  
                (parent_school, parent_fname, parent_lname, parent_gender, parent_phone, 
                 parent_email, parent_profession, parent_work_place, parent_password, 
                 parent_login_chanel, parent_status) 
                VALUES 
                ('$parent_school', '$parent_fname', '$parent_lname', '$parent_gender', '$parent_phone', 
                 '$parent_email', '$parent_profession', '$parent_work_place', '$hashed_password', 
                 '$parent_login_chanel', '$parent_status')";
            
            if (mysqli_query($conn, $insert_sql)) {
                $new_parent_id = mysqli_insert_id($conn);
                
                // Set session for new user
                $_SESSION['logged_in'] = true;
                $_SESSION['last_activity'] = time();
                $_SESSION['parent_id'] = $new_parent_id;
                $_SESSION['parent_fname'] = $parent_fname;
                $_SESSION['parent_lname'] = $parent_lname;
                $_SESSION['permissio_location'] = "Auth/Parents/";
                $success = "registration_success";
            } else {
                $errors[] = "Registration failed: " . mysqli_error($conn);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Parent Registration | BLIS MIS</title>
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
      /* background: linear-gradient(135deg, #1a2a6c, #b21f1f, #1a2a6c);*/
      background: url('./dist/images/Microprocessor.jpg');
      background-size: 400% 400%;
      animation: gradientBG 15s ease infinite;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
      color: #333;
      line-height: 1.6;
    }
    
    @keyframes gradientBG {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }
    
    .registration-container {
      background: rgba(255, 255, 255, 0.95);
      border-radius: 15px;
      box-shadow: var(--shadow);
      width: 100%;
      max-width: 850px;
      overflow: hidden;
      transform: translateY(0);
      transition: transform 0.3s ease;
    }
    
    .registration-container:hover {
      transform: translateY(-5px);
    }
    
    .registration-header {
      background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
      color: white;
      padding: 25px 30px;
      text-align: center;
      position: relative;
      border-bottom: 3px solid var(--secondary);
    }
    
    .registration-header h2 {
      font-size: 1.8rem;
      margin-bottom: 5px;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 12px;
      font-weight: 600;
    }
    
    .registration-header a {
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
    
    .registration-header a:hover {
      transform: translateY(-50%) translateX(-5px);
      background: rgba(255, 255, 255, 0.25);
    }
    
    .form-content {
      padding: 30px;
      max-height: 70vh;
      overflow-y: auto;
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
    
    .form-group input,
    .form-group select {
      width: 100%;
      padding: 15px 15px 15px 45px;
      border: 2px solid var(--border);
      border-radius: 10px;
      font-size: 16px;
      transition: all 0.3s ease;
      background: #f9f9f9;
      color: #333;
    }
    
    .form-group input:focus,
    .form-group select:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.15);
      background: white;
    }
    
    .form-group::before {
      content: "";
      position: absolute;
      left: 15px;
      top: 42px;
      color: var(--primary);
      font-size: 18px;
      z-index: 2;
    }
    
    .form-row {
      display: flex;
      gap: 25px;
      margin-bottom: 20px;
    }
    
    .form-col {
      flex: 1;
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
    
    .btn-outline {
      background: transparent;
      border: 2px solid var(--primary);
      color: var(--primary);
    }
    
    .btn-outline:hover {
      background: var(--primary);
      color: white;
    }
    
    .form-links {
      display: flex;
      gap: 20px;
      flex-wrap: wrap;
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
    
    .school-option {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 10px;
    }
    
    .school-icon {
      background: var(--primary);
      color: white;
      width: 36px;
      height: 36px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 18px;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
      .form-row {
        flex-direction: column;
        gap: 0;
      }
      
      .form-content {
        padding: 20px;
      }
      
      .registration-header {
        padding: 20px 15px;
      }
      
      .registration-header h2 {
        font-size: 1.5rem;
      }
      
      .form-links {
        gap: 10px;
        justify-content: center;
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
      
      .form-links {
        flex-direction: column;
        gap: 10px;
      }
    }
    
    /* Scrollbar styling */
    .form-content::-webkit-scrollbar {
      width: 10px;
    }
    
    .form-content::-webkit-scrollbar-track {
      background: #f1f1f1;
      border-radius: 10px;
    }
    
    .form-content::-webkit-scrollbar-thumb {
      background: var(--primary);
      border-radius: 10px;
    }
    
    .form-content::-webkit-scrollbar-thumb:hover {
      background: var(--primary-dark);
    }
    
    /* Animation for form elements */
    @keyframes slideIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    .animate-form {
      animation: slideIn 0.6s ease forwards;
    }
    
    .password-toggle {
      position: absolute;
      right: 15px;
      top: 48px;
      cursor: pointer;
      color: var(--gray);
    }
  </style>
</head>
<body>
<div class="registration-container">
  <div class="registration-header">
    <a href="index">
      <i class="fas fa-arrow-left"></i> Back to Home
    </a>
    <h2>
      <i class="fas fa-user-friends"></i> Parent Registration
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
                window.location.href = "' . $_SESSION['permissio_location'].'Add_New_Student' . '";
              }, 1500);
            </script>';
    }
    elseif ($success === "registration_success") {
      echo '<div class="alert alert-success">
              <i class="fas fa-check-circle alert-icon"></i>
              <div>
                <p><strong>Registration successful!</strong> Welcome, ' . htmlspecialchars($_SESSION['parent_fname']) . '!</p>
                <p>You are being logged in and redirected to your dashboard...</p>
              </div>
            </div>
            <script>
              setTimeout(function() {
                window.location.href = "' . $_SESSION['permissio_location'] .'Add_New_Student' . '";
              }, 1500);
            </script>';
    }
    
    // Display errors
    if (!empty($errors)) {
      echo '<div class="alert alert-danger">
              <i class="fas fa-exclamation-circle alert-icon"></i>
              <div>
                <p><strong>Please fix the following errors:</strong></p>
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
        <label for="parent_school" class="required"><i class="fas fa-school"></i> Registering Student At</label>
        <select id="parent_school" name="parent_school" required>
          <?php
          $select_school = mysqli_query($conn, "SELECT * FROM schools WHERE country_ref='186' AND school_status='Active'");
          while ($school = mysqli_fetch_assoc($select_school)) {
            echo '<option value="' . $school['school_id'] . '">' . $school['school_name'] . '</option>';
          }
          ?>
        </select>
      </div>
      
      <div class="form-row">
        <div class="form-col animate-form" style="animation-delay: 0.1s">
          <div class="form-group">
            <label for="parent_fname" class="required"><i class="fas fa-user"></i> First Name</label>
            <input type="text" id="parent_fname" name="parent_fname" required 
                   placeholder="Enter first name"
                   value="<?php echo isset($_POST['parent_fname']) ? htmlspecialchars($_POST['parent_fname']) : ''; ?>">
          </div>
        </div>
        
        <div class="form-col animate-form" style="animation-delay: 0.2s">
          <div class="form-group">
            <label for="parent_lname" class="required"><i class="fas fa-user"></i> Last Name</label>
            <input type="text" id="parent_lname" name="parent_lname" required 
                   placeholder="Enter last name"
                   value="<?php echo isset($_POST['parent_lname']) ? htmlspecialchars($_POST['parent_lname']) : ''; ?>">
          </div>
        </div>
      </div>
      
      <div class="form-group animate-form" style="animation-delay: 0.3s">
        <label for="parent_gender" class="required"><i class="fas fa-venus-mars"></i> Gender</label>
        <select id="parent_gender" name="parent_gender" required>
          <option value="">Select Gender</option>
          <option value="Male" <?php echo (isset($_POST['parent_gender']) && $_POST['parent_gender'] === 'Male') ? 'selected' : ''; ?>>Male</option>
          <option value="Female" <?php echo (isset($_POST['parent_gender']) && $_POST['parent_gender'] === 'Female') ? 'selected' : ''; ?>>Female</option>
        </select>
      </div>
      
      <div class="form-row">
        <div class="form-col animate-form" style="animation-delay: 0.4s">
          <div class="form-group">
            <label for="parent_phone" class="required"><i class="fas fa-phone"></i> Phone Number</label>
            <input type="tel" id="parent_phone" name="parent_phone" required 
                   placeholder="+250 700 000 000"
                   value="<?php echo isset($_POST['parent_phone']) ? htmlspecialchars($_POST['parent_phone']) : ''; ?>">
          </div>
        </div>
        
        <div class="form-col animate-form" style="animation-delay: 0.5s">
          <div class="form-group">
            <label for="parent_email" class="required"><i class="fas fa-envelope"></i> Email Address</label>
            <input type="email" id="parent_email" name="parent_email" required 
                   placeholder="your.email@example.com"
                   value="<?php echo isset($_POST['parent_email']) ? htmlspecialchars($_POST['parent_email']) : ''; ?>">
          </div>
        </div>
      </div>
      
      <div class="form-row">
        <div class="form-col animate-form" style="animation-delay: 0.6s">
          <div class="form-group">
            <label for="parent_profession"><i class="fas fa-briefcase"></i> Profession</label>
            <input type="text" id="parent_profession" name="parent_profession" 
                   placeholder="Your profession"
                   value="<?php echo isset($_POST['parent_profession']) ? htmlspecialchars($_POST['parent_profession']) : ''; ?>">
          </div>
        </div>
        
        <div class="form-col animate-form" style="animation-delay: 0.7s">
          <div class="form-group">
            <label for="parent_work_place"><i class="fas fa-building"></i> Place of Work</label>
            <input type="text" id="parent_work_place" name="parent_work_place" 
                   placeholder="Your workplace"
                   value="<?php echo isset($_POST['parent_work_place']) ? htmlspecialchars($_POST['parent_work_place']) : ''; ?>">
          </div>
        </div>
      </div>
      
      <div class="form-group animate-form" style="animation-delay: 0.8s">
        <label for="parent_login_chanel" class="required"><i class="fas fa-sign-in-alt"></i> Login Method</label>
        <select id="parent_login_chanel" name="parent_login_chanel" required>
          <option value="">Select Login Method</option>
          <option value="Password" <?php echo (isset($_POST['parent_login_chanel']) && $_POST['parent_login_chanel'] === 'Password') ? 'selected' : ''; ?>>Email & Password</option>
          <option value="Phone" <?php echo (isset($_POST['parent_login_chanel']) && $_POST['parent_login_chanel'] === 'Phone') ? 'selected' : ''; ?>>Email & Phone Number</option>
        </select>
      </div>
      
      <div class="form-group animate-form" style="animation-delay: 0.9s">
        <label for="parent_password" class="required"><i class="fas fa-lock"></i> Password</label>
        <input type="password" id="parent_password" name="parent_password" required 
               placeholder="Create a password">
        <span class="password-toggle" id="passwordToggle">
          <i class="fas fa-eye"></i>
        </span>
      </div>
      
      <div class="form-group animate-form" style="animation-delay: 1s">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="terms" required>
          <label class="form-check-label" for="terms">
            I agree to the <a href="#" style="color: var(--primary);">Terms and Conditions</a>
          </label>
        </div>
      </div>
  </div>
  
  <div class="form-footer">
    <button type="submit" class="btn btn-primary">
      <i class="fas fa-user-plus"></i> Register
    </button>
    
    <div class="form-links">
      <a href="Reset_password">
        <i class="fas fa-key"></i> Forgot Password?
      </a>
      <a href="Student_login">
        <i class="fas fa-user-graduate"></i> Student Login
      </a>
      <a href="index">
        <i class="fas fa-home"></i> Go Home
      </a>
    </div>
  </div>
  </form>
</div>

<script>
  // Toggle password visibility
  const passwordToggle = document.getElementById('passwordToggle');
  const passwordInput = document.getElementById('parent_password');
  
  passwordToggle.addEventListener('click', function() {
    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', type);
    this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
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