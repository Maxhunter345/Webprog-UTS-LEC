<?php
session_start();
include 'includes/db.php';

$event_id = $_GET['id'];

// Ambil data event
$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch();

// Ambil data lokasi event
$stmt = $pdo->prepare("SELECT * FROM event_locations WHERE event_id = ?");
$stmt->execute([$event_id]);
$locations = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($event['name']) ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1><?= htmlspecialchars($event['name']) ?></h1>
    </header>

    <main>
        <section id="event-info">
            <h2>About Event</h2>
            <p><?= htmlspecialchars($event['description']) ?></p>
        </section>

        <section id="event-locations">
            <h2>Locations</h2>

            <?php foreach ($locations as $location): ?>
                <div class="location">
                    <h3><?= htmlspecialchars($location['location_name']) ?>, <?= htmlspecialchars($location['country']) ?></h3>

                    <!-- Tampilkan tanggal event -->
                    <?php
                    $stmt = $pdo->prepare("SELECT * FROM event_dates WHERE event_location_id = ?");
                    $stmt->execute([$location['id']]);
                    $event_dates = $stmt->fetchAll();
                    ?>

                    <p>
                        <?php foreach ($event_dates as $date): ?>
                            From <?= htmlspecialchars($date['start_date']) ?> to <?= htmlspecialchars($date['end_date']) ?><br>
                        <?php endforeach; ?>
                    </p>

                    <!-- Tampilkan perusahaan yang berpartisipasi -->
                    <?php
                    $stmt = $pdo->prepare("SELECT * FROM event_companies WHERE event_location_id = ?");
                    $stmt->execute([$location['id']]);
                    $companies = $stmt->fetchAll();
                    ?>
                    <h4>Participating Companies:</h4>
                    <ul>
                        <?php foreach ($companies as $company): ?>
                            <li><?= htmlspecialchars($company['company_name']) ?></li>
                        <?php endforeach; ?>
                    </ul>

                    <!-- Form Pemesanan Tiket -->
                    <h4>Book Tickets</h4>
                    <form action="book_ticket.php" method="POST">
                        <input type="hidden" name="event_location_id" value="<?= $location['id'] ?>">
                        <label for="quantity">Number of Tickets:</label>
                        <input type="number" name="quantity" min="1" required>
                        <button type="submit">Book Now</button>
                    </form>

                    <!-- Tampilkan hotel -->
                    <?php
                    $stmt = $pdo->prepare("SELECT * FROM hotels WHERE event_location_id = ?");
                    $stmt->execute([$location['id']]);
                    $hotels = $stmt->fetchAll();
                    ?>
                    <h4>Available Hotels</h4>
                    <ul>
                        <?php foreach ($hotels as $hotel): ?>
                            <li>
                                <?= htmlspecialchars($hotel['hotel_name']) ?> - $<?= htmlspecialchars($hotel['price_per_night']) ?> per night
                                <!-- Form Pemesanan Hotel -->
                                <form action="book_hotel.php" method="POST">
                                    <input type="hidden" name="hotel_id" value="<?= $hotel['id'] ?>">
                                    <label for="checkin">Check-in:</label>
                                    <input type="date" name="checkin" required>
                                    <label for="checkout">Check-out:</label>
                                    <input type="date" name="checkout" required>
                                    <button type="submit">Book Hotel</button>
                                </form>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        </section>
    </main>

    <footer>
        <p>© 2024 Event Registration System</p>
    </footer>
</body>
</html>
