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
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $first_name = htmlspecialchars($_POST['first_name'], ENT_QUOTES, 'UTF-8');
    $last_name = htmlspecialchars($_POST['last_name'], ENT_QUOTES, 'UTF-8');
    $email = htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8'); 
    $phone_number = htmlspecialchars($_POST['phone_number'], ENT_QUOTES, 'UTF-8');
    $country = htmlspecialchars($_POST['country'], ENT_QUOTES, 'UTF-8');
    
    try {
        $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, phone_number = ?, country = ? WHERE id = ?");
        $stmt->execute([$first_name, $last_name, $email, $phone_number, $country, $user_id]);
        $success_message = "Profile updated successfully!";
        
        // Update local user data
        $user['first_name'] = $first_name;
        $user['last_name'] = $last_name;
        $user['email'] = $email;
        $user['phone_number'] = $phone_number;
        $user['country'] = $country;
    } catch (PDOException $e) {
        $error_message = "Error updating profile: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Division Defence Expo 2024</title>
    <link rel="stylesheet" href="styles.css">
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
            <form method="post" action="profile.php">
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
                <!-- Pertanyaan Pemulihan -->
                <label for="recovery_question_1">Pilih Pertanyaan Pemulihan 1:</label>
            <select name="recovery_question_1" id="recovery_question_1" required>
                <option value="">-- Pilih Pertanyaan --</option>
                <option value="Siapa Nama Orang tua?" <?php if ($user['recovery_question'] == "Siapa nama gadis ibu Anda?") echo 'selected'; ?>>Siapa nama gadis ibu Anda?</option>
                <option value="Apa nama kota tempat Anda dilahirkan?" <?php if ($user['recovery_question'] == "Apa nama hewan peliharaan pertama Anda?") echo 'selected'; ?>>Apa nama hewan peliharaan pertama Anda?</option>
                <option value="Kamu bersekolah di SD mana?" <?php if ($user['recovery_question'] == "Apa nama sekolah dasar Anda?") echo 'selected'; ?>>Apa nama sekolah dasar Anda?</option>
            </select>
                <input type="text" name="recovery_answer_1" placeholder="Jawaban Anda" required>

            <label for="recovery_question_2">Pilih Pertanyaan Pemulihan 2:</label>
            <select name="recovery_question_2" id="recovery_question_2" required>
                <option value="">-- Pilih Pertanyaan --</option>
                <option value="Siapa pahlawan masa kecil?" <?php if ($user['recovery_question_2'] == "Apa warna favorit Anda?") echo 'selected'; ?>>Apa warna favorit Anda?</option>
                <option value="Di mana liburan keluarga terbaik Anda saat kecil?" <?php if ($user['recovery_question_2'] == "Apa makanan favorit Anda?") echo 'selected'; ?>>Apa makanan favorit Anda?</option>
                <option value="Apa mobil pertama Anda?" <?php if ($user['recovery_question_2'] == "Di kota mana Anda lahir?") echo 'selected'; ?>>Di kota mana Anda lahir?</option>
            </select>
                <input type="text" name="recovery_answer_2" placeholder="Jawaban Anda" required>
                <button type="submit" name="update_password">Update Password</button>
            </form>
        <?php else: ?>
            <div class="profile-info">
                <p><strong>Name:</strong> <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone_number']); ?></p>
                <p><strong>Country:</strong> <?php echo htmlspecialchars($user['country']); ?></p>
                <a href="profile.php?edit=true" class="btn">Edit Profile</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>