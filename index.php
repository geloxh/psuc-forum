<?php
    require_once 'includes/auth.php';
    require_once 'includes/forum.php';
    require_once 'config/database.php';


    $auth = new Auth();
    $forum = new Forum();
    $user = $auth -> getCurrentUser();
    $categories = $forum -> getCategories();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Page - PSUC Forum</title>
    <!-- ===== CSS ===== -->
    <link rel="stylesheet" href="assets/stylesheets/main.css">
    <link rel="stylesheet" href="assets/stylesheets/dark-theme.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php 
        include 'includes/header.php'; 
    ?>

    <main class="container">
        <div class="main-content">
            <div class="forum-content">
                <div class="p-3">
                    <h1><i class="fas fa-comments"></i> Welcome to PSUC Forum</h1>
                    <p class="text-secondary mb-3">Connect, collaborate, and share knowledge with fellow students and faculty from Philippine State Universities and Colleges.</p>
                </div>

                <?php foreach($categories as $category): ?>
                    <div class="category">
                        <div class="category-header">
                            <i class="<?php echo $category['icon']; ?>"></i>
                            <?php echo htmlspecialchars($category['name']); ?>
                        </div>
                        <div class="forum-list">
                            <?php 
                            $forums = $forum->getForumsByCategory($category['id']);
                            foreach($forums as $f): 
                                $last_post_data = $f['last_post'] ? explode('|', $f['last_post']) : null;
                            ?>
                                <div class="forum-item">
                                    <div class="forum-info">
                                        <h4><a href="forum.php?id=<?php echo $f['id']; ?>"><?php echo htmlspecialchars($f['name']); ?></a></h4>
                                        <p><?php echo htmlspecialchars($f['description']); ?></p>
                                    </div>
                                    <div class="forum-stats">
                                        <strong><?php echo $f['topics_count']; ?></strong>
                                        Topics
                                    </div>
                                    <div class="forum-stats">
                                        <strong><?php echo $f['posts_count']; ?></strong>
                                        Posts
                                    </div>
                                    <div class="last-post">
                                        <?php if($last_post_data): ?>
                                            <strong><?php echo htmlspecialchars($last_post_data[1]); ?></strong><br>
                                            by <?php echo htmlspecialchars($last_post_data[0]); ?><br>
                                            <small><?php echo date('M j, Y g:i A', strtotime($last_post_data[2])); ?></small>
                                        <?php else: ?>
                                            <em>No posts yet</em>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <aside class="sidebar">
                <div class="widget">
                    <h3><i class="fas fa-chart-line"></i> Forum Statistics</h3>
                    <?php
                    $database = new Database();
                    $conn = $database->getConnection();
                    $stats_query = "SELECT 
                        (SELECT COUNT(*) FROM users) as total_users,
                        (SELECT COUNT(*) FROM topics) as total_topics,
                        (SELECT COUNT(*) FROM posts) as total_posts,
                        (SELECT username FROM users ORDER BY created_at DESC LIMIT 1) as newest_user";
                    $stmt = $conn->prepare($stats_query);
                    $stmt->execute();
                    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
                    ?>
                    <div class="stats-grid">
                        <div class="stat-item">
                            <strong><?php echo $stats['total_users']; ?></strong>
                            <span>Members</span>
                        </div>
                        <div class="stat-item">
                            <strong><?php echo $stats['total_topics']; ?></strong>
                            <span>Topics</span>
                        </div>
                        <div class="stat-item">
                            <strong><?php echo $stats['total_posts']; ?></strong>
                            <span>Posts</span>
                        </div>
                        <div class="stat-item">
                            <strong><?php echo htmlspecialchars($stats['newest_user'] ?? 'None'); ?></strong>
                            <span>Newest Member</span>
                        </div>
                    </div>
                </div>

                <div class="widget">
                    <h3><i class="fas fa-fire"></i> Recent Activity</h3>
                    <?php
                    $recent_query = "SELECT t.title, t.created_at, u.username, f.name as forum_name 
                                   FROM topics t 
                                   JOIN users u ON t.user_id = u.id 
                                   JOIN forums f ON t.forum_id = f.id 
                                   ORDER BY t.created_at DESC LIMIT 5";
                    $stmt = $conn->prepare($recent_query);
                    $stmt->execute();
                    $recent_topics = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    <?php foreach($recent_topics as $topic): ?>
                        <div class="activity-item mb-2">
                            <strong><?php echo htmlspecialchars($topic['title']); ?></strong><br>
                            <small>by <?php echo htmlspecialchars($topic['username']); ?> in <?php echo htmlspecialchars($topic['forum_name']); ?></small><br>
                            <small class="text-secondary"><?php echo date('M j, g:i A', strtotime($topic['created_at'])); ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="widget">
                    <h3><i class="fas fa-users"></i> Online Users</h3>
                    <?php
                    $online_query = "SELECT username FROM users WHERE last_active > DATE_SUB(NOW(), INTERVAL 15 MINUTE) ORDER BY last_active DESC LIMIT 10";
                    $stmt = $conn->prepare($online_query);
                    $stmt->execute();
                    $online_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    <?php if(count($online_users) > 0): ?>
                        <?php foreach($online_users as $online_user): ?>
                            <span class="badge mb-1"><?php echo htmlspecialchars($online_user['username']); ?></span>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-secondary">No users online</p>
                    <?php endif; ?>
                </div>
            </aside>
        </div>
    </main>
    <!-- ===== MAIN JS ===== -->
    <script src="assets/scripts/main.js"></script>
</body>
</html>