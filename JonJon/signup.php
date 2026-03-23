<?php
// Database configuration
$host = 'localhost';
$dbname = 'school_grades_portal';
$username = 'root'; // Change this to your MySQL username
$password = ''; // Change this to your MySQL password

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables for feedback message
$message = '';
$messageType = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize form data
    $student_number = mysqli_real_escape_string($conn, $_POST['snumber']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password_input = $_POST['password'];
    $first_name = mysqli_real_escape_string($conn, $_POST['fname']);
    $last_name = mysqli_real_escape_string($conn, $_POST['lname']);
    $year_level = mysqli_real_escape_string($conn, $_POST['ylevel']);
    $section = mysqli_real_escape_string($conn, $_POST['section']);
    $course = mysqli_real_escape_string($conn, $_POST['course']);
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format!";
        $messageType = "error";
    } 
    // Check if student number already exists
    else {
        $check_student = $conn->prepare("SELECT student_id FROM student WHERE student_number = ?");
        $check_student->bind_param("s", $student_number);
        $check_student->execute();
        $check_student->store_result();
        
        if ($check_student->num_rows > 0) {
            $message = "Student number already exists!";
            $messageType = "error";
        } else {
            // Check if email already exists
            $check_email = $conn->prepare("SELECT student_id FROM student WHERE email = ?");
            $check_email->bind_param("s", $email);
            $check_email->execute();
            $check_email->store_result();
            
            if ($check_email->num_rows > 0) {
                $message = "Email already registered!";
                $messageType = "error";
            } else {
                // Hash the password for security
                $hashed_password = password_hash($password_input, PASSWORD_DEFAULT);
                
                // Insert new student
                $insert_query = "INSERT INTO student (student_number, email, password, first_name, last_name, year_level, section, course) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                
                $stmt = $conn->prepare($insert_query);
                $stmt->bind_param("ssssssss", $student_number, $email, $hashed_password, $first_name, $last_name, $year_level, $section, $course);
                
                if ($stmt->execute()) {
                    $message = "Account created successfully! You can now log in.";
                    $messageType = "success";
                    // Clear form data (optional)
                    $_POST = array();
                } else {
                    $message = "Error creating account: " . $conn->error;
                    $messageType = "error";
                }
                $stmt->close();
            }
            $check_email->close();
        }
        $check_student->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>School Grades Portal · Sign Up</title>
  <link rel="stylesheet" href="style.css">
  <style>
    /* Additional styles for feedback messages */
    .feedback-message {
        padding: 12px 20px;
        border-radius: 12px;
        margin-bottom: 20px;
        text-align: center;
        font-weight: 500;
        animation: slideDown 0.3s ease;
    }
    
    .feedback-message.success {
        background: rgba(30, 126, 52, 0.2);
        border: 1px solid #1e7e34;
        color: #9cd9a4;
    }
    
    .feedback-message.error {
        background: rgba(220, 53, 69, 0.2);
        border: 1px solid #dc3545;
        color: #f8a8a8;
    }
    
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* Form fields adjustment */
    .alt-login-form {
        max-height: 70vh;
        overflow-y: auto;
        padding-right: 5px;
    }
    
    .alt-login-form::-webkit-scrollbar {
        width: 5px;
    }
    
    .alt-login-form::-webkit-scrollbar-track {
        background: #1e2c3a;
        border-radius: 10px;
    }
    
    .alt-login-form::-webkit-scrollbar-thumb {
        background: #3b82f6;
        border-radius: 10px;
    }
  </style>
</head>
<body>
  <div class="bg-shape bg-shape-1"></div>
  <div class="bg-shape bg-shape-2"></div>
  <div class="bg-shape bg-shape-3"></div>
  
  <div class="alt-login-container">
    <div class="brand-panel">
      <div class="brand-content">
        <div class="brand-icon">🎓</div>
        <h1 class="brand-title">Grade<span>Portal</span></h1>
        <p class="brand-tagline">Your academic journey, streamlined</p>
        
        <div class="feature-list">
          <div class="feature-item">
            <span class="feature-check">✓</span>
            <span>Real-time grade updates</span>
          </div>
          <div class="feature-item">
            <span class="feature-check">✓</span>
            <span>Teacher communication</span>
          </div>
          <div class="feature-item">
            <span class="feature-check">✓</span>
            <span>Report card access</span>
          </div>
        </div>
        
        <div class="testimonial">
          <div class="testimonial-text">"Nidana pen."</div>
          <div class="testimonial-author">— Jon Raniel B., BSIT</div>
        </div>
      </div>
    </div>

    <div class="form-panel">
      <div class="form-header">
        <h2>Sign Up</h2>
        <p>Create your student account</p>
      </div>
      
      <?php if ($message): ?>
        <div class="feedback-message <?php echo $messageType; ?>">
          <?php echo $message; ?>
        </div>
      <?php endif; ?>

      <form action="signup.php" method="post" class="alt-login-form">
        <div class="alt-input-group">
          <label for="snumber">Student Number *</label>
          <input type="text" id="snumber" name="snumber" 
                 placeholder="Enter Student Number" 
                 value="<?php echo isset($_POST['snumber']) ? htmlspecialchars($_POST['snumber']) : ''; ?>"
                 required>
        </div>
      
        <div class="alt-input-group">
          <label for="email">Email *</label>
          <input type="email" id="email" name="email" 
                 placeholder="e.g., j.smith@edu.ph" 
                 value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                 required>
        </div>

        <div class="alt-input-group">
          <label for="alt-password">Password *</label>
          <input type="password" id="alt-password" name="password" 
                 placeholder="Enter your password" 
                 required>
        </div>

        <div class="alt-input-group">
          <label for="fname">First Name *</label>
          <input type="text" id="fname" name="fname" 
                 placeholder="Enter First Name" 
                 value="<?php echo isset($_POST['fname']) ? htmlspecialchars($_POST['fname']) : ''; ?>"
                 required>
        </div>

        <div class="alt-input-group">
          <label for="lname">Last Name *</label>
          <input type="text" id="lname" name="lname" 
                 placeholder="Enter Last Name" 
                 value="<?php echo isset($_POST['lname']) ? htmlspecialchars($_POST['lname']) : ''; ?>"
                 required>
        </div>

        <div class="alt-input-group">
          <label for="ylevel">Year Level *</label>
          <select id="ylevel" name="ylevel" required>
            <option value="">Select Year Level</option>
            <option value="1st Year" <?php echo (isset($_POST['ylevel']) && $_POST['ylevel'] == '1st Year') ? 'selected' : ''; ?>>1st Year</option>
            <option value="2nd Year" <?php echo (isset($_POST['ylevel']) && $_POST['ylevel'] == '2nd Year') ? 'selected' : ''; ?>>2nd Year</option>
            <option value="3rd Year" <?php echo (isset($_POST['ylevel']) && $_POST['ylevel'] == '3rd Year') ? 'selected' : ''; ?>>3rd Year</option>
            <option value="4th Year" <?php echo (isset($_POST['ylevel']) && $_POST['ylevel'] == '4th Year') ? 'selected' : ''; ?>>4th Year</option>
          </select>
        </div>

        <div class="alt-input-group">
          <label for="section">Section *</label>
          <input type="text" id="section" name="section" 
                 placeholder="Enter Section" 
                 value="<?php echo isset($_POST['section']) ? htmlspecialchars($_POST['section']) : ''; ?>"
                 required>
        </div>

        <div class="alt-input-group">
          <label for="course">Course *</label>
          <select id="course" name="course" required>
            <option value="">Select Course</option>
            <option value="Bachelor of Science in Information Technology" <?php echo (isset($_POST['course']) && $_POST['course'] == 'Bachelor of Science in Information Technology') ? 'selected' : ''; ?>>BS Information Technology</option>
            <option value="Bachelor of Science in Computer Science" <?php echo (isset($_POST['course']) && $_POST['course'] == 'Bachelor of Science in Computer Science') ? 'selected' : ''; ?>>BS Computer Science</option>
          </select>
        </div>
        
        <button type="submit" class="alt-login-btn">Sign Up</button>
        
        <div class="alt-divider">
          <span class="divider-line"></span>
          <span class="divider-text">or</span>
          <span class="divider-line"></span>
        </div>
        
        <div class="alt-signup-prompt">
          <span class="alt-prompt-text">Already have an account?</span>
          <a href="index.php" class="alt-signup-btn">Sign In</a>
        </div>
      </form>
      
      <div class="alt-footer">
        <p>© 2026 · Saint Mary Angel's College of Pampanga</p>
      </div>
    </div>
  </div>
</body>
</html>