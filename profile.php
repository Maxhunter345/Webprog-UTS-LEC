<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success_message = $error_message = '';

// Fetch user profile
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_profile'])) {
        // Mengambil dan membersihkan input
        $first_name = htmlspecialchars(trim($_POST['first_name']), ENT_QUOTES, 'UTF-8');
        $last_name = htmlspecialchars(trim($_POST['last_name']), ENT_QUOTES, 'UTF-8');
        $email = htmlspecialchars(trim($_POST['email']), ENT_QUOTES, 'UTF-8');
        $phone_number = htmlspecialchars(trim($_POST['phone_number']), ENT_QUOTES, 'UTF-8');
        $country = htmlspecialchars(trim($_POST['country']), ENT_QUOTES, 'UTF-8');
        
        // Validasi email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = "Format email tidak valid.";
        } else {
            try {
                // Cek apakah email sudah digunakan oleh pengguna lain
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $stmt->execute([$email, $user_id]);
                if ($stmt->rowCount() > 0) {
                    $error_message = "Email sudah digunakan oleh pengguna lain.";
                } else {
                    // Update profil pengguna
                    $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, phone_number = ?, country = ? WHERE id = ?");
                    $stmt->execute([$first_name, $last_name, $email, $phone_number, $country, $user_id]);
                    $success_message = "Profil berhasil diperbarui!";
                    
                    // Update local user data
                    $user['first_name'] = $first_name;
                    $user['last_name'] = $last_name;
                    $user['email'] = $email;
                    $user['phone_number'] = $phone_number;
                    $user['country'] = $country;
                }
            } catch (PDOException $e) {
                $error_message = "Error memperbarui profil: " . $e->getMessage();
            }
        }
    }
    
    // Handle password update
    if (isset($_POST['update_password'])) {
        // Mengambil dan membersihkan input
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $recovery_question_1 = htmlspecialchars(trim($_POST['recovery_question_1']), ENT_QUOTES, 'UTF-8');
        $recovery_answer_1 = htmlspecialchars(trim($_POST['recovery_answer_1']), ENT_QUOTES, 'UTF-8');
        $recovery_question_2 = htmlspecialchars(trim($_POST['recovery_question_2']), ENT_QUOTES, 'UTF-8');
        $recovery_answer_2 = htmlspecialchars(trim($_POST['recovery_answer_2']), ENT_QUOTES, 'UTF-8');
        
        // Validasi input
        if (empty($current_password) || empty($new_password) || empty($recovery_question_1) || empty($recovery_answer_1) || empty($recovery_question_2) || empty($recovery_answer_2)) {
            $error_message = "Silakan isi semua bidang untuk mengganti password.";
        } else {
            // Verifikasi password saat ini
            if (!password_verify($current_password, $user['password'])) {
                $error_message = "Password saat ini salah.";
            } else {
                // Hash password baru
                $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
                
                try {
                    // Update password dan recovery questions
                    $stmt = $pdo->prepare("UPDATE users SET password = ?, recovery_question = ?, recovery_answer = ?, recovery_question_2 = ?, recovery_answer_2 = ? WHERE id = ?");
                    $stmt->execute([$hashed_new_password, $recovery_question_1, $recovery_answer_1, $recovery_question_2, $recovery_answer_2, $user_id]);
                    $success_message = "Password dan pertanyaan pemulihan berhasil diperbarui!";
                    
                    // Update local user data
                    $user['password'] = $hashed_new_password;
                    $user['recovery_question'] = $recovery_question_1;
                    $user['recovery_answer'] = $recovery_answer_1;
                    $user['recovery_question_2'] = $recovery_question_2;
                    $user['recovery_answer_2'] = $recovery_answer_2;
                } catch (PDOException $e) {
                    $error_message = "Error mengganti password: " . $e->getMessage();
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Division Defence Expo 2024</title>
    <link rel="stylesheet" href="styles.css">
    <!-- Font Awesome untuk Ikon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="container">
        <h2>My Profile</h2>
        
        <?php if ($success_message): ?>
            <p class="success"><?php echo $success_message; ?></p>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <p class="error"><?php echo $error_message; ?></p>
        <?php endif; ?>

        <?php if (isset($_GET['edit'])): ?>
            <form method="post" action="profile.php?edit=true">
                <h3>Edit Profile</h3>
                <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" placeholder="First Name" required>
                <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" placeholder="Last Name" required>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" placeholder="Email" required>
                <input type="tel" name="phone_number" value="<?php echo htmlspecialchars($user['phone_number']); ?>" placeholder="Phone Number" required>
                <input type="text" name="country" value="<?php echo htmlspecialchars($user['country']); ?>" placeholder="Country" required>
                <button type="submit" name="update_profile">Update Profile</button>
            </form>

            <h3>Change Password</h3>
            <form method="post" action="profile.php?edit=true">
                <input type="password" name="current_password" placeholder="Current Password" required>
                <input type="password" name="new_password" placeholder="New Password" required>
                
                <h4>Pertanyaan Pemulihan 1</h4>
                <label for="recovery_question_1">Pilih Pertanyaan Pemulihan 1:</label>
                <select name="recovery_question_1" id="recovery_question_1" required>
                    <option value="">-- Pilih Pertanyaan --</option>
                    <option value="Siapa nama gadis ibu Anda?" <?php if ($user['recovery_question'] == "Siapa nama gadis ibu Anda?") echo 'selected'; ?>>Siapa nama gadis ibu Anda?</option>
                    <option value="Apa nama hewan peliharaan pertama Anda?" <?php if ($user['recovery_question'] == "Apa nama hewan peliharaan pertama Anda?") echo 'selected'; ?>>Apa nama hewan peliharaan pertama Anda?</option>
                    <option value="Apa nama sekolah dasar Anda?" <?php if ($user['recovery_question'] == "Apa nama sekolah dasar Anda?") echo 'selected'; ?>>Apa nama sekolah dasar Anda?</option>
                </select>
                <input type="text" name="recovery_answer_1" placeholder="Jawaban Anda" value="<?php echo htmlspecialchars($user['recovery_answer']); ?>" required>

                <h4>Pertanyaan Pemulihan 2</h4>
                <label for="recovery_question_2">Pilih Pertanyaan Pemulihan 2:</label>
                <select name="recovery_question_2" id="recovery_question_2" required>
                    <option value="">-- Pilih Pertanyaan --</option>
                    <option value="Apa warna favorit Anda?" <?php if ($user['recovery_question_2'] == "Apa warna favorit Anda?") echo 'selected'; ?>>Apa warna favorit Anda?</option>
                    <option value="Apa makanan favorit Anda?" <?php if ($user['recovery_question_2'] == "Apa makanan favorit Anda?") echo 'selected'; ?>>Apa makanan favorit Anda?</option>
                    <option value="Di kota mana Anda lahir?" <?php if ($user['recovery_question_2'] == "Di kota mana Anda lahir?") echo 'selected'; ?>>Di kota mana Anda lahir?</option>
                </select>
                <input type="text" name="recovery_answer_2" placeholder="Jawaban Anda" value="<?php echo htmlspecialchars($user['recovery_answer_2']); ?>" required>

                <button type="submit" name="update_password">Update Password</button>
                <a href="profile.php" class="back-btn">Kembali ke Profile</a>
            </form>
        <?php else: ?>
            <div class="profile-info">
                <p><strong>Name:</strong> <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone_number']); ?></p>
                <p><strong>Country:</strong> <?php echo htmlspecialchars($user['country']); ?></p>
                <a href="profile.php?edit=true" class="btn">Edit Profile</a>
            </div>
            <div>
                <a href="user.php" class="back-btn">Kembali ke Main Page</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Tombol Scroll ke Atas -->
    <button id="scrollTopBtn" title="Kembali ke atas">
        <i class="fa-solid fa-arrow-up" style="color: #74C0FC;"></i>
    </button>

    <!-- Script JavaScript -->
    <script>
        // Toggle Navbar Links
        const navbarToggler = document.getElementById('navbarToggler');
        const navbarLinks = document.getElementById('navbarLinks');

        navbarToggler.addEventListener('click', function() {
            navbarLinks.classList.toggle('active');
            // Tambahkan animasi rotasi ikon hamburger
            const icon = navbarToggler.querySelector('.fas');
            icon.classList.toggle('rotate');
        });

        // Scroll Function
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
        document.getElementById('scrollTopBtn').addEventListener('click', function(){
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        // Fungsi Toggle Tema
        function toggleTheme() {
            const isDarkMode = document.documentElement.classList.toggle('dark-mode');
            localStorage.setItem('theme', isDarkMode ? 'dark' : 'light');
            updateThemeIcon(isDarkMode);
        }

        // Fungsi untuk Mengupdate Ikon Tema
        function updateThemeIcon(isDarkMode) {
            const themeIcon = document.querySelector('.theme-toggle i');
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
            updateThemeIcon(isDarkMode);
        });

        // Event Listener untuk Tombol Dark Mode
        document.querySelector('.theme-toggle').addEventListener('click', toggleTheme);
    </script>
</body>
</html>
