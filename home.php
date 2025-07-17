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
    $sport = $_POST['sport'];
    $location = $_POST['location'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $end_time = $_POST['endTime'];
    $skill_level = $_POST['skillLevel'];
    $people_needed = $_POST['peopleNeeded'];
    // $equipment = $_POST['equipment'];

    // OPTIONAL: validate inputs (e.g., not empty, valid formats)
    
    // Insert into the 'requests' table
    $datetime = $date . " " . $time;
    date_default_timezone_set("America/Chicago");
    $today = date("Y-m-d H:i:s");
    if (strtotime($datetime) >= strtotime($today)){
        // $stmt = $conn->prepare("INSERT INTO requests (user_id, sport, location, date, time, end_time, skill_level, people_needed, equipment) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        // $stmt->bind_param("isssssiis", $user_id, $sport, $location, $date, $time, $end_time, $skill_level, $people_needed, $equipment);
        $stmt = $conn->prepare("INSERT INTO requests (user_id, sport, location, date, time, end_time, skill_level, people_needed) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssiis", $user_id, $sport, $location, $date, $time, $end_time, $skill_level, $people_needed);

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







if (isset($_POST['attendEvent'])){
    $peopleNeeded = $_POST['peopleNeeded'];
    $requestId = $_POST['requestId'];
    $equipmentUpdated = $_POST['equipmentUpdated'];
    if ($peopleNeeded > 0){
        $peopleNeeded --;
        $stmt=$conn->prepare("UPDATE requests SET people_needed = ?, equipment = ? WHERE id = ?");
        $stmt->bind_param("isi", $peopleNeeded, $equipmentUpdated, $requestId);

        if ($stmt->execute()){
            // echo "<script>alert('Attendance Confirmed!')</script>";
        }
        else{
            echo "<script>alert('Error: ,'" . $stmt->error . ")</script>";
        }

        
    }
        // $query = "DELETE FROM requests WHERE people_needed = 0";
        // mysqli_query($conn, $query);
    
}






if (isset($_POST['attendEvent'])) {
    $requestId = $_POST['requestId'];
    $userId = $_SESSION['id'];
    $userEquipment = $_POST['userEquipment'];








    // Insert into attendances table
    $check = $conn->prepare("SELECT * FROM attendances WHERE user_id = ? AND request_id = ?");
    $check->bind_param("ii", $userId, $requestId);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows == 0) {
        $insert = $conn->prepare("INSERT INTO attendances (user_id, request_id, equipment_checked) VALUES (?, ?, ?)");
        $insert->bind_param("iis", $userId, $requestId, $userEquipment);
        $insert->execute();
        $insert->close();

        // Then decrement people_needed and delete if needed (as you already do)






        $playersQuery = mysqli_query($conn, "SELECT u.Username, u.Phone_Num FROM attendances a JOIN users u 
        ON a.user_id = u.Id WHERE a.request_id = '$requestId'");

        while ($player = mysqli_fetch_assoc($playersQuery)){
            $playerNames[] = htmlspecialchars($player['Username']);
            // $playerPhoneNums[] = htmlspecialchars($player['Phone_Num']);
            $strLength = (string)$player['Phone_Num'];
            if ((strlen($strLength) === 10)){
                $firstThree = substr($player['Phone_Num'], 0, 3);
                $secondThree = substr($player['Phone_Num'], 3, 3);
                $lastFour = substr($player['Phone_Num'], 6, 4);
                $playerPhoneNums[] = htmlspecialchars($firstThree . "-" . $secondThree . "-" . $lastFour);
                
            }
            else{
                $playerPhoneNums[] = htmlspecialchars($player['Phone_Num']);
            }
        }
        // echo "<p class='innerText'>Players: " . implode(", ", $playerNames) . implode(", ", $playerPhoneNums) . "</p>";
        $combined = "";
        for ($i = 0; $i < count($playerNames)-1; $i++){
            $combined .= $playerNames[$i] . " (" . $playerPhoneNums[$i] . ")・";
            // echo "<script>console.log('Combined: " . $combined . "' );</script>";
        }
        $combined .= $playerNames[count($playerNames)-1] . " (" . $playerPhoneNums[count($playerNames)-1] . ")";
        // echo "<script>console.log('Final Combined: " . $combined . "' );</script>";
        
    











        $allUsersQuery = $conn->prepare("SELECT user_id FROM attendances WHERE request_id = ?");
        $allUsersQuery->bind_param("i", $requestId);
        $allUsersQuery->execute();
        $allUsersResult = $allUsersQuery->get_result();
        while ($row = $allUsersResult->fetch_assoc()){
            $userId = $row['user_id'];


            $emailQuery = $conn->prepare("SELECT Email from users WHERE Id = ?");
            $emailQuery->bind_param("i", $userId);
            $emailQuery->execute();
            $emailResult = $emailQuery->get_result();
            if ($emailResult->num_rows > 0){
                $userData = $emailResult->fetch_assoc();
                $email = $userData['Email'];
            }
            $emailQuery->close();
    
            $nameQuery = $conn->prepare("SELECT Username from users WHERE Id = ?");
            $nameQuery->bind_param("i", $userId);
            $nameQuery->execute();
            $nameResult = $nameQuery->get_result();
            if ($nameResult->num_rows > 0){
                $userData = $nameResult->fetch_assoc();
                $name = $userData['Username'];
            }
            $nameQuery->close();
    
    
    
    
            $dateQuery = $conn->prepare("SELECT * FROM requests WHERE id = ?");
            $dateQuery->bind_param("i", $requestId);
            $dateQuery->execute();
            $dateResult = $dateQuery->get_result();
            if ($dateResult->num_rows > 0){
                $eventData = $dateResult->fetch_assoc();
                // $date = $eventData['date'];
                $date = date("F j, Y", strtotime($eventData['date']));
                // $time = $eventData['time'];
                // $end_time = $eventData['end_time'];
                $time = date("g:i A", strtotime($eventData['time']));
                $end_time = date("g:i A", strtotime($eventData['end_time']));
                $sport = $eventData['sport'];
                $location = $eventData['location'];
                // $equipment = $eventData['equipment'];
            }
    
    
    
    
            require "vendor/autoload.php";
            
            
            
            $mail = new PHPMailer(true);
            
            $mail->isSMTP();
            $mail->SMTPAuth = true;
            
            $mail->Host = "smtp.gmail.com";
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            
            $mail->Username = "spand0225@gmail.com";
            $mail->Password = $mailPassword;
            
            $mail->setFrom("spand0225@gmail.com", "TeamUp Team"); //whatever email the user inputs is the email it sends from
            $mail->addAddress("$email", $name); //recipient
            
            $mail->Subject = "Participant Added - " . $date . " - " . $sport;
            // $mail->Body = "Hey $name,\nJust wanted to let you know that you're confirmed to attend $sport on $date from $time to $end_time!\nPlayers: $combined, \n\nHave fun,\nTeamUp Team";
            $mail->Body = "Hey $name,\n\nJust wanted to let you know that a new participant has confirmed to attend $sport on $date from $time to $end_time at $location!\nPlayers: $combined\n\nHave fun,\nTeamUp Team";
            
            $mail->send();
            
            header("Location: home.php");
        }
        } 
    // else {
    //     echo "<script>alert('You are already attending this event.')</script>";
    // }

    $check->close();
}










?>










<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo $apiKey ?>&libraries=places"
        defer></script>
    <link rel="stylesheet" href="style/home.css">
    <link rel="icon" type="image/png" href="favicons.png">
    <title>Home | TeamUp</title>
</head>
<body>
    <div class="nav">
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
                    $res_PhoneNum = $result['Phone_Num'];
                }

                echo "<a href='edit.php?Id=$res_id' class='btn'>Change Profile</a>";
            
            ?>


            
            <!-- <a href="#">Change Profile</a> -->
            <a href="php/logout.php"> <button class="btn">Log Out</button></a>
        </div>
    </div>

    <main id="mainCont">
        <div class="main-box top">
                <div class="box">
                    <!-- <p style="margin: 0 3%;"><b>Welcome back <?php echo $res_Uname?>!</b></p> -->
                    <p style="width: 90%; margin: auto;"><b>Welcome back, <?php echo $res_Uname?>!</b></p>
                </div>
                <!-- <div class="box">
                    <p>Your email is <b><?php echo $res_Email?></b></p>
                </div>
                <div class="box">
                    <p>And you are <b><?php echo $res_Age?> years old</b></p>
                </div> -->
        </div>


<!-- Google Places API Sport and Location search -->


    <!-- <div id="place">
        <div id="googleSearch">
        <input type="text" id="userInputTodoLocation" name="userInputTodoLocation" placeholder="e.g., pizza in New York" value="tennis in schaumburg"
            size="40">
            </div>
        <button onclick="searchPlaces()" class="btn">Search</button>
        <div id="results"></div>
        <div id="secondResults">
            <ol id="list">
            </ol>
        </div>
    </div> -->


<!-- Form to create a new request to play a sport (post) -->
 <!-- POP UP FORM -->
 <!-- <button onclick="showPostForm()" class="btn" style="margin: 0 3%;">Show Post Form</button>
 <div class="popup-overlay" id="formPopupOverlay">
        <form action="" method="post" id="postForm" class="popup-card">
        <div class="userInpPostGroup">    
        <label for="sport">Sport</label>
            <input class="userInpPost" type="text" id="sport" name="sport" required placeholder="Eg. Tennis">
</div>

            <div class="userInpPostGroup">
            <label for="location">Location</label>
            <input class="userInpPost" type="text" name="location" id="location" required>
            <div id="suggestions"></div>
</div>

            <div class="userInpPostGroup">
            <label for="date">Date</label>
            <input class="userInpPost" type="date" name="date" id="date" required>
</div>

            <div class="userInpPostGroup">
            <label for="time">Time</label>
            <input class="userInpPost" type="time" name="time" id="time" required>
</div>

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
                <input type="number" class="userInpPost" name="peopleNeeded" id="peopleNeeded" required>
            </div>

            <div class="userInpPostGroup">
            <input type="submit" name="submit" value="Post" class="btn" style="border: none; margin-top: 10px;" required>
            </div>
        </form>
        </div> -->
        


        <!-- See Posts -->
        <div class="viewPosts">
            <h2 style="margin-top: 2%;">Open Requests</h2>
            <div class="viewPostCards">
            <?php
            
            $currentUser = $_SESSION['id'];
            $currentUserTown = $_SESSION['town'];
            $currentTime = date("Y-m-d H:i:s");
            if ($res_Age >= 15 && $res_Age <= 20){
                $userAgeRange = "15-20";
            }
            else if ($res_Age > 20 && $res_Age <= 30){
                $userAgeRange = "20-30";
            }
            else if ($res_Age > 30 && $res_Age <= 40){
                $userAgeRange = "30-40";
            }
            else if ($res_Age > 40 && $res_Age <= 50){
                $userAgeRange = "40-50";
            }
            else if ($res_Age > 50 && $res_Age <= 60){
                $userAgeRange = "50-60";
            }
            else if ($res_Age > 60 && $res_Age <= 70){
                $userAgeRange = "60-70";
            }
            else if ($res_Age > 70 && $res_Age <= 80){
                $userAgeRange = "70-80";
            }
                // $result = mysqli_query($conn, "SELECT * FROM requests WHERE user_id != '$currentUser' AND CONCAT(date, ' ', time) >= '$currentTime' ORDER BY date, time");
                $result = mysqli_query($conn, "SELECT * FROM requests r WHERE r.user_id != '$currentUser' AND CONCAT(r.date, ' ', r.time) >= '$currentTime' AND r.id NOT IN (SELECT request_id FROM attendances WHERE user_id = '$currentUser') ORDER BY r.date, r.time");

                while ($row = mysqli_fetch_assoc($result)){
                    if ($row['people_needed'] > 0 && $row['age_range'] == $userAgeRange){
                    $location = htmlspecialchars($row['location']);
                    
                    // $geocodeDataStart = file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?address={$currentUserTown}&key={$apiKey}");
                    // $outputFrom = json_decode($geocodeDataStart);

                    // $geocodeDataEnd = file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?address={$location}&key={$apiKey}");
                    // $outputTo = json_decode($geocodeDataEnd);

                    // $latFrom = $outputFrom->results[0]->geometry->location->lat;
                    // $lngFrom = $outputFrom->results[0]->geometry->location->lng;
                    // $latTo = $outputTo->results[0]->geometry->location->lat;
                    // $lngTo = $outputTo->results[0]->geometry->location->lng;

                    // $latFrom = deg2rad($latFrom);
                    // $lngFrom = deg2rad($lngFrom);
                    // $latTo = deg2rad($latTo);
                    // $lngTo = deg2rad($lngTo);

                    // $inside = (1-cos($latTo-$latFrom) + cos($latFrom)*cos($latTo)*(1-cos($lngTo-$lngFrom))) / 2;
                    // $km = 2*6371 * asin(sqrt($inside));
                    // $miles = $km*0.62137;

                    // if ($miles <= 6){
                    

                    $formattedDate = date("F j, Y", strtotime($row['date']));
                    $formattedTime = date("g:i A", strtotime($row['time']));
                    $formattedEndTime = date("g:i A", strtotime($row['end_time']));
                    echo "<div class='postCard'>";
                    echo "<table><tr><td style='width: 35%; vertical-align: top;'>";
                    echo "<h3>" . htmlspecialchars($row['sport']) . "</h3>";
                    $imgUrl = "";
                    if (str_contains(strtolower($row['sport']), "basketball")){
                        $imgUrl = "https://plus.unsplash.com/premium_photo-1671436822261-2c99507bfc70?q=80&w=1471&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D";
                    }
                    else if (str_contains(strtolower($row['sport']), "tennis")){
                        $imgUrl = "https://images.unsplash.com/photo-1567220720374-a67f33b2a6b9?q=80&w=1632&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D";
                    }
                    else if (str_contains(strtolower($row['sport']), "volleyball")){
                        $imgUrl = "https://cdn.pixabay.com/photo/2022/09/15/12/29/volleyball-7456365_960_720.jpg";
                    }
                    else if (str_contains(strtolower($row['sport']), "soccer")){
                        $imgUrl = "https://cdn.pixabay.com/photo/2020/08/21/13/00/soccer-5506097_1280.jpg";
                    }
                    else if (str_contains(strtolower($row['sport']), "cricket")){
                        $imgUrl = "https://images.unsplash.com/photo-1624897174291-1bd715e371d5?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MTJ8fGNyaWNrZXR8ZW58MHx8MHx8fDA%3D";
                    }
                    else if (str_contains(strtolower($row['sport']), "pickleball")){
                        $imgUrl = "https://images.unsplash.com/photo-1659318006095-4d44845f3a1b?q=80&w=2110&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D";
                    }
                    else{
                        $imgUrl = "sports-tools.jpg";
                    }
                    
                    // echo "<a href='https://www.google.com/maps/search/?api=1&query=" . $location . "' target='_blank'>" . $location . "</a><br>" . 
                    // $formattedDate . "<br>" . 
                    // $formattedTime . "<br>" .
                    // (
                    //     $row['skill_level'] == 1 ? 'Beginner' :
                    //     ($row['skill_level'] == 2 ? 'Intermediate' : 'Advanced')
                    // ) . 
                    // "</p>";
                    
                    // echo "<p>Open Spaces: " . htmlspecialchars($row['people_needed']) . "</p>";
                    echo "<a class='innerText' href='https://www.google.com/maps/search/?api=1&query=" . $location . "' target='_blank'>" . $location . "</a><br><p class='innerText'>" . 
                    $formattedDate . "・" . 
                    $formattedTime . "-" . $formattedEndTime . "・" .
                    (
                        $row['skill_level'] == 1 ? 'Beginner' :
                        ($row['skill_level'] == 2 ? 'Intermediate' : 'Advanced')
                    ) . 
                    "</p>";
                    
                    echo "<p style='color: #858585;'><strong>Open Spaces:</strong></p>";
                    echo "<p class='innerText'>" . htmlspecialchars($row['people_needed']) . "</p>";








                    $equipmentList = explode(",", $row['equipment']);
                    if (count($equipmentList) > 0 && trim($row['equipment']) !== "") {
                        echo "<p style='color: #858585;'><strong>Equipment:</strong></p>";
                        foreach ($equipmentList as $equipmentPiece) {
                            // if (!str_contains($equipmentPiece, "_checked")){
                            $equipmentPiece = trim($equipmentPiece);
                            // $checkboxId = 'equip_' . $row['id'] . '_' . preg_replace('/[^a-zA-Z0-9]/', '', $equipmentPiece);
                            $checkboxId = $equipmentPiece;
                            
                                // echo "<input hidden type='checkbox' name='equipment[]' class='toggle-btn homePageCheckboxes' value='" . htmlspecialchars($equipmentPiece) . "' id='" . $checkboxId . "'>";
                                // echo "<label hidden style='display: inline-flex; align-items: center; margin-right: 10px;' for='$checkboxId' class='toggle-btn'>";
                            // }
                            // else{
                            echo "<input type='checkbox' name='equipment[]' class='toggle-btn homePageCheckboxes' value='" . htmlspecialchars($equipmentPiece) . "' id='" . $checkboxId . "'>";
                            if (!str_contains($equipmentPiece, "_checked")){
                                echo "<label style='display: inline-flex; align-items: center; margin-right: 10px;' for='$checkboxId' class='toggle-btn'>";
                            }
                            else{
                                echo "<label style='display: none; align-items: center; margin-right: 10px;' for='$checkboxId' class='toggle-btn'>";
                            }
                            echo htmlspecialchars($equipmentPiece);
                            echo "</label>";
                            // }
                        }
                    }





                    // echo "<p class='innerText'>Equipment: " . htmlspecialchars($row['equipment']) . "</p>";
                    // echo "<button class='btn' style='margin-top: 10px;'>Attend</button>";
                    echo "<form method='post' action=''>
                    <input type='hidden' name='requestId' value='" . $row['id'] . "'>
                    <input type='hidden' name='peopleNeeded' value='" . $row['people_needed'] . "'>
                    <input type='hidden' name='equipmentUpdated' id='allEquipmentChecked' value='" . $row['equipment'] . "'>
                    <input type='hidden' name='userEquipment' id='userEquipment' value='" . "" . "'>
                    <button type='submit' name='attendEvent' class='btn' style='margin-top: 10px; width: 100%;'>Attend</button>
                    </form>";
                    echo "</td><td style='vertical-align: top; text-align: right;'>";
                    echo "<img class='postImage' src='" . $imgUrl . "'>";
                    echo "</td>";
                    echo "</tr></table>";
                    echo "<hr style='margin-top: 10px; border-top: 1px solid #858585;'>";
                    echo "</div>";
                    }
                }
            // }


                
                
            ?>
            </div>
        </div>


    </main>
    <script src="index.js"></script>
</body>
</html> 