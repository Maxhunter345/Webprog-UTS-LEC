<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Division Defence Expo 2024</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="container">
        <h2>About Division Defence Expo 2024</h2>
        
        <div class="about-section">
            <h3>Who We Are</h3>
            <p>Division Defence Expo is the premier international exhibition for defense technology and innovation. Our annual expo brings together industry leaders, military officials, and defense professionals from around the world.</p>
            
            <h3>Our Mission</h3>
            <p>Our mission is to facilitate collaboration and innovation in the defense sector by providing a platform for networking, knowledge sharing, and business development.</p>
            
            <h3>What We Offer</h3>
            <ul>
                <li>Latest defense technology exhibitions</li>
                <li>Networking opportunities with industry leaders</li>
                <li>Technical seminars and workshops</li>
                <li>Live demonstrations of defense equipment</li>
                <li>Business-to-business meetings</li>
            </ul>
            
            <h3>Contact Information</h3>
            <p>Email: info@divisionexpo.com</p>
            <p>Phone: +1 (555) 123-4567</p>
            <p>Address: 123 Defense Boulevard, Military District, DC 12345</p>
        </div>
    </div>
</body>
</html>