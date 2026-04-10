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

// ============ STUDENT VIEW ============
if ($user_type == 'student') {
    // Fetch GPA from database
    $overall_gpa = 0;
    $total_units = 0;
    $total_grade_points = 0;

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

    // Fetch course grades for display with all grade components
    $courses = [];
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

// ============ TEACHER VIEW ============
if ($user_type == 'teacher') {
    // Fetch students with missing grades (no grade records yet)
    $students_no_grades = [];
    
    $query_no_grades = "SELECT 
                            s.student_id,
                            s.student_number,
                            s.first_name,
                            s.last_name,
                            s.year_level,
                            s.course,
                            s.section,
                            s.email
                        FROM student s
                        WHERE NOT EXISTS (
                            SELECT 1 
                            FROM grade g 
                            WHERE g.student_id = s.student_id
                        )
                        ORDER BY s.year_level, s.last_name, s.first_name";
    
    $result_no_grades = $conn->query($query_no_grades);
    while ($row = $result_no_grades->fetch_assoc()) {
        $students_no_grades[] = $row;
    }
    
    // Also fetch students who have some subjects without grades
    $students_incomplete = [];
    
    $query_incomplete = "SELECT 
                            s.student_id,
                            s.student_number,
                            s.first_name,
                            s.last_name,
                            s.year_level,
                            s.course,
                            s.section,
                            COUNT(DISTINCT sub.subject_id) as total_subjects,
                            COUNT(g.grade_id) as graded_subjects
                        FROM student s
                        CROSS JOIN subject sub
                        LEFT JOIN grade g ON g.student_id = s.student_id AND g.subject_id = sub.subject_id
                        GROUP BY s.student_id
                        HAVING COUNT(g.grade_id) < COUNT(DISTINCT sub.subject_id)
                        ORDER BY s.year_level, s.last_name, s.first_name";
    
    $result_incomplete = $conn->query($query_incomplete);
    while ($row = $result_incomplete->fetch_assoc()) {
        $students_incomplete[] = $row;
    }
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

    /* Teacher view table styles */
    .students-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .students-table th,
    .students-table td {
        padding: 0.8rem;
        text-align: left;
        border-bottom: 1px solid #2a4058;
    }
    
    .students-table th {
        background: rgba(30, 44, 58, 0.5);
        color: #60a5fa;
        font-weight: 600;
    }
    
    .students-table tr:hover {
        background: rgba(59, 130, 246, 0.05);
    }
    
    .student-name {
        font-weight: 600;
        color: white;
    }
    
    .warning-badge {
        background: #b45309;
        color: white;
        padding: 0.2rem 0.8rem;
        border-radius: 60px;
        font-size: 0.7rem;
        font-weight: 500;
    }
  </style>
</head>
<body>
  <!-- animated background -->
  <div class="bg-shape bg-shape-1"></div>
  <div class="bg-shape bg-shape-2"></div>
  <div class="bg-shape bg-shape-3"></div>

  <!-- main app container -->
  <div class="dashboard-container">
    <!-- sidebar navigation -->
    <aside class="sidebar">
      <div class="sidebar-header">
        <div class="sidebar-logo">
          <img src="logosmacp.png" alt="Logo" class="sidebar-logo-img">Grade<span>Portal</span>
        </div>
      <div class="school-badge">Saint Mary Angel's College of Pampanga</div>
      </div>
      
      <nav class="sidebar-nav">
        <a href="homepage.php" class="nav-item active">
          <span class="nav-icon">📊</span>
          <span>Dashboard</span>
        </a>

        <?php if ($user_type == 'student'): ?>
        <a href="grades.php" class="nav-item">
          <span class="nav-icon">📝</span>
          <span>My Grades</span>
        </a>
        <?php else: ?>
        <a href="grades.php" class="nav-item">
          <span class="nav-icon">📝</span>
          <span>Grades</span>
        </a>
        <?php endif; ?>

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

      <!-- ============ STUDENT VIEW ============ -->
      <?php if ($user_type == 'student'): ?>
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

      <!-- ============ TEACHER VIEW ============ -->
      <?php elseif ($user_type == 'teacher'): ?>
      
      <!-- Stats for Teacher -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon">👨‍🎓</div>
          <div class="stat-content">
            <span class="stat-value"><?php echo count($students_no_grades); ?></span>
            <span class="stat-label">Students with No Grades</span>
          </div>
          <div class="stat-trend warning">Pending</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon">⚠️</div>
          <div class="stat-content">
            <span class="stat-value"><?php echo count($students_incomplete); ?></span>
            <span class="stat-label">Incomplete Records</span>
          </div>
          <div class="stat-trend">Needs attention</div>
        </div>
      </div>

      <!-- Students with NO GRADES AT ALL -->
      <div class="card">
        <div class="card-header">
          <h3>⚠️ Students with No Grades Yet</h3>
          <span class="warning-badge">Urgent</span>
        </div>
        <?php if (count($students_no_grades) > 0): ?>
        <div class="grades-table-wrapper">
          <table class="students-table">
            <thead>
              <tr>
                <th>Student Number</th>
                <th>Student Name</th>
                <th>Year Level</th>
                <th>Course</th>
                <th>Section</th>
                <th>Email</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($students_no_grades as $student): ?>
              <tr>
                <td><?php echo htmlspecialchars($student['student_number']); ?></td>
                <td class="student-name"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                <td><?php echo htmlspecialchars($student['year_level']); ?></td>
                <td><?php echo htmlspecialchars($student['course']); ?></td>
                <td><?php echo htmlspecialchars($student['section']); ?></td>
                <td><?php echo htmlspecialchars($student['email']); ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
           aperçu
        </div>
        <?php else: ?>
        <div class="empty-state">
          <p>✅ All students have grades recorded!</p>
        </div>
        <?php endif; ?>
      </div>

      <!-- Students with INCOMPLETE GRADES (some subjects missing) -->
      <div class="card">
        <div class="card-header">
          <h3>📝 Students with Incomplete Grades</h3>
          <span class="warning-badge">In Progress</span>
        </div>
        <?php if (count($students_incomplete) > 0): ?>
        <div class="grades-table-wrapper">
          <table class="students-table">
            <thead>
              <tr>
                <th>Student Number</th>
                <th>Student Name</th>
                <th>Year Level</th>
                <th>Course</th>
                <th>Section</th>
                <th>Progress</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($students_incomplete as $student): ?>
              <tr>
                <td><?php echo htmlspecialchars($student['student_number']); ?></td>
                <td class="student-name"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                <td><?php echo htmlspecialchars($student['year_level']); ?></td>
                <td><?php echo htmlspecialchars($student['course']); ?></td>
                <td><?php echo htmlspecialchars($student['section']); ?></td>
                <td>
                  <span style="color: #fbbf24;">
                    <?php echo $student['graded_subjects']; ?>/<?php echo $student['total_subjects']; ?> subjects graded
                  </span>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
          <p>✅ All students have complete grades!</p>
        </div>
        <?php endif; ?>
      </div>

      <!-- Quick Actions for Teachers -->
      <div class="quick-actions">
        <h3>Quick Actions</h3>
        <div class="action-buttons">
          <a href="grades.php" class="action-btn">📝 Encode Grades</a>
          <a href="#" class="action-btn">📊 View Grade Summary</a>
          <a href="#" class="action-btn">✉️ Message Students</a>
          <a href="#" class="action-btn">📅 Check Schedule</a>
        </div>
      </div>

      <?php endif; ?>

      <br>

      <footer class="dashboard-footer">
        <p>© 2026 Saint Mary Angel's College of Pampanga · <a href="logout.php">Log out</a></p>
      </footer>
    </main>
  </div>
</body>
</html>