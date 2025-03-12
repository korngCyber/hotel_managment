<?php

require_once 'core_config/config.php';

$host = DB_SERVER;
$user = DB_USERNAME;
$pass = DB_PASSWORD;
$dbname = DB_NAME;

function db_connect() {
    global $host, $user, $pass, $dbname;
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        return $pdo;
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

function db_close($pdo) {
    $pdo = null;
}

try {
    $pdo = db_connect();

    // SQL queries to create tables
    $sql = "
    CREATE TABLE IF NOT EXISTS tbHotels (
        htId INT AUTO_INCREMENT PRIMARY KEY,
        htName VARCHAR(100) NOT NULL,
        htAddr TEXT,
        htCon VARCHAR(20)
    );

    CREATE TABLE IF NOT EXISTS tbRooms (
        rId INT AUTO_INCREMENT PRIMARY KEY,
        htId INT,
        rName VARCHAR(100),
        rType ENUM('Single', 'Double', 'Suite') NOT NULL,
        rPrice DECIMAL(10, 2) NOT NULL,
        rStatus ENUM('available', 'booked') DEFAULT 'available',
        FOREIGN KEY (htId) REFERENCES tbHotels(htId) ON DELETE CASCADE ON UPDATE CASCADE
    );

    CREATE TABLE IF NOT EXISTS tbGuests (
        gId INT AUTO_INCREMENT PRIMARY KEY,
        gName VARCHAR(50) NOT NULL,
        gMail VARCHAR(100) UNIQUE NOT NULL,
        gPhone VARCHAR(20),
        gDob DATE,
        gImage VARCHAR(255)
    );

    CREATE TABLE IF NOT EXISTS tbStaffs (
        sId INT AUTO_INCREMENT PRIMARY KEY,
        sName VARCHAR(100) NOT NULL,
        sPos ENUM('Admin','Manager', 'Receptionist', 'Fulltime Staff','Parttime Staff','Cleaner') NOT NULL,
        sCon VARCHAR(20),
        sAddr VARCHAR(255),
        sImage VARCHAR(255),
        sWork BOOLEAN
    );

    CREATE TABLE IF NOT EXISTS tbBookings (
        bId INT AUTO_INCREMENT PRIMARY KEY,
        rId INT,
        gId INT,
        bCheckIn DATE NOT NULL,
        bCheckout DATE NOT NULL,
        bPrice DECIMAL(10, 2) NOT NULL,
        FOREIGN KEY (rId) REFERENCES tbRooms(rId) ON DELETE CASCADE ON UPDATE CASCADE,
        FOREIGN KEY (gId) REFERENCES tbGuests(gId) ON DELETE CASCADE ON UPDATE CASCADE
    );

    INSERT INTO tbStaffs (sName, sPos, sCon, sAddr, sImage, sWork)  
    VALUES ('John', 'Manager', '0123456789', '123 Street, City', 'john_doe.jpg', TRUE);
    
    INSERT INTO tbStaffs (sName, sPos, sCon, sAddr, sImage, sWork)  
    VALUES ('John', 'Manager', '0123456789', '123 Street, City', 'john_doe.jpg', TRUE);
    ";

    // Execute the query
    $pdo->exec($sql);

    echo "Database and tables created successfully!";
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Close the connection
db_close($pdo);

?>
