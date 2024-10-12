<?php
// create_event.php
session_start();
include 'includes/db.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: login.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $location = $_POST['location'];
    $max_participants = $_POST['max_participants'];
    $status = 'open'; // Default status adalah open

    // Upload gambar
    $image = $_FILES['image']['name'];
    $target_dir = "images/";
    $target_file = $target_dir . basename($image);
    move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);

    // Simpan event ke database
    $stmt = $pdo->prepare("INSERT INTO events (name, description, date, time, location, max_participants, image, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$name, $description, $date, $time, $location, $max_participants, $image, $status])) {
        header("Location: dashboard.php");
        exit();
    } else {
        echo "Error creating event!";
    }
}
?>
