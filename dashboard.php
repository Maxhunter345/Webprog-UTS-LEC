<?php
// dashboard.php
session_start();
include 'includes/db.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: login.html");
    exit();
}

// Ambil daftar event
$stmt = $pdo->prepare("SELECT * FROM events");
$stmt->execute();
$events = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>Admin Dashboard</h1>
        <nav>
            <a href="create_event.php">Create New Event</a>
        </nav>
    </header>

    <main>
        <div class="dashboard-events">
            <h2>Manage Events</h2>
            <table>
                <thead>
                    <tr>
                        <th>Event Name</th>
                        <th>Date</th>
                        <th>Registrants</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $event): ?>
                    <tr>
                        <td><?= htmlspecialchars($event['name']) ?></td>
                        <td><?= htmlspecialchars($event['date']) ?></td>
                        <td>
                            <?php
                            // Hitung jumlah registrasi
                            $stmt = $pdo->prepare("SELECT COUNT(*) FROM registrations WHERE event_id = ?");
                            $stmt->execute([$event['id']]);
                            echo $stmt->fetchColumn();
                            ?>
                        </td>
                        <td>
                            <a href="edit_event.php?id=<?= $event['id'] ?>">Edit</a> | 
                            <a href="delete_event.php?id=<?= $event['id'] ?>" class="delete-btn">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <footer>
        <p>© 2024 Event Registration System</p>
    </footer>
</body>
</html>
