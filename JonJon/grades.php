<?php
// Start session to access user data
session_start();

// Check if user is logged in, if not redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

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
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
$user_type = isset($_SESSION['user_type']) ? $_SESSION['user_type'] : 'student';

// Fetch grades for the logged-in student
$grades = [];
$overall_gpa = 0;
$total_units = 0;
$total_grade_points = 0;

if ($user_type == 'student') {
    $query = "SELECT 
                g.grade_id,
                g.student_id,
                g.subject_id,
                g.school_year,
                g.semester,
                g.prelim_grade,
                g.midterm_grade,
                g.final_grade,
                g.average_grade,
                s.subject_code,
                s.subject_name,
                s.units,
                CONCAT(t.first_name, ' ', t.last_name) AS teacher_name
              FROM grade g
              JOIN subject s ON g.subject_id = s.subject_id
              LEFT JOIN teacher t ON g.teacher_id = t.teacher_id
              WHERE g.student_id = ?
              ORDER BY s.subject_code";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $grades[] = $row;
        
        // Calculate GPA (if average_grade exists)
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

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Grades · GradePortal</title>
  <link rel="stylesheet" href="homepage.css">
  <style>
    /* Additional styles for grades page */
    .grades-container {
        background: rgba(18, 28, 40, 0.7);
        backdrop-filter: blur(8px);
        border: 1px solid #2a4058;
        border-radius: 1.5rem;
        padding: 1.8rem;
        margin-bottom: 1.8rem;
    }
    
    .grades-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
        gap: 1rem;
    }
    
    .grades-header h2 {
        color: white;
        font-size: 1.5rem;
        font-weight: 600;
    }
    
    .gpa-card {
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        padding: 1rem 2rem;
        border-radius: 1rem;
        text-align: center;
    }
    
    .gpa-card .gpa-label {
        color: rgba(255, 255, 255, 0.8);
        font-size: 0.9rem;
        font-weight: 500;
    }
    
    .gpa-card .gpa-value {
        color: white;
        font-size: 2rem;
        font-weight: 700;
        line-height: 1;
    }
    
    .grades-table-wrapper {
        overflow-x: auto;
    }
    
    .grades-table {
        width: 100%;
        border-collapse: collapse;
        color: #e2e8f0;
    }
    
    .grades-table th,
    .grades-table td {
        padding: 1rem;
        text-align: left;
        border-bottom: 1px solid #2a4058;
    }
    
    .grades-table th {
        background: rgba(30, 44, 58, 0.5);
        color: #60a5fa;
        font-weight: 600;
        font-size: 0.9rem;
    }
    
    .grades-table tr:hover {
        background: rgba(59, 130, 246, 0.05);
    }
    
    .grade-passed {
        color: #9cd9a4;
        font-weight: 600;
    }
    
    .grade-failed {
        color: #f8a8a8;
        font-weight: 600;
    }
    
    .empty-grades {
        text-align: center;
        padding: 3rem;
        color: #809bb8;
    }
    
    .semester-badge-small {
        background: #2b5797;
        color: white;
        padding: 0.3rem 1rem;
        border-radius: 60px;
        font-size: 0.8rem;
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
        <div class="sidebar-logo">🎓 Grade<span>Portal</span></div>
        <div class="school-badge">Kanto Buan Academy</div>
      </div>
      
      <nav class="sidebar-nav">
        <a href="homepage.php" class="nav-item">
          <span class="nav-icon">📊</span>
          <span>Dashboard</span>
        </a>

        <a href="grades.php" class="nav-item active">
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
          <h1 class="page-title">My Grades</h1>
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
          <h2>Academic Records 📚</h2>
        </div>
        <div class="welcome-action">
          <span class="semester-badge">Semester 2 · 2025-2026</span>
        </div>
      </div>

      <!-- Grades Container -->
      <div class="grades-container">
        <div class="grades-header">
          <h2>Grade Report</h2>
          <?php if ($overall_gpa > 0): ?>
          <div class="gpa-card">
            <div class="gpa-label">Overall GPA</div>
            <div class="gpa-value"><?php echo number_format($overall_gpa, 2); ?></div>
          </div>
          <?php endif; ?>
        </div>

        <?php if (count($grades) > 0): ?>
        <div class="grades-table-wrapper">
          <table class="grades-table">
            <thead>
              <tr>
                <th>Subject Code</th>
                <th>Subject Name</th>
                <th>Teacher</th>
                <th>Prelim</th>
                <th>Midterm</th>
                <th>Final</th>
                <th>Average</th>
                <th>Units</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($grades as $grade): ?>
              <tr>
                <td><?php echo htmlspecialchars($grade['subject_code']); ?></td>
                <td><?php echo htmlspecialchars($grade['subject_name']); ?></td>
                <td><?php echo htmlspecialchars($grade['teacher_name'] ?? 'Not Assigned'); ?></td>
                <td><?php echo $grade['prelim_grade'] ? number_format($grade['prelim_grade'], 2) : '-'; ?></td>
                <td><?php echo $grade['midterm_grade'] ? number_format($grade['midterm_grade'], 2) : '-'; ?></td>
                <td><?php echo $grade['final_grade'] ? number_format($grade['final_grade'], 2) : '-'; ?></td>
                <td class="<?php echo ($grade['average_grade'] && $grade['average_grade'] >= 75) ? 'grade-passed' : ''; ?>">
                  <?php echo $grade['average_grade'] ? number_format($grade['average_grade'], 2) : '-'; ?>
                </td>
                <td><?php echo $grade['units']; ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        
        <div style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid #2a4058;">
          <p style="color: #809bb8; font-size: 0.85rem;">
            <span class="grade-passed" style="display: inline-block; width: 12px; height: 12px; background: #9cd9a4; border-radius: 2px; margin-right: 0.5rem;"></span> 
            Passed (≥75)
          </p>
        </div>
        
        <?php else: ?>
        <div class="empty-grades">
          <p style="font-size: 1.2rem; margin-bottom: 0.5rem;">📭 No grades available yet</p>
          <p>Your grades will appear here once they are posted by your teachers.</p>
        </div>
        <?php endif; ?>
      </div>
      
      <!-- footer -->
      <footer class="dashboard-footer">
        <p>© 2026 Kanto Buan Academy · <a href="logout.php">Log out</a></p>
      </footer>
    </main>
  </div>
</body>
</html>