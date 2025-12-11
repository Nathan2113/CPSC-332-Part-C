<?php
require __DIR__ . '/../includes/functions.php';
require __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/index.php');
}

check_csrf(get_string($_POST, 'csrf_token'));

$showtimeId    = get_int($_POST, 'showtime_id');
$seatNumbers   = array_filter(array_map('intval', $_POST['seat_numbers'] ?? []));
$customerName  = get_string($_POST, 'customer_name');
$customerEmail = get_email($_POST, 'customer_email');
$discountCode  = get_string($_POST, 'discount_code');

if (!$showtimeId || !$seatNumbers || !$customerName || !$customerEmail) {
    flash('error', 'Missing required fields or seats.');
    redirect("/seats.php?showtime_id={$showtimeId}");
}

$pdo = db();

// Validate showtime and auditorium
$showStmt = $pdo->prepare("
    SELECT s.ShowtimeID, s.AuditoriumID
    FROM Showtime s
    WHERE s.ShowtimeID = :id
");
$showStmt->execute([':id' => $showtimeId]);
$showtime = $showStmt->fetch();

if (!$showtime) {
    flash('error', 'Showtime not found.');
    redirect('/showtimes.php');
}

// Validate seats belong to the auditorium and are not sold.
$inPlaceholders = implode(',', array_fill(0, count($seatNumbers), '?'));
$seatCheckSql = "
    SELECT se.SeatNumber,
           se.AuditoriumID,
           tk.Status
    FROM Seat se
    LEFT JOIN Ticket tk ON tk.SeatNumber = se.SeatNumber AND tk.ShowtimeID = ?
    WHERE se.SeatNumber IN ($inPlaceholders)
";
$seatStmt = $pdo->prepare($seatCheckSql);
$seatStmt->execute(array_merge([$showtimeId], $seatNumbers));
$foundSeats = $seatStmt->fetchAll();

if (count($foundSeats) !== count($seatNumbers)) {
    flash('error', 'One or more seats are invalid for this auditorium.');
    redirect("/seats.php?showtime_id={$showtimeId}");
}

foreach ($foundSeats as $seat) {
    if ((int)$seat['AuditoriumID'] !== (int)$showtime['AuditoriumID']) {
        flash('error', 'A selected seat is not in this auditorium.');
        redirect("/seats.php?showtime_id={$showtimeId}");
    }
    if ($seat['Status'] === 'ACTIVE') {
        flash('error', 'A selected seat is already sold.');
        redirect("/seats.php?showtime_id={$showtimeId}");
    }
}

// Find or create customer
$customerStmt = $pdo->prepare("SELECT CustomerID FROM Customer WHERE Email = :email");
$customerStmt->execute([':email' => $customerEmail]);
$customerId = $customerStmt->fetchColumn();

if (!$customerId) {
    $insertCustomer = $pdo->prepare("INSERT INTO Customer (CustomerName, Email) VALUES (:name, :email)");
    $insertCustomer->execute([
        ':name'  => $customerName,
        ':email' => $customerEmail,
    ]);
    $customerId = (int)$pdo->lastInsertId();
}

try {
    $pdo->beginTransaction();

    $useProc = true;
    $procStmt = $pdo->prepare("CALL sell_ticket(:showtime_id, :seat_id, :customer_id, :discount_code, @ticket_key, @final_price)");
    $priceStmt = $pdo->prepare("
        SELECT st.BasePrice
        FROM Seat se
        JOIN SeatType st ON st.SeatTypeID = se.SeatTypeID
        WHERE se.SeatNumber = :seat_number
    ");
    $insertStmt = $pdo->prepare("
        INSERT INTO Ticket (ShowtimeID, SeatNumber, CustomerID, Discount, Price, Status)
        VALUES (:showtime_id, :seat_number, :customer_id, :discount, :price, 'ACTIVE')
    ");

    foreach ($seatNumbers as $seatNumber) {
        if ($useProc) {
            try {
                $procStmt->execute([
                    ':showtime_id'   => $showtimeId,
                    ':seat_id'       => $seatNumber,
                    ':customer_id'   => $customerId,
                    ':discount_code' => $discountCode,
                ]);
                while ($procStmt->nextRowset()) {}
                continue;
            } catch (Throwable $e) {
                // Fallback to plain insert if stored procedure not available.
                $useProc = false;
            }
        }

        // Fallback manual pricing (mirror proc discounts)
        $priceStmt->execute([':seat_number' => $seatNumber]);
        $basePrice = $priceStmt->fetchColumn();
        $discount = 0.0;
        if ($discountCode) {
            $dc = strtoupper($discountCode);
            if ($dc === 'STUDENT') {
                $discount = 0.20;
            } elseif ($dc === 'SENIOR') {
                $discount = 0.15;
            } elseif ($dc === 'EMPLOYEE') {
                $discount = 1.00;
            }
        }
        $finalPrice = $basePrice !== false ? round($basePrice * (1 - $discount), 2) : 0;

        $insertStmt->execute([
            ':showtime_id' => $showtimeId,
            ':seat_number' => $seatNumber,
            ':customer_id' => $customerId,
            ':discount'    => $discount,
            ':price'       => $finalPrice,
        ]);
    }

    $pdo->commit();
    flash('success', 'Tickets purchased! A confirmation has been recorded.');
    redirect('/my_tickets.php?email=' . urlencode($customerEmail));
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    flash('error', 'Could not complete purchase: ' . $e->getMessage());
    redirect("/seats.php?showtime_id={$showtimeId}");
}
