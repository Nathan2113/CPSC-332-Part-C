<?php
require __DIR__ . '/../includes/functions.php';
<<<<<<< HEAD
require __DIR__ . '/../includes/header.php';
?>

<h2>Welcome</h2>
<p>Use the links below to browse movies, find showtimes, pick seats, and manage your tickets.</p>

<ul>
    <li><a href="/movies.php">Browse Movies</a></li>
    <li><a href="/showtimes.php">Find Showtimes</a></li>
    <li><a href="/my_tickets.php">My Tickets &amp; Refunds</a></li>
</ul>

<?php require __DIR__ . '/../includes/footer.php'; ?>
=======
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/header.php';

$pdo = db();
$row = $pdo->query("SELECT COUNT(*) AS c FROM Movie")->fetch();
$movieCount = $row['c'] ?? 0;
?>
<h2>Welcome</h2>
<p>This is the Movie Theatre demo application.</p>
<p>Current movies in the database: <strong><?= esc($movieCount) ?></strong></p>

<ul>
    <li><a href="movies.php">Browse Movies</a></li>
    <li><a href="showtimes.php">Find Showtimes</a></li>
    <li><a href="my_tickets.php">View / Refund Tickets</a></li>
    <li><a href="reports.php">View Reports</a></li>
</ul>
<?php
require __DIR__ . '/../includes/footer.php';

>>>>>>> 35c37ff1b165e0c72cba96d85d82eb9478467533
