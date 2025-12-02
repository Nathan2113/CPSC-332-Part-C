<?php
require __DIR__ . '/../includes/functions.php';
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/header.php';

$pdo = db();

// If ?id= is present, show detail page for a single movie
$movieId = get_int($_GET, 'id');

if ($movieId !== null) {
    $stmt = $pdo->prepare("
        SELECT *
        FROM Movie
        WHERE MovieID = :id
    ");
    $stmt->execute([':id' => $movieId]);
    $movie = $stmt->fetch();

    if (!$movie) {
        echo "<h2>Movie not found</h2>";
        echo '<p><a href="movies.php">&laquo; Back to list</a></p>';
        require __DIR__ . '/../includes/footer.php';
        exit;
    }

    echo "<h2>" . esc($movie['Title']) . "</h2>";
    echo "<p>Rating: " . esc($movie['MPAA']) . "</p>";
    echo "<p>Runtime: " . (int)$movie['Runtime'] . " minutes</p>";
    echo "<p>Release Date: " . esc($movie['ReleaseDate']) . "</p>";

    // Upcoming showtimes for this movie
    $stmt = $pdo->prepare("
        SELECT s.ShowtimeID, s.StartTime, s.Format,
               t.TheatreName, a.AuditoriumName
        FROM Showtime s
        JOIN Auditorium a ON s.AuditoriumID = a.AuditoriumID
        JOIN Theatre t ON a.TheatreID = t.TheatreID
        WHERE s.MovieID = :id
          AND s.StartTime >= NOW()
        ORDER BY s.StartTime
    ");
    $stmt->execute([':id' => $movieId]);
    $shows = $stmt->fetchAll();

    if ($shows) {
        echo "<h3>Upcoming Showtimes</h3>";
        echo "<ul>";
        foreach ($shows as $show) {
            echo "<li>";
            echo esc($show['StartTime']) . " (" . esc($show['Format']) . ") - ";
            echo esc($show['TheatreName']) . " / " . esc($show['AuditoriumName']) . " ";
            echo '<a href="seats.php?showtime_id=' . (int)$show['ShowtimeID'] . '">View Seats</a>';
            echo "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No upcoming showtimes for this movie.</p>";
    }

    echo '<p><a href="movies.php">&laquo; Back to movie list</a></p>';
    require __DIR__ . '/../includes/footer.php';
    exit;
}

// Otherwise: list view with optional filters
$rating = get_string($_GET, 'rating');
$format = get_string($_GET, 'format');
$theatreId = get_int($_GET, 'theatre_id');
$date = get_string($_GET, 'date');

$params = [];
$sql = "
    SELECT DISTINCT m.MovieID, m.Title, m.MPAA, m.Runtime
    FROM Movie m
    LEFT JOIN Showtime s ON s.MovieID = m.MovieID
    LEFT JOIN Auditorium a ON s.AuditoriumID = a.AuditoriumID
    LEFT JOIN Theatre t ON a.TheatreID = t.TheatreID
    WHERE 1=1
";

if ($rating) {
    $sql .= " AND m.MPAA = :rating";
    $params[':rating'] = $rating;
}
if ($format) {
    $sql .= " AND s.Format = :format";
    $params[':format'] = $format;
}
if ($theatreId) {
    $sql .= " AND t.TheatreID = :theatre_id";
    $params[':theatre_id'] = $theatreId;
}
if ($date) {
    $sql .= " AND DATE(s.StartTime) = :date";
    $params[':date'] = $date;
}

$sql .= " ORDER BY m.Title";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$movies = $stmt->fetchAll();

// Need theatres list for dropdown
$theatres = $pdo->query("
    SELECT TheatreID, TheatreName
    FROM Theatre
    ORDER BY TheatreName
")->fetchAll();
?>

<h2>Browse Movies</h2>

<form method="get">
    <label>
        Rating:
        <input type="text" name="rating" value="<?= esc($rating ?? '') ?>">
    </label>
    <label>
        Format:
        <input type="text" name="format" value="<?= esc($format ?? '') ?>">
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
        <input type="date" name="date" value="<?= esc($date ?? '') ?>">
    </label>
    <button type="submit">Filter</button>
</form>

<?php if (!$movies): ?>
    <p>No movies match your filters.</p>
<?php else: ?>
    <ul>
        <?php foreach ($movies as $m): ?>
            <li>
                <a href="movies.php?id=<?= (int)$m['MovieID'] ?>">
                    <?= esc($m['Title']) ?>
                </a>
                (<?= esc($m['MPAA']) ?>, <?= (int)$m['Runtime'] ?> min)
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php
require __DIR__ . '/../includes/footer.php';

