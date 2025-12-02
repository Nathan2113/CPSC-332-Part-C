<?php
require __DIR__ . '/../includes/functions.php';
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

