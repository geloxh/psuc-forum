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

    // Get statistics
    $stats_query = "SELECT 
        (SELECT COUNT(*) FROM users) as total_users,
        (SELECT COUNT(*) FROM topics) as total_topics,
        (SELECT COUNT(*) FROM posts) as total_posts,
        (SELECT COUNT(*) FROM users WHERE created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)) as new_users_week,
        (SELECT COUNT(*) FROM topics WHERE created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)) as topics_today,
        (SELECT COUNT(*) FROM posts WHERE created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)) as posts_today";
    $stmt = $conn -> prepare($stats_query);
    $stmt -> execute();
    $stats = $stmt -> fetch(PDO::FETCH_ASSOC);

    // Get recent activities
    $recent_query = "SELECT 'user' as type, username as title, created_at FROM users
        UNION ALL
        SELECT 'topic' as type, title, created_at FROM topics
        ORDER BY created_at DESC LIMIT 10";
    $stmt = $conn -> prepare($recent_query);
    $stmt -> execute();
    $recent_activities = $stmt -> fetchAll(PDO::FETCH_ASSOC);
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
    <?php
        include 'includes/header.php';

    ?>

    <main class="container">
        <div class="main-content" style="grid-template-columns: 1fr;">
            <div class="forum-content">
                <div class="p-3">
                    <h1><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h1>
                    <p class="text-secondary">Welcome back, <?php echo htmlspecialchars($user['full_name']); ?>!</p>
                </div>

                <!-- Enhanced Statistics Cards -->
                <div class="stats-grid p-3">
                    <div class="stat-card" style="background: linear-gradient(135deg, #3b82f6, #1e40af);">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <h3 style="margin: 0; font-size: 2rem;"><?php echo $stats['total_users']; ?></h3>
                                <p style="margin: 0; opacity: 0.9;">Total Users</p>
                            </div>
                            <i class="fas fa-users" style="font-size: 2.5rem; opacity: 0.7;"></i>
                        </div>
                        <small style="opacity: 0.8;">+<?php echo $stats['new_users_week']; ?> this week</small>
                    </div>

                    <div class="stat-card" style="background: linear-gradient(135deg, #10b981, #047857);">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <h3 style="margin: 0; font-size: 2rem;"><?php echo $stats['total_topics']; ?></h3>
                                <p style="margin: 0; opacity: 0.9;">Total Topics</p>
                            </div>
                            <i class="fas fa-comments" style="font-size: 2.5rem; opacity: 0.7;"></i>
                        </div>
                        <small style="opacity: 0.8;">+<?php echo $stats['topics_today']; ?> today</small>
                    </div>

                    <div class="stat-card" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <h3 style="margin: 0; font-size: 2rem;"><?php echo $stats['total_posts']; ?></h3>
                                <p style="margin: 0; opacity: 0.9;">Total Posts</p>
                            </div>
                            <i class="fas fa-reply" style="font-size: 2.5rem; opacity: 0.7;"></i>
                        </div>
                        <small style="opacity: 0.8;">+<?php echo $stats['posts_today']; ?> today</small>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; padding: 1.5rem;">
                    <!-- Quick Actions -->
                    <div>
                        <h3><i class="fas fa-tools"></i> Quick Actions</h3>
                        <div class="quick-actions">
                            <a href="users.php" class="btn btn-primary" style="text-align: center; padding: 1rem;">
                                <i class="fas fa-users"></i><br>Manage Users
                            </a>
                            <a href="categories.php" class="btn btn-secondary" style="text-align: center; padding: 1rem;">
                                <i class="fas fa-folder"></i><br>Categories
                            </a>
                            <a href="reports.php" class="btn btn-warning" style="text-align: center; padding: 1rem;">
                                <i class="fas fa-chart-bar"></i><br>Reports
                            </a>
                            <a href="settings.php" class="btn btn-success" style="text-align: center; padding: 1rem;">
                                <i class="fas fa-cog"></i><br>Settings
                            </a>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div>
                        <h3><i class="fas fa-clock"></i> Recent Activity</h3>
                        <div style="background: var(--card-bg); border-radius: 8px; overflow: hidden;">
                            <?php foreach(array_slice($recent_activities, 0, 5) as $activity): ?>
                                <div class="activity-item">
                                    <div class="activity-icon" style="background: <?php echo $activity['type'] == 'user' ? '#3b82f6' : '#10b981'; ?>;">
                                        <i class="fas fa-<?php echo $activity['type'] == 'user' ? 'user-plus' : 'comment'; ?>" style="color: white;"></i>
                                    </div>
                                    <div>
                                        <p style="margin: 0; font-weight: 500;"><?php echo htmlspecialchars($activity['title']); ?></p>
                                        <small class="text-secondary"><?php echo date('M j, H:i', strtotime($activity['created_at'])); ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        
    </script>
</body>
</html>