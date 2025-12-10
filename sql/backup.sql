USE MovieTheatreDB;

-- BACKUP SCRIPT

SET @backup_date := DATE_FORMAT(CURDATE(), 'bak_%Y%m%d_');

-- Helper: drop+clone Theatre
SET @sql := CONCAT('DROP TABLE IF EXISTS ', @backup_date, 'Theatre');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql := CONCAT('CREATE TABLE ', @backup_date, 'Theatre AS SELECT * FROM Theatre');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Auditorium
SET @sql := CONCAT('DROP TABLE IF EXISTS ', @backup_date, 'Auditorium');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql := CONCAT('CREATE TABLE ', @backup_date, 'Auditorium AS SELECT * FROM Auditorium');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- SeatType
SET @sql := CONCAT('DROP TABLE IF EXISTS ', @backup_date, 'SeatType');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql := CONCAT('CREATE TABLE ', @backup_date, 'SeatType AS SELECT * FROM SeatType');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Seat
SET @sql := CONCAT('DROP TABLE IF EXISTS ', @backup_date, 'Seat');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql := CONCAT('CREATE TABLE ', @backup_date, 'Seat AS SELECT * FROM Seat');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Movie
SET @sql := CONCAT('DROP TABLE IF EXISTS ', @backup_date, 'Movie');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql := CONCAT('CREATE TABLE ', @backup_date, 'Movie AS SELECT * FROM Movie');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Showtime
SET @sql := CONCAT('DROP TABLE IF EXISTS ', @backup_date, 'Showtime');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql := CONCAT('CREATE TABLE ', @backup_date, 'Showtime AS SELECT * FROM Showtime');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Customer
SET @sql := CONCAT('DROP TABLE IF EXISTS ', @backup_date, 'Customer');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql := CONCAT('CREATE TABLE ', @backup_date, 'Customer AS SELECT * FROM Customer');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Ticket
SET @sql := CONCAT('DROP TABLE IF EXISTS ', @backup_date, 'Ticket');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql := CONCAT('CREATE TABLE ', @backup_date, 'Ticket AS SELECT * FROM Ticket');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- TicketAudit
SET @sql := CONCAT('DROP TABLE IF EXISTS ', @backup_date, 'TicketAudit');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql := CONCAT('CREATE TABLE ', @backup_date, 'TicketAudit AS SELECT * FROM TicketAudit');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
