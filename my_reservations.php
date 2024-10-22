<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success_message = $error_message = '';

// Handle registration cancellation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cancel_registration'])) {
    $event_id = (int)$_POST['event_id'];
    
    try {
        $pdo->beginTransaction();
        
        // Delete registration
        $stmt = $pdo->prepare("DELETE FROM registrations WHERE user_id = ? AND event_id = ?");
        $stmt->execute([$user_id, $event_id]);
        
        $pdo->commit();
        $success_message = "Registration cancelled successfully!";
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error_message = "Error cancelling registration: " . $e->getMessage();
    }
}

// Fetch user's registered events
$stmt = $pdo->prepare("
    SELECT e.*, r.id as registration_id
    FROM events e
    INNER JOIN registrations r ON e.id = r.event_id
    WHERE r.user_id = ?
    ORDER BY e.date_time
");
$stmt->execute([$user_id]);
$registered_events = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Reservations - Division Defence Expo 2024</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="container">
        <h2>My Reservations</h2>
        
        <?php if ($success_message): ?>
            <p class="success"><?php echo $success_message; ?></p>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <p class="error"><?php echo $error_message; ?></p>
        <?php endif; ?>

        <?php if (empty($registered_events)): ?>
            <p>You haven't registered for any events yet.</p>
        <?php else: ?>
            <?php foreach ($registered_events as $event): ?>
                <div class="event">
                    <h4><?php echo htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8'); ?></h4>
                    <p>Date & Time: <?php echo $event['date_time']; ?></p>
                    <p>Country: <?php echo htmlspecialchars($event['country'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <p>Location: <?php echo htmlspecialchars($event['location'], ENT_QUOTES, 'UTF-8'); ?></p>
                    
                    <form method="post" action="">
                        <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                        <button type="submit" name="cancel_registration" onclick="return confirm('Are you sure you want to cancel this registration?')">Cancel Registration</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
