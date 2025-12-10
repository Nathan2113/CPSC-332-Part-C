USE MovieTheatreDB;
-- Drop triggers if they already exist
DROP TRIGGER IF EXISTS trg_ticket_before_insert;
DROP TRIGGER IF EXISTS trg_ticket_after_update_refunded;

-- Recreate TicketAudit table 
DROP TABLE IF EXISTS TicketAudit;

CREATE TABLE TicketAudit (
    AuditID     INT AUTO_INCREMENT PRIMARY KEY,
    ShowtimeID  INT NOT NULL,
    SeatNumber  INT NOT NULL,
    CustomerID  INT NULL,
    Action      VARCHAR(20) NOT NULL,
    PerformedAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

DELIMITER //

-- BEFORE INSERT: enforce seat availability and audit
CREATE TRIGGER trg_ticket_before_insert
BEFORE INSERT ON Ticket
FOR EACH ROW
BEGIN
    DECLARE v_cnt INT;

    -- Seat cannot already have an ACTIVE ticket for this showtime
    SELECT COUNT(*)
    INTO v_cnt
    FROM Ticket
    WHERE ShowtimeID = NEW.ShowtimeID
      AND SeatNumber = NEW.SeatNumber
      AND Status = 'ACTIVE';

    IF v_cnt > 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Seat is already sold for this showtime.';
    END IF;

    -- Audit insert
    INSERT INTO TicketAudit (ShowtimeID, SeatNumber, CustomerID, Action)
    VALUES (NEW.ShowtimeID, NEW.SeatNumber, NEW.CustomerID, 'INSERT');
END//


-- AFTER UPDATE: when status becomes REFUNDED, audit it
CREATE TRIGGER trg_ticket_after_update_refunded
AFTER UPDATE ON Ticket
FOR EACH ROW
BEGIN
    IF NEW.Status = 'REFUNDED' AND OLD.Status <> 'REFUNDED' THEN
        INSERT INTO TicketAudit (ShowtimeID, SeatNumber, CustomerID, Action)
        VALUES (NEW.ShowtimeID, NEW.SeatNumber, NEW.CustomerID, 'REFUNDED');
    END IF;
END//

DELIMITER ;
