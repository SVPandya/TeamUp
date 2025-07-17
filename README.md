# TeamUp
After downloading this repository, create a `.env` file in the base directory and add the following code:

`GOOGLE_API_KEY = {API KEY}`

In your Google Account, search for `App Passwords` and create a new password with the name `SMTP`. Add the following code:

`PHPMAILER_KEY = "{APP PASSWORD}"`

## Package Installation (Windows)
Navigate to the C: drive and run to install Composer:

`php -r "copy('http://getcomposer.org/installer', 'composer-setup.php');"`

`php -r "if (hash_file('sha384', 'composer-setup.php') === 'dac665fdc30fdd8ec78b38b9800061b4150413ff2e3b6f88543c636f7cd84f6db9189d43a81e5503cda447da73c7e5b6') { echo 'Installer verified'.PHP_EOL; } else { echo 'Installer corrupt'.PHP_EOL; unlink('composer-setup.php'); exit(1); }"`

`php composer-setup.php --disable-tls`

`php -r "unlink('composer-setup.php');"`

After Composer is installed, run in the project directory:

`composer require phpmailer/phpmailer`

Install XAMPP from [Apache Friends](https://www.apachefriends.org/) and move this repository to `C:/xampp/htdocs`.
To run, open the XAMPP control panel and start Apache and MySQL. 

## MySQL Set Up
Go to [localhost/phpmyadmin](locahost/phpmyadmin) and create a database called `newteamup`. Inside this database, create 3 tables: `users`, `requests`, `attendances`. The `users` table should have 7 columns: Id (Auto-incrementing, primary), Username (varchar(200)), Email (varchar(200)), Age (int(11)), Password (varchar(200)), Phone_Num (bigint(11)), town (text). 

The `requests` table should have 12 columns: id (Auto-incrementing, primary), user_id (index_choice: index, int(11)), sport (varchar(100)), location (varchar(255)), date (date), time (time), end_time (time), created_at (timestamp, default: current_timestamp()), skill_level (int(11)), people_needed (int(11)), age_range (text), equipment (text). 

The `attendances` table can be made by running this SQL code: 

`CREATE TABLE attendances (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    request_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(Id),
    FOREIGN KEY (request_id) REFERENCES requests(id),
    UNIQUE (user_id, request_id) -- Prevents duplicate attendance
);`. 

Then make a new column called equipment_checked (text).


Once the database is set up, the website can be accessed at [localhost/newTeamUp](localhost/newTeamUp).
