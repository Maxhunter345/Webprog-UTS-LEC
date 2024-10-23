<?php
session_start();
require_once 'db_config.php';

$error = '';
$success = '';

// Logout handler
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    // Clear all session variables
    $_SESSION = array();
    
    // Destroy the session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time()-3600, '/');
    }
    
    // Destroy the session
    session_destroy();
    
    // Redirect to home page
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
            // Check for lockout
            if ($user['lockout_time'] && strtotime($user['lockout_time']) > time()) {
                $error = "Your account is locked. Please try again later.";
            } else {
                if (password_verify($password, $user['password'])) {
                    // Successful login
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['is_admin'] = $user['is_admin'];

                    // Reset failed login attempts
                    $stmt = $pdo->prepare("UPDATE users SET failed_login_attempts = 0, lockout_time = NULL WHERE id = ?");
                    $stmt->execute([$user['id']]);

                    if ($user['is_admin']) {
                        header("Location: admin.php");
                    } else {
                        header("Location: user.php");
                    }
                    exit();
                } else {
                    // Incorrect password
                    $failed_attempts = $user['failed_login_attempts'] + 1;
                    $lockout_time = null;

                    if ($failed_attempts >= 5) {
                        $lockout_time = date("Y-m-d H:i:s", strtotime('+15 minutes'));
                        $error = "Too many failed login attempts. Your account is locked for 15 minutes.";
                    } else {
                        $error = "Invalid email or password";
                    }

                    // Update failed login attempts and lockout time
                    $stmt = $pdo->prepare("UPDATE users SET failed_login_attempts = ?, lockout_time = ? WHERE id = ?");
                    $stmt->execute([$failed_attempts, $lockout_time, $user['id']]);
                }
            }
        } else {
            $error = "Invalid email or password";
        }
    } elseif (isset($_POST['register'])) {
        $email = htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8');
        $password = $_POST['password']; // Don't use htmlspecialchars on passwords
        $confirm_password = $_POST['confirm_password'];
        
        if ($password !== $confirm_password) {
            $error = "Passwords do not match";
        } else {
            $is_admin = (strpos($email, "@division.expo.com") !== false) ? 1 : 0;
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("INSERT INTO users (email, password, is_admin) VALUES (?, ?, ?)");
            
            if ($stmt->execute([$email, $hashed_password, $is_admin])) {
                $success = "Registration successful. Please log in.";
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"], ENT_QUOTES, 'UTF-8'); ?>">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <button type="submit" name="login">Login</button>
            <button type="submit" name="register">Register</button>
        </form>
        <a href="forgot_password.php" class="forgot-password-link">Forgot Password?</a>
        <a href="index.php" class="back-btn">Back to Home</a>
    </div>
</body>
</html>