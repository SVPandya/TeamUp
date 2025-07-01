<?php
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

    // OPTIONAL: validate inputs (e.g., not empty, valid formats)
    
    // Insert into the 'requests' table
    $datetime = $date . " " . $time;
    date_default_timezone_set("America/Chicago");
    $today = date("Y-m-d H:i:s");
    if (strtotime($datetime) >= strtotime($today)){
        $stmt = $conn->prepare("INSERT INTO requests (user_id, sport, location, date, time, end_time, skill_level, people_needed) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssii", $user_id, $sport, $location, $date, $time, $end_time, $skill_level, $people_needed);

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
    if ($peopleNeeded > 0){
        $peopleNeeded --;
        $stmt=$conn->prepare("UPDATE requests SET people_needed = ? WHERE id = ?");
        $stmt->bind_param("ii", $peopleNeeded, $requestId);

        if ($stmt->execute()){
            echo "<script>alert('Attendance Confirmed!')</script>";
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

    // Insert into attendances table
    $check = $conn->prepare("SELECT * FROM attendances WHERE user_id = ? AND request_id = ?");
    $check->bind_param("ii", $userId, $requestId);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows == 0) {
        $insert = $conn->prepare("INSERT INTO attendances (user_id, request_id) VALUES (?, ?)");
        $insert->bind_param("ii", $userId, $requestId);
        $insert->execute();
        $insert->close();

        // Then decrement people_needed and delete if needed (as you already do)
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
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAuy24KIJyJtG01xMGEFhwMJiRadDjFxeM&libraries=places"
        defer></script>
    <link rel="stylesheet" href="style/home.css">
    <link rel="icon" type="image/png" href="favicons.png">
    <title>My Commitments | TeamUp</title>
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
                    $res_phoneNum = $result['Phone_Num'];
                }

                echo "<a href='edit.php?Id=$res_id' class='btn'>Change Profile</a>";
            
            ?>


            
            <!-- <a href="#">Change Profile</a> -->
            <a href="php/logout.php"> <button class="btn">Log Out</button></a>
        </div>
    </div>

   

        <!-- See Posts -->
        <div class="viewPosts">
            <h2 style="margin-top: 2%;">My Commitments</h2>
            <div class="viewPostCards">
            <?php
            
            $currentUser = $_SESSION['id'];
            $currentTime = date("Y-m-d H:i:s");
                // $result = mysqli_query($conn, "SELECT * FROM requests WHERE user_id != '$currentUser' AND CONCAT(date, ' ', time) >= '$currentTime' ORDER BY date, time");
                $result = mysqli_query($conn, "SELECT r.* FROM attendances a JOIN requests r ON a.request_id = r.id WHERE a.user_id
                ='$currentUser' AND CONCAT(r.date, ' ', r.time) >= '$currentTime' ORDER BY r.date, r.time");

                while ($row = mysqli_fetch_assoc($result)){
                    $requestId = $row['id'];

                    $playersQuery = mysqli_query($conn, "SELECT u.Username, u.Phone_Num FROM attendances a JOIN users u 
                    ON a.user_id = u.Id WHERE a.request_id = '$requestId'");

                    $formattedDate = date("F j, Y", strtotime($row['date']));
                    $formattedEndTime = date("g:i A", strtotime($row['end_time']));
                    $formattedTime = date("g:i A", strtotime($row['time']));

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

                    $location = htmlspecialchars($row['location']);
                    echo "<a href='https://www.google.com/maps/search/?api=1&query=" . urlencode($location) . "' target='_blank' class='innerText'>" . $location . "</a><br><p class='innerText'>" .
                        $formattedDate . "・" . $formattedTime . "-" . $formattedEndTime . "・" .
                        (
                            $row['skill_level'] == 1 ? 'Beginner' :
                            ($row['skill_level'] == 2 ? 'Intermediate' : 'Advanced')
                        ) . "</p>";

                    echo "<p class='innerText'>Age Range: " . htmlspecialchars($row['age_range']) . "</p><br>";
                    echo "<p class='innerText'>Open Spaces: " . htmlspecialchars($row['people_needed']) . "</p><br>";
                    echo "<p class='innerText'>Equipment: " . htmlspecialchars($row['equipment']) . "</p><br>";
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
                        $combined = $playerNames[$i] . " (" . $playerPhoneNums[$i] . ")・";
                    }
                    $combined .= $playerNames[count($playerNames)-1] . " (" . $playerPhoneNums[count($playerNames)-1] . ")";
                    echo "<p class='innerText'>Players: " . $combined . "</p>";
                    echo "</td><td style='vertical-align: top; text-align: right;'>";
                    echo "<img class='postImage' src='" . $imgUrl . "'>";
                    echo "</td>";
                    echo "</tr></table>";
                    echo "<hr style='margin-top: 10px; border-top: 1px solid #858585;'>";
                    echo "</div>";
                }
                
            ?>
            </div>
        </div>

    </main>
    <script src="index.js"></script>
</body>
</html> 