# hotel_managment



this is query for database 


CREATE DATABASE hotel_managment;

CREATE TABLE tbHotels (
    htId INT AUTO_INCREMENT PRIMARY KEY,
    htName VARCHAR(100) NOT NULL,
    htAddr TEXT,
    htCon VARCHAR(20)
);

CREATE TABLE tbRooms (
    rId INT AUTO_INCREMENT PRIMARY KEY,
    htId INT,
    rName VARCHAR(100),
    rType ENUM('Single', 'Double', 'Suite') NOT NULL,
    rPrice DECIMAL(10, 2) NOT NULL,
    rStatus ENUM('available', 'booked') DEFAULT 'available',
    FOREIGN KEY (htId) REFERENCES tbHotels(htId) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE
);

CREATE TABLE tbGuests (
    gId INT AUTO_INCREMENT PRIMARY KEY,
    gName VARCHAR(50) NOT NULL,
    gMail VARCHAR(100) UNIQUE NOT NULL,
    gPhone VARCHAR(20),
    gDob DATE,
    gImage VARCHAR(255),
);
CREATE TABLE tbStaffs (
    sId INT AUTO_INCREMENT PRIMARY KEY,
    sName VARCHAR(100) NOT NULL,
    sPos ENUM('Admin','Manager', 'Receptionist', 'Fulltime Staff','Parttime Staff','Cleaner') NOT NULL,
    sCon VARCHAR(20),
    sAddr VARCHAR(255),
    sImage VARCHAR(255),
    sWork BOOLEAN,
);


CREATE TABLE tbBookings (
    bId INT AUTO_INCREMENT PRIMARY KEY,
    rId INT,
    gId INT,
    bCheckIn DATE NOT NULL,
    bCheckout DATE NOT NULL,
    bPrice DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (rId) REFERENCES tbRooms(rId) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    FOREIGN KEY (gId) REFERENCES tbGuests(gId) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE
);




***noted:
geuest login by using email 
staff login by using name 
