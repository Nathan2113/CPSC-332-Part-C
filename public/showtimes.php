<?php
require __DIR__ . '/../includes/functions.php';
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/header.php';

$pdo = db();

// Load theatres for dropdown
$theatres = $pdo->query("
    SELECT TheatreID, TheatreName
    FROM Theatre
    ORDER BY TheatreName
")->fetchAll();

$selectedTheatre = get_int($_GET, 'theatre_id');
$selectedDate    = get_string($_GET, 'date') ?? date('Y-m-d');

$params = [];
$sql = "
    SELECT s.ShowtimeID, s.StartTime, s.Format,
           m.Title,
           a.AuditoriumName,
           t.TheatreName
    FROM Showtime s
    JOIN Movie m ON s.MovieID = m.MovieID
    JOIN Auditorium a ON s.AuditoriumID = a.AuditoriumID
    JOIN Theatre t ON a.TheatreID = t.TheatreID
    WHERE 1=1
";

if ($selectedTheatre) {
    $sql .= " AND t.TheatreID = :theatre_id";
    $params[':theatre_id'] = $selectedTheatre;
}
if ($selectedDate) {
    $sql .= " AND DATE(s.StartTime) = :date";
    $params[':date'] = $selectedDate;
}

$sql .= " ORDER BY s.StartTime";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$showtimes = $stmt->fetchAll();
?>

<h2>Showtime Finder</h2>

<form method="get">
    <label>
        Theatre:
        <select name="theatre_id">
            <option value="">-- any --</option>
            <?php foreach ($theatres as $t): ?>
                <option value="<?= (int)$t['TheatreID'] ?>" <?= $selectedTheatre == $t['TheatreID'] ? 'selected' : '' ?>>
                    <?= esc($t['TheatreName']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>
        Date:
        <input type="date" name="date" value="<?= esc($selectedDate) ?>">
    </label>
    <button type="submit">Find Showtimes</button>
</form>

<?php if (!$showtimes): ?>
    <p>No showtimes found for that theatre/date.</p>
<?php else: ?>
    <table>
        <tr>
            <th>Time</th>
            <th>Movie</th>
            <th>Format</th>
            <th>Theatre</th>
            <th>Auditorium</th>
            <th>Action</th>
        </tr>
        <?php foreach ($showtimes as $s): ?>
            <tr>
                <td><?= esc($s['StartTime']) ?></td>
                <td><?= esc($s['Title']) ?></td>
                <td><?= esc($s['Format']) ?></td>
                <td><?= esc($s['TheatreName']) ?></td>
                <td><?= esc($s['AuditoriumName']) ?></td>
                <td>
                    <a href="seats.php?showtime_id=<?= (int)$s['ShowtimeID'] ?>">View Seats</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<?php
require __DIR__ . '/../includes/footer.php';