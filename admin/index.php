<?php
    require_once '../includes/auth.php';

    $auth = new Auth();
    $user = $auth->getCurrentUser();

    if(!$user || $user['role'] != 'admin') {
        header('Location: ../login.php');
        exit;
    }

    $database = new Database();
    $conn = $database -> getConnection();

    // Get statistics
    $stats_query = "SELECT 
        (SELECT COUNT(*) FROM users) as total_users,
        (SELECT COUNT(*) FROM topics) as total_topics,
        (SELECT COUNT(*) FROM posts) as total_posts,
        (SELECT COUNT(*) FROM users WHERE created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)) as new_users_week";
    $stmt = $conn -> prepare($stats_query);
    $stmt -> execute();
    $stats = $stmt -> fetch(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - PSUC Forum</title>
    <link rel="stylesheet" href="assets/stylsheets/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="../index.php" class="logo">
                    <i class="../assets/imgs/suc-logo.jpg" alt="PSUC Admin Logo" style="height: 40px;"></i> PSUC Admin
                </a>
                <nav>
                    <ul class="nav-menu">
                        <li><a href="index.php"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
                        <li><a href="users.php"><i class="fas fa-users"></i>Manage Users</a></li>
                        <li><a href="settings.php"><i class="fas fa-cog"></i>Forum Settings</a></li>
                        <li><a href="../index.php"><i class="fas fa-arrow-left"></i>Back to Forum</a></li>
                        <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main class="container">
        <div class="main-content" style="grid-template-columns: 1fr;">
            <div class="forum-content">
                <div class="p-3">
                    <h1><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h1>
                    <p class="text-secondary">Welcome back, <?php echo htmlspecialchars($user['full_name']); ?>!</p>
                </div>

                <!-- Statistics Cards -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; padding: 1.5rem;">
                    <div style="background: linear-gradient(135deg, #3b82f6, #1e40af); color: white; padding: 1.5rem; border-radius: 12px;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <h3 style="margin: 0; font-size: 2rem;"><?php echo $stats['total_users']; ?></h3>
                                <p style="margin: 0; opacity: 0.9;">Total Users</p>
                            </div>
                            <i class="fas fa-users" style="font-size: 2.5rem; opacity: 0.7;"></i>
                        </div>
                        <small style="opacity: 0.8;">+<?php echo $stats['new_users_week']; ?> this week</small>
                    </div>

                    <div style="background: linear-gradient(135deg, #10b981, #047857); color: white; padding: 1.5rem; border-radius: 12px;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <h3 style="margin: 0; font-size: 2rem;"><?php echo $stats['total_topics']; ?></h3>
                                <p style="margin: 0; opacity: 0.9;">Total Topics</p>
                            </div>
                            <i class="fas fa-comments" style="font-size: 2.5rem; opacity: 0.7;"></i>
                        </div>
                    </div>

                    <div style="background: linear-gradient(135deg, #f59e0b, #d97706); color: white; padding: 1.5rem; border-radius: 12px;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <h3 style="margin: 0; font-size: 2rem;"><?php echo $stats['total_posts']; ?></h3>
                                <p style="margin: 0; opacity: 0.9;">Total Posts</p>
                            </div>
                            <i class="fas fa-reply" style="font-size: 2.5rem; opacity: 0.7;"></i>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="p-3" style="border-top: 1px solid var(--border-color);">
                    <h3><i class="fas fa-tools"></i> Quick Actions</h3>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-top: 1rem;">
                        <a href="../index.php" class="btn btn-primary" style="text-align: center; padding: 1rem;">
                            <i class="fas fa-eye"></i><br>View Forum
                        </a>
                        <a href="users.php" class="btn btn-secondary" style="text-align: center; padding: 1rem;">
                            <i class="fas fa-users"></i><br>Manage Users
                        </a>
                        <a href="settings.php" class="btn btn-success" style="text-align: center; padding: 1rem;">
                            <i class="fas fa-cog"></i><br>Forum Settings
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>