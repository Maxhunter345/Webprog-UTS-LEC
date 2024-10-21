<?php
session_start();
include 'includes/db.php'; // file koneksi database

// Handle login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['name'];
        $_SESSION['role'] = $user['role'];

        // Redirect ke halaman admin atau user berdasarkan role
        if ($user['role'] == 'admin') {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: user_dashboard.php");
        }
        exit();
    } else {
        $login_error = "Invalid email or password!";
    }
}

// Handle register
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = 'user'; // Default role

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->rowCount() > 0) {
        $register_error = "Email already exists!";
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$name, $email, $password, $role])) {
            header("Location: index.php");
            exit();
        } else {
            $register_error = "Error registering user!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Defense Expo 2024</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/script.js" defer></script>
</head>
<body>
    <header>
        <h1>Defense Expo 2024</h1>
        <nav>
            <a href="#login-section">Login</a>
            <a href="#register-section">Register</a>
            <button id="toggleTheme">Toggle Dark Mode</button> <!-- Tombol untuk dark mode -->
        </nav>
        <div class="hamburger" onclick="toggleMenu()">
            <div class="line"></div>
            <div class="line"></div>
            <div class="line"></div>
        </div>
        <div class="mobile-menu hidden" id="mobileMenu">
            <ul>
                <li><a href="#login-section">Login</a></li>
                <li><a href="#register-section">Register</a></li>
            </ul>
        </div>
    </header>

    <main>
        <!-- Section Login -->
        <section id="login-section">
            <div class="login-container">
                <div class="login-box">
                    <h2>Login</h2>
                    <?php if (isset($login_error)) echo "<p style='color:red;'>$login_error</p>"; ?>
                    <form action="index.php" method="POST">
                        <div class="input-box">
                            <label for="email">Email:</label>
                            <input type="email" id="email" name="email" required>
                        </div>

                        <div class="input-box">
                            <label for="password">Password:</label>
                            <input type="password" id="password" name="password" required onfocus="showPassword(this)">
                        </div>

                        <button type="submit" class="login-btn" name="login">Login</button>
                    </form>
                </div>
            </div>
        </section>

        <!-- Section Register -->
        <section id="register-section">
            <div class="login-container">
                <div class="login-box">
                    <h2>Register</h2>
                    <?php if (isset($register_error)) echo "<p style='color:red;'>$register_error</p>"; ?>
                    <form action="index.php" method="POST">
                        <div class="input-box">
                            <label for="name">Name:</label>
                            <input type="text" id="name" name="name" required>
                        </div>

                        <div class="input-box">
                            <label for="email">Email:</label>
                            <input type="email" id="email" name="email" required>
                        </div>

                        <div class="input-box">
                            <label for="password">Password:</label>
                            <input type="password" id="password" name="password" required onfocus="showPassword(this)">
                        </div>

                        <button type="submit" class="login-btn" name="register">Register</button>
                    </form>
                </div>
            </div>
        </section>

        <!-- Section Event -->
        <section id="events-section">
            <h2>Upcoming Events</h2>
            <div class="events-container">
                <?php
                // Fetch upcoming events
                $stmt = $pdo->prepare("SELECT * FROM events WHERE date >= CURDATE() ORDER BY date ASC");
                $stmt->execute();
                $events = $stmt->fetchAll();

                foreach ($events as $event):
                ?>
                    <div class="event">
                        <img src="images/<?= htmlspecialchars($event['image']) ?>" alt="Event Banner">
                        <h2><?= htmlspecialchars($event['name']) ?></h2>
                        <p>Date: <?= htmlspecialchars($event['date']) ?></p>
                        <a href="event-details.php?id=<?= $event['id'] ?>">View Details</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Countdown Timer -->
        <div id="countdown">
            <div>
                <span id="days"></span> Days
                <span id="hours"></span> Hours
                <span id="minutes"></span> Minutes
                <span id="seconds"></span> Seconds
            </div>
        </div>
    </main>

    <footer>
        <p>© 2024 Defense Expo</p>
    </footer>
</body>
</html>
