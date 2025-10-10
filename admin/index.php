<?php
    require_once '../includes/auth.php';

    $auth = new Auth();

    if(!$auth -> isAdmin()) {
        header('Location: ../login.php');
        exit;
    }

    $user = $auth -> getCurrentUser();
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

    // Get recent users
    $recent_users_query = "SELECT username, full_name, created_at FROM users ORDER BY created_at DESC LIMIT 5";
    $stmt = $conn -> prepare($recent_users_query);
    $stmt -> execute();
    $recent_users = $stmt -> fetchAll(PDO::FETCH_ASSOC);

    // Get top topics
    $top_topics_query = "SELECT t.title, t.created_at, u.username, 
        (SELECT COUNT(*) FROM posts p WHERE p.topic_id = t.id) as reply_count 
            FROM topics t JOIN users u ON t.user_id = u.id 
            ORDER BY reply_count DESC LIMIT 5";
    $stmt = $conn -> prepare($top_topics_query);
    $stmt -> execute();
    $top_topics = $stmt -> fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - PSUC Forum</title>
    <link rel="stylesheet" href="assets/stylesheets/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php
        include __DIR__ . '/includes/header.php';

    ?>

     <div class="dashboard-container">
        <div class="container">
            <!-- Welcome Section -->
            <div class="welcome-section">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <h1 style="margin: 0; color: #2d3748; font-size: 2rem;">
                            Welcome back, <?php echo htmlspecialchars($user['full_name']); ?>! 👋
                        </h1>
                        <p style="margin: 0.5rem 0 0 0; color: #718096;">
                            Here's what's happening with your forum today.
                        </p>
                    </div>
                    <div style="text-align: right; color: #718096;">
                        <div><?php echo date('l, F j, Y'); ?></div>
                        <div id="current-time"><?php echo date('g:i A'); ?></div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="stat-number"><?php echo number_format($stats['total_users']); ?></h3>
                    <p class="stat-label">Total Users</p>
                    <div class="stat-change">+<?php echo $stats['new_users_week']; ?> this week</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-comments"></i>
                    </div>
                    <h3 class="stat-number"><?php echo number_format($stats['total_topics']); ?></h3>
                    <p class="stat-label">Total Topics</p>
                    <div class="stat-change">+<?php echo $stats['topics_today']; ?> today</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-reply"></i>
                    </div>
                    <h3 class="stat-number"><?php echo number_format($stats['total_posts']); ?></h3>
                    <p class="stat-label">Total Posts</p>
                    <div class="stat-change">+<?php echo $stats['posts_today']; ?> today</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 class="stat-number"><?php echo round(($stats['posts_today'] + $stats['topics_today']) / max($stats['total_users'], 1) * 100, 1); ?>%</h3>
                    <p class="stat-label">Activity Rate</p>
                    <div class="stat-change">Daily engagement</div>
                </div>
            </div>

            <!-- Dashboard Grid -->
            <div class="dashboard-grid">
                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-bolt" style="color: #667eea;"></i>
                        <h3 class="card-title">Quick Actions</h3>
                    </div>
                    <div class="quick-actions">
                        <a href="users.php" class="action-btn">
                            <i class="fas fa-users" style="color: #667eea;"></i>
                            <span>Manage Users</span>
                        </a>
                        <a href="categories.php" class="action-btn">
                            <i class="fas fa-folder" style="color: #f093fb;"></i>
                            <span>Categories</span>
                        </a>
                        <a href="reports.php" class="action-btn">
                            <i class="fas fa-chart-bar" style="color: #4facfe;"></i>
                            <span>Reports</span>
                        </a>
                        <a href="settings.php" class="action-btn">
                            <i class="fas fa-cog" style="color: #43e97b;"></i>
                            <span>Settings</span>
                        </a>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div>
                    <!-- Recent Users -->
                    <div class="card" style="margin-bottom: 1.5rem;">
                        <div class="card-header">
                            <i class="fas fa-user-plus" style="color: #48bb78;"></i>
                            <h3 class="card-title">Recent Users</h3>
                        </div>
                        <?php foreach($recent_users as $recent_user): ?>
                            <div class="list-item">
                                <div class="list-avatar">
                                    <?php echo strtoupper(substr($recent_user['username'], 0, 1)); ?>
                                </div>
                                <div class="list-content">
                                    <p class="list-title"><?php echo htmlspecialchars($recent_user['full_name']); ?></p>
                                    <p class="list-subtitle">@<?php echo htmlspecialchars($recent_user['username']); ?></p>
                                </div>
                                <div class="badge">
                                    <?php echo date('M j', strtotime($recent_user['created_at'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Top Topics -->
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-fire" style="color: #f56565;"></i>
                            <h3 class="card-title">Popular Topics</h3>
                        </div>
                        <?php foreach($top_topics as $topic): ?>
                            <div class="list-item">
                                <div class="list-avatar" style="background: var(--gradient-2);">
                                    <i class="fas fa-comment"></i>
                                </div>
                                <div class="list-content">
                                    <p class="list-title"><?php echo htmlspecialchars(substr($topic['title'], 0, 30)) . '...'; ?></p>
                                    <p class="list-subtitle">by <?php echo htmlspecialchars($topic['username']); ?></p>
                                </div>
                                <div class="badge">
                                    <?php echo $topic['reply_count']; ?> replies
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/scripts/main.js"></script>
</body>
</html>