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
$username = 'root';
$password = '';

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize messages
$message = '';
$messageType = '';

// Process grade submission for teachers
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_grades') {
    $student_id = intval($_POST['student_id']);
    
    // Loop through each subject's grades
    foreach ($_POST['grades'] as $subject_id => $grades_data) {
        $prelim_grade = !empty($grades_data['prelim']) ? floatval($grades_data['prelim']) : null;
        $midterm_grade = !empty($grades_data['midterm']) ? floatval($grades_data['midterm']) : null;
        $final_grade = !empty($grades_data['final']) ? floatval($grades_data['final']) : null;
        
        // Calculate average if all three grades exist
        $average_grade = null;
        $grade_count = 0;
        $sum = 0;
        
        if ($prelim_grade !== null) { $sum += $prelim_grade; $grade_count++; }
        if ($midterm_grade !== null) { $sum += $midterm_grade; $grade_count++; }
        if ($final_grade !== null) { $sum += $final_grade; $grade_count++; }
        
        if ($grade_count > 0) {
            $average_grade = $sum / $grade_count;
        }
        
        // Check if grade record exists
        $check_query = "SELECT grade_id FROM grade WHERE student_id = ? AND subject_id = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("ii", $student_id, $subject_id);
        $check_stmt->execute();
        $check_stmt->store_result();
        
        if ($check_stmt->num_rows > 0) {
            // Update existing grade
            $update_query = "UPDATE grade SET 
                                prelim_grade = ?,
                                midterm_grade = ?,
                                final_grade = ?,
                                average_grade = ?,
                                teacher_id = ?
                            WHERE student_id = ? AND subject_id = ?";
            $update_stmt = $conn->prepare($update_query);
            $teacher_id = $_SESSION['user_id'];
            $update_stmt->bind_param("ddddiii", $prelim_grade, $midterm_grade, $final_grade, $average_grade, $teacher_id, $student_id, $subject_id);
            $update_stmt->execute();
            $update_stmt->close();
        } else {
            // Insert new grade
            $insert_query = "INSERT INTO grade (student_id, subject_id, teacher_id, prelim_grade, midterm_grade, final_grade, average_grade, school_year, semester) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, '2025-2026', '2nd Sem')";
            $insert_stmt = $conn->prepare($insert_query);
            $teacher_id = $_SESSION['user_id'];
            $insert_stmt->bind_param("iiidddd", $student_id, $subject_id, $teacher_id, $prelim_grade, $midterm_grade, $final_grade, $average_grade);
            $insert_stmt->execute();
            $insert_stmt->close();
        }
        $check_stmt->close();
    }
    
    $message = "All grades updated successfully!";
    $messageType = "success";
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
$department = isset($_SESSION['department']) ? $_SESSION['department'] : '';

// STUDENT VIEW - Fetch grades
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
        
        if ($row['average_grade'] && $row['units']) {
            $total_grade_points += $row['average_grade'] * $row['units'];
            $total_units += $row['units'];
        }
    }
    $stmt->close();
    
    if ($total_units > 0) {
        $overall_gpa = $total_grade_points / $total_units;
    }
}

// TEACHER VIEW - Fetch all students and all subjects with grades
$students = [];
$selected_student_id = isset($_GET['student_id']) ? intval($_GET['student_id']) : 0;
$student_grades = [];
$selected_student_info = null;

if ($user_type == 'teacher') {
    // Fetch all students
    $students_query = "SELECT student_id, student_number, first_name, last_name, year_level, course, section 
                       FROM student 
                       ORDER BY last_name, first_name";
    $students_result = $conn->query($students_query);
    while ($row = $students_result->fetch_assoc()) {
        $students[] = $row;
    }
    
    // If a student is selected, fetch all subjects with their grades
    if ($selected_student_id > 0) {
        // Get selected student info
        $student_info_query = "SELECT student_id, student_number, first_name, last_name, year_level, course, section 
                               FROM student WHERE student_id = ?";
        $student_info_stmt = $conn->prepare($student_info_query);
        $student_info_stmt->bind_param("i", $selected_student_id);
        $student_info_stmt->execute();
        $student_info_result = $student_info_stmt->get_result();
        $selected_student_info = $student_info_result->fetch_assoc();
        $student_info_stmt->close();
        
        // Fetch all subjects with their grades for this student
        $subjects_query = "SELECT 
                            s.subject_id,
                            s.subject_code,
                            s.subject_name,
                            s.units,
                            g.grade_id,
                            g.prelim_grade,
                            g.midterm_grade,
                            g.final_grade,
                            g.average_grade,
                            CONCAT(t.first_name, ' ', t.last_name) AS teacher_name
                          FROM subject s
                          LEFT JOIN grade g ON g.subject_id = s.subject_id AND g.student_id = ?
                          LEFT JOIN teacher t ON g.teacher_id = t.teacher_id
                          ORDER BY s.subject_code";
        
        $subjects_stmt = $conn->prepare($subjects_query);
        $subjects_stmt->bind_param("i", $selected_student_id);
        $subjects_stmt->execute();
        $subjects_result = $subjects_stmt->get_result();
        
        while ($row = $subjects_result->fetch_assoc()) {
            $student_grades[] = $row;
        }
        $subjects_stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $user_type == 'student' ? 'My Grades' : 'Grade Management'; ?> · GradePortal</title>
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
    
    /* Teacher grade input form styles */
    .grade-input-form {
        background: rgba(18, 28, 40, 0.7);
        backdrop-filter: blur(8px);
        border: 1px solid #2a4058;
        border-radius: 1.5rem;
        padding: 1.8rem;
        margin-bottom: 1.8rem;
    }
    
    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }
    
    .form-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .form-group label {
        color: #cbd5e1;
        font-size: 0.9rem;
        font-weight: 500;
    }
    
    .form-group select {
        padding: 0.8rem 1rem;
        background: #1e2c3a;
        border: 2px solid #2c4054;
        border-radius: 12px;
        font-size: 1rem;
        color: white;
        outline: none;
        transition: all 0.2s;
    }
    
    .form-group select:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
    }
    
    .student-info {
        background: #1e3349;
        padding: 1rem;
        border-radius: 12px;
        margin-bottom: 1.5rem;
        color: #e2e8f0;
    }
    
    .student-info strong {
        color: #60a5fa;
    }
    
    .grades-editor-table {
        width: 100%;
        border-collapse: collapse;
        color: #e2e8f0;
    }
    
    .grades-editor-table th,
    .grades-editor-table td {
        padding: 0.8rem;
        text-align: left;
        border-bottom: 1px solid #2a4058;
        vertical-align: middle;
    }
    
    .grades-editor-table th {
        background: rgba(30, 44, 58, 0.5);
        color: #60a5fa;
        font-weight: 600;
    }
    
    .grade-input-small {
        width: 80px;
        padding: 0.5rem;
        background: #1e2c3a;
        border: 2px solid #2c4054;
        border-radius: 8px;
        font-size: 0.9rem;
        color: white;
        text-align: center;
    }
    
    .grade-input-small:focus {
        border-color: #3b82f6;
        outline: none;
    }
    
    .average-display {
        font-weight: 600;
        padding: 0.3rem 0.6rem;
        border-radius: 6px;
        display: inline-block;
    }
    
    .average-passed {
        background: rgba(30, 126, 52, 0.3);
        color: #9cd9a4;
    }
    
    .average-failed {
        background: rgba(220, 53, 69, 0.3);
        color: #f8a8a8;
    }
    
    .average-none {
        color: #809bb8;
    }
    
    .submit-btn {
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        color: white;
        border: none;
        padding: 0.8rem 2rem;
        border-radius: 12px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        margin-top: 1.5rem;
        width: 100%;
    }
    
    .submit-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px -8px #1e3a8a;
    }
    
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
    
    .info-text {
        color: #809bb8;
        font-size: 0.85rem;
        margin-top: 1rem;
        text-align: center;
    }
    
    .section-title {
        color: white;
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 1rem;
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
          <img src="logosmacp.png" alt="Logo" class="sidebar-logo-img">
          Grade<span>Portal</span>
        </div>
        <div class="school-badge">Saint Mary Angel's College of Pampanga</div>
      </div>
      
      <nav class="sidebar-nav">
        <a href="homepage.php" class="nav-item">
          <span class="nav-icon">📊</span>
          <span>Dashboard</span>
        </a>

        <a href="grades.php" class="nav-item active">
          <span class="nav-icon">📝</span>
          <span><?php echo $user_type == 'student' ? 'My Grades' : 'Grade Management'; ?></span>
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
          <h1 class="page-title"><?php echo $user_type == 'student' ? 'My Grades' : 'Grade Management'; ?></h1>
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
          <h2><?php echo $user_type == 'student' ? 'Academic Records 📚' : 'Grade Entry Panel 📝'; ?></h2>
          <?php if ($user_type == 'teacher'): ?>
          <p>Select a student to view and edit all their grades</p>
          <?php endif; ?>
        </div>
        <div class="welcome-action">
          <span class="semester-badge">Semester 2 · 2025-2026</span>
        </div>
      </div>

      <?php if ($user_type == 'student'): ?>
      <!-- STUDENT VIEW - Grade Report -->
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

      <?php elseif ($user_type == 'teacher'): ?>
      <!-- TEACHER VIEW - Grade Input Form -->
      
      <?php if ($message): ?>
        <div class="feedback-message <?php echo $messageType; ?>">
          <?php echo htmlspecialchars($message); ?>
        </div>
      <?php endif; ?>
      
      <div class="grade-input-form">
        <form method="get" action="grades.php" class="selection-form">
          <div class="form-row">
            <div class="form-group">
              <label for="student_id">Select Student</label>
              <select id="student_id" name="student_id" required onchange="this.form.submit()">
                <option value="">-- Select Student --</option>
                <?php foreach ($students as $student): ?>
                <option value="<?php echo $student['student_id']; ?>" <?php echo ($selected_student_id == $student['student_id']) ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($student['student_number'] . ' - ' . $student['first_name'] . ' ' . $student['last_name'] . ' (' . $student['course'] . ' - ' . $student['year_level'] . ')'); ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        </form>
        
        <?php if ($selected_student_id > 0 && $selected_student_info): ?>
        <!-- Student Information -->
        <div class="student-info">
          <strong><?php echo htmlspecialchars($selected_student_info['first_name'] . ' ' . $selected_student_info['last_name']); ?></strong><br>
          Student Number: <?php echo htmlspecialchars($selected_student_info['student_number']); ?> | 
          Year Level: <?php echo htmlspecialchars($selected_student_info['year_level']); ?> | 
          Course: <?php echo htmlspecialchars($selected_student_info['course']); ?> | 
          Section: <?php echo htmlspecialchars($selected_student_info['section']); ?>
        </div>
        
        <form method="post" action="grades.php" class="grade-form">
          <input type="hidden" name="action" value="update_grades">
          <input type="hidden" name="student_id" value="<?php echo $selected_student_id; ?>">
          
          <div class="section-title">📖 All Subjects - Enter Grades Below</div>
          
          <div class="grades-table-wrapper">
            <table class="grades-editor-table">
              <thead>
                <tr>
                  <th>Subject Code</th>
                  <th>Subject Name</th>
                  <th>Units</th>
                  <th>Prelim (%)</th>
                  <th>Midterm (%)</th>
                  <th>Final (%)</th>
                  <th>Average</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($student_grades as $subject): ?>
                <tr>
                  <td><?php echo htmlspecialchars($subject['subject_code']); ?></td>
                  <td><?php echo htmlspecialchars($subject['subject_name']); ?></td>
                  <td><?php echo $subject['units']; ?></td>
                  <td>
                    <input type="number" 
                           name="grades[<?php echo $subject['subject_id']; ?>][prelim]" 
                           value="<?php echo $subject['prelim_grade'] ? htmlspecialchars($subject['prelim_grade']) : ''; ?>"
                           class="grade-input-small" 
                           step="0.01" min="0" max="100"
                           placeholder="-">
                  </td>
                  <td>
                    <input type="number" 
                           name="grades[<?php echo $subject['subject_id']; ?>][midterm]" 
                           value="<?php echo $subject['midterm_grade'] ? htmlspecialchars($subject['midterm_grade']) : ''; ?>"
                           class="grade-input-small" 
                           step="0.01" min="0" max="100"
                           placeholder="-">
                  </td>
                  <td>
                    <input type="number" 
                           name="grades[<?php echo $subject['subject_id']; ?>][final]" 
                           value="<?php echo $subject['final_grade'] ? htmlspecialchars($subject['final_grade']) : ''; ?>"
                           class="grade-input-small" 
                           step="0.01" min="0" max="100"
                           placeholder="-">
                  </td>
                  <td>
                    <?php if ($subject['average_grade']): ?>
                    <span class="average-display <?php echo $subject['average_grade'] >= 75 ? 'average-passed' : 'average-failed'; ?>">
                      <?php echo number_format($subject['average_grade'], 2); ?>%
                    </span>
                    <?php else: ?>
                    <span class="average-display average-none">Not yet</span>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          
          <button type="submit" class="submit-btn">💾 Save All Grades</button>
        </form>
        <?php endif; ?>
      </div>
      
      <?php if ($selected_student_id == 0): ?>
        <div class="info-text">👆 Select a student from the dropdown above to view and edit their grades.</div>
      <?php endif; ?>
      
      <?php endif; ?>
      
      <!-- footer -->
      <footer class="dashboard-footer">
        <p>© 2026 Saint Mary Angel's College of Pampanga · <a href="logout.php">Log out</a></p>
      </footer>
    </main>
  </div>
</body>
</html>