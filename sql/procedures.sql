USE MovieTheatreDB;

DELIMITER //

DROP PROCEDURE IF EXISTS sell_ticket//

CREATE PROCEDURE sell_ticket (
    IN  p_showtime_id   INT,
    IN  p_seat_id       INT,
    IN  p_customer_id   INT,
    IN  p_discount_code VARCHAR(20),
    OUT p_ticket_key    VARCHAR(50),
    OUT p_final_price   DECIMAL(6,2)
)
BEGIN
    DECLARE v_exists INT;
    DECLARE v_base_price DECIMAL(6,2);
    DECLARE v_discount   DECIMAL(4,2);

    -- Validate showtime exists
    SELECT COUNT(*) INTO v_exists
    FROM Showtime
    WHERE ShowtimeID = p_showtime_id;
    IF v_exists = 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Invalid showtime.';
    END IF;

    -- Validate seat exists
    SELECT COUNT(*) INTO v_exists
    FROM Seat
    WHERE SeatNumber = p_seat_id;
    IF v_exists = 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Invalid seat.';
    END IF;

    -- Validate seat belongs to this showtime's auditorium
    SELECT COUNT(*) INTO v_exists
    FROM Showtime s
    JOIN Seat se ON se.AuditoriumID = s.AuditoriumID
    WHERE s.ShowtimeID = p_showtime_id
      AND se.SeatNumber = p_seat_id;
    IF v_exists = 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Seat does not belong to this showtime''s auditorium.';
    END IF;

    -- Validate customer
    SELECT COUNT(*) INTO v_exists
    FROM Customer
    WHERE CustomerID = p_customer_id;
    IF v_exists = 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Invalid customer.';
    END IF;

    -- Check availability: seat not already ACTIVE
    SELECT COUNT(*) INTO v_exists
    FROM Ticket
    WHERE ShowtimeID = p_showtime_id
      AND SeatNumber = p_seat_id
      AND Status = 'ACTIVE';
    IF v_exists > 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Seat already sold.';
    END IF;

    -- Base price from SeatType
    SELECT st.BasePrice
    INTO v_base_price
    FROM Seat se
    JOIN SeatType st ON st.SeatTypeID = se.SeatTypeID
    WHERE se.SeatNumber = p_seat_id;

    -- Discount mapping
    SET v_discount = 0.0;
    IF UPPER(p_discount_code) = 'STUDENT' THEN
        SET v_discount = 0.20;
    ELSEIF UPPER(p_discount_code) = 'SENIOR' THEN
        SET v_discount = 0.15;
    ELSEIF UPPER(p_discount_code) = 'EMPLOYEE' THEN
        SET v_discount = 1.00;
    END IF;

    SET p_final_price = ROUND(v_base_price * (1 - v_discount), 2);

    -- Insert ticket (explicit column list!)
    INSERT INTO Ticket (
        ShowtimeID,
        SeatNumber,
        CustomerID,
        Discount,
        Price,
        Status
    )
    VALUES (
        p_showtime_id,
        p_seat_id,
        p_customer_id,
        v_discount,
        p_final_price,
        'ACTIVE'
    );

    -- Logical ticket id
    SET p_ticket_key = CONCAT(p_showtime_id, '-', p_seat_id);
END//

DELIMITER ;

-- Example usage:
-- SET @k := NULL; SET @p := NULL;
-- CALL sell_ticket(1, 10, 5, 'STUDENT', @k, @p);
-- SELECT @k AS TicketKey, @p AS FinalPrice;
