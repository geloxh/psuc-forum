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
        include 'includes/web_sidebar.php';
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

                <div class="timeline-feed">
                    <?php
                    $database = new Database();
                    $conn = $database->getConnection();
                    $topics_query = "SELECT 
                                        t.id,
                                        t.title,
                                        t.content,
                                        t.created_at,
                                        t.views,
                                        u.username,
                                        u.avatar,
                                        f.name as forum_name,
                                        (SELECT COUNT(*) FROM posts p WHERE p.topic_id = t.id) as reply_count
                                    FROM 
                                        topics t
                                    JOIN 
                                        users u ON t.user_id = u.id
                                    JOIN 
                                        forums f ON t.forum_id = f.id
                                    ORDER BY 
                                        t.created_at DESC";
                    $stmt = $conn->prepare($topics_query);
                    $stmt->execute();
                    $topics = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach($topics as $topic):
                    ?>
                        <div class="topic-card">
                            <div class="topic-card-header">
                                <img src="assets/avatars/<?php echo htmlspecialchars($topic['avatar']); ?>" alt="<?php echo htmlspecialchars($topic['username']); ?>'s avatar" class="avatar">
                                <div class="topic-info">
                                    <h3 class="topic-title"><a href="topic.php?id=<?php echo $topic['id']; ?>"><?php echo htmlspecialchars($topic['title']); ?></a></h3>
                                    <p class="topic-meta">
                                        Posted by <a href="profile.php?username=<?php echo htmlspecialchars($topic['username']); ?>"><?php echo htmlspecialchars($topic['username']); ?></a>
                                        in <a href="forum.php?name=<?php echo urlencode($topic['forum_name']); ?>"><?php echo htmlspecialchars($topic['forum_name']); ?></a>
                                        - <span class="topic-time"><?php echo date('M j, Y g:i A', strtotime($topic['created_at'])); ?></span>
                                    </p>
                                </div>
                            </div>
                            <div class="topic-content">
                                <?php
                                $content_snippet = strip_tags($topic['content']);
                                if (strlen($content_snippet) > 200) {
                                    $content_snippet = substr($content_snippet, 0, 200) . '...';
                                }
                                echo $content_snippet;
                                ?>
                            </div>
                            <div class="topic-footer">
                                <div class="topic-stat">
                                    <i class="fas fa-eye"></i>
                                    <span><?php echo $topic['views']; ?></span>
                                </div>
                                <div class="topic-stat">
                                    <i class="fas fa-reply"></i>
                                    <span><?php echo $topic['reply_count']; ?></span>
                                </div>
                                <a href="topic.php?id=<?php echo $topic['id']; ?>" class="read-more-btn">Read More</a>
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
            <a href="includes/new_topic.php" class="action-btn">
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
