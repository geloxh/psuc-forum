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
 <?php
        include 'includes/dropdown-sidebar.php';
    ?>
<body>
    <?php 
        include 'includes/header.php'; 
    ?>
    <?php
        renderDropdownSidebar();
    ?>

    <main class="container">
        <div class="main-content">
            <div class="forum-content">
                <div class="p-3">
                    <h1><i class="fas fa-comments"></i>Welcome to Philippines State Universities and Colleges Forum</h1>
                    <p class="text-secondary mb-3">Connect, collaborate, and share knowledge with fellow students and faculty from Philippine State Universities and Colleges.</p>
                </div>

                <div class="categories-grid">
                    <?php foreach($categories as $category): ?>
                        <div class="category-card">
                            <div class="category-card-header">
                                <div class="category-icon">
                                    <i class="<?php echo $category['icon']; ?>"></i>
                                </div>
                                <div class="category-info">
                                    <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                                    <p><?php echo htmlspecialchars($category['description']); ?></p>
                                </div>
                            </div>
                            <div class="category-forums">
                                <?php 
                                $forums = $forum->getForumsByCategory($category['id']);
                                foreach($forums as $f): 
                                    $last_post_data = $f['last_post'] ? explode('|', $f['last_post']) : null;
                                ?>
                                    <div class="forum-card" onclick="location.href='forum.php?id=<?php echo $f['id']; ?>'">
                                        <div class="forum-card-header">
                                            <h4 class="forum-title">
                                                <a href="forum.php?id=<?php echo $f['id']; ?>"><?php echo htmlspecialchars($f['name']); ?></a>
                                            </h4>
                                            <div class="forum-stats">
                                                <div class="forum-stat">
                                                    <i class="fas fa-comments"></i>
                                                    <span><?php echo $f['topics_count']; ?></span>
                                                </div>
                                                <div class="forum-stat">
                                                    <i class="fas fa-reply"></i>
                                                    <span><?php echo $f['posts_count']; ?></span>
                                                </div>
                                            </div>
                                        </div>
                                        <p class="forum-description"><?php echo htmlspecialchars($f['description']); ?></p>
                                        <div class="forum-meta">
                                            <span><?php echo $f['topics_count']; ?> topics, <?php echo $f['posts_count']; ?> posts</span>
                                            <div class="last-post-info">
                                                <?php if($last_post_data): ?>
                                                    <strong><?php echo htmlspecialchars($last_post_data[1]); ?></strong><br>
                                                    by <?php echo htmlspecialchars($last_post_data[0]); ?><br>
                                                    <small><?php echo date('M j, Y g:i A', strtotime($last_post_data[2])); ?></small>
                                                <?php else: ?>
                                                    <em>No posts yet</em>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <aside class="sidebar">
    <!-- Forum Statistics Widget -->
    <div class="widget">
        <div class="widget-header">
            <div class="widget-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <h3>Forum Statistics</h3>
        </div>
        <?php
        $database = new Database();
        $conn = $database -> getConnection();
        $stats_query = "SELECT 
            (SELECT COUNT(*) FROM users) as total_users,
            (SELECT COUNT(*) FROM topics) as total_topics,
            (SELECT COUNT(*) FROM posts) as total_posts,
            (SELECT username FROM users ORDER BY created_at DESC LIMIT 1) as newest_user";
        $stmt = $conn -> prepare($stats_query);
        $stmt -> execute();
        $stats = $stmt -> fetch(PDO::FETCH_ASSOC);
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

    <!-- Quick Actions Widget -->
    <div class="widget">
        <div class="widget-header">
            <div class="widget-icon">
                <i class="fas fa-bolt"></i>
            </div>
            <h3>Quick Actions</h3>
        </div>
        <div class="quick-actions">
            <a href="new_topic.php" class="action-btn">
                <i class="fas fa-plus"></i>
                <span>New Topic</span>
            </a>
            <a href="search.php" class="action-btn">
                <i class="fas fa-search"></i>
                <span>Search</span>
            </a>
            <a href="messages.php" class="action-btn">
                <i class="fas fa-envelope"></i>
                <span>Messages</span>
            </a>
            <a href="profile.php" class="action-btn">
                <i class="fas fa-user"></i>
                <span>Profile</span>
            </a>
        </div>
    </div>

    <!-- Recent Activity Widget -->
    <div class="widget">
        <div class="widget-header">
            <div class="widget-icon">
                <i class="fas fa-fire"></i>
            </div>
            <h3>Recent Activity</h3>
        </div>
        <?php
        $recent_query = "SELECT t.title, t.created_at, u.username, f.name as forum_name 
                       FROM topics t 
                       JOIN users u ON t.user_id = u.id 
                       JOIN forums f ON t.forum_id = f.id 
                       ORDER BY t.created_at DESC LIMIT 5";
        $stmt = $conn -> prepare($recent_query);
        $stmt -> execute();
        $recent_topics = $stmt -> fetchAll(PDO::FETCH_ASSOC);
        ?>
        <div class="activity-list">
            <?php foreach($recent_topics as $topic): ?>
                <div class="activity-item">
                    <span class="activity-title"><?php echo htmlspecialchars($topic['title']); ?></span>
                    <div class="activity-meta">
                        <span>by <?php echo htmlspecialchars($topic['username']); ?> in <?php echo htmlspecialchars($topic['forum_name']); ?></span>
                        <span class="activity-time"><?php echo date('M j, g:i A', strtotime($topic['created_at'])); ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Trending Topics Widget -->
    <div class="widget">
        <div class="widget-header">
            <div class="widget-icon">
                <i class="fas fa-trending-up"></i>
            </div>
            <h3>Trending Topics</h3>
        </div>
        <?php
        $trending_query = "SELECT t.title, t.views, u.username, 
                          (SELECT COUNT(*) FROM posts p WHERE p.topic_id = t.id) as reply_count
                          FROM topics t 
                          JOIN users u ON t.user_id = u.id 
                          ORDER BY t.views DESC, reply_count DESC LIMIT 5";
        $stmt = $conn -> prepare($trending_query);
        $stmt -> execute();
        $trending_topics = $stmt -> fetchAll(PDO::FETCH_ASSOC);
        ?>
        <div class="trending-list">
            <?php foreach($trending_topics as $index => $topic): ?>
                <div class="trending-item">
                    <div class="trending-rank"><?php echo $index + 1; ?></div>
                    <div class="trending-content">
                        <div class="trending-title"><?php echo htmlspecialchars($topic['title']); ?></div>
                        <div class="trending-meta"><?php echo $topic['views']; ?> views • <?php echo $topic['reply_count']; ?> replies</div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Online Users Widget -->
    <div class="widget">
        <div class="widget-header">
            <div class="widget-icon">
                <i class="fas fa-users"></i>
            </div>
            <h3>Online Users</h3>
        </div>
        <?php
        $online_query = "SELECT username FROM users WHERE last_active > DATE_SUB(NOW(), INTERVAL 15 MINUTE) ORDER BY last_active DESC LIMIT 10";
        $stmt = $conn -> prepare($online_query);
        $stmt -> execute();
        $online_users = $stmt -> fetchAll(PDO::FETCH_ASSOC);
        ?>
        <div class="online-users">
            <?php if(count($online_users) > 0): ?>
                <?php foreach($online_users as $online_user): ?>
                    <div class="user-badge">
                        <div class="user-avatar"><?php echo strtoupper(substr($online_user['username'], 0, 1)); ?></div>
                        <span><?php echo htmlspecialchars($online_user['username']); ?></span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-secondary">No users online</p>
            <?php endif; ?>
        </div>
    </div>
</aside>

        </div>
    </main>
    <!-- ===== MAIN JS ===== -->
    <script src="assets/scripts/main.js"></script>
</body>
</html>
