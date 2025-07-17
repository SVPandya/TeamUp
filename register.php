<?php

require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$apiKey = $_ENV['GOOGLE_API_KEY'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/style.css">
    <title>Sign Up | TeamUp</title>
    <link rel="icon" type="image/png" href="favicons.png">
    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo $apiKey?>&libraries=places" defer></script>
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

                // use PHPMailer\PHPMailer\PhpMailer;
                // use PHPMailer\PHPMailer\SMTP;
            
                include("php/config.php");
                if (isset($_POST['submit'])){
                    $username = ucwords($_POST['username']);
                    $email = $_POST['email'];
                    $age = $_POST['age'];
                    $password = $_POST['password'];
                    $phoneNum = $_POST['phoneNum'];
                    $town = $_POST['town'];

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
                        mysqli_query($conn, "INSERT INTO users(Username, Email, Age, Password, Phone_Num, town) VALUES('$username', '$email', '$age', '$password', '$phoneNum', '$town')") or die ("Error occurred");
                        echo "<div class='message'>
                        <p>Registration successful</p>
                        </div> <br>";
                        echo "<a href='index.php'><button class='btn'>Login Now</button></a>";

                        // require "vendor/autoload.php";
                        // $mail = new PHPMailer(true);
                        
                        // $mail->isSMTP();
                        // $mail->SMTPAuth = true;
                        
                        // $mail->Host = "smtp.gmail.com";
                        // $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        // $mail->Port = 587;
                        
                        // $mail->Username = "spand0225@gmail.com";
                        // $mail->Password = "kkzm ymho mhys fsfq";
                        
                        // $mail->setFrom("spand0225@gmail.com", "TeamUp Team"); //whatever email the user inputs is the email it sends from
                        // $mail->addAddress("$email", $username); //recipient
                        
                        // $mail->Subject = "TeamUp Sign Up";
                        // $mail->Body = "Hey $username,\n\nThanks for signing up for TeamUp!\n\nHave fun,\nTeamUp Team";
                        
                        // $mail->send();
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

                <div class="field input">
                    <label for="town">City</label>
                    <input type="text" name="town" id="town" required autocomplete="off">
                    <div id="citySuggestions" style="border: 1px solid #ccc; max-height: 150px; overflow-y: auto; display: none;"></div>
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
    <script src="register.js"></script>
</body>
</html>