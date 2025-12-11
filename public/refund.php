<?php
require __DIR__ . '/../includes/functions.php';
require __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/my_tickets.php');
}

check_csrf(get_string($_POST, 'csrf_token'));

$showtimeId = get_int($_POST, 'showtime_id');
$seatNumber = get_int($_POST, 'seat_number');
$email      = get_email($_POST, 'email');

if (!$showtimeId || !$seatNumber) {
    flash('error', 'Missing ticket info.');
    redirect('/my_tickets.php');
}

$pdo = db();
$ticketStmt = $pdo->prepare("
    SELECT tk.ShowtimeID, tk.SeatNumber, tk.Status, c.Email
    FROM Ticket tk
    JOIN Customer c ON tk.CustomerID = c.CustomerID
    WHERE tk.ShowtimeID = :sid AND tk.SeatNumber = :seat
");
$ticketStmt->execute([':sid' => $showtimeId, ':seat' => $seatNumber]);
$ticket = $ticketStmt->fetch();

if (!$ticket) {
    flash('error', 'Ticket not found.');
    redirect('/my_tickets.php');
}

if ($email && isset($ticket['Email']) && strcasecmp($email, $ticket['Email']) !== 0) {
    flash('error', 'Email does not match ticket.');
    redirect('/my_tickets.php?email=' . urlencode($email));
}

if ($ticket['Status'] === 'REFUNDED') {
    flash('info', 'Ticket already refunded.');
    redirect('/my_tickets.php?email=' . urlencode($email ?? ''));
}

try {
    $pdo->beginTransaction();

    // No refund proc provided; simple status update.
    $update = $pdo->prepare("UPDATE Ticket SET Status = 'REFUNDED' WHERE ShowtimeID = :sid AND SeatNumber = :seat");
    $update->execute([':sid' => $showtimeId, ':seat' => $seatNumber]);
    $pdo->commit();
    flash('success', 'Ticket refunded.');
} catch (Throwable $e) {
    $pdo->rollBack();
    flash('error', 'Could not refund ticket: ' . $e->getMessage());
}

redirect('/my_tickets.php?email=' . urlencode($email ?? ''));
