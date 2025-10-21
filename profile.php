<?php
    require_once 'includes/auth.php';

    $auth = new Auth();
    $user = $auth->getCurrentUser();

    if(!$user) {
        header('Location: login.php');
        exit;
    }

    $database = new Database();
    $conn = $database->getConnection();

    // Get user statistics
    $stats_query = "SELECT 
        (SELECT COUNT(*) FROM topics WHERE user_id = ?) as topics_created,
        (SELECT COUNT(*) FROM posts WHERE user_id = ?) as posts_made,
        (SELECT COUNT(*) FROM votes WHERE user_id = ?) as votes_cast,
        (SELECT COUNT(*) FROM messages WHERE sender_id = ?) as messages_sent";

    $stmt = $conn -> prepare($stats_query);
    $stmt -> execute([$user['id'], $user['id'], $user['id'], $user['id']]);
    $stats = $stmt -> fetch(PDO::FETCH_ASSOC);

    // Get recent topics
    $recent_topics_query = "SELECT t.*, f.name as forum_name FROM topics t 
                        JOIN forums f ON t.forum_id = f.id 
                        WHERE t.user_id = ? ORDER BY t.created_at DESC LIMIT 5";
    $stmt = $conn -> prepare($recent_topics_query);
    $stmt -> execute([$user['id']]);
    $recent_topics = $stmt -> fetchAll(PDO::FETCH_ASSOC);

?>

<?php
    if(!isset($_SESSION)) { 
        session_start(); 
    }

    if(isset($_SESSION['upload_error'])) {
        echo '<div class="alert alert-danger">' . $_SESSION['upload_error'] . '</div>';
        unset($_SESSION['upload_error']);
    }
    
    if(isset($_SESSION['upload_success'])) {
        echo '<div class="alert alert-success">' . $_SESSION['upload_success'] . '</div>';
        unset($_SESSION['upload_success']);
    }
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - PSUC Forum</title>
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
                    <div style="display: flex; align-items: center; gap: 2rem; margin-bottom: 2rem;">
                        <img src="assets/avatars/<?php echo $user['avatar']; ?>" alt="Avatar" 
                             style="width: 120px; height: 120px; border-radius: 50%; border: 4px solid var(--primary-color);"
                             onerror="this.src='https://via.placeholder.com/120/007bff/ffffff?text=<?php echo strtoupper(substr($user['username'], 0, 1)); ?>'">
                        <div>
                            <h1><?php echo htmlspecialchars($user['full_name']); ?></h1>
                            <p class="text-secondary">@<?php echo htmlspecialchars($user['username']); ?></p>
                            <div style="margin: 1rem 0;">
                                <span class="badge" style="background: var(--primary-color); margin-right: 0.5rem;">
                                    <?php echo ucfirst($user['role']); ?>
                                </span>
                                <span class="badge" style="background: var(--success-color);">
                                    <i class="fas fa-star"></i> <?php echo $user['reputation']; ?>Reputation
                                </span>
                            </div>
                            <p><i class="fas fa-university"></i> <?php echo htmlspecialchars($user['university']); ?></p>
                            <p><i class="fas fa-calendar"></i>Joined<?php echo date('M Y', strtotime($user['created_at'])); ?></p>
                        </div>
                    </div>

                    <div class="widget" style="margin-bottom: 2rem;">
                        <h3><i class="fas fa-camera"></i>Change Avatar</h3>
                        <form action="upload_avatar.php" method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <input type="file" name="avatar" id="avatar" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Upload</button>
                        </form>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
                        <div class="stat-item">
                            <strong><?php echo $stats['topics_created']; ?></strong>
                            <span>Topics Created</span>
                        </div>
                        <div class="stat-item">
                            <strong><?php echo $stats['posts_made']; ?></strong>
                            <span>Posts Made</span>
                        </div>
                        <div class="stat-item">
                            <strong><?php echo $stats['votes_cast']; ?></strong>
                            <span>Votes Cast</span>
                        </div>
                        <div class="stat-item">
                            <strong><?php echo $stats['messages_sent']; ?></strong>
                            <span>Messages Sent</span>
                        </div>
                    </div>

                    <h3><i class="fas fa-history"></i> Recent Topics</h3>
                    <?php if(count($recent_topics) > 0): ?>
                        <?php foreach($recent_topics as $topic): ?>
                            <div class="activity-item mb-2">
                                <strong><a href="topic.php?id=<?php echo $topic['id']; ?>" style="text-decoration: none;">
                                    <?php echo htmlspecialchars($topic['title']); ?>
                                </a></strong><br>
                                <small>in <?php echo htmlspecialchars($topic['forum_name']); ?> • 
                                <?php echo date('M j, Y g:i A', strtotime($topic['created_at'])); ?></small>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-secondary">No topics created yet.</p>
                    <?php endif; ?>
                </div>
            </div>

            <aside class="sidebar">
                <div class="widget">
                    <h3><i class="fas fa-cog"></i>Profile Actions</h3>
                    <a href="settings.php" class="btn btn-primary" style="width: 100%; margin-bottom: 0.5rem;">
                        <i class="fas fa-edit"></i>Edit Profile
                    </a>
                    <a href="messages.php" class="btn btn-secondary" style="width: 100%; margin-bottom: 0.5rem;">
                        <i class="fas fa-envelope"></i>Messages
                    </a>
                    <a href="notifications.php" class="btn btn-success" style="width: 100%;">
                        <i class="fas fa-bell"></i>Notifications
                    </a>
                </div>

                <div class="widget">
                    <h3><i class="fas fa-share-alt"></i>Social Media</h3>
                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                        <a href="#" class="btn" style="background: #1877f2; color: white; padding: 0.5rem;">
                            <i class="fab fa-facebook"></i>
                        </a>
                        <a href="#" class="btn" style="background: #1da1f2; color: white; padding: 0.5rem;">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="btn" style="background: #0077b5; color: white; padding: 0.5rem;">
                            <i class="fab fa-linkedin"></i>
                        </a>
                        <a href="#" class="btn" style="background: #25d366; color: white; padding: 0.5rem;">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    </div>
                    <p style="font-size: 0.8rem; margin-top: 1rem; color: var(--text-secondary);">
                        Share your profile or connect on social media
                    </p>
                </div>

                <div class="widget">
                    <h3><i class="fas fa-trophy"></i>Achievements</h3>
                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                        <?php if($stats['topics_created'] >= 10): ?>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <i class="fas fa-medal" style="color: #ffd700;"></i>
                                <span>Topic Creator</span>
                            </div>
                        <?php endif; ?>
                        <?php if($user['reputation'] >= 50): ?>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <i class="fas fa-star" style="color: #ffd700;"></i>
                                <span>Respected Member</span>
                            </div>
                        <?php endif; ?>
                        <?php if($stats['posts_made'] >= 100): ?>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <i class="fas fa-comments" style="color: #ffd700;"></i>
                                <span>Active Contributor</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </aside>
        </div>
    </main>

    <script src="assets/scripts/main.js"></script>
</body>
</html>