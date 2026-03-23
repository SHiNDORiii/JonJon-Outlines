<?php
// Start session to access session data
session_start();

// Destroy all session data
session_unset();
session_destroy();

// Redirect to login page
header("Location: index.php");
exit();
?>