<?php
use PHPMailer\PHPMailer\PhpMailer;
use PHPMailer\PHPMailer\SMTP;

session_start();

include("php/config.php");
if (!isset($_SESSION['valid'])){
    header("Location: index.php");
}
date_default_timezone_set('America/Chicago'); // or dynamically detect/set per user

$currentDateTime = date("Y-m-d H:i:s");

$deleteQuery = "DELETE FROM requests WHERE CONCAT(date, ' ', time) < ?";
$stmt = $conn->prepare($deleteQuery);
$stmt->bind_param("s", $currentDateTime);
$stmt->execute();
$stmt->close();


// Handle request form submission
if (isset($_POST['submit'])) {
$user_id = $_SESSION['id']; // Current logged-in user
$sport = ucwords($_POST['sport']);
$location = $_POST['location'];
$date = $_POST['date'];
$time = $_POST['time'];
$end_time = $_POST['endTime'];
$skill_level = $_POST['skillLevel'];
$people_needed = $_POST['peopleNeeded'];
$age_range = $_POST['ageRange'];

// OPTIONAL: validate inputs (e.g., not empty, valid formats)

// Insert into the 'requests' table
$datetime = $date . " " . $time;
date_default_timezone_set("America/Chicago");
$today = date("Y-m-d H:i:s");
if (strtotime($datetime) >= strtotime($today)){
    $stmt = $conn->prepare("INSERT INTO requests (user_id, sport, location, date, time, end_time, skill_level, people_needed, age_range) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssssiis", $user_id, $sport, $location, $date, $time, $end_time, $skill_level, $people_needed, $age_range);

    if ($stmt->execute()) {
        // echo "<p style='color: green; font-weight: bold;'>Request posted successfully!</p>";
        $requestId = $conn->insert_id;
        $attendStmt = $conn->prepare("INSERT INTO attendances (user_id, request_id) VALUES (?, ?)");
        $attendStmt->bind_param("ii", $user_id, $requestId);
        $attendStmt->execute();
        $attendStmt->close();
        echo "<script>alert('Posted Successfully.)</script>";
        // echo $datetime;
        // echo "<br>";
        // echo $today;

        $emailQuery = $conn->prepare("SELECT Email from users WHERE Id = ?");
        $emailQuery->bind_param("i", $user_id);
        $emailQuery->execute();
        $emailResult = $emailQuery->get_result();
        if ($emailResult->num_rows > 0){
            $userData = $emailResult->fetch_assoc();
            $email = $userData['Email'];
        }
        $emailQuery->close();

        $nameQuery = $conn->prepare("SELECT Username from users WHERE Id = ?");
        $nameQuery->bind_param("i", $user_id);
        $nameQuery->execute();
        $nameResult = $nameQuery->get_result();
        if ($nameResult->num_rows > 0){
            $userData = $nameResult->fetch_assoc();
            $name = $userData['Username'];
        }
        $nameQuery->close();






        // $name = "Svar";
        // $email = $_POST['email'];
        
        require "vendor/autoload.php";
        
        
        
        $mail = new PHPMailer(true);
        
        $mail->isSMTP();
        $mail->SMTPAuth = true;
        
        $mail->Host = "smtp.gmail.com";
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        $mail->Username = "spand0225@gmail.com";
        $mail->Password = "kkzm ymho mhys fsfq";
        
        $mail->setFrom("spand0225@gmail.com", "TeamUp Team"); //whatever email the user inputs is the email it sends from
        $mail->addAddress("$email", $name); //recipient
        
        $mail->Subject = "Event Created - " . $date . " - " . $sport;
        $mail->Body = "Hey $name,\nJust wanted to let you know that your event for $sport on $date from $time to $end_time at $location has been created successfully!\n\nHave fun,\nTeamUp Team";
        
        $mail->send();
        
        header("Location: createRequest.php");


    } else {
        // echo "<p style='color: red;'>Error: " . $stmt->error . "</p>";
        echo "<script>alert('Error: ', " . $stmt->error . ")</script>";
    }
    $stmt->close();
}
else{
    echo "<script>alert('Error: You cannot post for a past time.')</script>";
}  
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Event | TeamUp</title>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAuy24KIJyJtG01xMGEFhwMJiRadDjFxeM&libraries=places"
        defer></script>
    <link rel="stylesheet" href="style/home.css">
    <link rel="icon" type="image/png" href="favicons.png">
</head>
<body>
   
<div class="nav" style="z-index: 20; position: relative;">
        <div class="logo">
            <p><a href="home.php">TeamUp</a></p>
        </div>

        <div class="right-links">
            <a href="createRequest.php" class="btn">Create Event</a>
            <a href="myCommitments.php" class="btn">My Commitments</a>
            <?php
            
                $id=$_SESSION['id'];
                $query = mysqli_query($conn, "SELECT * FROM users WHERE Id = $id");

                while($result = mysqli_fetch_assoc($query)){
                    $res_Uname = $result['Username'];
                    $res_Email = $result['Email'];
                    $res_Age = $result['Age'];
                    $res_id = $result['Id'];
                    $res_phoneNum = $result['Phone_Num'];
                }

                echo "<a href='edit.php?Id=$res_id' class='btn'>Change Profile</a>";
            
            ?>


            
            <!-- <a href="#">Change Profile</a> -->
            <a href="php/logout.php"> <button class="btn">Log Out</button></a>
        </div>
    </div>



<!-- <form action="" method="post" id="postForm" class="popup-card">
    <table> 
        <tr>
            <td>
                <div class="userInpPostGroup">   
                    <label for="sport">Sport</label>
                    <input class="userInpPost" type="text" id="sport" name="sport" required placeholder="Eg. Tennis">
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="userInpPostGroup">
                    <label for="location">Location</label>
                    <input class="userInpPost" type="text" name="location" id="location" placeholder="Enter location" required>
                    <div id="suggestions"></div>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="userInpPostGroup">
                    <label for="date">Date</label>
                    <input class="userInpPost" type="date" name="date" id="date" required>
                </div>
            </td>
            <td>
                <div class="userInpPostGroup">
                    <label for="time">Time</label>
                    <input class="userInpPost" type="time" name="time" id="time" required>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="userInpPostGroup">
                    <label for="skillLevel">Skill Level</label>
                    <select class="userInpPost" name="skillLevel" id="skillLevel" required>
                        <option value="1">Beginner</option>
                        <option value="2">Intermediate</option>
                        <option value="3">Advanced</option>
                    </select>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="userInpPostGroup">
                    <label for="peopleNeeded">People Needed</label>
                    <input type="number" class="userInpPost" name="peopleNeeded" id="peopleNeeded" min=1 required>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="userInpPostGroup">
                    <input type="submit" name="submit" value="Post" class="btn" style="border: none; margin-top: 10px;" required>
                </div>
            </td>
        </tr>
            </table>
        </form> -->
        
        <table style="width: 100%; height: calc(100vh - 60px); border: none; border-spacing: inherit;">
        <tr style="width: 100%;">
            <td style="width: 50%; padding-bottom: 110px;">
        <div class="viewPosts">
            <!-- <h2 style="margin-top: 2%;">My Commitments</h2> -->
            <h2 style="margin-top: 2%;">Create an Event</h2>
            <form action="" method="post" id="postForm" class="popup-card">
                
                        <!-- <td> -->
                        <div class="userInpPostGroup">   
                            <label for="sport">Sport</label>
                            <input class="userInpPost" type="text" id="sport" name="sport" required placeholder="Eg. Tennis">
                        </div>
                        
                    
                    
                        
                        <div class="userInpPostGroup">
                            <label for="location">Location</label>
                            <input class="userInpPost" type="text" name="location" id="location" placeholder="Enter location" required>
                            <div id="suggestions"></div>
                        </div>
                        
                        <table style="width: 100%;">
                            <tr style="width: 100%;">
                                <td style="width: 33%;">
                                    <div class="userInpPostGroup">
                                        <label for="date">Date</label>
                                        <input class="userInpPost" type="date" name="date" id="date" placeholder=none required>
                                    </div>
                                </td>
                                <td style="width: 33%;">
                                    <div class="userInpPostGroup">
                                        <label for="time">Start Time</label>
                                        <input class="userInpPost" type="time" name="time" id="time" required>
                                    </div>
                                </td>
                                <td style="width: 33%;">
                                    <div class="userInpPostGroup">
                                        <label for="endTime">End Time</label>
                                        <input class="userInpPost" type="time" name="endTime" id="endTime" required>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    
                        
                        <div class="userInpPostGroup">
                            <label for="skillLevel">Skill Level</label>
                            <select class="userInpPost" name="skillLevel" id="skillLevel" required>
                                <option value="1">Beginner</option>
                                <option value="2">Intermediate</option>
                                <option value="3">Advanced</option>
                            </select>
                        </div>
                        
                    
                    
                        
                        <div class="userInpPostGroup">
                            <label for="peopleNeeded">People Needed</label>
                            <input type="number" class="userInpPost" name="peopleNeeded" id="peopleNeeded" min=1 required placeholder="Enter number">
                        </div>
                    
                        <div class="userInpPostGroup">
                            <label for="ageRange">Age Range</label>
                            <select name="ageRange" id="ageRange" class="userInpPost" required>
                                <option value="10-20">10-20</option>
                                <option value="20-30">20-30</option>
                                <option value="30-40">30-40</option>
                                <option value="40-50">40-50</option>
                                <option value="50-60">50-60</option>
                                <option value="60-70">60-70</option>
                                <option value="70-80">70-80</option>
                            </select>
                        </div>
                    
                
                
                    
                        <div class="userInpPostGroup">
                            <input type="submit" name="submit" value="Post" class="btn" style="border: none; margin-top: 10px;" required>
                        </div>
                        
                    
                    
            </form>
        </div>
        </td>
        <td style="width: 50%; height: 100%; background: #e8871e; z-index: 10;">
            <!-- <div id="testRightSide">fd</div> -->
        </td>
        </tr>
        </table> 
        

        <script src="index.js"></script>
</body>
</html>