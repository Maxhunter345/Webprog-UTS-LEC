<?php
session_start();
require_once 'db_config.php';

$error = '';
$success = '';

// Handler logout (opsional, jika Anda ingin memungkinkan logout dari halaman login)
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    // Hapus semua variabel sesi
    $_SESSION = array();
    
    // Hancurkan cookie sesi
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time()-3600, '/');
    }
    
    // Hancurkan sesi
    session_destroy();
    
    // Redirect ke halaman utama
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    // Mengambil dan membersihkan input
    $email = htmlspecialchars(trim($_POST['email']), ENT_QUOTES, 'UTF-8');
    $password = $_POST['password'];

    // Validasi input
    if (empty($email) || empty($password)) {
        $error = "Silakan isi semua bidang.";
    } else {
        // Mencari pengguna berdasarkan email
        $stmt = $pdo->prepare("SELECT id, email, password, is_admin, failed_login_attempts, lockout_time FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            // Cek apakah akun terkunci
            if ($user['lockout_time'] && strtotime($user['lockout_time']) > time()) {
                $error = "Akun Anda terkunci. Silakan gunakan fitur Lupa Password atau coba lagi nanti.";
            } else {
                // Verifikasi password
                if (password_verify($password, $user['password'])) {
                    // Login berhasil
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['is_admin'] = $user['is_admin'];

                    // Reset percobaan login gagal
                    $stmt = $pdo->prepare("UPDATE users SET failed_login_attempts = 0, lockout_time = NULL WHERE id = ?");
                    $stmt->execute([$user['id']]);

                    // Redirect sesuai peran
                    if ($user['is_admin']) {
                        header("Location: admin.php");
                    } else {
                        header("Location: user.php");
                    }
                    exit();
                } else {
                    // Password salah
                    $failed_attempts = $user['failed_login_attempts'] + 1;
                    $lockout_time = null;

                    if ($failed_attempts >= 5) {
                        // Kunci akun selama 15 menit
                        $lockout_time = date("Y-m-d H:i:s", strtotime('+15 minutes'));
                        $error = "Akun Anda terkunci setelah 5 kali percobaan login yang gagal. Silakan coba lagi setelah 15 menit.";
                    } else {
                        $error = "Email atau password salah.";
                    }

                    // Update percobaan login gagal dan waktu lockout
                    $stmt = $pdo->prepare("UPDATE users SET failed_login_attempts = ?, lockout_time = ? WHERE id = ?");
                    $stmt->execute([$failed_attempts, $lockout_time, $user['id']]);
                }
            }
        } else {
            $error = "Email atau password salah.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - Division Defence Expo 2024</title>
    <link rel="stylesheet" href="styles.css">
    <!-- Font Awesome untuk Ikon (Pastikan sudah terhubung) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <!-- Konten Login -->
    <div class="header">
        <div class="container">
            <h1>Division Defence Expo 2024</h1>
            <h2>Login</h2>
            <?php
            if (!empty($error)) {
                echo "<p class='error'>" . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . "</p>";
            }
            if (!empty($success)) {
                echo "<p class='success'>" . htmlspecialchars($success, ENT_QUOTES, 'UTF-8') . "</p>";
            }
            ?>
            <form method="post" action="">
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="login">Login</button>
            </form>
            <p>Belum memiliki akun? <a href="register.php">Register di sini</a></p>
            <a href="forgot_password.php" class="forgot-password-link">Lupa Password?</a>
            <div><a href="index.php" class="back-btn">Kembali ke Home</a></div>
        </div>
    </div>
</body>
</html>
