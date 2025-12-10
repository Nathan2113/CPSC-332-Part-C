<?php
require_once __DIR__ . '/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movie Theatre</title>
    <link rel="stylesheet" href="/styles.css">
</head>
<body>
<header>
    <h1>Movie Theatre</h1>
    <nav>
        <a href="/index.php">Home</a> |
        <a href="/movies.php">Movies</a> |
        <a href="/showtimes.php">Showtimes</a> |
        <a href="/my_tickets.php">My Tickets</a> |
        <a href="/reports.php">Reports</a>
    </nav>
</header>
<main>
<?php $flashes = get_flashes(); ?>
<?php if ($flashes): ?>
    <div class="flash-container">
        <?php foreach ($flashes as $type => $messages): ?>
            <?php foreach ($messages as $msg): ?>
                <div class="flash <?= esc($type) ?>"><?= esc($msg) ?></div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
