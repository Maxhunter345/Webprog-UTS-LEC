<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success_message = $error_message = '';

// Handle event registration
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register_event'])) {
    $event_id = (int)$_POST['event_id'];
    
    try {
        // Check if user is already registered
        $stmt = $pdo->prepare("SELECT * FROM registrations WHERE user_id = ? AND event_id = ?");
        $stmt->execute([$user_id, $event_id]);
        if ($stmt->rowCount() > 0) {
            $error_message = "You are already registered for this event.";
        } else {
            // Check if event has reached max visitors
            $stmt = $pdo->prepare("SELECT max_visitors, (SELECT COUNT(*) FROM registrations WHERE event_id = events.id) as current_visitors FROM events WHERE id = ?");
            $stmt->execute([$event_id]);
            $event = $stmt->fetch();
            
            if ($event['current_visitors'] >= $event['max_visitors']) {
                $error_message = "This event has reached its maximum number of visitors.";
            } else {
                // Register user for the event
                $stmt = $pdo->prepare("INSERT INTO registrations (user_id, event_id) VALUES (?, ?)");
                $stmt->execute([$user_id, $event_id]);
                $success_message = "You have successfully registered for the event!";
            }
        }
    } catch (PDOException $e) {
        $error_message = "Error registering for event: " . $e->getMessage();
    }
}

// Fetch all events with current visitor count and featured companies
$stmt = $pdo->query("
    SELECT e.*, 
           (SELECT COUNT(*) FROM registrations WHERE event_id = e.id) as current_visitors,
           (SELECT COUNT(*) FROM registrations WHERE event_id = e.id AND user_id = {$user_id}) as user_registered,
           GROUP_CONCAT(CONCAT(c.name, ':', c.logo_path) SEPARATOR '|') as featured_companies
    FROM events e
    LEFT JOIN event_companies ec ON e.id = ec.event_id
    LEFT JOIN companies c ON ec.company_id = c.id
    GROUP BY e.id
    ORDER BY e.date_time
");
$events = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Division Defence Expo 2024</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Division Defence Expo 2024</h1>
        <h2>User Dashboard</h2>
        
        <?php if ($success_message): ?>
            <p class="success"><?php echo $success_message; ?></p>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <p class="error"><?php echo $error_message; ?></p>
        <?php endif; ?>

        <h3>Upcoming Events</h3>
        <?php foreach ($events as $event): ?>
            <div class="event">
                <h4><?php echo htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8'); ?></h4>
                <p>Date & Time: <?php echo $event['date_time']; ?></p>
                <p>Country: <?php echo htmlspecialchars($event['country'], ENT_QUOTES, 'UTF-8'); ?></p>
                <p>Location: <?php echo htmlspecialchars($event['location'], ENT_QUOTES, 'UTF-8'); ?></p>
                <p>Description: <?php echo htmlspecialchars($event['description'], ENT_QUOTES, 'UTF-8'); ?></p>
                <p>Visitors: <?php echo $event['current_visitors']; ?> / <?php echo $event['max_visitors']; ?></p>
                
                <h5>Featured Companies:</h5>
                <div class="company-logos">
                    <?php
                    $companies = explode('|', $event['featured_companies']);
                    foreach ($companies as $company):
                        list($name, $logo_path) = explode(':', $company);
                    ?>
                        <div class="company">
                            <img src="<?php echo htmlspecialchars($logo_path, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?> logo">
                            <span><?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if ($event['user_registered']): ?>
                    <p class="registered">You are registered for this event</p>
                <?php elseif ($event['current_visitors'] < $event['max_visitors']): ?>
                    <form method="post" action="">
                        <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                        <button type="submit" name="register_event">Register</button>
                    </form>
                <?php else: ?>
                    <p class="full">This event is full</p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <a href="login.php?action=logout" class="btn">Logout</a>
    </div>
</body>
</html>