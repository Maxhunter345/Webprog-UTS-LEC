<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<link rel="stylesheet" href="styles.css">
<!-- Pastikan Font Awesome Sudah Termuat -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<nav class="navbar">
    <div class="navbar-logo">
        <span>Division Defence Expo 2024 - Admin</span>
    </div>
    <!-- Hamburger Menu untuk Mobile -->
    <button class="navbar-toggler" id="navbarToggler" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"><i class="fas fa-bars"></i></span>
    </button>
    <div class="navbar-links" id="navbarLinks">
        <!-- Tombol untuk Mode Gelap -->
        <button id="toggleDarkModeAdmin" class="theme-toggle" aria-label="Toggle Dark Mode">
            <i id="theme-icon-admin" class="fas fa-moon"></i>
        </button>
        <a href="admin.php" class="<?php echo ($current_page == 'admin.php') ? 'active' : ''; ?>">Events</a>
        <a href="registrants.php" class="<?php echo ($current_page == 'registrants.php') ? 'active' : ''; ?>">Registrants</a>
        <a href="userslist.php" class="<?php echo ($current_page == 'userslist.php') ? 'active' : ''; ?>">Users</a>
        <a href="login.php?action=logout">Logout</a>
    </div>
</nav>

<!-- Tombol Scroll to Top (Opsional) -->
<button id="scrollTopBtn" title="Go to top"><i class="fas fa-arrow-up"></i></button>

<!-- Script JavaScript untuk Dark Mode dan Mobile Menu -->
<script>
    // Fungsi Toggle Tema
    function toggleThemeAdmin() {
        const isDarkMode = document.documentElement.classList.toggle('dark-mode');
        localStorage.setItem('theme', isDarkMode ? 'dark' : 'light');
        updateThemeIconAdmin(isDarkMode);
    }

    // Fungsi untuk Mengupdate Ikon Tema
    function updateThemeIconAdmin(isDarkMode) {
        const themeIcon = document.getElementById('theme-icon-admin');
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
        updateThemeIconAdmin(isDarkMode);
    });

    // Event Listener untuk Tombol Dark Mode
    document.getElementById('toggleDarkModeAdmin').addEventListener('click', toggleThemeAdmin);

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
