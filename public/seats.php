<?php
require __DIR__ . '/../includes/functions.php';
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/header.php';

$pdo = db();
$showtimeId = get_int($_GET, 'showtime_id');

if (!$showtimeId) {
    echo "<p>Missing showtime.</p>";
    require __DIR__ . '/../includes/footer.php';
    exit;
}

$infoStmt = $pdo->prepare("
    SELECT s.ShowtimeID, s.StartTime, s.Format,
           m.Title,
           a.AuditoriumID, a.AuditoriumName,
           t.TheatreName
    FROM Showtime s
    JOIN Movie m ON s.MovieID = m.MovieID
    JOIN Auditorium a ON s.AuditoriumID = a.AuditoriumID
    JOIN Theatre t ON a.TheatreID = t.TheatreID
    WHERE s.ShowtimeID = :id
");
$infoStmt->execute([':id' => $showtimeId]);
$showtime = $infoStmt->fetch();

if (!$showtime) {
    echo "<p>Showtime not found.</p>";
    require __DIR__ . '/../includes/footer.php';
    exit;
}

$seatsStmt = $pdo->prepare("
    SELECT se.SeatNumber,
           se.RowLabel,
           se.SeatIndex,
           CASE WHEN tk.Status IS NULL OR tk.Status <> 'ACTIVE' THEN 0 ELSE 1 END AS is_sold
    FROM Seat se
    LEFT JOIN Ticket tk ON tk.SeatNumber = se.SeatNumber AND tk.ShowtimeID = :showtime_id
    WHERE se.AuditoriumID = :auditorium_id
    ORDER BY se.RowLabel, se.SeatIndex
");
$seatsStmt->execute([
    ':showtime_id'   => $showtimeId,
    ':auditorium_id' => $showtime['AuditoriumID'],
]);
$seats = $seatsStmt->fetchAll();

// Group seats by row label for table rendering
$rows = [];
foreach ($seats as $seat) {
    $rowKey = $seat['RowLabel'] ?? '';
    $rows[$rowKey][] = $seat;
}
ksort($rows);
?>

<h2>Select Seats</h2>
<p>
    <?= esc($showtime['Title']) ?> —
    <?= esc($showtime['TheatreName']) ?> / <?= esc($showtime['AuditoriumName']) ?> —
    <?= esc($showtime['StartTime']) ?> (<?= esc($showtime['Format']) ?>)
</p>

<?php if (!$seats): ?>
    <p>No seat map found for this auditorium.</p>
<?php else: ?>
    <form method="post" action="/purchase.php">
        <input type="hidden" name="showtime_id" value="<?= (int)$showtimeId ?>">
        <input type="hidden" name="csrf_token" value="<?= esc(csrf_token()) ?>">

        <table class="seat-map">
            <tr><th colspan="<?= count(reset($rows) ?? []) ?>">Screen</th></tr>
            <?php foreach ($rows as $rowNumber => $rowSeats): ?>
                <tr>
                    <th>Row <?= esc($rowNumber) ?></th>
                    <?php foreach ($rowSeats as $seat): ?>
                        <td class="<?= $seat['is_sold'] ? 'sold' : 'available' ?>">
                            <?php if ($seat['is_sold']): ?>
                                <?= esc($seat['SeatIndex']) ?><br><small>Sold</small>
                            <?php else: ?>
                                <label>
                                    <input type="checkbox" name="seat_numbers[]" value="<?= (int)$seat['SeatNumber'] ?>">
                                    <?= esc($seat['SeatIndex']) ?>
                                </label>
                            <?php endif; ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </table>

        <h3>Purchaser</h3>
        <label>
            Name:
            <input type="text" name="customer_name" required>
        </label>
        <label>
            Email:
            <input type="email" name="customer_email" required>
        </label>
        <label>
            Discount Code (optional):
            <input type="text" name="discount_code">
        </label>

        <p><button type="submit">Buy Tickets</button></p>
    </form>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
