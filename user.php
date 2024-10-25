<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success_message = $error_message = '';

// Fetch user profile information
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Handle profile submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $first_name = htmlspecialchars($_POST['first_name'], ENT_QUOTES, 'UTF-8');
    $last_name = htmlspecialchars($_POST['last_name'], ENT_QUOTES, 'UTF-8');
    $phone_number = htmlspecialchars($_POST['phone_number'], ENT_QUOTES, 'UTF-8');
    $country = htmlspecialchars($_POST['country'], ENT_QUOTES, 'UTF-8');
    $recovery_question_1 = htmlspecialchars($_POST['recovery_question_1'], ENT_QUOTES, 'UTF-8');
    $recovery_answer_1 = htmlspecialchars($_POST['recovery_answer_1'], ENT_QUOTES, 'UTF-8');
    $recovery_question_2 = htmlspecialchars($_POST['recovery_question_2'], ENT_QUOTES, 'UTF-8');
    $recovery_answer_2 = htmlspecialchars($_POST['recovery_answer_2'], ENT_QUOTES, 'UTF-8');
    
    // Validasi bahwa pertanyaan dan jawaban pemulihan telah diisi
    if (empty($recovery_question_1) || empty($recovery_answer_1) || empty($recovery_question_2) || empty($recovery_answer_2)) {
        $error_message = "Silakan pilih dua pertanyaan pemulihan dan isi jawabannya.";
    } elseif ($recovery_question_1 == $recovery_question_2) {
        $error_message = "Pertanyaan pemulihan tidak boleh sama.";
    } else {
    try {
        // Hash jawaban pemulihan
        $hashed_recovery_answer_1 = password_hash($recovery_answer_1, PASSWORD_DEFAULT);
        $hashed_recovery_answer_2 = password_hash($recovery_answer_2, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, phone_number = ?, country = ?, recovery_question = ?, recovery_answer = ?, recovery_question_2 = ?, recovery_answer_2 = ?, profile_completed = TRUE WHERE id = ?");
        $stmt->execute([$first_name, $last_name, $phone_number, $country, $recovery_question_1, $hashed_recovery_answer_1, $recovery_question_2, $hashed_recovery_answer_2, $user_id]);

        $success_message = "Profil berhasil diperbarui!";
        $user['profile_completed'] = true;
        $user['first_name'] = $first_name;
        $user['last_name'] = $last_name;
        $user['phone_number'] = $phone_number;
        $user['country'] = $country;
        $user['recovery_question'] = $recovery_question_1;
        $user['recovery_question_2'] = $recovery_question_2;
    } catch (PDOException $e) {
        $error_message = "Error updating profile: " . $e->getMessage();
    }
}

// Menangani pengaturan pertanyaan pemulihan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['set_recovery'])) {
    $recovery_question = htmlspecialchars($_POST['recovery_question'], ENT_QUOTES, 'UTF-8');
    $recovery_answer = htmlspecialchars($_POST['recovery_answer'], ENT_QUOTES, 'UTF-8');

    if (!empty($recovery_question) && !empty($recovery_answer)) {
        $hashed_recovery_answer = password_hash($recovery_answer, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("UPDATE users SET recovery_question = ?, recovery_answer = ? WHERE id = ?");
        if ($stmt->execute([$recovery_question, $hashed_recovery_answer, $user_id])) {
            $success_message = "Pertanyaan pemulihan berhasil disimpan!";
            $user['recovery_question'] = $recovery_question;
        } else {
            $error_message = "Terjadi kesalahan saat menyimpan pertanyaan pemulihan.";
        }
    } else {
        $error_message = "Silakan pilih pertanyaan dan masukkan jawaban Anda.";
        }
    }
}

// Handle event registration
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register_event'])) {
    if (!$user['profile_completed']) {
        $error_message = "Please complete your profile before registering for events.";
    } else {
        $event_id = (int)$_POST['event_id'];
        
        try {
            // Check if user is already registered
            $stmt = $pdo->prepare("SELECT * FROM registrations WHERE user_id = ? AND event_id = ?");
            $stmt->execute([$user_id, $event_id]);
            if ($stmt->rowCount() > 0) {
                $error_message = "You are already registered for this event.";
            } else {
                // Check if event has reached max visitors
                $stmt = $pdo->prepare("SELECT max_visitors, (SELECT COUNT(*) FROM registrations WHERE event_id = events.id) as current_visitors FROM events WHERE id = ?");
                $stmt->execute([$event_id]);
                $event = $stmt->fetch();
                
                if ($event['current_visitors'] >= $event['max_visitors']) {
                    $error_message = "This event has reached its maximum number of visitors.";
                } else {
                    $pdo->beginTransaction();
                    
                    // Register user for the event
                    $stmt = $pdo->prepare("INSERT INTO registrations (user_id, event_id) VALUES (?, ?)");
                    $stmt->execute([$user_id, $event_id]);
                    $registration_id = $pdo->lastInsertId();
                    
                    $pdo->commit();
                    $success_message = "You have successfully registered for the event!";
                }
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error_message = "Error registering for event: " . $e->getMessage();
        }
    }
}

// Fetch all events with current visitor count and featured companies
$stmt = $pdo->query("
    SELECT e.*, 
            (SELECT COUNT(*) FROM registrations WHERE event_id = e.id) as current_visitors,
            (SELECT COUNT(*) FROM registrations WHERE event_id = e.id AND user_id = {$user_id}) as user_registered,
            GROUP_CONCAT(CONCAT(c.name, ':', c.logo_path) SEPARATOR '|') as featured_companies
    FROM events e
    LEFT JOIN event_companies ec ON e.id = ec.event_id
    LEFT JOIN companies c ON ec.company_id = c.id
    GROUP BY e.id
    ORDER BY e.date_time
");
$events = $stmt->fetchAll();
?>

<script>
    (function() {
        const isDarkMode = localStorage.getItem('theme') === 'dark';
        if (isDarkMode) {
            document.documentElement.classList.add('dark-mode');
        }
    })();
</script>

<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <meta name="color-scheme" content="light dark">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Division Defence Expo 2024</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    <!-- Header dengan Efek Parallax -->
    <div class="header">
    <div class="container">
        <h1>Division Defence Expo 2024</h1>
        <h2>User Dashboard</h2>
        
        <?php if ($success_message): ?>
            <p class="success"><?php echo htmlspecialchars($success_message, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <p class="error"><?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>

        <?php if (!$user['profile_completed']): ?>
            <div class="profile-form">
                <h3>Complete Your Profile</h3>
                <p>Please complete your profile before registering for events.</p>
                <form method="post" action="">
                    <input type="text" name="first_name" placeholder="First Name" required>
                    <input type="text" name="last_name" placeholder="Last Name" required>
                    <input type="tel" name="phone_number" placeholder="Phone Number" required>
                    <input type="text" name="country" placeholder="Country" required>
                <!-- Pertanyaan Pemulihan -->
                <label for="recovery_question_1">Pilih Pertanyaan Pemulihan 1:</label>
                    <select name="recovery_question_1" id="recovery_question_1" required>
                        <option value="">-- Pilih Pertanyaan --</option>
                        <option value="Siapa Nama Orang tua?">Siapa Nama Orang tua?</option>
                        <option value="Apa nama kota tempat Anda dilahirkan?">Apa nama kota tempat Anda dilahirkan?</option>
                        <option value="Kamu bersekolah di SD mana?">Kamu bersekolah di SD mana?</option>
                    </select>
                    <input type="text" name="recovery_answer_1" placeholder="Jawaban Anda" required>

                    <label for="recovery_question_2">Pilih Pertanyaan Pemulihan 2:</label>
                    <select name="recovery_question_2" id="recovery_question_2" required>
                        <option value="">-- Pilih Pertanyaan --</option>
                        <option value="Siapa pahlawan masa kecil?">Siapa Nama Orang tua?</option>
                        <option value="Di mana liburan keluarga terbaik Anda saat kecil?">Apa nama kota tempat Anda dilahirkan?</option>
                        <option value="Apa mobil pertama Anda?">Kamu bersekolah di SD mana?</option>
                    </select>
                    <input type="text" name="recovery_answer_2" placeholder="Jawaban Anda" required>
                    <button type="submit" name="update_profile">Save Profile</button>
                </form>
                </button>
            </div>
        <?php else: ?>
            <div class="profile-section">
                <h3>My Profile</h3>
                <p>Name: <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name'], ENT_QUOTES, 'UTF-8'); ?></p>
                <p>Email: <?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></p>
                <p>Phone: <?php echo htmlspecialchars($user['phone_number'], ENT_QUOTES, 'UTF-8'); ?></p>
                <p>Country: <?php echo htmlspecialchars($user['country'], ENT_QUOTES, 'UTF-8'); ?></p>
                <button onclick="showEditProfile()">Edit Profil</button>
                
                <div id="edit-profile-form" style="display: none;">
                    <form method="post" action="">
                        <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                        <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                        <input type="tel" name="phone_number" value="<?php echo htmlspecialchars($user['phone_number']); ?>" required>
                        <input type="text" name="country" value="<?php echo htmlspecialchars($user['country']); ?>" required>
                        <button type="submit" name="update_profile">Update Profile</button>
                    </form>
                </div>
            </div>

        <h3>Upcoming Events</h3>
        <?php foreach ($events as $event): ?>
            <div class="event">
            <?php if ($event['event_image']): ?>
                <div class="event-image">
                    <img src="<?php echo htmlspecialchars($event['event_image']); ?>" alt="<?php echo htmlspecialchars($event['title']); ?>">
                </div>
            <?php endif; ?>
            <h4><?php echo htmlspecialchars($event['title']); ?></h4>
            <p>Status: <span class="event-status <?php echo $event['status']; ?>"><?php echo ucfirst($event['status']); ?></span></p>
            <p>Date & Time: <?php echo $event['date_time']; ?></p>
            <p>Country: <?php echo htmlspecialchars($event['country']); ?></p>
            <p>Location: <?php echo htmlspecialchars($event['location']); ?></p>
            <p>Description: <?php echo htmlspecialchars($event['description']); ?></p>
            <p>Visitors: <?php echo $event['current_visitors']; ?> / <?php echo $event['max_visitors']; ?></p>
    
            <?php if ($event['user_registered']): ?>
                <p class="registered">You are registered for this event</p>
            <?php elseif ($event['status'] == 'open' && $event['current_visitors'] < $event['max_visitors']): ?>
                <form method="post" action="">
                    <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                    <button type="submit" name="register_event">Register</button>
                </form>
            <?php elseif ($event['status'] != 'open'): ?>
                <p class="event-closed">Registration <?php echo $event['status']; ?></p>
            <?php else: ?>
                <p class="full">This event is full</p>
            <?php endif; ?>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
            <a href="login.php?action=logout" class="btn">Logout</a>
            <div>
            <a href="index.php" class="back-btn">Kembali ke Home</a>
            </div>
    </div>
    <!-- Tombol Scroll ke Atas -->
    <button id="scrollTopBtn" title="Kembali ke atas">
            <i class="fa-solid fa-arrow-up" style="color: #74C0FC;"></i>
        </button>
</div>

<div id="theme-toggle-root"></div>
        <!-- Script JavaScript -->
    <script>
        navbarToggler.addEventListener('click', function() {
        navbarLinks.classList.toggle('active');
        // Tambahkan animasi rotasi ikon hamburger
        const icon = navbarToggler.querySelector('.fas');
        icon.classList.toggle('rotate');

        // Fungsi untuk Tombol Scroll ke Atas
        window.onscroll = function() {scrollFunction()};

        function scrollFunction() {
            const scrollTopBtn = document.getElementById("scrollTopBtn");
            if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
                scrollTopBtn.style.display = "block";
            } else {
                scrollTopBtn.style.display = "none";
            }
        }

        document.getElementById('scrollTopBtn').addEventListener('click', function(){
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
        
        // Function to scroll to top
        function scrollToTop() {
            window.scrollTo({
            top: 0,
            behavior: 'smooth'
            });
        }
        
    // Fungsi Toggle Tema
    function toggleTheme() {
        const isDarkMode = document.documentElement.classList.toggle('dark-mode');
        localStorage.setItem('theme', isDarkMode ? 'dark' : 'light');
        updateThemeIcon(isDarkMode);
    }

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

    // Fungsi untuk menampilkan/menghilangkan form edit profil
    function showEditProfile() {
        const form = document.getElementById('edit-profile-form');
        form.style.display = form.style.display === 'none' ? 'block' : 'none';
    }
</script>
</body>
</html>
