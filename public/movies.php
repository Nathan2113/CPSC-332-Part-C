<?php
require __DIR__ . '/../includes/functions.php';
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/header.php';

$pdo = db();

$movieId     = get_int($_GET, 'id');
$mpaa        = get_string($_GET, 'rating');
$format      = get_string($_GET, 'format');
$theatreId   = get_int($_GET, 'theatre_id');
$dateFilter  = get_string($_GET, 'date');

// Dropdown data
$ratings = $pdo->query("SELECT DISTINCT MPAA FROM Movie WHERE MPAA IS NOT NULL ORDER BY MPAA")->fetchAll();
$formats = $pdo->query("SELECT DISTINCT Format FROM Showtime WHERE Format IS NOT NULL ORDER BY Format")->fetchAll();
$theatres = $pdo->query("SELECT TheatreID, TheatreName FROM Theatre ORDER BY TheatreName")->fetchAll();

if ($movieId) {
    // Detail view
    $movieStmt = $pdo->prepare("SELECT MovieID, Title, MPAA, Language, Runtime, ReleaseDate FROM Movie WHERE MovieID = :id");
    $movieStmt->execute([':id' => $movieId]);
    $movie = $movieStmt->fetch();

    if (!$movie) {
        echo "<p>Movie not found.</p>";
        require __DIR__ . '/../includes/footer.php';
        exit;
    }

    $showStmt = $pdo->prepare("
        SELECT s.ShowtimeID, s.StartTime, s.Format,
               a.AuditoriumName, t.TheatreName
        FROM Showtime s
        JOIN Auditorium a ON s.AuditoriumID = a.AuditoriumID
        JOIN Theatre t ON a.TheatreID = t.TheatreID
        WHERE s.MovieID = :id
        ORDER BY s.StartTime
    ");
    $showStmt->execute([':id' => $movieId]);
    $showtimes = $showStmt->fetchAll();
    ?>

    <h2><?= esc($movie['Title']) ?></h2>
    <p><strong>MPAA:</strong> <?= esc($movie['MPAA'] ?? 'NR') ?></p>
    <p><strong>Language:</strong> <?= esc($movie['Language'] ?? 'N/A') ?></p>
    <p><strong>Runtime:</strong> <?= esc($movie['Runtime'] ?? 'N/A') ?> min</p>
    <p><strong>Release Date:</strong> <?= esc($movie['ReleaseDate'] ?? 'N/A') ?></p>

    <h3>Showtimes</h3>
    <?php if (!$showtimes): ?>
        <p>No showtimes scheduled.</p>
    <?php else: ?>
        <table>
            <tr>
                <th>Time</th>
                <th>Format</th>
                <th>Theatre</th>
                <th>Auditorium</th>
                <th>Action</th>
            </tr>
            <?php foreach ($showtimes as $s): ?>
                <tr>
                    <td><?= esc($s['StartTime']) ?></td>
                    <td><?= esc($s['Format']) ?></td>
                    <td><?= esc($s['TheatreName']) ?></td>
                    <td><?= esc($s['AuditoriumName']) ?></td>
                    <td><a href="seats.php?showtime_id=<?= (int)$s['ShowtimeID'] ?>">View Seats</a></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <p><a href="/movies.php">Back to movies</a></p>

    <?php
    require __DIR__ . '/../includes/footer.php';
    exit;
}

// List view with filters
$params = [];
$sql = "
    SELECT m.MovieID,
           m.Title,
           m.MPAA,
           GROUP_CONCAT(DISTINCT s.Format ORDER BY s.Format SEPARATOR ', ') AS Formats
    FROM Movie m
    LEFT JOIN Showtime s ON s.MovieID = m.MovieID
    LEFT JOIN Auditorium a ON s.AuditoriumID = a.AuditoriumID
    LEFT JOIN Theatre t ON a.TheatreID = t.TheatreID
    WHERE 1=1
";

if ($mpaa) {
    $sql .= " AND m.MPAA = :mpaa";
    $params[':mpaa'] = $mpaa;
}
if ($format) {
    $sql .= " AND s.Format = :format";
    $params[':format'] = $format;
}
if ($theatreId) {
    $sql .= " AND t.TheatreID = :theatre_id";
    $params[':theatre_id'] = $theatreId;
}
if ($dateFilter) {
    $sql .= " AND DATE(s.StartTime) = :date";
    $params[':date'] = $dateFilter;
}

$sql .= "
    GROUP BY m.MovieID, m.Title, m.MPAA
    ORDER BY m.Title
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$movies = $stmt->fetchAll();
?>

<h2>Browse Movies</h2>

<form method="get">
    <label>
        MPAA:
        <select name="rating">
            <option value="">-- any --</option>
            <?php foreach ($ratings as $r): ?>
                <option value="<?= esc($r['MPAA']) ?>" <?= $mpaa === $r['MPAA'] ? 'selected' : '' ?>>
                    <?= esc($r['MPAA']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>
        Format:
        <select name="format">
            <option value="">-- any --</option>
            <?php foreach ($formats as $f): ?>
                <option value="<?= esc($f['Format']) ?>" <?= $format === $f['Format'] ? 'selected' : '' ?>>
                    <?= esc($f['Format']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>
        Theatre:
        <select name="theatre_id">
            <option value="">-- any --</option>
            <?php foreach ($theatres as $t): ?>
                <option value="<?= (int)$t['TheatreID'] ?>" <?= $theatreId == $t['TheatreID'] ? 'selected' : '' ?>>
                    <?= esc($t['TheatreName']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>
        Date:
        <input type="date" name="date" value="<?= esc($dateFilter ?? '') ?>">
    </label>
    <button type="submit">Filter</button>
</form>

<?php if (!$movies): ?>
    <p>No movies match those filters.</p>
<?php else: ?>
    <ul>
        <?php foreach ($movies as $m): ?>
            <li>
                <a href="/movies.php?id=<?= (int)$m['MovieID'] ?>"><?= esc($m['Title']) ?></a>
                <?php if ($m['MPAA']): ?> (<?= esc($m['MPAA']) ?>)<?php endif; ?>
                <?php if ($m['Formats']): ?> - <?= esc($m['Formats']) ?><?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
