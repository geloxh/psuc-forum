<?php
    require_once 'includes/auth.php';
    require_once 'includes/forum.php';
    require_once 'includes/web_sidebar.php';
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
    <link rel="stylesheet" href="assets/stylesheets/media-preview.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
    /* Mobile-First Responsive Styles */

    .timeline-feed { max-width: 100%; padding: 0; }
    .empty-feed { text-align: center; padding: 40px 16px; color: #65676b; }
    .empty-feed i { font-size: 48px; color: #e4e6ea; margin-bottom: 16px; }
    .empty-feed h3 { font-size: 18px; font-weight: 600; margin: 0 0 8px 0; color: #1c1e21; }
    .empty-feed p { margin: 0 0 24px 0; font-size: 14px; line-height: 1.4; }
    .btn-create { background: #1877f2; color: white; padding: 12px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 15px; transition: all 0.2s; display: inline-block; }
    .btn-create:hover { background: #166fe5; transform: translateY(-1px); }
    
    /* Post Cards - Mobile */
    .post { background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); margin-bottom: 16px; border: 1px solid #e4e6ea; overflow: hidden; }
    .post-header { padding: 16px 16px 0; }
    .user-info { display: flex; align-items: center; gap: 12px; }
    .user-avatar { width: 44px; height: 44px; border-radius: 50%; background: linear-gradient(135deg, #e4e6ea, #f0f2f5); display: flex; align-items: center; justify-content: center; font-weight: 600; color: #65676b; font-size: 16px; flex-shrink: 0; }
    .user-details { flex: 1; min-width: 0; }
    .username { font-weight: 600; color: #1c1e21; text-decoration: none; font-size: 15px; line-height: 1.2; }
    .username:hover { text-decoration: underline; }
    .post-meta { font-size: 13px; color: #65676b; margin-top: 4px; display: flex; flex-wrap: wrap; gap: 8px; }
    .post-meta a { color: #1877f2; text-decoration: none; font-weight: 500; }
    .post-meta a:hover { text-decoration: underline; }
    .post-content { padding: 16px; }
    .post-title { margin: 0 0 12px 0; font-size: 18px; font-weight: 600; line-height: 1.3; }
    .post-title a { color: #1c1e21; text-decoration: none; }
    .post-title a:hover { color: #1877f2; }
    .post-text { color: #1c1e21; font-size: 15px; line-height: 1.4; margin-bottom: 16px; }
    .post-media { width: 100%; margin-bottom: 16px; border-radius: 8px; overflow: hidden; }
    .post-footer { border-top: 1px solid #e4e6ea; padding: 12px 16px; display: flex; justify-content: space-between; align-items: center; background: #fafbfc; }
    .post-stats { font-size: 13px; color: #65676b; display: flex; gap: 16px; }
    .post-actions { display: flex; gap: 8px; }
    .action-btn { color: #65676b; text-decoration: none; padding: 8px 16px; border-radius: 8px; font-size: 14px; font-weight: 600; transition: all 0.2s; display: flex; align-items: center; gap: 6px; min-height: 44px; }
    .action-btn:hover { background: #f2f3f5; color: #1877f2; }
    
    /* Media Items - Responsive */
    .media-item { position: relative; border-radius: 8px; overflow: hidden; width: 100%; max-width: 100%; height: 250px; }
    .media-item img, .media-item video { width: 100%; height: 100%; object-fit: cover; }
    .media-overlay { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: rgba(0,0,0,0.7); color: white; padding: 16px; border-radius: 50%; }
    .file-preview { display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 200px; background: #f8f9fa; color: #6c757d; text-align: center; padding: 20px; }
    .file-preview i { font-size: 2.5rem; margin-bottom: 12px; }
    .file-preview span { font-size: 0.9rem; word-break: break-word; }
    
    /* Hero Section - Mobile */
    .hero-section { 
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.05), rgba(147, 197, 253, 0.05));
        border-radius: 16px;
        padding: 24px 16px;
        margin-bottom: 24px;
        text-align: center;
        border: 1px solid rgba(59, 130, 246, 0.1);
    }
    .hero-title { font-size: 24px; font-weight: 600; margin: 0 0 12px 0; line-height: 1.2; }
    .hero-subtitle { font-size: 16px; color: var(--text-secondary); line-height: 1.4; }
    
    .main-content {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1rem;
        padding: 2rem 0;
        max-width: 1200px;
        margin: 0 auto;
    }
    /* Sidebar - Mobile Handling */
    .sidebar-right { display: none; }
    
    /* Mobile Responsive Breakpoints */
    @media (min-width: 768px) {
        .timeline-feed { padding: 0; }
        .empty-feed { padding: 60px 20px; }
        .empty-feed h3 { font-size: 20px; }
        .empty-feed p { font-size: 15px; }
        .post-header { padding: 16px 20px 0; }
        .post-content { padding: 16px 20px; }
        .post-footer { padding: 12px 20px; }
        .post-title { font-size: 20px; }
        .hero-section { padding: 40px 32px; }
        .hero-title { font-size: 32px; }
        .hero-subtitle { font-size: 18px; }
        .media-item { height: 300px; }
        .main-content { grid-template-columns: 1fr 280px; gap: 2rem; }
        .sidebar-right { display: block; position: sticky; top: 100px; height: fit-content; max-height: calc(100vh - 120px); overflow-y: auto; } }
    
    @media (min-width: 1024px) {
        .hero-section { padding: 48px 40px; }
        .hero-title { font-size: 36px; }
        .media-item { height: 350px; max-width: 600px; }
    }
    
    /* Touch Improvements */
    @media (hover: none) and (pointer: coarse) {
        .action-btn { min-height: 48px; padding: 12px 16px; }
        .btn-create { padding: 16px 24px; font-size: 16px; }
        .post { margin-bottom: 20px; }
        .user-avatar { width: 48px; height: 48px; }
    }
    
    /* Very Small Screens */
    @media (max-width: 360px) {
        .timeline-feed { padding: 0 8px; }
        .post { border-radius: 8px; margin-bottom: 12px; }
        .post-header { padding: 12px 12px 0; }
        .post-content { padding: 12px; }
        .post-footer { padding: 8px 12px; }
        .hero-section { padding: 20px 12px; margin-bottom: 16px; }
        .hero-title { font-size: 20px; }
        .hero-subtitle { font-size: 14px; }
        .user-info { gap: 8px; }
        .user-avatar { width: 40px; height: 40px; }
        .post-stats { gap: 12px; font-size: 12px; }
        .action-btn { padding: 6px 12px; font-size: 13px; }
    }
    
    /* Activity List Styles for Mobile */
    .activity-list { display: flex; flex-direction: column; gap: 12px; }
    .activity-item { padding: 12px; background: rgba(248, 250, 252, 0.6); border-radius: 8px; border: 1px solid rgba(229, 231, 235, 0.3); }
    .activity-title { font-weight: 500; color: var(--text-primary); font-size: 14px; text-decoration: none; display: block; margin-bottom: 4px; line-height: 1.3; }
    .activity-title:hover { color: var(--secondary-blue); }
    .activity-meta { font-size: 12px; color: var(--text-secondary); }
    .activity-time { display: block; margin-top: 2px; }
    </style>
</head>

<body>

    <?php include 'includes/header.php'; ?>
    <?php renderDropdownSidebar(); ?>

    <main class="container">
        <div class="main-content">
            <div class="forum-content">
                <div class="hero-section">
                    <div class="hero-content">
                        <h1 class="hero-title"><i class="fas fa-graduation-cap"></i> Welcome to PSUC Forum</h1>
                        <p class="hero-subtitle">Connect, collaborate, and share knowledge with fellow students and faculty from Philippine State Universities and Colleges.</p>
                    </div>
                </div>

                <div class="timeline-feed">
                    <?php
                    try {
                        $database = new Database();
                        $conn = $database -> getConnection();
                        
                        if (!$conn) {
                            throw new Exception('Database connection failed');
                        }
                        
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
                                            t.created_at DESC
                                        LIMIT 10";
                        $stmt = $conn -> prepare($topics_query);
                        $stmt -> execute();
                        $topics = $stmt -> fetchAll(PDO::FETCH_ASSOC);
                        
                        foreach($topics as $key => $topic) {
                            $attachment_query = "SELECT file_path, file_type FROM attachments
                                                 WHERE topic_id = ?
                                                 ORDER BY uploaded_at ASC";
                            $attachment_stmt = $conn -> prepare($attachment_query);
                            $attachment_stmt -> execute([$topic['id']]);
                            $topics[$key]['attachments'] = $attachment_stmt -> fetchAll(PDO::FETCH_ASSOC);
                        }

                        if (empty($topics)):
                    ?>
                        <div class="empty-feed">
                            <i class="fas fa-comments"></i>
                            <h3>No posts yet</h3>
                            <p>Be the first to share something with the community</p>
                            <?php if ($user): ?>
                                <a href="new_topic.php" class="btn-create">Create Post</a>
                            <?php else: ?>
                                <a href="login.php" class="btn-create">Sign In</a>
                            <?php endif; ?>
                        </div>
                    <?php
                    else:
                        foreach($topics as $topic):
                    ?>
                        <article class="post">
                            <header class="post-header">
                                <div class="user-info">
                                    <?php if (!empty($topic['avatar'])): ?>
                                        <img src="assets/avatars/<?php echo htmlspecialchars($topic['avatar']); ?>" alt="" class="user-avatar">
                                    <?php else: ?>
                                        <div class="user-avatar">
                                            <?php echo strtoupper(substr($topic['username'], 0, 1)); ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="user-details">
                                        <a href="profile.php?username=<?php echo htmlspecialchars($topic['username']); ?>" class="username"><?php echo htmlspecialchars($topic['username']); ?></a>
                                        <div class="post-meta">
                                            <span><?php echo date('M j \a\t g:i A', strtotime($topic['created_at'])); ?></span>
                                            <span>•</span>
                                            <a href="forum.php?name=<?php echo urlencode($topic['forum_name']); ?>"><?php echo htmlspecialchars($topic['forum_name']); ?></a>
                                        </div>
                                    </div>
                                </div>
                            </header>
                            
                            <div class="post-content">
                                <h2 class="post-title">
                                    <a href="topic.php?id=<?php echo $topic['id']; ?>"><?php echo htmlspecialchars($topic['title']); ?></a>
                                </h2>
                                <div class="post-text">
                                    <?php
                                    $content = strip_tags($topic['content']);
                                    echo strlen($content) > 200 ? substr($content, 0, 200) . '...' : $content;
                                    ?>
                                </div>
                                
                                <?php if (!empty($topic['attachments'])): ?>
                                    <div class="post-media">
                                        <?php foreach (array_slice($topic['attachments'], 0, 4) as $attachment): ?>
                                            <?php if (strpos($attachment['file_type'], 'image/') === 0): ?>
                                                <div class="media-item">
                                                    <img src="<?php echo htmlspecialchars($attachment['file_path']); ?>" alt="Image attachment" loading="lazy">
                                                </div>
                                            <?php elseif (strpos($attachment['file_type'], 'video/') === 0): ?>
                                                <div class="media-item video-item">
                                                    <video controls preload="metadata">
                                                        <source src="<?php echo htmlspecialchars($attachment['file_path']); ?>" type="<?php echo htmlspecialchars($attachment['file_type']); ?>">
                                                    </video>
                                                    <div class="media-overlay"><i class="fas fa-play"></i></div>
                                                </div>
                                            <?php else: ?>
                                                <div class="media-item file-item">
                                                    <div class="file-preview">
                                                        <i class="fas fa-<?php echo $attachment['file_type'] === 'application/pdf' ? 'file-pdf' : 'file'; ?>"></i>
                                                        <span><?php echo htmlspecialchars(basename($attachment['file_path'])); ?></span>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <footer class="post-footer">
                                <div class="post-stats">
                                    <span><i class="fas fa-eye"></i> <?php echo $topic['views']; ?></span>
                                    <span><i class="fas fa-comments"></i> <?php echo $topic['reply_count']; ?></span>
                                </div>
                                <div class="post-actions">
                                    <a href="topic.php?id=<?php echo $topic['id']; ?>" class="action-btn">
                                        <i class="far fa-comment"></i> Comment
                                    </a>
                                </div>
                            </footer>
                        </article>
                    <?php 
                        endforeach;
                    endif;
                    ?>
                </div>
                <?php
                    } catch (Exception $e) {
                        echo '<div class="alert alert-danger">Error loading topics: ' . htmlspecialchars($e->getMessage()) . '</div>';
                    }
                ?>
            </div>

            <aside class="sidebar-right">
                <!-- Forum Statistics Widget -->
                <div class="widget">
                    <div class="widget-header">
                        <div class="widget-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3>Forum Statistics</h3>
                    </div>
                    <?php
                        try {
                            $database = new Database();
                            $conn = $database->getConnection();
                        
                            if (!$conn) {
                                throw new Exception('Database connection failed');
                            }

                        $stats_query = "SELECT 
                        (SELECT COUNT(*) FROM users WHERE status = 'active') as total_users,
                        (SELECT COUNT(*) FROM topics) as total_topics,
                        (SELECT COUNT(*) FROM posts) as total_posts,
                        (SELECT username FROM users WHERE status = 'active' ORDER BY created_at DESC LIMIT 1) as newest_user";
                            
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
                            <strong style="margin-right: 1rem;"><?php echo htmlspecialchars($stats['newest_user'] ?? 'None'); ?></strong>
                            <span>Newest Member</span>
                        </div>
                        <?php
                            } catch (Exception $e) {
                                echo '<div class="stat-item"><strong>Error</strong><span>Unable to load stats</span></div>';
                            }
                        ?>
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
                        <a href="new_topic.php" class="action-item">
                            <i class="fas fa-plus"></i>
                            <span>New Topic</span>
                        </a>
                        <a href="search.php" class="action-item">
                            <i class="fas fa-search"></i>
                            <span>Search</span>
                        </a>
                        <a href="messages.php" class="action-item">
                            <i class="fas fa-envelope"></i>
                            <span>Messages</span>
                        </a>
                        <a href="profile.php" class="action-item">
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
                        $recent_query = "SELECT t.id, t.title, t.created_at, u.username, f.name as forum_name 
                            FROM topics t 
                            JOIN users u ON t.user_id = u.id 
                            JOIN forums f ON t.forum_id = f.id 
                            WHERE u.status = 'active'
                            ORDER BY t.created_at DESC LIMIT 5";
                    
                        $stmt = $conn->prepare($recent_query);
                        $stmt->execute();
                        $recent_topics = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    <div class="activity-list">
                        <?php foreach($recent_topics as $topic): ?>
                        <div class="activity-item">
                            <a href="topic.php?id=<?php echo $topic['id']; ?>" class="activity-title"><?php echo htmlspecialchars($topic['title']); ?></a>
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
                        $trending_query = "SELECT t.id, t.title, t.views, u.username, 
                        (SELECT COUNT(*) FROM posts p WHERE p.topic_id = t.id) as reply_count
                        FROM topics t 
                        JOIN users u ON t.user_id = u.id 
                        WHERE u.status = 'active'
                        ORDER BY t.views DESC, reply_count DESC LIMIT 5";
                        
                        $stmt = $conn->prepare($trending_query);
                        $stmt->execute();
                        $trending_topics = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    <div class="trending-list">
                        <?php foreach($trending_topics as $index => $topic): ?>
                        <div class="trending-item">
                            <div class="trending-rank"><?php echo $index + 1; ?></div>
                                <div class="trending-content">
                                    <a href="topic.php?id=<?php echo $topic['id']; ?>" class="trending-title"><?php echo htmlspecialchars($topic['title']); ?></a>
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
                        $online_query = "SELECT username FROM users WHERE last_active > DATE_SUB(NOW(), INTERVAL 15 MINUTE) AND status = 'active' ORDER BY last_active DESC LIMIT 10";
                        $stmt = $conn->prepare($online_query);
                        $stmt->execute();
                        $online_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    <div class="online-users">
                        <?php if(count($online_users) > 0): ?>
                            <?php foreach($online_users as $online_user): ?>
                                <div class="user-badge">
                                    <div class="user-avatar"><?php echo strtoupper(substr($online_user['username'], 0, 1)); ?></div>
                                        <span>
                                        <?php echo htmlspecialchars($online_user['username']); ?></span>
                                    </div>
                                        <?php endforeach; ?>
                            <?php else: ?>
                            <p class="text-secondary">No users online</p>
                        <?php endif; ?>
                    </div>
            </aside>
        </div>
    </main>
    <?php require_once 'includes/footer.php'; ?>
    
    <!-- ===== MAIN JS ===== -->
    <script src="assets/scripts/main.js"></script>
    <script>
        // mobile interactions
        document.addEventListener('DOMContentLoaded', function() {
            // Touch-friendly post interactions
            const posts = document.querySelectorAll('.post');
            posts.forEach(post => {
                post.addEventListener('touchstart', function() {
                    this.style.transform = 'scale(0.98)';
                });
                
                post.addEventListener('touchend', function() {
                    this.style.transform = 'scale(1)';
                });
            });
            
            // Lazy loading for images
            const images = document.querySelectorAll('img[loading="lazy"]');
            if ('IntersectionObserver' in window) {
                const imageObserver = new IntersectionObserver((entries, observer) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const img = entry.target;
                            img.src = img.dataset.src || img.src;
                            img.classList.remove('lazy');
                            observer.unobserve(img);
                        }
                    });
                });
                
                images.forEach(img => imageObserver.observe(img));
            }
            
            // Smooth scroll for anchor links
            const anchorLinks = document.querySelectorAll('a[href^="#"]');
            anchorLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    const href = this.getAttribute('href');
                    if (href && href !== '#') {
                        e.preventDefault();
                        const target = document.querySelector(href);
                        if (target) {
                            target.scrollIntoView({ behavior: 'smooth' });
                        }
                    }
                });
            });
            
            // Auto-refresh stats every 5 minutes
            setInterval(function() {
                const statsSection = document.querySelector('.stats-grid');
                if (statsSection && window.innerWidth >= 768) {
                    fetch(window.location.href)
                        .then(response => response.text())
                        .then(html => {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');
                            const newStats = doc.querySelector('.stats-grid');
                            if (newStats) {
                                statsSection.innerHTML = newStats.innerHTML;
                            }
                        })
                        .catch(error => console.log('Stats refresh failed:', error));
                }
            }, 300000);
        });
    </script>
</body>
</html>