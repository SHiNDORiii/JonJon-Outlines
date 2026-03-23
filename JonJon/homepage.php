<?php
// Start session to access user data
session_start();

// Check if user is logged in, if not redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Get current date
$current_date = date("l, F j, Y");

// Function to get user role display name
function getRoleDisplay($user_type) {
    switch($user_type) {
        case 'student':
            return 'Student';
        case 'teacher':
            return 'Teacher';
        case 'admin':
            return 'Administrator';
        case 'registrar':
            return 'Registrar';
        default:
            return 'User';
    }
}

// Get user role display with fallback
$role_display = isset($_SESSION['user_type']) ? getRoleDisplay($_SESSION['user_type']) : 'User';

// Get user-specific details with fallbacks
$first_name = isset($_SESSION['first_name']) ? $_SESSION['first_name'] : (isset($_SESSION['user_first_name']) ? $_SESSION['user_first_name'] : 'User');
$full_name = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : (isset($_SESSION['user_first_name']) ? $_SESSION['user_first_name'] . ' ' . ($_SESSION['user_last_name'] ?? '') : 'User');
$year_level = isset($_SESSION['year_level']) ? $_SESSION['year_level'] : 'N/A';
$course = isset($_SESSION['course']) ? $_SESSION['course'] : 'N/A';
$section = isset($_SESSION['section']) ? $_SESSION['section'] : 'N/A';
$department = isset($_SESSION['department']) ? $_SESSION['department'] : 'N/A';
$office_role = isset($_SESSION['office_role']) ? $_SESSION['office_role'] : 'N/A';
$user_type = isset($_SESSION['user_type']) ? $_SESSION['user_type'] : 'student';

// Debug: If first_name is still not set, try to fetch from database
if ($first_name == 'User' && isset($_SESSION['user_id']) && isset($_SESSION['user_email'])) {
    // Database connection
    $host = 'localhost';
    $dbname = 'school_grades_portal';
    $username = 'root';
    $password = '';
    
    $conn = new mysqli($host, $username, $password, $dbname);
    
    if (!$conn->connect_error) {
        $user_id = $_SESSION['user_id'];
        $user_email = $_SESSION['user_email'];
        
        // Try to fetch from student table
        $query = "SELECT first_name, last_name FROM student WHERE student_id = ? OR email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("is", $user_id, $user_email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user_data = $result->fetch_assoc();
            $first_name = $user_data['first_name'];
            $full_name = $user_data['first_name'] . ' ' . $user_data['last_name'];
            $_SESSION['first_name'] = $first_name;
            $_SESSION['full_name'] = $full_name;
        }
        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard · GradePortal</title>
  <link rel="stylesheet" href="homepage.css">
  <style>
    /* Additional styles for dynamic content */
    .user-welcome-details {
        font-size: 0.9rem;
        color: #b3cef0;
        margin-top: 0.2rem;
    }
    
    .stat-value-large {
        font-size: 2rem;
        font-weight: 700;
        color: white;
    }
    
    .empty-state {
        text-align: center;
        padding: 2rem;
        color: #809bb8;
    }
    
    /* Course info with department for teachers */
    .course-department {
        font-size: 0.75rem;
        color: #60a5fa;
        margin-top: 0.2rem;
    }
  </style>
</head>
<body>
  <!-- animated background (matching alt login) -->
  <div class="bg-shape bg-shape-1"></div>
  <div class="bg-shape bg-shape-2"></div>
  <div class="bg-shape bg-shape-3"></div>

  <!-- main app container -->
  <div class="dashboard-container">
    <!-- sidebar navigation -->
    <aside class="sidebar">
      <div class="sidebar-header">
        <div class="sidebar-logo">🎓 Grade<span>Portal</span></div>
        <div class="school-badge">Saint Mary Angel's College of Pampanga</div>
      </div>
      
      <nav class="sidebar-nav">
        <a href="homepage.php" class="nav-item active">
          <span class="nav-icon">📊</span>
          <span>Dashboard</span>
         </a>

        <a href="grades.php" class="nav-item">
          <span class="nav-icon">📝</span>
          <span>My Grades</span>
        </a>

        <a href="#" class="nav-item">
          <span class="nav-icon">⚙️</span>
          <span>Settings</span>
        </a>
      </nav>
      
      <div class="sidebar-footer">
        <div class="user-info-compact">
          <div class="user-avatar">👤</div>
          <div class="user-details">
            <span class="user-name"><?php echo htmlspecialchars($full_name); ?></span>
            <span class="user-role"><?php echo htmlspecialchars($role_display); ?></span>
          </div>
        </div>
        <a href="logout.php" class="logout-link">🚪 Log out</a>
      </div>
    </aside>

    <!-- main content area -->
    <main class="main-content">
      <!-- top header -->
      <header class="content-header">
        <div class="header-left">
          <h1 class="page-title">Dashboard</h1>
          <div class="date-display"><?php echo $current_date; ?></div>
        </div>
        <div class="header-right">
          <div class="header-user">
            <span class="header-user-name"><?php echo htmlspecialchars($full_name); ?></span>
            <div class="header-avatar">👤</div>
          </div>
        </div>
      </header>

      <!-- welcome banner -->
      <div class="welcome-card">
        <div class="welcome-message">
          <h2>Welcome, <?php echo htmlspecialchars($first_name); ?>! 👋</h2>
          <p>
            <?php 
            if ($user_type == 'student') {
                echo htmlspecialchars($year_level) . ' Year · ' . htmlspecialchars($course) . ' · Section: ' . htmlspecialchars($section);
            } elseif ($user_type == 'teacher') {
                echo 'Department: ' . htmlspecialchars($department);
            } elseif ($user_type == 'registrar') {
                echo 'Office Role: ' . htmlspecialchars($office_role);
            } else {
                echo 'Administrator Account';
            }
            ?>
          </p>
        </div>
        <div class="welcome-action">
          <span class="semester-badge">Semester 2 · 2026</span>
        </div>
      </div>

      <!-- stats grid -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon">📊</div>
          <div class="stat-content">
            <span class="stat-value">3.85</span>
            <span class="stat-label">Current GPA</span>
          </div>
          <div class="stat-trend positive">↑ 0.2</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon">⭐</div>
          <div class="stat-content">
            <span class="stat-value">96%</span>
            <span class="stat-label">Attendance</span>
          </div>
          <div class="stat-trend positive">↑ 2%</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon">📚</div>
          <div class="stat-content">
            <span class="stat-value">3</span>
            <span class="stat-label">Active Courses</span>
          </div>
          <div class="stat-trend">This semester</div>
        </div>
      </div>

      <!-- two column layout -->
      <div class="dashboard-grid">
        <!-- left column: current courses & grades -->
        <div class="grid-left">
          <section class="card">
            <div class="card-header">
              <h3>Current Courses</h3>
              <a href="#" class="card-link">View all →</a>
            </div>
            <div class="courses-list">
              <div class="course-item">
                <div class="course-info">
                  <span class="course-name">Web Development</span>
                  <span class="course-teacher">Prof. Reyes</span>
                </div>
                <div class="course-progress">
                  <div class="progress-bar">
                    <div class="progress-fill" style="width: 85%"></div>
                  </div>
                  <span class="course-grade">A- (85%)</span>
                </div>
              </div>
              <div class="course-item">
                <div class="course-info">
                  <span class="course-name">Discrete Math</span>
                  <span class="course-teacher">Dr. Santos</span>
                </div>
                <div class="course-progress">
                  <div class="progress-bar">
                    <div class="progress-fill" style="width: 78%"></div>
                  </div>
                  <span class="course-grade">B+ (78%)</span>
                </div>
              </div>
              <div class="course-item">
                <div class="course-info">
                  <span class="course-name">PFIT2</span>
                  <span class="course-teacher">Ms. Garcia</span>
                </div>
                <div class="course-progress">
                  <div class="progress-bar">
                    <div class="progress-fill" style="width: 92%"></div>
                  </div>
                  <span class="course-grade">A (92%)</span>
                </div>
              </div>
            </div>
          </section>
        </div>
      </div>

      <br>

      <footer class="dashboard-footer">
        <p>© 2026 Saint Mary Angel's College of Pampanga · <a href="logout.php">Log out</a></p>
      </footer>
    </main>
  </div>
</body>
</html>