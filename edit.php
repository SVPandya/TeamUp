<?php
    session_start();

    include("php/config.php");
    if (!isset($_SESSION['valid'])){
        header("Location: index.php");
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/style.css">
    <title>Change Profile | TeamUp</title>
    <link rel="icon" type="image/png" href="favicons.png">
</head>
<body>
    <div class="nav">
        <div class="logo">
            <p><a href="home.php">TeamUp</a></p>
        </div>

        <div class="right-links">
            <a href="createRequest.php" class="btn">Create Event</a>
            <a href="myCommitments.php" class="btn">My Commitments</a>
            <a href="#" class="btn">Change Profile</a>
            <a href="php/logout.php"> <button class="btn">Log Out</button></a>
        </div>
    </div>




    <div class="container">
        <div class="box form-box">

            <?php
                if (isset($_POST['submit'])){
                    $username = $_POST['username'];
                    $email = $_POST['email'];
                    $age = $_POST['age'];
                    $phoneNum = $_POST['phoneNum'];

                    $id=$_SESSION['id'];

                    $edit_query = mysqli_query($conn, "UPDATE users SET Username='$username', Email = '$email', Age = '$age', Phone_Num = '$phoneNum' WHERE Id=$id") or die ("Error occurred");

                    if ($edit_query){
                        echo "<div class='message'>
                        <p>Profile Updated!</p>
                        </div> <br>";
                        echo "<a href='home.php'><button class='btn'>Go Home</button></a>";
                    }
                   
                }

                else{
                    $id = $_SESSION['id'];
                    $query = mysqli_query($conn, "SELECT * FROM users WHERE Id=$id");

                    while($result = mysqli_fetch_assoc($query)){
                        $res_Uname = $result['Username'];
                        $res_Email = $result['Email'];
                        $res_Age = $result['Age'];
                        $res_Phone = $result['Phone_Num'];
                    }
                ?>


            <header>Change Profile</header>
            <form action="" method="post">
                <div class="field input">
                    <label for="username">Name</label>
                    <input type="text" name="username" id="username" value=<?php echo $res_Uname; ?> required autocomplete="off">
                </div>

                <div class="field input">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" value=<?php echo $res_Email; ?> required autocomplete="off">
                </div>

                <div class="field input">
                    <label for="age">Age</label>
                    <input type="number" name="age" id="age" value=<?php echo $res_Age; ?> required min="0" autocomplete="off">
                </div>

                <div class="field input">
                    <label for="phoneNum">Phone Number</label>
                    <input type="number" name="phoneNum" id="phoneNum" value=<?php echo $res_Phone; ?> required min="0" autocomplete="off">
                </div>



                <div class="field">
                    <input type="submit" class="btn" name="submit" value="Update" required>
                </div>

            </form>
        </div>
        <?php } ?>
    </div>
</body>
</html>