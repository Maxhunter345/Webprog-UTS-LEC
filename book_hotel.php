<?php
session_start();
include 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $hotel_id = $_POST['hotel_id'];
    $checkin = $_POST['checkin'];
    $checkout = $_POST['checkout'];
    $user_id = $_SESSION['user_id'];

    // Hitung total harga hotel berdasarkan tanggal
    $stmt = $pdo->prepare("SELECT price_per_night FROM hotels WHERE id = ?");
    $stmt->execute([$hotel_id]);
    $hotel = $stmt->fetch();

    $checkin_date = new DateTime($checkin);
    $checkout_date = new DateTime($checkout);
    $interval = $checkin_date->diff($checkout_date);
    $nights = $interval->days;
    $total_price = $nights * $hotel['price_per_night'];

    // Simpan pemesanan hotel
    $stmt = $pdo->prepare("INSERT INTO hotel_booking (user_id, hotel_id, checkin_date, checkout_date, total_price) VALUES (?, ?, ?, ?, ?)");
    if ($stmt->execute([$user_id, $hotel_id, $checkin, $checkout, $total_price])) {
        header("Location: confirmation.php");
        exit();
    } else {
        echo "Error booking hotel!";
    }
}
?>
