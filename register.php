<?php
session_start();
require_once 'db_config.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    // Mengambil dan membersihkan input
    $email = htmlspecialchars(trim($_POST['email']), ENT_QUOTES, 'UTF-8');
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validasi input
    if (empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Silakan isi semua bidang.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid.";
    } elseif ($password !== $confirm_password) {
        $error = "Password tidak cocok.";
    } else {
        // Menentukan peran admin atau user berdasarkan email
        $is_admin = (strpos($email, "@division.expo.com") !== false) ? 1 : 0;

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Cek apakah email sudah terdaftar
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            $error = "Email sudah terdaftar.";
        } else {
            // Insert pengguna baru
            $stmt = $pdo->prepare("INSERT INTO users (email, password, is_admin) VALUES (?, ?, ?)");
            if ($stmt->execute([$email, $hashed_password, $is_admin])) {
                $success = "Registrasi berhasil. Silakan <a href='login.php'>Login</a>.";
            } else {
                $error = "Registrasi gagal. Silakan coba lagi.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Register - Division Defence Expo 2024</title>
    <link rel="stylesheet" href="styles.css">
    <!-- Font Awesome untuk Ikon (Pastikan sudah terhubung) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <!-- Konten Registrasi -->
    <div class="header">
        <div class="container">
            <h1>Division Defence Expo 2024</h1>
            <h2>Register</h2>
            <?php
            if (!empty($error)) {
                echo "<p class='error'>" . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . "</p>";
            }
            if (!empty($success)) {
                echo "<p class='success'>" . $success . "</p>"; // Sudah termasuk link
            }
            ?>
            <form method="post" action="">
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="password" name="confirm_password" placeholder="Konfirmasi Password" required>
                <button type="submit" name="register">Register</button>
            </form>
            <p>Sudah memiliki akun? <a href="login.php">Login di sini</a></p>
            <a href="index.php" class="back-btn">Kembali ke Home</a>
        </div>
    </div>
</body>
</html>
