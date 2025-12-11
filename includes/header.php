<?php
<<<<<<< HEAD
=======
// includes/header.php
>>>>>>> 35c37ff1b165e0c72cba96d85d82eb9478467533
require_once __DIR__ . '/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
<<<<<<< HEAD
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movie Theatre</title>
    <link rel="stylesheet" href="/styles.css">
=======
    <title>Movie Theatre</title>
    <link rel="stylesheet" href="/movie-theatre/assets/styles.css">
>>>>>>> 35c37ff1b165e0c72cba96d85d82eb9478467533
</head>
<body>
<header>
    <h1>Movie Theatre</h1>
    <nav>
<<<<<<< HEAD
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
=======
        <a href="/movie-theatre/public/index.php">Home</a>
        <a href="/movie-theatre/public/movies.php">Movies</a>
        <a href="/movie-theatre/public/showtimes.php">Showtimes</a>
        <a href="/movie-theatre/public/my_tickets.php">My Tickets</a>
        <a href="/movie-theatre/public/reports.php">Reports</a>
    </nav>
</header>
<main>

>>>>>>> 35c37ff1b165e0c72cba96d85d82eb9478467533
