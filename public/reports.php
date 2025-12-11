<?php
require __DIR__ . '/../includes/functions.php';
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/header.php';

$pdo = db();

function runReport(PDO $pdo, string $sql): array
{
    try {
        return $pdo->query($sql)->fetchAll();
    } catch (Throwable $e) {
        return ['__error' => $e->getMessage()];
    }
}

function renderTable(array $rows): void
{
    if (!$rows) {
        echo "<p>No rows.</p>";
        return;
    }
    if (isset($rows['__error'])) {
        echo "<p>Error: " . esc($rows['__error']) . "</p>";
        return;
    }
    $headers = array_keys($rows[0]);
    echo "<table><tr>";
    foreach ($headers as $h) {
        echo "<th>" . esc($h) . "</th>";
    }
    echo "</tr>";
    foreach ($rows as $row) {
        echo "<tr>";
        foreach ($headers as $h) {
            echo "<td>" . esc($row[$h]) . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
}

$topMovies   = runReport($pdo, "SELECT * FROM vw_movie_ticket_sales ORDER BY TicketsSold DESC, Title");
$soldOut     = runReport($pdo, "SELECT * FROM vw_soldout_showtimes");
$utilization = runReport($pdo, "SELECT * FROM vw_theatre_utilization_next7");
?>

<h2>Reports</h2>

<h3>Top Movies</h3>
<?php renderTable($topMovies); ?>

<h3>Sold-out Showtimes</h3>
<?php renderTable($soldOut); ?>

<h3>Utilization Next 7 Days</h3>
<?php renderTable($utilization); ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>

