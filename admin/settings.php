<?php
    require_once '../includes/auth.php';

    $auth = new Auth();
    $user = $auth -> getCurrentUser();

    if(!$user || $user['role'] != 'admin') {
        header('Location: ../login.php');
        exit;
    }

    $database = new Database();
    $conn = $database -> getConnection();

    // For now this is a placeholder for forum settings
    // You can add more settings/expand this to manage categories, forums, and other settings.
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Forum Settings - PSUC Admin</title>
        <link rel="stylesheet" href="assets/stylesheets/main.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    </head>
    <body>
        <header class="header">
            <div class="container">
                <div class="header-content">
                    <a href="admin_dashboard.php" class="logo">
                        <i class="../assets/imgs/suc-logo.jpg" alt="PSUC Admin Logo" style="height: 40px;">></i>PSUC Admin
                    </a>
                    <nav>
                        <ul class="nav-menu">
                            <li><a href="index.php"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
                            <li><a href="users.php"><i class="fas fa-users"></i> Manage Users</a></li>
                            <li><a href="settings.php"><i class="fas fa-cog"></i> Forum Settings</a></li>
                            <li><a href="../index.php"><i class="fas fa-arrow-left"></i> Back to Forum</a></li>
                            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        </ul>
                    </nav>
                </div>
            </div>
        </header>
        <main class="container">
            <div class="main-content" style="grid-template-columns: 1fr;">
                <div class="forum-content">
                    <div class="p-3">
                        <h1><i class="fas fa-cog"></i>Forum Settings</h1>
                        <p class="text-secondary">Manage forum-wide settings.</p>
                    </div>
                    <div class="p-3">
                    <div class="alert alert-info">
                        Forum settings management is not yet implemented. This is a placeholder for future functionality.
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>