<?php
    session_start();
?>

<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" href="style.css">
        <title>Grade Portal | Log In</title>
    </head>

    <body>
        <div class="container">
            <div class="box form-box">
                <?php

                    include("php/config.php");
                    if(isset($_POST['submit'])) {
                        $email = mysqli_real_escape_string($con, $_POST['email']);
                        $password = mysqli_real_escape_string($con, $_POST['password']); // Fixed: $POST -> $_POST

                        $result = mysqli_query($con, "SELECT * FROM users WHERE email = '$email' AND password = '$password'") or die("Select Error");
                        $row = mysqli_fetch_assoc($result);

                        if(is_array($row) && !empty($row)){
                            $_SESSION['valid'] = $row['email'];  // Fixed: removed spaces
                            $_SESSION['first_name'] = $row['first_name'];  // Fixed: was using email for all fields
                            $_SESSION['last_name'] = $row['last_name'];    // Fixed: was using email for all fields
                            $_SESSION['user_id'] = $row['id'];  // Assuming you have an 'id' column in your users table
                            
                            header("Location: home.php");  // Moved header inside the if block
                            exit();  // Always call exit after header redirect
                        } else {
                            echo "<div class='message'>
                                    <p>Wrong Email or Password. Please try Again.</p>
                                  </div> <br>";
                            echo "<a href='index.php'><button class='btn'>Go Back</button></a>";
                        }
                    } else {

                ?>

                <header>Log In</header>
                <form action="" method="post">

                    <div class="field input">
                        <label for="email">Email</label>
                        <input type="email" name="email" id="email" required>  <!-- Changed to type="email" -->
                    </div>

                    <div class="field input">
                        <label for="password">Password</label>
                        <input type="password" name="password" id="password" required>  <!-- Changed to type="password" -->
                    </div>

                    <div class="field">
                        <input type="submit" class="btn" name="submit" value="Log In">  <!-- Fixed value and removed required from submit -->
                    </div>

                    <div class="links">
                        <p>Don't have an account? <a href="register.php">Sign Up!</a></p>
                    </div>

                </form>
            </div>
            <?php } ?>
        </div>
    </body>
</html>