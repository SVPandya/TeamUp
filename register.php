<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/style.css">
    <title>Sign Up | TeamUp</title>
    <link rel="icon" type="image/png" href="favicons.png">
</head>
<body>

<div class="nav">
        <div class="logo">
            <p><a href="home.php">TeamUp</a></p>
        </div>

        <div class="right-links">
            <a href="index.php" class="btn">Login</a>
            <a href="register.php" class="btn">Sign Up</a>
        </div>
    </div>

    <div class="container">
        <div class="box form-box">

            <?php
            
                include("php/config.php");
                if (isset($_POST['submit'])){
                    $username = ucwords($_POST['username']);
                    $email = $_POST['email'];
                    $age = $_POST['age'];
                    $password = $_POST['password'];
                    $phoneNum = $_POST['phoneNum'];

                    //verifying the unique email

                    $verify_query = mysqli_query($conn, "SELECT Email FROM users WHERE Email = '$email'");

                    if (mysqli_num_rows($verify_query) != 0){
                        echo "<div class='message' style='
                            text-align: center;
                            background: #f9eded;
                            padding: 15px 0px;
                            border: 1px solid #699053;
                            border-radius: 5px;
                            margin-bottom: 10px;
                            color: red;'>
                        <p>This email is used, try another one please!</p>
                        </div> <br>";
                        echo "<a href='javascript:self.history.back()'><button class='btn'>Go Back</button></a>";
                    }
                    else{
                        mysqli_query($conn, "INSERT INTO users(Username, Email, Age, Password, Phone_Num) VALUES('$username', '$email', '$age', '$password', '$phoneNum')") or die ("Error occurred");
                        echo "<div class='message'>
                        <p>Registration successful</p>
                        </div> <br>";
                        echo "<a href='index.php'><button class='btn'>Login Now</button></a>";
                    }
                }
                else{

            ?>

            <header>Sign Up</header>
            <form action="" method="post">
                <div class="field input">
                    <label for="username">Name</label>
                    <input type="text" name="username" id="username" required autocomplete="off">
                </div>

                <div class="field input">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" required autocomplete="off">
                </div>

                <div class="field input">
                    <label for="age">Age</label>
                    <input type="number" name="age" id="age" min="0" required autocomplete="off">
                </div>

                <div class="field input">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" required autocomplete="off">
                </div>

                <div class="field input">
                    <label for="phoneNum">Phone Number</label>
                    <input type="number" name="phoneNum" id="phoneNum" required autocomplete="off">
                </div>


                <div class="field">
                    <input type="submit" class="btn" name="submit" value="Register" required>
                </div>

                <div class="links">
                    Already a member? <a href="index.php">Sign In</a>
                </div>
            </form>
        </div>
        <?php } ?>
    </div>
</body>
</html>