USE MovieTheatreDB;
-- Top-N movies by tickets sold (time-bounded; parameterized in WHERE)
CREATE OR REPLACE VIEW vw_movie_ticket_sales AS
SELECT
    m.MovieID,
    m.Title,
    DATE(s.StartTime) AS ShowDate,
    COUNT(CASE WHEN t.Status = 'ACTIVE' THEN 1 END) AS TicketsSold
FROM Movie m
JOIN Showtime s     ON s.MovieID = m.MovieID
LEFT JOIN Ticket t  ON t.ShowtimeID = s.ShowtimeID
GROUP BY
    m.MovieID,
    m.Title,
    DATE(s.StartTime);

-- Upcoming sold-out showtimes per theatre
CREATE OR REPLACE VIEW vw_soldout_showtimes AS
SELECT
    th.TheatreName,
    a.AuditoriumName,
    s.ShowtimeID,
    s.StartTime,
    m.Title,
    COUNT(DISTINCT CASE WHEN t.Status = 'ACTIVE' THEN t.SeatNumber END) AS TicketsSold,
    a.Capacity
FROM Showtime s
JOIN Auditorium a ON s.AuditoriumID = a.AuditoriumID
JOIN Theatre th   ON a.TheatreID    = th.TheatreID
JOIN Movie m      ON s.MovieID      = m.MovieID
LEFT JOIN Ticket t ON t.ShowtimeID  = s.ShowtimeID
WHERE s.StartTime >= NOW()
GROUP BY
    th.TheatreName,
    a.AuditoriumName,
    s.ShowtimeID,
    s.StartTime,
    m.Title,
    a.Capacity
HAVING TicketsSold >= a.Capacity;

--Theatre utilization: % seats sold per showtime for next 7 days
CREATE OR REPLACE VIEW vw_theatre_utilization_next7 AS
SELECT
    th.TheatreName,
    a.AuditoriumName,
    s.ShowtimeID,
    s.StartTime,
    m.Title,
    a.Capacity,
    COUNT(DISTINCT CASE WHEN t.Status = 'ACTIVE' THEN t.SeatNumber END) AS TicketsSold,
    ROUND(
        100.0 * COUNT(DISTINCT CASE WHEN t.Status = 'ACTIVE' THEN t.SeatNumber END) / a.Capacity,
        1
    ) AS UtilizationPercent
FROM Showtime s
JOIN Auditorium a ON s.AuditoriumID = a.AuditoriumID
JOIN Theatre th   ON a.TheatreID    = th.TheatreID
JOIN Movie m      ON s.MovieID      = m.MovieID
LEFT JOIN Ticket t ON t.ShowtimeID  = s.ShowtimeID
WHERE s.StartTime BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY)
GROUP BY
    th.TheatreName,
    a.AuditoriumName,
    s.ShowtimeID,
    s.StartTime,
    m.Title,
    a.Capacity;

