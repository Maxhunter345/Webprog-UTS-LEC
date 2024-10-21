<?php
session_start();
include 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $event_location_id = $_POST['event_location_id'];
    $quantity = $_POST['quantity'];
    $user_id = $_SESSION['user_id'];

    // Hitung total harga tiket
    $ticket_price = 50; // Misalnya harga tiket $50
    $total_price = $quantity * $ticket_price;

    // Simpan ke database
    $stmt = $pdo->prepare("INSERT INTO tickets (user_id, event_location_id, quantity, total_price) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$user_id, $event_location_id, $quantity, $total_price])) {
        header("Location: confirmation.php");
        exit();
    } else {
        echo "Error booking tickets!";
    }
}
?>
