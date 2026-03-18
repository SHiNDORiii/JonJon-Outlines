<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" href="https://fonts.googleapis.com">
        <link rel="stylesheet" href="style.css">
        <title>Grade Portal | Sign Up</title>
    </head>

    <body>
        <div class="container">
            <div class="box form-box">

                <?php
                    include("php/config.php");
                    if(isset($_POST['submit'])){
                        $first_name = $_POST['first_name'];
                        $last_name = $_POST['last_name'];
                        $email = $_POST['email'];
                        $password = $_POST['password'];

                        //verify if email is unique//

                        $verify_query = mysqli_query($con,"SELECT email FROM users WHERE email='$email'");

                        if(mysqli_num_rows($verify_query) != 0) {
                            echo "<div class='message'>
                                    <p>This email is used. Try a different email.</p>
                                  </div> <br>";
                            echo "<a href='javascript:self.history.back()'><button class='btn'>Go Back</button></a>";
                        }
                        else {

                            mysqli_query($con, "INSERT INTO users (first_name, last_name, email, password) VALUES ('$first_name','$last_name','$email','$password')") or die("Error Occurred.");
                            
                            echo "<div class='message'>
                                    <p>Register Success!</p>
                                  </div> <br>";
                            echo "<a href='index.php'><button class='btn'>Log In Now</button></a>";

                        }

                    } else {
                ?>

                <header>Sign Up</header>
                <form action="" method="post">

                    <div class="field input">
                        <label for="first_name">First Name</label>
                        <input type="text" name="first_name" id="first_name" autocomplete="off" required>
                    </div>

                    <div class="field input">
                        <label for="last_name">Last Name</label>
                        <input type="text" name="last_name" id="last_name" autocomplete="off" required>
                    </div>

                    <div class="field input">
                        <label for="email">Email</label>
                        <input type="email" name="email" id="email" autocomplete="off" required>
                    </div>

                    <div class="field input">
                        <label for="password">Password</label>
                        <input type="password" name="password" id="password" autocomplete="off" required>
                    </div>

                    <div class="field">
                        <input type="submit" class="btn" name="submit" value="SignUp">
                    </div>

                    <div class="links">
                        <p>Already have an existing account? <a href="index.php">Sign In!</a></p>
                    </div>

                </form>
            </div>
            <?php } ?>
        </div>
    </body>
</html>