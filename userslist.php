<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: login.php");
    exit();
}

$success_message = $error_message = '';

// Handle user deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_user'])) {
    $user_id = (int)$_POST['user_id'];
    
    try {
        $pdo->beginTransaction();
        
        // Delete user's registrations first
        $stmt = $pdo->prepare("DELETE FROM registrations WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        // Delete the user
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND is_admin = 0"); // Prevent admin deletion
        $stmt->execute([$user_id]);
        
        $pdo->commit();
        $success_message = "User deleted successfully!";
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error_message = "Error deleting user: " . $e->getMessage();
    }
}

// Fetch all non-admin users
$stmt = $pdo->query("
    SELECT 
        u.*,
        COUNT(r.id) as registration_count
    FROM users u
    LEFT JOIN registrations r ON u.id = r.user_id
    WHERE u.is_admin = 0
    GROUP BY u.id
    ORDER BY u.id DESC
");
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Division Defence Expo 2024</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'navbar-admin.php'; ?>
    
    <div class="container">
        <h2>User Management</h2>
        
        <?php if ($success_message): ?>
            <p class="success"><?php echo $success_message; ?></p>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <p class="error"><?php echo $error_message; ?></p>
        <?php endif; ?>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Country</th>
                        <th>Registrations</th>
                        <th>Profile Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo $user['first_name'] ? htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) : 'Not set'; ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo $user['phone_number'] ? htmlspecialchars($user['phone_number']) : 'Not set'; ?></td>
                            <td><?php echo $user['country'] ? htmlspecialchars($user['country']) : 'Not set'; ?></td>
                            <td><?php echo $user['registration_count']; ?></td>
                            <td><?php echo $user['profile_completed'] ? 'Complete' : 'Incomplete'; ?></td>
                            <td>
                                <form method="post" action="" style="display: inline;">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" name="delete_user" 
                                            onclick="return confirm('Are you sure you want to delete this user? This will also delete all their event registrations.')"
                                            class="btn-delete">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>