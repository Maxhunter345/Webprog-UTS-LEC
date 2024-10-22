<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: login.php");
    exit();
}

// Get all events for the filter dropdown
$events_query = $pdo->query("SELECT id, title FROM events ORDER BY date_time");
$events = $events_query->fetchAll();

// Handle event filter
$event_filter = isset($_GET['event_id']) ? (int)$_GET['event_id'] : null;

// Updated query to include country
$query = "
    SELECT 
        u.first_name,
        u.last_name,
        u.email,
        u.phone_number,
        u.country,
        e.title as event_title,
        r.registration_date
    FROM registrations r
    JOIN users u ON r.user_id = u.id
    JOIN events e ON r.event_id = e.id
";

// Add filter if event is selected
if ($event_filter) {
    $query .= " WHERE e.id = :event_id";
}

$query .= " ORDER BY r.registration_date DESC";

try {
    $stmt = $pdo->prepare($query);
    if ($event_filter) {
        $stmt->bindParam(':event_id', $event_filter);
    }
    $stmt->execute();
    $registrants = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_message = "Error fetching registrants: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrants - Division Defence Expo 2024</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'navbar-admin.php'; ?>
    
    <div class="container">
        <h2>Event Registrants</h2>
        
        <!-- Event Filter Form -->
        <form method="get" action="" class="filter-form">
            <select name="event_id" onchange="this.form.submit()">
                <option value="">All Events</option>
                <?php foreach ($events as $event): ?>
                    <option value="<?php echo $event['id']; ?>" <?php echo $event_filter == $event['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($event['title']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <?php if (empty($registrants)): ?>
            <p>No registrants found.</p>
        <?php else: ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Country</th>
                            <th>Event</th>
                            <th>Registration Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($registrants as $registrant): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($registrant['first_name'] . ' ' . $registrant['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($registrant['email']); ?></td>
                                <td><?php echo htmlspecialchars($registrant['phone_number']); ?></td>
                                <td><?php echo htmlspecialchars($registrant['country']); ?></td>
                                <td><?php echo htmlspecialchars($registrant['event_title']); ?></td>
                                <td><?php echo date('Y-m-d H:i', strtotime($registrant['registration_date'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>