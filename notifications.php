<?php
    require_once 'includes/auth.php';
    require_once 'includes/forum.php';

    $auth = new Auth();
    $forum = new Forum();
    $user = $auth->getCurrentUser();

    if(!$user) {
        header('Location: login.php');
        exit;
    }

    // Mark notification as read
    if(isset($_GET['action']) && $_GET['action'] == 'read' && isset($_GET['id'])) {
        $forum -> markNotificationRead($_GET['id'], $user['id']);
        if(isset($_GET['url']) && $_GET['url']) {
            header('Location: ' . urldecode($_GET['url']));
            exit;
        }
    }

    $notifications = $forum -> getNotifications($user['id'], 50);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - PSUC Forum</title>
    <link rel="stylesheet" href="assets/stylesheets/main.css">
    <link rel="stylesheet" href="assets/stylesheets/dark-theme.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container">
        <div class="main-content">
            <div class="forum-content">
                <div class="p-3">
                    <h1><i class="fas fa-bell"></i> Notifications</h1>
                    <p class="text-secondary">Stay updated with your forum activity</p>
                </div>

                <?php if(count($notifications) > 0): ?>
                    <?php foreach($notifications as $notification): ?>
                        <div class="notification-item" style="padding: 1rem 1.5rem; border-bottom: 1px solid var(--border-color); <?php echo !$notification['is_read'] ? 'background: rgba(59, 130, 246, 0.05);' : ''; ?>">
                            <div style="display: flex; justify-content: space-between; align-items: start;">
                                <div style="flex: 1;">
                                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                                        <i class="fas fa-<?php echo $notification['type'] == 'welcome' ? 'hand-wave' : ($notification['type'] == 'reply' ? 'reply' : 'info-circle'); ?>" style="color: var(--primary-color);"></i>
                                        <strong><?php echo htmlspecialchars($notification['title']); ?></strong>
                                        <?php if(!$notification['is_read']): ?>
                                            <span class="badge" style="background: var(--primary-color);">New</span>
                                        <?php endif; ?>
                                    </div>
                                    <p style="margin: 0; color: var(--text-secondary);">
                                        <?php echo htmlspecialchars($notification['content']); ?>
                                    </p>
                                    <small class="text-secondary">
                                        <?php echo date('M j, Y g:i A', strtotime($notification['created_at'])); ?>
                                    </small>
                                </div>
                                <?php if($notification['url']): ?>
                                    <a href="notifications.php?action=read&id=<?php echo $notification['id']; ?>&url=<?php echo urlencode($notification['url']); ?>" 
                                       class="btn btn-primary" style="padding: 0.5rem 1rem;">
                                        View
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="p-3 text-center">
                        <i class="fas fa-bell-slash" style="font-size: 3rem; color: var(--text-secondary); margin-bottom: 1rem;"></i>
                        <h3>No notifications</h3>
                        <p class="text-secondary">You're all caught up! New notifications will appear here.</p>
                    </div>
                <?php endif; ?>
            </div>

            <aside class="sidebar">
                <div class="widget">
                    <h3><i class="fas fa-cog"></i> Notification Settings</h3>
                    <p style="font-size: 0.9rem; line-height: 1.6;">
                        Manage your notification preferences to stay informed about forum activity.
                    </p>
                    <div style="margin-top: 1rem;">
                        <label style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                            <input type="checkbox" checked> New replies to my topics
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                            <input type="checkbox" checked> Private messages
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.5rem;">
                            <input type="checkbox"> Weekly digest
                        </label>
                    </div>
                </div>

                <div class="widget">
                    <h3><i class="fas fa-chart-line"></i> Your Activity</h3>
                    <?php
                    $database = new Database();
                    $conn = $database->getConnection();
                    $activity_query = "SELECT 
                        (SELECT COUNT(*) FROM topics WHERE user_id = ?) as topics_created,
                        (SELECT COUNT(*) FROM posts WHERE user_id = ?) as posts_made,
                        (SELECT COUNT(*) FROM votes WHERE user_id = ?) as votes_cast";
                    $stmt = $conn->prepare($activity_query);
                    $stmt->execute([$user['id'], $user['id'], $user['id']]);
                    $activity = $stmt->fetch(PDO::FETCH_ASSOC);
                    ?>
                    <div class="stats-grid">
                        <div class="stat-item">
                            <strong><?php echo $activity['topics_created']; ?></strong>
                            <span>Topics</span>
                        </div>
                        <div class="stat-item">
                            <strong><?php echo $activity['posts_made']; ?></strong>
                            <span>Posts</span>
                        </div>
                        <div class="stat-item">
                            <strong><?php echo $activity['votes_cast']; ?></strong>
                            <span>Votes</span>
                        </div>
                        <div class="stat-item">
                            <strong><?php echo $user['reputation']; ?></strong>
                            <span>Reputation</span>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </main>

    <!-- ===== MAIN JS ===== -->
    <script src="assets/scripts/main.js"></script>
    
</body>
</html>