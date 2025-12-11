<?php
require __DIR__ . '/../includes/functions.php';
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/header.php';

$pdo = db();

$searchEmail = get_email($_GET, 'email');

$tickets = [];
if ($searchEmail) {
    $params = [':email' => $searchEmail];
    $sql = "
        SELECT tk.ShowtimeID,
               tk.SeatNumber,
               se.RowLabel,
               se.SeatIndex,
               tk.Status,
               tk.Price,
               tk.Discount,
               s.StartTime, s.Format,
               m.Title,
               a.AuditoriumName,
               t.TheatreName,
               c.CustomerName,
               c.Email
        FROM Ticket tk
        JOIN Showtime s ON tk.ShowtimeID = s.ShowtimeID
        JOIN Movie m ON s.MovieID = m.MovieID
        JOIN Auditorium a ON s.AuditoriumID = a.AuditoriumID
        JOIN Theatre t ON a.TheatreID = t.TheatreID
        JOIN Customer c ON tk.CustomerID = c.CustomerID
        JOIN Seat se ON se.SeatNumber = tk.SeatNumber
        WHERE c.Email = :email
        ORDER BY s.StartTime DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $tickets = $stmt->fetchAll();
}
?>

<h2>My Tickets</h2>
<form method="get">
    <label>
        Email:
        <input type="email" name="email" value="<?= esc($searchEmail ?? '') ?>">
    </label>
    <button type="submit">Lookup</button>
</form>

<?php if ($searchEmail && !$tickets): ?>
    <p>No tickets found.</p>
<?php elseif ($tickets): ?>
    <table>
        <tr>
            <th>Movie</th>
            <th>Time</th>
            <th>Theatre</th>
            <th>Seat</th>
            <th>Status</th>
            <th>Price</th>
            <th>Action</th>
        </tr>
        <?php foreach ($tickets as $t): ?>
            <tr>
                <td><?= esc($t['Title']) ?></td>
                <td><?= esc($t['StartTime']) ?></td>
                <td><?= esc($t['TheatreName']) ?> / <?= esc($t['AuditoriumName']) ?></td>
                <td><?= esc($t['RowLabel']) ?>-<?= esc($t['SeatIndex']) ?> (#<?= esc($t['SeatNumber']) ?>)</td>
                <td><?= esc($t['Status']) ?></td>
                <td><?= esc($t['Price']) ?></td>
                <td>
                    <?php if ($t['Status'] !== 'REFUNDED'): ?>
                        <form method="post" action="/refund.php" style="display:inline">
                            <input type="hidden" name="csrf_token" value="<?= esc(csrf_token()) ?>">
                            <input type="hidden" name="showtime_id" value="<?= (int)$t['ShowtimeID'] ?>">
                            <input type="hidden" name="seat_number" value="<?= (int)$t['SeatNumber'] ?>">
                            <input type="hidden" name="email" value="<?= esc($searchEmail ?? $t['Email']) ?>">
                            <button type="submit">Refund</button>
                        </form>
                    <?php else: ?>
                        Refunded
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
