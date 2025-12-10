-- Movie Theatre Database Schema

CREATE DATABASE IF NOT EXISTS MovieTheatreDB;
USE MovieTheatreDB;

CREATE TABLE Theatre (
    TheatreID INT AUTO_INCREMENT PRIMARY KEY,
    TheatreName VARCHAR(100) NOT NULL,
    Address VARCHAR(255) NOT NULL
);

CREATE TABLE Auditorium (
    AuditoriumID INT AUTO_INCREMENT PRIMARY KEY,
    TheatreID INT NOT NULL,
    AuditoriumName VARCHAR(100) NOT NULL,
    Capacity INT NOT NULL,
    FOREIGN KEY (TheatreID) REFERENCES Theatre(TheatreID)
);

CREATE TABLE SeatType (
    SeatTypeID INT AUTO_INCREMENT PRIMARY KEY,
    TypeName VARCHAR(50) NOT NULL,
    BasePrice DECIMAL(6,2) NOT NULL
);

CREATE TABLE Seat (
    SeatNumber INT AUTO_INCREMENT PRIMARY KEY,
    AuditoriumID INT NOT NULL,
    RowLabel VARCHAR(5) NOT NULL,
    SeatIndex INT NOT NULL,
    SeatTypeID INT NOT NULL,
    FOREIGN KEY (AuditoriumID) REFERENCES Auditorium(AuditoriumID),
    FOREIGN KEY (SeatTypeID) REFERENCES SeatType(SeatTypeID)
);

CREATE TABLE Movie (
    MovieID INT AUTO_INCREMENT PRIMARY KEY,
    Title VARCHAR(200) NOT NULL,
    Language VARCHAR(50) NOT NULL,
    Runtime INT NOT NULL,
    MPAA VARCHAR(10),
    ReleaseDate DATE NOT NULL
);

CREATE TABLE Showtime (
    ShowtimeID INT AUTO_INCREMENT PRIMARY KEY,
    AuditoriumID INT NOT NULL,
    MovieID INT NOT NULL,
    StartTime DATETIME NOT NULL,
    EndTime DATETIME NOT NULL,
    Format VARCHAR(20) NOT NULL,
    FOREIGN KEY (AuditoriumID) REFERENCES Auditorium(AuditoriumID),
    FOREIGN KEY (MovieID) REFERENCES Movie(MovieID)
);

CREATE TABLE Customer (
    CustomerID INT AUTO_INCREMENT PRIMARY KEY,
    CustomerName VARCHAR(100) NOT NULL,
    Email VARCHAR(255) NOT NULL UNIQUE
);

CREATE TABLE Ticket (
    ShowtimeID INT NOT NULL,
    SeatNumber INT NOT NULL,
    CustomerID INT NULL,
    Discount DECIMAL(4,2) NOT NULL DEFAULT 0.00,
    Price DECIMAL(6,2) NOT NULL,
    Status ENUM('ACTIVE','REFUNDED','CANCELLED') NOT NULL DEFAULT 'ACTIVE',
    PRIMARY KEY (ShowtimeID, SeatNumber),
    FOREIGN KEY (ShowtimeID) REFERENCES Showtime(ShowtimeID),
    FOREIGN KEY (SeatNumber) REFERENCES Seat(SeatNumber),
    FOREIGN KEY (CustomerID) REFERENCES Customer(CustomerID)
);
