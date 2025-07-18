<?php
use PHPMailer\PHPMailer\PhpMailer;
use PHPMailer\PHPMailer\SMTP;

require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$apiKey = $_ENV['GOOGLE_API_KEY'];
$mailPassword = $_ENV['PHPMAILER_KEY'];


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
// $equipment = $_POST['equipment'];
$equipmentString = $_POST['allEquipment'];
$equipmentStringChecked = $_POST['allEquipmentChecked'];
$equipmentChecked = $_POST['checkedEquipmentOnly'];
// $equipmentChecked = $_POST['sport'];


$datetime = $date . " " . $time;
date_default_timezone_set("America/Chicago");
$today = date("Y-m-d H:i:s");
if (strtotime($datetime) >= strtotime($today)){
    // $stmt = $conn->prepare("INSERT INTO requests (user_id, sport, location, date, time, end_time, skill_level, people_needed, age_range, equipment) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    // $stmt->bind_param("isssssiiss", $user_id, $sport, $location, $date, $time, $end_time, $skill_level, $people_needed, $age_range, $equipment);
    $stmt = $conn->prepare("INSERT INTO requests (user_id, sport, location, date, time, end_time, skill_level, people_needed, age_range, equipment) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssssiiss", $user_id, $sport, $location, $date, $time, $end_time, $skill_level, $people_needed, $age_range, $equipmentStringChecked);

    if ($stmt->execute()) {
        // echo "<p style='color: green; font-weight: bold;'>Request posted successfully!</p>";
        $requestId = $conn->insert_id;
        $attendStmt = $conn->prepare("INSERT INTO attendances (user_id, request_id, equipment_checked) VALUES (?, ?, ?)");
        $attendStmt->bind_param("iis", $user_id, $requestId, $equipmentChecked);
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



        
        require "vendor/autoload.php";
        
        
        $date = date("F j, Y", strtotime($date));
        $time = date("g:i A", strtotime($time));
        $end_time = date("g:i A", strtotime($end_time));
        
        $mail = new PHPMailer(true);
        
        $mail->isSMTP();
        $mail->SMTPAuth = true;
        
        $mail->Host = "smtp.gmail.com";
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        $mail->Username = "spand0225@gmail.com";
        $mail->Password = $mailPassword;
        
        $mail->setFrom("spand0225@gmail.com", "TeamUp Team");
        $mail->addAddress("$email", $name); //recipient
        
        $mail->Subject = "Event Created - " . $date . " - " . $sport;
        $mail->Body = "Hey $name,\n\nJust wanted to let you know that your event for $sport on $date from $time to $end_time at $location has been created successfully!\n\nHave fun,\nTeamUp Team";
        
        $mail->send();
        
        header("Location: createRequest.php");


    } else {
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
    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo $apiKey ?>&v=beta&libraries=places"
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


            
            <a href="php/logout.php"> <button class="btn">Log Out</button></a>
        </div>
    </div>



        <table style="width: 100%; height: calc(100vh - 60px); border: none; border-spacing: inherit;">
        <tr style="width: 100%;">
            <td style="width: 50%; padding-bottom: 110px;">
        <div class="viewPosts">
            <!-- <h2 style="margin-top: 2%;">My Commitments</h2> -->
            <h2 style="margin-top: 2%;">Create an Event</h2>
            <form action="" method="post" id="postForm" class="popup-card">
                
                        <!-- <td> -->
                        <div class="userInpPostGroup" id="sportGroup">   
                            <label for="sport" class="formLabels">Sport</label>
                            <input class="userInpPost" type="text" id="sport" name="sport" required placeholder="Select or start typing" onclick="showSportList()" onkeyup="filterSport()">
                            <div class="sportListCont">
                            <a value="Badminton" class="sportList">Badminton</a>
                            <a value="Baseball" class="sportList">Baseball</a>
                            <a value="Basketball" class="sportList">Basketball</a>
                            <a value="Bowling" class="sportList">Bowling</a>
                            <a value="Capture the Flag" class="sportList">Capture the Flag</a>
                            <a value="Cricket" class="sportList">Cricket</a>
                            <a value="Disk Golf" class="sportList">Disk Golf</a>
                            <a value="Flag Football" class="sportList">Flag Football</a>
                            <a value="Football" class="sportList">Football</a>
                            <a value="Golf" class="sportList">Golf</a>
                            <a value="Hockey" class="sportList">Hockey</a>
                            <a value="Pickleball" class="sportList">Pickleball</a><a value="Soccer" class="sportList">Soccer</a>
                            <a value="Softball" class="sportList">Softball</a>
                            <a value="Spikeball" class="sportList">Spikeball</a>
                            <a value="Table Tennis" class="sportList">Table Tennis</a>
                            <a value="Tennis" class="sportList">Tennis</a>
                            <a value="Ultimate Frisbee" class="sportList">Ultimate Frisbee</a>
                            <a value="Volleyball" class="sportList">Volleyball</a>
                            <a value="Water Polo" class="sportList">Water Polo</a>
                            </div>
                        </div>
                        
                    
                    
                        
                        <div class="userInpPostGroup">
                            <label for="location" class="formLabels">Location</label>
                            <input class="userInpPost" type="text" name="location" id="location" placeholder="Enter location" required>
                            <div id="suggestions"></div>
                        </div>
                        
                        <table style="width: 100%;">
                            <tr style="width: 100%;">
                                <!-- style="width: 100%;" -->
                                <td style="width: 33%;">
                                    <div class="userInpPostGroup">
                                        <label for="date" class="formLabels">Date</label>
                                        <input class="userInpPost" type="date" name="date" id="date" placeholder=none required>
                                    </div>
                                </td>
                                <td style="width: 33%;">
                                    <div class="userInpPostGroup">
                                        <label for="time" class="formLabels">Start Time</label>
                                        <input class="userInpPost" type="time" name="time" id="time" required>
                                    </div>
                                </td>
                                <td style="width: 33%;">
                                    <div class="userInpPostGroup">
                                        <label for="endTime" class="formLabels">End Time</label>
                                        <input class="userInpPost" type="time" name="endTime" id="endTime" required>
                                    </div>
                                </td>
                            </tr>
                        </table>

                        <ul id="hourlyWeather"></ul>
                    
                        
                        <div class="userInpPostGroup">
                            <label for="skillLevel" class="formLabels">Skill Level</label>
                            <select class="userInpPost" name="skillLevel" id="skillLevel" required>
                                <option value="1">Beginner</option>
                                <option value="2">Intermediate</option>
                                <option value="3">Advanced</option>
                            </select>
                        </div>
                        
                    
                    
                        
                        <div class="userInpPostGroup">
                            <label for="peopleNeeded" class="formLabels">People Needed</label>
                            <input type="number" class="userInpPost" name="peopleNeeded" id="peopleNeeded" min=1 required placeholder="Enter number">
                        </div>
                    
                        <div class="userInpPostGroup">
                            <label for="ageRange" class="formLabels">Age Range</label>
                            <select name="ageRange" id="ageRange" class="userInpPost" required>
                                <option value="15-20">15-20</option>
                                <option value="20-30">20-30</option>
                                <option value="30-40">30-40</option>
                                <option value="40-50">40-50</option>
                                <option value="50-60">50-60</option>
                                <option value="60-70">60-70</option>
                                <option value="70-80">70-80</option>
                            </select>
                        </div>

                        <!-- <div class="userInpPostGroup">
                            <label for="equipment">Equipment</label>
                            <input type="text" class="userInpPost" name="equipment" id="equipment" required placeholder="Eg. I will bring 2 rackets and 3 tennis balls.">
                        </div> -->
                    
                
                
                    
                        
                        <table style="width: 100%;">
                            <tr style="width: 100%;">
                                <!-- style="width: 100%;" -->
                                <td style="width: 50%;">
                                    <div class="userInpPostGroup">
                                        <input type="text" class="userInpPost" name="equipment" id="equipment" placeholder="Racket">
                                    </div>
                                </td>
                                <td style="width: 50%;">
                                    <div class="userInpPostGroup">
                                        <button name="addEquipment" class='btn' required onclick="addEquip(event)" >Add Equipment Item</button>
                                    </div>
                                </td>
                            </tr>
                        </table>

                        <div class="userInpPostGroup">
                            <h4 class="formLabels">What will you bring?</h4>
                            <div class="equipmentContainer"></div>
                            <input type="hidden" id="allEquipment" name="allEquipment">
                            <input type="hidden" id="allEquipmentChecked" name="allEquipmentChecked">
                            <input type="hidden" id="checkedEquipmentOnly" name="checkedEquipmentOnly">

                        </div>

                        <div class="userInpPostGroup">
                            <input type="submit" name="submit" value="Post" class="btn" style="border: none; margin-top: 10px;" required>
                        </div>
                        
                    
                    
            </form>
        </div>
        </td>
        <td style="width: 50%; height: 100vh; background: #e8871e; z-index: 10; position: relative;" id="right">
        <div id="map3d" style="height: 100%; margin: 0; padding: 0;"></div>
        </td>
        </tr>
        </table> 
        

        <script src="index.js"></script>


</body>
</html>