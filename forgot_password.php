<?php
session_start();
require_once 'db_config.php';

$error = $success = '';
$step = 1; // Melacak langkah dalam proses

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['submit_email'])) {
        $email = htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8');

        // Cek apakah email ada dan pertanyaan pemulihan telah diatur
        $stmt = $pdo->prepare("SELECT id, recovery_question, recovery_question_2 FROM users WHERE email = ? AND recovery_question IS NOT NULL AND recovery_question_2 IS NOT NULL");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $_SESSION['reset_email'] = $email;
            $recovery_question_1 = $user['recovery_question'];
            $recovery_question_2 = $user['recovery_question_2'];
            $step = 2;
        } else {
            $error = "Email not found or recovery questions not set.";
        }
    } elseif (isset($_POST['submit_answers'])) {
        // Verifikasi jawaban pemulihan
        $recovery_answer_1 = htmlspecialchars($_POST['recovery_answer_1'], ENT_QUOTES, 'UTF-8');
        $recovery_answer_2 = htmlspecialchars($_POST['recovery_answer_2'], ENT_QUOTES, 'UTF-8');
        $email = $_SESSION['reset_email'];

        $stmt = $pdo->prepare("SELECT id, recovery_answer, recovery_answer_2 FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        $answer_1_correct = password_verify($recovery_answer_1, $user['recovery_answer']);
        $answer_2_correct = password_verify($recovery_answer_2, $user['recovery_answer_2']);

        if ($user && $answer_1_correct && $answer_2_correct) {
            // Jawaban benar, izinkan reset password
            $_SESSION['reset_user_id'] = $user['id'];
            $step = 3;
        } else {
            $error = "Recovery answers are incorrect.";
            $recovery_question_1 = $_SESSION['recovery_question_1'];
            $recovery_question_2 = $_SESSION['recovery_question_2'];
            $step = 2;
        }
    } elseif (isset($_POST['reset_password'])) {
        // Reset password
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if ($new_password !== $confirm_password) {
            $error = "Passwords do not match.";
            $step = 3;
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $user_id = $_SESSION['reset_user_id'];

            $stmt = $pdo->prepare("UPDATE users SET password = ?, failed_login_attempts = 0, lockout_time = NULL WHERE id = ?");
            if ($stmt->execute([$hashed_password, $user_id])) {
                $success = "Password has been reset successfully. Please log in.";
                // Hapus variabel sesi
                unset($_SESSION['reset_email'], $_SESSION['reset_user_id'], $_SESSION['recovery_question_1'], $_SESSION['recovery_question_2']);
            } else {
                $error = "An error occurred while resetting the password.";
                $step = 3;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password - Division Defence Expo 2024</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Header dengan Efek Parallax -->
    <div class="header">
    <div class="container">
        <h2>Lupa Password</h2>
        <?php
        if (!empty($error)) {
            echo "<p class='error'>" . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . "</p>";
        }
        if (!empty($success)) {
            echo "<p class='success'>" . htmlspecialchars($success, ENT_QUOTES, 'UTF-8') . "</p>";
            echo "<a href='login.php' class='btn'>Login</a>";
        } elseif ($step == 1) {
        ?>
        <form method="post" action="">
            <input type="email" name="email" placeholder="Enter your email" required>
            <button type="submit" name="submit_email">Continue</button>
        </form>
        <?php } elseif ($step == 2) { ?>
        <form method="post" action="">
            <p>Recovery Question 1: <strong><?php echo htmlspecialchars($recovery_question_1, ENT_QUOTES, 'UTF-8'); ?></strong></p>
            <input type="text" name="recovery_answer_1" placeholder="Your Answer" required>
            <p>Recovery Question 2: <strong><?php echo htmlspecialchars($recovery_question_2, ENT_QUOTES, 'UTF-8'); ?></strong></p>
            <input type="text" name="recovery_answer_2" placeholder="Your Answer" required>
            <button type="submit" name="submit_answers">Continue</button>
        </form>
        <?php
            $_SESSION['recovery_question_1'] = $recovery_question_1;
            $_SESSION['recovery_question_2'] = $recovery_question_2;
        } elseif ($step == 3) { ?>
        <form method="post" action="">
            <input type="password" name="new_password" placeholder="New Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
            <button type="submit" name="reset_password">Reset Password</button>
        </form>
        <?php } ?>
        <a href="login.php" class="back-btn">Back to Login</a>
        </div>
    </div>
</body>
</html>