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
    <script>
        // Inisialisasi tema secepat mungkin
        (function() {
            const isDarkMode = localStorage.getItem('theme') === 'dark';
            if (isDarkMode) {
                document.documentElement.classList.add('dark-mode');
            }
        })();
    </script>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <!-- Header dengan Efek Parallax -->
    <div class="header">
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
            <div class="about-section">
    <h3>Our Team</h3>
    <div class="team-container">
        <div class="team-card">
            <div class="team-image">
                <img src= "images/1.jpg" alt="Alif Faiz">
            </div>
            <h4>Alif Nurfaiz Widyatmoko</h4>
        </div>

        <div class="team-card">
            <div class="team-image">
                <img src="images/2.png" alt="Max">
            </div>
            <h4>Maxell Nathanael</h4>
        </div>

        <div class="team-card">
            <div class="team-image">
                <img src="images/3.png" alt="Alfin">
            </div>
            <h4>Alfin Sanders</h4>
        </div>

        <div class="team-card">
            <div class="team-image">
                <img src="images/4.png" alt="Kevan">
            </div>
            <h4>Eugenius Kevan Kusuma</h4>
            </div>
        </div>
    </div>
        <div>
            <a href="user.php" class="back-btn">Kembali ke Main Page</a>
    </div>
        <!-- Scroll to Top Button -->
        <button id="scrollTopBtn" title="Kembali ke atas">
            <i class="fa-solid fa-arrow-up" style="color: #74C0FC;"></i>
        </button>
    </div>

    <!-- Script JavaScript -->
<script>
    // Show/Hide Scroll to Top Button based on scroll position
    window.onscroll = function() {
            const scrollTopBtn = document.getElementById("scrollTopBtn");
            scrollTopBtn.style.display = (document.documentElement.scrollTop > 20) ? "block" : "none";
        };

        // Smooth scroll to top
        document.getElementById('scrollTopBtn').addEventListener('click', function() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        // Fungsi untuk Mengupdate Ikon Tema
        function updateThemeIcon(isDarkMode) {
        const themeIcon = document.getElementById('theme-icon');
        if (themeIcon) {
            themeIcon.className = isDarkMode ? 'fas fa-sun' : 'fas fa-moon';
        }
        }

        // Inisialisasi Tema dan Ikon pada Saat Halaman Dimuat
        document.addEventListener('DOMContentLoaded', function() {
            const isDarkMode = localStorage.getItem('theme') === 'dark';
            if (isDarkMode) {
            document.documentElement.classList.add('dark-mode');
        }
            updateThemeIcon(isDarkMode);
        });
    </script>
</body>
</html>