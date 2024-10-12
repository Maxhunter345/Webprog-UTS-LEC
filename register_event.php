<?php
// register_event.php
session_start();
include 'includes/db.php';

if ($_SESSION['role'] !== 'user') {
    header("Location: login.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['event_id']) && !empty($_POST['event_id'])) {
        $user_id = $_SESSION['user_id'];
        $event_id = $_POST['event_id'];

        // Cek apakah user sudah terdaftar di event ini
        $stmt = $pdo->prepare("SELECT * FROM registrations WHERE user_id = ? AND event_id = ?");
        $stmt->execute([$user_id, $event_id]);

        if ($stmt->rowCount() == 0) {
            // Tambah registrasi
            $stmt = $pdo->prepare("INSERT INTO registrations (user_id, event_id) VALUES (?, ?)");
            if ($stmt->execute([$user_id, $event_id])) {
                header("Location: index.html");
                exit();
            } else {
                echo "Error registering for the event!";
            }
        } else {
            echo "You have already registered for this event!";
        }
    } else {
        echo "Event ID is missing!";
    }
}
?>
