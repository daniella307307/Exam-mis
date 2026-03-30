<?php
include('db.php');
session_start();

// Initialize variables
$errors = [];
$success = '';

// Form submission handling
if(isset($_POST['register'])) {
    // Process form data
    $firstname = mysqli_real_escape_string($conn, $_POST['firstname']);
    $lastname = mysqli_real_escape_string($conn, $_POST['lastname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $status = 'Active'; // Default status
    $access_level = intval($_POST['access_level']);
    $school_ref = intval($_POST['school_ref']);
    $user_country = intval($_POST['user_country']);
    $user_region = intval($_POST['user_region']);
    $user_group_ref = 1; // Default group
    
    // Handle file upload
    $user_image = '';
    if(isset($_FILES['user_image']) && $_FILES['user_image']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "uploads/";
        if(!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $target_file = $target_dir . basename($_FILES["user_image"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        // Check if image file is actual image
        $check = getimagesize($_FILES["user_image"]["tmp_name"]);
        if($check !== false) {
            // Generate unique filename
            $new_filename = uniqid() . '.' . $imageFileType;
            $target_file = $target_dir . $new_filename;
            
            // Move the uploaded file
            if(move_uploaded_file($_FILES["user_image"]["tmp_name"], $target_file)) {
                $user_image = $target_file;
            }
        }
    }
    
    // Insert into database
    $query = "INSERT INTO users (firstname, lastname, email_address, phone_number, password, status, 
                access_level, school_ref, user_country, user_region, user_group_ref, user_image)
              VALUES ('$firstname', '$lastname', '$email', '$phone', '$password', '$status', 
                $access_level, $school_ref, $user_country, $user_region, $user_group_ref, '$user_image')";
    
    if(mysqli_query($conn, $query)) {
        $success = "Registration successful!";
        // Reset form
        $_POST = array();
    } else {
        $errors[] = "Database error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Registration System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <style>
    :root {
      --primary: #4361ee;
      --secondary: #3f37c9;
      --success: #4cc9f0;
      --light: #f8f9fa;
      --dark: #212529;
      --border-radius: 8px;
      --shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    body {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      padding: 20px;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    .registration-container {
      background: white;
      border-radius: var(--border-radius);
      box-shadow: var(--shadow);
      overflow: hidden;
      max-width: 800px;
      width: 100%;
      margin: 0 auto;
    }
    
    .registration-header {
      background: var(--primary);
      color: white;
      padding: 20px;
      text-align: center;
    }
    
    .step-indicator {
      display: flex;
      justify-content: space-between;
      padding: 20px 40px;
      background: #f0f5ff;
    }
    
    .step {
      display: flex;
      flex-direction: column;
      align-items: center;
      position: relative;
    }
    
    .step-number {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: #d1d1d1;
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 8px;
      font-weight: bold;
      z-index: 2;
    }
    
    .step.active .step-number {
      background: var(--primary);
    }
    
    .step.completed .step-number {
      background: var(--success);
    }
    
    .step-title {
      font-size: 14px;
      color: #666;
      font-weight: 500;
    }
    
    .step.active .step-title {
      color: var(--primary);
      font-weight: 600;
    }
    
    .progress-connector {
      position: absolute;
      height: 4px;
      top: 20px;
      left: calc(50% + 20px);
      right: calc(-50% + 20px);
      background: #d1d1d1;
      z-index: 1;
    }
    
    .step.completed + .step .progress-connector,
    .step.active + .step .progress-connector {
      background: var(--success);
    }
    
    .form-section {
      padding: 30px;
      display: none;
    }
    
    .form-section.active {
      display: block;
    }
    
    .form-footer {
      padding: 20px;
      background: #f8f9fa;
      display: flex;
      justify-content: space-between;
      border-top: 1px solid #eee;
    }
    
    .btn-primary {
      background: var(--primary);
      border: none;
      padding: 10px 20px;
      font-weight: 500;
    }
    
    .btn-primary:hover {
      background: var(--secondary);
    }
    
    .btn-outline-primary {
      border-color: var(--primary);
      color: var(--primary);
    }
    
    .btn-outline-primary:hover {
      background: var(--primary);
      color: white;
    }
    
    .form-label {
      font-weight: 500;
      margin-bottom: 8px;
    }
    
    .form-control, .form-select {
      border-radius: 6px;
      padding: 10px;
      border: 1px solid #ddd;
    }
    
    .form-control:focus, .form-select:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
    }
    
    .alert {
      border-radius: 6px;
    }
    
    .preview-image {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      object-fit: cover;
      border: 3px solid var(--primary);
      display: block;
      margin: 15px auto;
    }
    
    .password-container {
      position: relative;
    }
    
    .toggle-password {
      position: absolute;
      right: 10px;
      top: 10px;
      cursor: pointer;
      color: #777;
    }
    
    .success-message {
      background: #d4edda;
      border-color: #c3e6cb;
      color: #155724;
      padding: 20px;
      border-radius: var(--border-radius);
      text-align: center;
      margin-bottom: 20px;
    }
    
    .error-message {
      background: #f8d7da;
      border-color: #f5c6cb;
      color: #721c24;
      padding: 10px;
      border-radius: 4px;
      margin-bottom: 15px;
    }
  </style>
</head>
<body>
  <div class="registration-container">
    <div class="registration-header">
      <h2><i class="fas fa-user-plus me-2"></i>Register as New Facilitator</h2>
    </div>
    
    <!-- Step Indicator -->
    <div class="step-indicator">
      <div class="step active" data-step="1">
        <div class="step-number">1</div>
        <div class="step-title">Location</div>
        <div class="progress-connector"></div>
      </div>
      <div class="step" data-step="2">
        <div class="step-number">2</div>
        <div class="step-title">Personal Info</div>
        <div class="progress-connector"></div>
      </div>
      <div class="step" data-step="3">
        <div class="step-number">3</div>
        <div class="step-title">Confirmation</div>
      </div>
    </div>
    
    <?php if($success): ?>
      <div class="success-message">
        <i class="fas fa-check-circle fa-2x mb-3"></i>
        <h4><?php echo $success; ?></h4>
        <p>You can now login to your account</p>
        <a href="login.php" class="btn btn-primary mt-3">Go to Login</a>
      </div>
    <?php endif; ?>
    
    <?php if(!empty($errors)): ?>
      <div class="error-message">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <?php foreach($errors as $error): ?>
          <div><?php echo $error; ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    
    <form action="" method="POST" enctype="multipart/form-data" id="registrationForm">
      <!-- Step 1: Location -->
      <div class="form-section active" id="step1">
        <div class="mb-4">
          <label for="REGION" class="form-label">Region</label>
          <select name="user_region" id="REGION" required class="form-select">
            <option value="">Select Region</option>
            <?php  
            $select_region = mysqli_query($conn, "SELECT * FROM regions_table WHERE region_status = 'Active'");
            while($region = mysqli_fetch_array($select_region)) {
                echo '<option value="' . $region['region_id'] . '">' . $region['region_name'] . '</option>';
            }
            ?>
          </select>
        </div>
        
        <div class="mb-4">
          <label for="COUNTRY" class="form-label">Country</label>
          <select name="user_country" id="COUNTRY" required class="form-select" disabled>
            <option value="">Select Country</option>
          </select>
          <div class="mt-2 text-muted" id="country-loading" style="display:none;">
            <i class="fas fa-spinner fa-spin"></i> Loading countries...
          </div>
        </div>
        
        <div class="mb-4">
          <label for="SCHOOL" class="form-label">School</label>
          <select name="school_ref" id="SCHOOL" required class="form-select" disabled>
            <option value="">Select School</option>
          </select>
          <div class="mt-2 text-muted" id="school-loading" style="display:none;">
            <i class="fas fa-spinner fa-spin"></i> Loading schools...
          </div>
        </div>
      </div>
      
      <!-- Step 2: Personal Information -->
      <div class="form-section" id="step2">
        <div class="text-center mb-4">
          <img id="imagePreview" src="https://via.placeholder.com/150" class="preview-image" alt="Profile Preview">
          <label for="user_image" class="btn btn-outline-primary mt-2">
            <i class="fas fa-upload me-2"></i>Upload Profile Picture
          </label>
          <input type="file" name="user_image" id="user_image" class="d-none" accept="image/*" required>
        </div>
        
        <div class="row">
          <div class="col-md-6 mb-3">
            <label for="firstname" class="form-label">First Name</label>
            <input type="text" name="firstname" id="firstname" required class="form-control" placeholder="Enter first name">
          </div>
          <div class="col-md-6 mb-3">
            <label for="lastname" class="form-label">Last Name</label>
            <input type="text" name="lastname" id="lastname" required class="form-control" placeholder="Enter last name">
          </div>
        </div>
        
        <div class="mb-3">
          <label for="phone" class="form-label">Phone Number</label>
          <input type="tel" name="phone" id="phone" required class="form-control" placeholder="+1 (123) 456-7890" pattern="^\+\d{7,15}$">
          <small class="form-text text-muted">Format: (country code) followed by 6-15 digits (e.g. (country code) 123456)</small>
        </div>
        
        <div class="mb-3">
          <label for="email" class="form-label">Email Address</label>
          <input type="email" name="email" id="email" required class="form-control" placeholder="Enter email">
        </div>
        
        <div class="row">
          <div class="col-md-6 mb-3">
            <label for="access_level" class="form-label">Access Level</label>
            <select name="access_level" id="access_level" required class="form-select">
              <?php
              $select_permission = mysqli_query($conn, "SELECT * FROM user_permission WHERE per_status='Default'");
              while($permission = mysqli_fetch_array($select_permission)) {
                  echo '<option value="' . $permission['permissio_id'] . '">' . $permission['permission'] . '</option>';
              }
              ?>
            </select>
          </div>
          <div class="col-md-6 mb-3">
            <label for="status" class="form-label">Status</label>
            <select name="status" id="status" required class="form-select">
              <option value="Active">Active</option> 
            </select>
          </div>
        </div>
        
        <div class="mb-3 password-container">
          <label for="password" class="form-label">Password</label>
          <input type="password" name="password" id="password" required class="form-control" placeholder="Create password">
          <i class="toggle-password fas fa-eye" data-target="password"></i>
        </div>
        
        <div class="mb-3">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="terms" required>
            <label class="form-check-label" for="terms">
              I agree to the <a href="#">Terms and Conditions</a>
            </label>
          </div>
        </div>
      </div>
      
      <!-- Step 3: Confirmation -->
      <div class="form-section" id="step3">
        <div class="text-center mb-4">
          <i class="fas fa-check-circle text-success fa-4x mb-3"></i>
          <h3>Confirm Your Details</h3>
          <p class="text-muted">Review your information before submitting</p>
        </div>
        
        <div class="card mb-4">
          <div class="card-header bg-light">
            <h5 class="mb-0">Location Information</h5>
          </div>
          <div class="card-body">
            <div class="row mb-2">
              <div class="col-4 fw-medium">Region:</div>
              <div class="col-8" id="confirm-region"></div>
            </div>
            <div class="row mb-2">
              <div class="col-4 fw-medium">Country:</div>
              <div class="col-8" id="confirm-country"></div>
            </div>
            <div class="row">
              <div class="col-4 fw-medium">School:</div>
              <div class="col-8" id="confirm-school"></div>
            </div>
          </div>
        </div>
        
        <div class="card">
          <div class="card-header bg-light">
            <h5 class="mb-0">Personal Information</h5>
          </div>
          <div class="card-body">
            <div class="row mb-3">
              <div class="col-md-3 text-center">
                <img id="confirm-image" src="https://via.placeholder.com/100" class="preview-image" style="width:100px;height:100px;">
              </div>
              <div class="col-md-9">
                <div class="row mb-2">
                  <div class="col-4 fw-medium">Name:</div>
                  <div class="col-8" id="confirm-name"></div>
                </div>
                <div class="row mb-2">
                  <div class="col-4 fw-medium">Email:</div>
                  <div class="col-8" id="confirm-email"></div>
                </div>
                <div class="row mb-2">
                  <div class="col-4 fw-medium">Phone:</div>
                  <div class="col-8" id="confirm-phone"></div>
                </div>
                <div class="row">
                  <div class="col-4 fw-medium">Access Level:</div>
                  <div class="col-8" id="confirm-access"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Form Navigation -->
      <div class="form-footer">
        <button type="button" class="btn btn-outline-primary" id="prevBtn" style="display:none;">
          <i class="fas fa-arrow-left me-2"></i>Previous
        </button>
        <button type="button" class="btn btn-primary ms-auto" id="nextBtn">
          Next Step<i class="fas fa-arrow-right ms-2"></i>
        </button>
        <button type="submit" name="register" class="btn btn-success" id="submitBtn" style="display:none;">
          <i class="fas fa-check me-2"></i>Complete Registration
        </button>
      </div>
    </form>
  </div>

  <script>
    $(document).ready(function() {
      // Step navigation
      let currentStep = 1;
      const totalSteps = 3;
      
      // Form elements
      const nextBtn = document.getElementById('nextBtn');
      const prevBtn = document.getElementById('prevBtn');
      const submitBtn = document.getElementById('submitBtn');
      const formSections = document.querySelectorAll('.form-section');
      const steps = document.querySelectorAll('.step');
      
      // Initialize steps
      updateStepIndicator();
      
      // Next button click
      nextBtn.addEventListener('click', function() {
        if (validateStep(currentStep)) {
          if (currentStep < totalSteps) {
            currentStep++;
            updateFormDisplay();
          }
        }
      });
      
      // Previous button click
      prevBtn.addEventListener('click', function() {
        if (currentStep > 1) {
          currentStep--;
          updateFormDisplay();
        }
      });
      
      // Toggle password visibility
      document.querySelectorAll('.toggle-password').forEach(icon => {
        icon.addEventListener('click', function() {
          const targetId = this.getAttribute('data-target');
          const passwordField = document.getElementById(targetId);
          const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
          passwordField.setAttribute('type', type);
          this.classList.toggle('fa-eye');
          this.classList.toggle('fa-eye-slash');
        });
      });
      
      // Image preview
      const imageInput = document.getElementById('user_image');
      const imagePreview = document.getElementById('imagePreview');
      const confirmImage = document.getElementById('confirm-image');
      
      imageInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
          const reader = new FileReader();
          
          reader.onload = function(e) {
            imagePreview.src = e.target.result;
            confirmImage.src = e.target.result;
          }
          
          reader.readAsDataURL(this.files[0]);
        }
      });
      
      // Region change handler - AJAX for countries
      $('#REGION').change(function() {
        const regionId = $(this).val();
        const countrySelect = $('#COUNTRY');
        const schoolSelect = $('#SCHOOL');
        
        if (regionId) {
          // Show loading
          $('#country-loading').show();
          countrySelect.prop('disabled', false);
          
          // AJAX request to get countries
          $.ajax({
            type: 'POST',
            url: 'get_countries.php',
            data: { region_id: regionId },
            success: function(data) {
              countrySelect.html(data);
              $('#country-loading').hide();
              
              // Reset school
              schoolSelect.html('<option value="">Select School</option>')
                          .prop('disabled', true);
            },
            error: function() {
              $('#country-loading').hide();
              alert('Error loading countries');
            }
          });
        } else {
          countrySelect.html('<option value="">Select Country</option>')
                       .prop('disabled', true);
          schoolSelect.html('<option value="">Select School</option>')
                       .prop('disabled', true);
        }
      });
      
      // Country change handler - AJAX for schools
      $('#COUNTRY').change(function() {
        const countryId = $(this).val();
        const schoolSelect = $('#SCHOOL');
        
        if (countryId) {
          // Show loading
          $('#school-loading').show();
          schoolSelect.prop('disabled', false);
          
          // AJAX request to get schools
          $.ajax({
            type: 'POST',
            url: 'get_schools.php',
            data: { country_id: countryId },
            success: function(data) {
              schoolSelect.html(data);
              $('#school-loading').hide();
            },
            error: function() {
              $('#school-loading').hide();
              alert('Error loading schools');
            }
          });
        } else {
          schoolSelect.html('<option value="">Select School</option>')
                      .prop('disabled', true);
        }
      });
      
      // Update confirmation data
      document.getElementById('nextBtn').addEventListener('click', function() {
        if (currentStep === 2) {
          // Update confirmation step
          document.getElementById('confirm-region').textContent = 
            $('#REGION option:selected').text();
          
          document.getElementById('confirm-country').textContent = 
            $('#COUNTRY option:selected').text();
          
          document.getElementById('confirm-school').textContent = 
            $('#SCHOOL option:selected').text();
          
          document.getElementById('confirm-name').textContent = 
            document.getElementById('firstname').value + ' ' + 
            document.getElementById('lastname').value;
          
          document.getElementById('confirm-email').textContent = 
            document.getElementById('email').value;
          
          document.getElementById('confirm-phone').textContent = 
            document.getElementById('phone').value;
          
          document.getElementById('confirm-access').textContent = 
            $('#access_level option:selected').text();
        }
      });
      
      // Validate current step
      function validateStep(step) {
        let isValid = true;
        
        if (step === 1) {
          const region = document.getElementById('REGION');
          const country = document.getElementById('COUNTRY');
          const school = document.getElementById('SCHOOL');
          
          if (!region.value) {
            alert('Please select a region');
            isValid = false;
          } else if (!country.value) {
            alert('Please select a country');
            isValid = false;
          } else if (!school.value) {
            alert('Please select a school');
            isValid = false;
          }
        } else if (step === 2) {
          const requiredFields = document.querySelectorAll('#step2 [required]');
          requiredFields.forEach(field => {
            if (!field.value) {
              field.classList.add('is-invalid');
              isValid = false;
            } else {
              field.classList.remove('is-invalid');
            }
          });
          
          // Special validation for phone format
          const phone = document.getElementById('phone');
          const phonePattern = /^\+\d{7,15}$/;
          if (phone.value && !phonePattern.test(phone.value)) {
            alert('Please enter a valid phone number starting with + and 7-15 digits');
            phone.classList.add('is-invalid');
            isValid = false;
          }
        }
        
        return isValid;
      }
      
      // Update form display based on current step
      function updateFormDisplay() {
        // Hide all sections
        formSections.forEach(section => section.classList.remove('active'));
        
        // Show current section
        document.getElementById(`step${currentStep}`).classList.add('active');
        
        // Update step indicator
        updateStepIndicator();
        
        // Update button visibility
        prevBtn.style.display = currentStep > 1 ? 'block' : 'none';
        nextBtn.style.display = currentStep < totalSteps ? 'block' : 'none';
        submitBtn.style.display = currentStep === totalSteps ? 'block' : 'none';
      }
      
      // Update step indicator
      function updateStepIndicator() {
        steps.forEach(step => {
          const stepNum = parseInt(step.getAttribute('data-step'));
          
          step.classList.remove('active', 'completed');
          
          if (stepNum < currentStep) {
            step.classList.add('completed');
          } else if (stepNum === currentStep) {
            step.classList.add('active');
          }
        });
      }
    });
  </script>
</body>
</html>