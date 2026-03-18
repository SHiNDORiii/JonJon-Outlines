<?php
session_start();

// Clear session
session_unset();
session_destroy();
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
    <title>Logged Out | Grade Portal</title>
</head>
<body>
    <div class="container">
        <div class="box form-box" style="text-align: center;">
            <div class="message" style="background: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 20px;">
                <h2>✅ Logged Out Successfully!</h2>
                <p>You have been logged out of your account.</p>
                <p>Redirecting to login page...</p>
            </div>
        </div>
    </div>
    
    <script>
        // Redirect to login page after 3 seconds
        setTimeout(function() {
            window.location.href = 'index.php';
        }, 3000);
    </script>
</body>
</html>