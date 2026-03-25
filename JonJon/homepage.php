<?php
// Start session to access user data
session_start();

// Check if user is logged in, if not redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Database connection
$host = 'localhost';
$dbname = 'school_grades_portal';
$username = 'root';
$password = '';

$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
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
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

// Fetch GPA from database
$overall_gpa = 0;
$total_units = 0;
$total_grade_points = 0;

if ($user_type == 'student' && $user_id > 0) {
    $query = "SELECT 
                g.average_grade,
                s.units
              FROM grade g
              JOIN subject s ON g.subject_id = s.subject_id
              WHERE g.student_id = ? AND g.average_grade IS NOT NULL";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        if ($row['average_grade'] && $row['units']) {
            $total_grade_points += $row['average_grade'] * $row['units'];
            $total_units += $row['units'];
        }
    }
    $stmt->close();
    
    // Calculate overall GPA
    if ($total_units > 0) {
        $overall_gpa = $total_grade_points / $total_units;
    }
}

// Fetch course grades for display with all grade components
$courses = [];
if ($user_type == 'student' && $user_id > 0) {
    $query_courses = "SELECT 
                        g.grade_id,
                        g.subject_id,
                        s.subject_name,
                        s.subject_code,
                        s.units,
                        g.prelim_grade,
                        g.midterm_grade,
                        g.final_grade,
                        g.average_grade,
                        CONCAT(t.first_name, ' ', t.last_name) AS teacher_name
                      FROM grade g
                      JOIN subject s ON g.subject_id = s.subject_id
                      LEFT JOIN teacher t ON g.teacher_id = t.teacher_id
                      WHERE g.student_id = ?
                      ORDER BY s.subject_code";
    
    $stmt_courses = $conn->prepare($query_courses);
    $stmt_courses->bind_param("i", $user_id);
    $stmt_courses->execute();
    $result_courses = $stmt_courses->get_result();
    
    while ($row = $result_courses->fetch_assoc()) {
        $courses[] = $row;
    }
    $stmt_courses->close();
}

$conn->close();

// Debug: If first_name is still not set, try to fetch from database
if ($first_name == 'User' && isset($_SESSION['user_id']) && isset($_SESSION['user_email'])) {
    $conn = new mysqli($host, $username, $password, $dbname);
    
    if (!$conn->connect_error) {
        $user_id_db = $_SESSION['user_id'];
        $user_email = $_SESSION['user_email'];
        
        // Try to fetch from student table
        $query = "SELECT first_name, last_name FROM student WHERE student_id = ? OR email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("is", $user_id_db, $user_email);
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

// Function to get grade letter based on percentage
function getGradeLetter($percentage) {
    if ($percentage >= 96) return 'A+';
    if ($percentage >= 91) return 'A';
    if ($percentage >= 88) return 'A-';
    if ($percentage >= 85) return 'B+';
    if ($percentage >= 82) return 'B';
    if ($percentage >= 79) return 'B-';
    if ($percentage >= 76) return 'C+';
    if ($percentage >= 73) return 'C';
    if ($percentage >= 70) return 'C-';
    if ($percentage >= 67) return 'D+';
    if ($percentage >= 60) return 'D';
    return 'F';
}

// Function to get grade color based on percentage
function getGradeColor($percentage) {
    if ($percentage >= 90) return '#9cd9a4';
    if ($percentage >= 80) return '#a5d8ff';
    if ($percentage >= 75) return '#fde68a';
    return '#f8a8a8';
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
    
    .course-grade-custom {
        font-weight: 600;
        font-size: 0.9rem;
        min-width: 80px;
        text-align: right;
    }
    
    .grade-details {
        font-size: 0.75rem;
        color: #809bb8;
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
            <span class="stat-value"><?php echo number_format($overall_gpa, 2); ?></span>
            <span class="stat-label">Current GPA</span>
          </div>
          <?php if ($overall_gpa > 0): ?>
          <div class="stat-trend positive">Active</div>
          <?php else: ?>
          <div class="stat-trend">No grades</div>
          <?php endif; ?>
        </div>
        <div class="stat-card">
          <div class="stat-icon">📚</div>
          <div class="stat-content">
            <span class="stat-value"><?php echo count($courses); ?></span>
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
              <a href="grades.php" class="card-link">View all →</a>
            </div>
            <div class="courses-list">
              <?php if (count($courses) > 0): ?>
                <?php foreach ($courses as $course): ?>
                <div class="course-item">
                  <div class="course-info">
                    <div>
                      <span class="course-name"><?php echo htmlspecialchars($course['subject_name']); ?></span>
                      <span class="course-teacher"><?php echo htmlspecialchars($course['teacher_name'] ?? 'Not Assigned'); ?></span>
                    </div>
                    <?php if ($course['average_grade']): ?>
                    <div class="grade-details">
                      Prelim: <?php echo $course['prelim_grade'] ? number_format($course['prelim_grade'], 0) : '-'; ?>% | 
                      Midterm: <?php echo $course['midterm_grade'] ? number_format($course['midterm_grade'], 0) : '-'; ?>% | 
                      Final: <?php echo $course['final_grade'] ? number_format($course['final_grade'], 0) : '-'; ?>%
                    </div>
                    <?php endif; ?>
                  </div>
                  <div class="course-progress">
                    <?php if ($course['average_grade']): ?>
                    <div class="progress-bar">
                      <?php 
                      $percentage = $course['average_grade'];
                      $progress_width = min(100, max(0, $percentage));
                      ?>
                      <div class="progress-fill" style="width: <?php echo $progress_width; ?>%; background: linear-gradient(90deg, <?php echo getGradeColor($percentage); ?>, <?php echo getGradeColor($percentage); ?>);"></div>
                    </div>
                    <span class="course-grade-custom" style="color: <?php echo getGradeColor($course['average_grade']); ?>;">
                      <?php echo getGradeLetter($course['average_grade']); ?> (<?php echo number_format($course['average_grade'], 0); ?>%)
                    </span>
                    <?php else: ?>
                    <span class="course-grade-custom" style="color: #809bb8;">No grade yet</span>
                    <?php endif; ?>
                  </div>
                </div>
                <?php endforeach; ?>
              <?php else: ?>
                <div class="empty-state">
                  <p>No courses enrolled yet</p>
                </div>
              <?php endif; ?>
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