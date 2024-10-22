<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success_message = $error_message = '';

// Fetch user profile information
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Handle profile submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $first_name = htmlspecialchars($_POST['first_name'], ENT_QUOTES, 'UTF-8');
    $last_name = htmlspecialchars($_POST['last_name'], ENT_QUOTES, 'UTF-8');
    $phone_number = htmlspecialchars($_POST['phone_number'], ENT_QUOTES, 'UTF-8');
    $country = htmlspecialchars($_POST['country'], ENT_QUOTES, 'UTF-8');
    
    try {
        $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, phone_number = ?, country = ?, profile_completed = TRUE WHERE id = ?");
        $stmt->execute([$first_name, $last_name, $phone_number, $country, $user_id]);
        $success_message = "Profile updated successfully!";
        $user['profile_completed'] = true;
        $user['first_name'] = $first_name;
        $user['last_name'] = $last_name;
        $user['phone_number'] = $phone_number;
        $user['country'] = $country;
    } catch (PDOException $e) {
        $error_message = "Error updating profile: " . $e->getMessage();
    }
}

// Handle event registration
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register_event'])) {
    if (!$user['profile_completed']) {
        $error_message = "Please complete your profile before registering for events.";
    } else {
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
                    $pdo->beginTransaction();
                    
                    // Register user for the event
                    $stmt = $pdo->prepare("INSERT INTO registrations (user_id, event_id) VALUES (?, ?)");
                    $stmt->execute([$user_id, $event_id]);
                    $registration_id = $pdo->lastInsertId();
                    
                    $pdo->commit();
                    $success_message = "You have successfully registered for the event!";
                }
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error_message = "Error registering for event: " . $e->getMessage();
        }
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
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Division Defence Expo 2024</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container">
        <h1>Division Defence Expo 2024</h1>
        <h2>User Dashboard</h2>
        
        <?php if ($success_message): ?>
            <p class="success"><?php echo $success_message; ?></p>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <p class="error"><?php echo $error_message; ?></p>
        <?php endif; ?>

        <?php if (!$user['profile_completed']): ?>
            <div class="profile-form">
                <h3>Complete Your Profile</h3>
                <p>Please complete your profile before registering for events.</p>
                <form method="post" action="">
                    <input type="text" name="first_name" placeholder="First Name" required>
                    <input type="text" name="last_name" placeholder="Last Name" required>
                    <input type="tel" name="phone_number" placeholder="Phone Number" required>
                    <input type="text" name="country" placeholder="Country" required>
                    <button type="submit" name="update_profile">Save Profile</button>
                </form>
            </div>
        <?php else: ?>
            <div class="profile-section">
                <h3>My Profile</h3>
                <p>Name: <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p>
                <p>Email: <?php echo htmlspecialchars($user['email']); ?></p>
                <p>Phone: <?php echo htmlspecialchars($user['phone_number']); ?></p>
                <p>Country: <?php echo htmlspecialchars($user['country']); ?></p>
                <button onclick="showEditProfile()">Edit Profile</button>
                
                <div id="edit-profile-form" style="display: none;">
                    <form method="post" action="">
                        <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                        <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                        <input type="tel" name="phone_number" value="<?php echo htmlspecialchars($user['phone_number']); ?>" required>
                        <input type="text" name="country" value="<?php echo htmlspecialchars($user['country']); ?>" required>
                        <button type="submit" name="update_profile">Update Profile</button>
                    </form>
                </div>
            </div>

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
        <?php endif; ?>
        <a href="login.php?action=logout" class="btn">Logout</a>
    </div>
    <script>
        function showEditProfile() {
            var form = document.getElementById('edit-profile-form');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }
    </script>
</body>
</html>
