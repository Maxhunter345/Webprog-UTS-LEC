<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: login.php");
    exit();
}

$success_message = $error_message = '';

// Handle company creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_company'])) {
    $company_name = htmlspecialchars($_POST['company_name'], ENT_QUOTES, 'UTF-8');
    
    // Handle file upload
    if (isset($_FILES['company_logo']) && $_FILES['company_logo']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['company_logo']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        if (in_array(strtolower($filetype), $allowed)) {
            // Create logo directory if it doesn't exist
            if (!file_exists('logo')) {
                mkdir('logo', 0777, true);
            }
            $new_filename = uniqid() . '.' . $filetype;
            $upload_path = 'logo/' . $new_filename;
            if (move_uploaded_file($_FILES['company_logo']['tmp_name'], $upload_path)) {
                $sql = "INSERT INTO companies (name, logo_path) VALUES (?, ?)";
                try {
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$company_name, $upload_path]);
                    $success_message = "Company created successfully!";
                } catch (PDOException $e) {
                    $error_message = "Error creating company: " . $e->getMessage();
                }
            } else {
                $error_message = "Failed to upload file.";
            }
        } else {
            $error_message = "Invalid file type. Allowed types: " . implode(', ', $allowed);
        }
    } else {
        $error_message = "No file uploaded or an error occurred.";
    }
}

// Handle event creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_event'])) {
    $title = htmlspecialchars($_POST['title'], ENT_QUOTES, 'UTF-8');
    $description = htmlspecialchars($_POST['description'], ENT_QUOTES, 'UTF-8');
    $date_time = $_POST['date_time'];
    $country = htmlspecialchars($_POST['country'], ENT_QUOTES, 'UTF-8');
    $location = htmlspecialchars($_POST['location'], ENT_QUOTES, 'UTF-8');
    $max_visitors = (int)$_POST['max_visitors'];
    $featured_companies = isset($_POST['featured_companies']) ? $_POST['featured_companies'] : [];

    $sql = "INSERT INTO events (title, description, date_time, country, location, max_visitors) 
            VALUES (?, ?, ?, ?, ?, ?)";
    
    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$title, $description, $date_time, $country, $location, $max_visitors]);
        $event_id = $pdo->lastInsertId();
        
        foreach ($featured_companies as $company_id) {
            $sql = "INSERT INTO event_companies (event_id, company_id) VALUES (?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$event_id, $company_id]);
        }
        
        $pdo->commit();
        $success_message = "Event created successfully!";
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error_message = "Error creating event: " . $e->getMessage();
    }
}

// Handle event editing
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_event'])) {
    $event_id = (int)$_POST['event_id'];
    $title = htmlspecialchars($_POST['title'], ENT_QUOTES, 'UTF-8');
    $description = htmlspecialchars($_POST['description'], ENT_QUOTES, 'UTF-8');
    $date_time = $_POST['date_time'];
    $country = htmlspecialchars($_POST['country'], ENT_QUOTES, 'UTF-8');
    $location = htmlspecialchars($_POST['location'], ENT_QUOTES, 'UTF-8');
    $max_visitors = (int)$_POST['max_visitors'];
    $featured_companies = isset($_POST['featured_companies']) ? $_POST['featured_companies'] : [];

    $sql = "UPDATE events SET title = ?, description = ?, date_time = ?, country = ?, location = ?, max_visitors = ? WHERE id = ?";
    
    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$title, $description, $date_time, $country, $location, $max_visitors, $event_id]);
        
        // Delete existing featured companies for this event
        $stmt = $pdo->prepare("DELETE FROM event_companies WHERE event_id = ?");
        $stmt->execute([$event_id]);
        
        // Insert new featured companies
        foreach ($featured_companies as $company_id) {
            $sql = "INSERT INTO event_companies (event_id, company_id) VALUES (?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$event_id, $company_id]);
        }
        
        $pdo->commit();
        $success_message = "Event updated successfully!";
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error_message = "Error updating event: " . $e->getMessage();
    }
}

// Handle event deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_event'])) {
    $event_id = (int)$_POST['event_id'];

    try {
        $pdo->beginTransaction();
        
        // Delete featured companies for this event
        $stmt = $pdo->prepare("DELETE FROM event_companies WHERE event_id = ?");
        $stmt->execute([$event_id]);
        
        // Delete registrations for this event
        $stmt = $pdo->prepare("DELETE FROM registrations WHERE event_id = ?");
        $stmt->execute([$event_id]);
        
        // Delete the event
        $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
        $stmt->execute([$event_id]);
        
        $pdo->commit();
        $success_message = "Event deleted successfully!";
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error_message = "Error deleting event: " . $e->getMessage();
    }
}

// Fetch all companies
$stmt = $pdo->query("SELECT * FROM companies ORDER BY name");
$companies = $stmt->fetchAll();

// Fetch all events with their featured companies
$stmt = $pdo->query("
    SELECT e.*, GROUP_CONCAT(c.name SEPARATOR ', ') as featured_companies
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
    <title>Admin Dashboard - Division Defence Expo 2024</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Division Defence Expo 2024</h1>
        <h2>Admin Dashboard</h2>
        
        <?php if ($success_message): ?>
            <p class="success"><?php echo $success_message; ?></p>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <p class="error"><?php echo $error_message; ?></p>
        <?php endif; ?>

        <h3>Create New Company</h3>
        <form method="post" action="" enctype="multipart/form-data">
            <input type="text" name="company_name" placeholder="Company Name" required>
            <input type="file" name="company_logo" required>
            <button type="submit" name="create_company">Create Company</button>
        </form>

        <h3>Create New Event</h3>
        <form method="post" action="">
            <input type="text" name="title" placeholder="Event Title" required>
            <textarea name="description" placeholder="Event Description" required></textarea>
            <input type="datetime-local" name="date_time" required>
            <input type="text" name="country" placeholder="Country" required>
            <input type="text" name="location" placeholder="Location" required>
            <input type="number" name="max_visitors" placeholder="Max Visitors" required>
            <select name="featured_companies[]" multiple>
                <?php foreach ($companies as $company): ?>
                    <option value="<?php echo $company['id']; ?>"><?php echo htmlspecialchars($company['name'], ENT_QUOTES, 'UTF-8'); ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" name="create_event">Create Event</button>
        </form>

        <h3>Manage Events</h3>
        <?php foreach ($events as $event): ?>
            <div class="event">
                <h4><?php echo htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8'); ?></h4>
                <p>Date & Time: <?php echo $event['date_time']; ?></p>
                <p>Country: <?php echo htmlspecialchars($event['country'], ENT_QUOTES, 'UTF-8'); ?></p>
                <p>Location: <?php echo htmlspecialchars($event['location'], ENT_QUOTES, 'UTF-8'); ?></p>
                <p>Max Visitors: <?php echo $event['max_visitors']; ?></p>
                <p>Featured Companies: <?php echo htmlspecialchars($event['featured_companies'], ENT_QUOTES, 'UTF-8'); ?></p>
                
                <button onclick="showEditForm(<?php echo $event['id']; ?>)">Edit</button>
                
                <form method="post" action="" style="display: inline;">
                    <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                    <button type="submit" name="delete_event" onclick="return confirm('Are you sure you want to delete this event?')">Delete</button>
                </form>
                
                <div id="edit-form-<?php echo $event['id']; ?>" style="display: none;">
                    <form method="post" action="">
                        <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                        <input type="text" name="title" value="<?php echo htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8'); ?>" required>
                        <textarea name="description" required><?php echo htmlspecialchars($event['description'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                        <input type="datetime-local" name="date_time" value="<?php echo date('Y-m-d\TH:i', strtotime($event['date_time'])); ?>" required>
                        <input type="text" name="country" value="<?php echo htmlspecialchars($event['country'], ENT_QUOTES, 'UTF-8'); ?>" required>
                        <input type="text" name="location" value="<?php echo htmlspecialchars($event['location'], ENT_QUOTES, 'UTF-8'); ?>" required>
                        <input type="number" name="max_visitors" value="<?php echo $event['max_visitors']; ?>" required>
                        <select name="featured_companies[]" multiple>
                            <?php foreach ($companies as $company): ?>
                                <option value="<?php echo $company['id']; ?>" <?php echo (strpos($event['featured_companies'], $company['name']) !== false) ? 'selected' : ''; ?>><?php echo htmlspecialchars($company['name'], ENT_QUOTES, 'UTF-8'); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" name="edit_event">Update Event</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>

        <a href="logout.php" class="btn">Logout</a>
    </div>

    <script>
        function showEditForm(eventId) {
            var form = document.getElementById('edit-form-' + eventId);
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }
    </script>
</body>
</html>