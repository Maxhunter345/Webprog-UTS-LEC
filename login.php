<?php
session_start();
require_once 'db_config.php';

$error = '';
$success = '';

// Handler logout
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['login'])) {
        $email = htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8');
        $password = $_POST['password'];

        $stmt = $pdo->prepare("SELECT id, email, password, is_admin, failed_login_attempts, lockout_time FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            // Cek lockout
            if ($user['lockout_time'] && strtotime($user['lockout_time']) > time()) {
                $error = "Akun Anda terkunci. Silakan gunakan fitur Lupa Password.";
            } else {
                if (password_verify($password, $user['password'])) {
                    // Login berhasil
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['is_admin'] = $user['is_admin'];

                    // Reset percobaan login gagal
                    $stmt = $pdo->prepare("UPDATE users SET failed_login_attempts = 0, lockout_time = NULL WHERE id = ?");
                    $stmt->execute([$user['id']]);

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
                        // Kunci akun
                        $lockout_time = date("Y-m-d H:i:s", strtotime('+1 minutes'));
                        $error = "Akun Anda terkunci setelah 5 kali percobaan login yang gagal.";
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
    } elseif (isset($_POST['register'])) {
        $email = htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8');
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        if ($password !== $confirm_password) {
            $error = "Password tidak cocok.";
        } else {
            $is_admin = (strpos($email, "@division.expo.com") !== false) ? 1 : 0;
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Cek apakah email sudah terdaftar
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);

            if ($stmt->rowCount() > 0) {
                $error = "Email sudah terdaftar.";
            } else {
                $stmt = $pdo->prepare("INSERT INTO users (email, password, is_admin) VALUES (?, ?, ?)");
                if ($stmt->execute([$email, $hashed_password, $is_admin])) {
                    $success = "Registrasi berhasil. Silakan login.";
                } else {
                    $error = "Registrasi gagal. Silakan coba lagi.";
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
    <title>Login/Register - Division Defence Expo 2024</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Division Defence Expo 2024</h1>
        <h2>Login/Register</h2>
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
            <input type="password" name="confirm_password" placeholder="Konfirmasi Password" required>
            <button type="submit" name="login">Login</button>
            <button type="submit" name="register">Register</button>
        </form>
        <a href="forgot_password.php" class="forgot-password-link">Lupa Password?</a>
        <a href="index.php" class="back-btn">Kembali ke Home</a>
    </div>
</body>
</html>
