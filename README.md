# TeamUp
Install XAMPP and move this repository to C:/xampp/htdocs.
To run, open the XAMPP control panel and start Apache and MySQL. Go to locahost/phpmyadmin and create a database called "newteamup". Inside this database, create 3 tables: users, requests, attendances. 

The "users" table should have 6 columns: Id (Auto-incrementing, primary), Username (varchar(200)), Email (varchar(200)), Age (int(11)), Password (varchar(200)), Phone_Num (int(11)). 

The "requests" table should have 11 columns: id (Auto-incrementing, primary), user_id (index_choice: index, int(11)), sport (varchar(100)), location (varchar(255)), date (date), time (time), end_time (time), created_at (timestamp, default: current_timestamp()), skill_level (int(11)), people_needed (int(11)), age_range (text). 

The "attendances" table can be made by running this SQL code: 
CREATE TABLE attendances (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    request_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(Id),
    FOREIGN KEY (request_id) REFERENCES requests(id),
    UNIQUE (user_id, request_id) -- Prevents duplicate attendance
);

Once the database is set up, the website can be accessed by running:
localhost/newTeamUp
