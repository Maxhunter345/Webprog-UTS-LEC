<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<link rel="stylesheet" href="styles.css">
<nav class="navbar">
    <div class="navbar-logo">
        <span>Division Defence Expo 2024 - Admin</span>
    </div>
    <div class="navbar-links">
        <a href="admin.php" class="<?php echo $current_page == 'admin.php' ? 'active' : ''; ?>">Events</a>
        <a href="registrants.php" class="<?php echo $current_page == 'registrants.php' ? 'active' : ''; ?>">Registrants</a>
        <a href="userslist.php" class="<?php echo $current_page == 'userslist.php' ? 'active' : ''; ?>">Users</a>
        <a href="login.php?action=logout">Logout</a>
    </div>
</nav>