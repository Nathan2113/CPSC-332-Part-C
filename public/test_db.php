<?php
require __DIR__ . '/../includes/db.php';

header('Content-Type: text/plain');

try {
    $pdo = db();
    echo "DB connection OK\n";

    // Simple check to see if it's getting the data
    $row = $pdo->query("SELECT COUNT(*) AS c FROM Movie")->fetch();
    echo "Movies in DB: " . $row['c'] . "\n";

    $row2 = $pdo->query("SELECT COUNT(*) AS c FROM Showtime")->fetch();
    echo "Showtimes in DB: " . $row2['c'] . "\n";

} catch (Throwable $e) {
    echo "ERROR:\n" . $e->getMessage() . "\n";
}

