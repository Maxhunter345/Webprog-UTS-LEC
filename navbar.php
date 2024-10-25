<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<link rel="stylesheet" href="styles.css">
<!-- Pastikan Font Awesome Sudah Termuat -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<nav class="navbar">
    <div class="navbar-logo">
        <img src="images/logo.png" alt="Division Defence Expo 2024 Logo">
    </div>
    <!-- Hamburger Menu untuk Mobile -->
    <button class="navbar-toggler" id="navbarToggler" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"><i class="fas fa-bars"></i></span>
    </button>
    <div class="navbar-links" id="navbarLinks">
        <!-- Tombol untuk Mode Gelap -->
        <button id="toggleDarkModeUser" class="theme-toggle" aria-label="Toggle Dark Mode">
            <i id="theme-icon-user" class="fas fa-moon"></i>
        </button>
        <a href="user.php" class="<?php echo ($current_page == 'user.php') ? 'active' : ''; ?>">Home</a>
        <a href="my_reservations.php" class="<?php echo ($current_page == 'my_reservations.php') ? 'active' : ''; ?>">My Reservations</a>
        <a href="about.php" class="<?php echo ($current_page == 'about.php') ? 'active' : ''; ?>">About Us</a>
        <div class="profile-dropdown">
            <a href="profile.php" class="<?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>">My Profile</a>
            <div class="profile-dropdown-content">
                <a href="profile.php">View Profile</a>
                <a href="profile.php?edit=true">Edit Profile</a>
                <a href="login.php?action=logout">Logout</a>
            </div>
        </div>
    </div>
</nav>

<!-- Tombol Scroll to Top (Opsional) -->
<button id="scrollTopBtn" title="Go to top"><i class="fas fa-arrow-up"></i></button>

<!-- Script JavaScript untuk Dark Mode dan Mobile Menu -->
<script>
    // Fungsi Toggle Tema
    function toggleThemeUser() {
        const isDarkMode = document.documentElement.classList.toggle('dark-mode');
        localStorage.setItem('theme', isDarkMode ? 'dark' : 'light');
        updateThemeIconUser(isDarkMode);
    }

    // Fungsi untuk Mengupdate Ikon Tema
    function updateThemeIconUser(isDarkMode) {
        const themeIcon = document.getElementById('theme-icon-user');
        if (themeIcon) {
            themeIcon.className = isDarkMode ? 'fas fa-sun' : 'fas fa-moon';
        }
    }

    // Inisialisasi Tema dan Ikon saat Halaman Dimuat
    document.addEventListener('DOMContentLoaded', function() {
        const isDarkMode = localStorage.getItem('theme') === 'dark';
        if (isDarkMode) {
            document.documentElement.classList.add('dark-mode');
        }
        updateThemeIconUser(isDarkMode);
    });

    // Event Listener untuk Tombol Dark Mode
    document.getElementById('toggleDarkModeUser').addEventListener('click', toggleThemeUser);

    // Script untuk Hamburger Menu
    const navbarToggler = document.getElementById('navbarToggler');
    const navbarLinks = document.getElementById('navbarLinks');

    navbarToggler.addEventListener('click', function() {
        navbarLinks.classList.toggle('active');
    });

    // Script untuk Tombol Scroll to Top (Opsional)
    window.onscroll = function() {scrollFunction()};

    function scrollFunction() {
        const scrollTopBtn = document.getElementById("scrollTopBtn");
        if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
            scrollTopBtn.style.display = "block";
        } else {
            scrollTopBtn.style.display = "none";
        }
    }

    // Smooth scroll to top
    document.getElementById('scrollTopBtn').addEventListener('click', function() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
</script>
