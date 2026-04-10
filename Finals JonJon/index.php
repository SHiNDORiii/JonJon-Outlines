<?php
// Start session for user authentication
session_start();

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

// Initialize variables
$error_message = '';
$success_message = '';

// Process login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize form data
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password_input = $_POST['password'];
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format!";
    } else {
        // Check if user exists in student table
        $query = "SELECT student_id, email, password, first_name, last_name, year_level, course, section 
                  FROM student 
                  WHERE email = ?";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password_input, $user['password'])) {
                // Password is correct, set session variables
                $_SESSION['user_id'] = $user['student_id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_first_name'] = $user['first_name'];
                $_SESSION['user_last_name'] = $user['last_name'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['user_type'] = 'student';
                $_SESSION['year_level'] = $user['year_level'];
                $_SESSION['course'] = $user['course'];
                $_SESSION['section'] = $user['section'];
                $_SESSION['full_name'] = $user['first_name'] . ' ' . $user['last_name'];
                
                // Redirect to homepage.php
                header("Location: homepage.php");
                exit();
            } else {
                $error_message = "Invalid password!";
            }
            $stmt->close();
        } else {
            // Check if user exists in teacher table
            $query_teacher = "SELECT teacher_id, email, password, first_name, last_name, department 
                             FROM teacher 
                             WHERE email = ?";
            
            $stmt_teacher = $conn->prepare($query_teacher);
            $stmt_teacher->bind_param("s", $email);
            $stmt_teacher->execute();
            $result_teacher = $stmt_teacher->get_result();
            
            if ($result_teacher->num_rows === 1) {
                $user = $result_teacher->fetch_assoc();
                
                // Verify password
                if (password_verify($password_input, $user['password'])) {
                    // Password is correct, set session variables for teacher
                    $_SESSION['user_id'] = $user['teacher_id'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_first_name'] = $user['first_name'];
                    $_SESSION['user_last_name'] = $user['last_name'];
                    $_SESSION['first_name'] = $user['first_name'];
                    $_SESSION['user_type'] = 'teacher';
                    $_SESSION['department'] = $user['department'];
                    $_SESSION['full_name'] = $user['first_name'] . ' ' . $user['last_name'];
                    
                    // Redirect to homepage.php
                    header("Location: homepage.php");
                    exit();
                } else {
                    $error_message = "Invalid password!";
                }
                $stmt_teacher->close();
            } else {
                // Check if user exists in admin table
                $query_admin = "SELECT admin_id, email, password, first_name, last_name 
                               FROM admin 
                               WHERE email = ?";
                
                $stmt_admin = $conn->prepare($query_admin);
                $stmt_admin->bind_param("s", $email);
                $stmt_admin->execute();
                $result_admin = $stmt_admin->get_result();
                
                if ($result_admin->num_rows === 1) {
                    $user = $result_admin->fetch_assoc();
                    
                    // Verify password
                    if (password_verify($password_input, $user['password'])) {
                        // Password is correct, set session variables
                        $_SESSION['user_id'] = $user['admin_id'];
                        $_SESSION['user_email'] = $user['email'];
                        $_SESSION['user_first_name'] = $user['first_name'];
                        $_SESSION['user_last_name'] = $user['last_name'];
                        $_SESSION['first_name'] = $user['first_name'];
                        $_SESSION['user_type'] = 'admin';
                        $_SESSION['full_name'] = $user['first_name'] . ' ' . $user['last_name'];
                        
                        // Redirect to homepage.php
                        header("Location: homepage.php");
                        exit();
                    } else {
                        $error_message = "Invalid password!";
                    }
                    $stmt_admin->close();
                } else {
                    // Check if user exists in registrar table
                    $query_registrar = "SELECT registrar_id, email, password, first_name, last_name, office_role 
                                       FROM registrar 
                                       WHERE email = ?";
                    
                    $stmt_registrar = $conn->prepare($query_registrar);
                    $stmt_registrar->bind_param("s", $email);
                    $stmt_registrar->execute();
                    $result_registrar = $stmt_registrar->get_result();
                    
                    if ($result_registrar->num_rows === 1) {
                        $user = $result_registrar->fetch_assoc();
                        
                        // Verify password
                        if (password_verify($password_input, $user['password'])) {
                            // Password is correct, set session variables
                            $_SESSION['user_id'] = $user['registrar_id'];
                            $_SESSION['user_email'] = $user['email'];
                            $_SESSION['user_first_name'] = $user['first_name'];
                            $_SESSION['user_last_name'] = $user['last_name'];
                            $_SESSION['first_name'] = $user['first_name'];
                            $_SESSION['user_type'] = 'registrar';
                            $_SESSION['office_role'] = $user['office_role'];
                            $_SESSION['full_name'] = $user['first_name'] . ' ' . $user['last_name'];
                            
                            // Redirect to homepage.php
                            header("Location: homepage.php");
                            exit();
                        } else {
                            $error_message = "Invalid password!";
                        }
                        $stmt_registrar->close();
                    } else {
                        $error_message = "Email not found! Please sign up first.";
                    }
                }
            }
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>School Grades Portal · Log In</title>
  <link rel="stylesheet" href="style.css?v=2">
  <style>
    /* Additional styles for feedback messages */
    .feedback-message {
        padding: 12px 20px;
        border-radius: 14px;
        margin-bottom: 20px;
        text-align: center;
        font-weight: 500;
        animation: slideDown 0.3s ease;
    }
    
    .feedback-message.error {
        background: rgba(220, 53, 69, 0.2);
        border: 1px solid #dc3545;
        color: #f8a8a8;
    }
    
    .feedback-message.success {
        background: rgba(30, 126, 52, 0.2);
        border: 1px solid #1e7e34;
        color: #9cd9a4;
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
    
    /* Logo image styling */
    .brand-icon img {
        width: 80px;
        height: auto;
        filter: drop-shadow(0 10px 15px rgba(0, 150, 255, 0.3));
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
        <div class="brand-icon">
          <img src="logosmacp.png" alt="Saint Mary Angel's College of Pampanga Logo">
        </div>
        <h1 class="brand-title">SMACP Grade<span>Portal</span></h1>
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
        <h2>Welcome!</h2>
        <p>Log in to access your grades and resources</p>
      </div>
      
      <?php if ($error_message): ?>
        <div class="feedback-message error">
          <?php echo htmlspecialchars($error_message); ?>
        </div>
      <?php endif; ?>
      
      <?php if ($success_message): ?>
        <div class="feedback-message success">
          <?php echo htmlspecialchars($success_message); ?>
        </div>
      <?php endif; ?>
      
      <form action="index.php" method="post" class="alt-login-form">
        <div class="alt-input-group">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" 
                 placeholder="e.g., j.smith@edu.ph" 
                 value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                 required>
        </div>
        
        <div class="alt-input-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" 
                 placeholder="Enter your password" 
                 required>
        </div>
        
        <button type="submit" class="alt-login-btn">Log in</button>
        
        <div class="alt-divider">
          <span class="divider-line"></span>
          <span class="divider-text">or</span>
          <span class="divider-line"></span>
        </div>
        
        <div class="alt-signup-prompt">
          <span class="alt-prompt-text">Don't have an account?</span>
          <a href="signup.php" class="alt-signup-btn">Create account</a>
        </div>
      </form>
      
      <div class="alt-footer">
        <p>© 2026 · Saint Mary Angel's College of Pampanga</p>
      </div>
    </div>
  </div>
</body>
</html>