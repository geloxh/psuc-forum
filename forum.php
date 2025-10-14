<?php
    require_once 'includes/auth.php';
    require_once 'includes/forum.php';

    $auth = new Auth();
    $forum = new Forum();
    $user = $auth -> getCurrentUser();

    $forum_id = $_GET['id'] ?? 0;
    $page = $_GET['page'] ?? 1;
    $limit = 20;
    $offset = ($page - 1) * $limit;

    $database = new Database();
    $conn = $database -> getConnection();

    // Get forum info
    $forum_query = "SELECT f.*, c.name as category_name FROM forums f JOIN categories c ON f.category_id = c.id WHERE f.id = ?";
    $stmt = $conn -> prepare($forum_query);
    $stmt -> execute([$forum_id]);
    $forum_info = $stmt -> fetch(PDO::FETCH_ASSOC);

    if(!$forum_info) {
        header('Location: index.php');
        exit;
    }

    $topics = $forum->getTopics($forum_id, $limit, $offset);

    // Get total topics for pagination
    $count_query = "SELECT COUNT(*) as total FROM topics WHERE forum_id = ?";
    $stmt = $conn -> prepare($count_query);
    $stmt -> execute([$forum_id]);
    $total_topics = $stmt -> fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total_topics / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($forum_info['name']); ?> - PSUC Forum</title>
    <link rel="stylesheet" href="assets/stylesheets/main.css">
    <link rel="stylesheet" href="assets/stylesheets/dark-theme.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container">
        <div class="main-content">
            <div class="forum-content">
                <div class="p-3" style="border-bottom: 1px solid var(--border-color);">
                    <nav style="margin-bottom: 1rem;">
                        <a href="index.php">Forum</a> > 
                        <span><?php echo htmlspecialchars($forum_info['category_name']); ?></span> > 
                        <strong><?php echo htmlspecialchars($forum_info['name']); ?></strong>
                    </nav>
                    
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <h1><?php echo htmlspecialchars($forum_info['name']); ?></h1>
                            <p class="text-secondary"><?php echo htmlspecialchars($forum_info['description']); ?></p>
                        </div>
                        <?php if($user): ?>
                            <a href="new_topic.php?forum_id=<?php echo $forum_id; ?>" class="btn btn-primary">
                                <i class="fas fa-plus"></i> New Topic
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if(count($topics) > 0): ?>
                    <?php foreach($topics as $topic): 
                        $last_reply = $topic['last_reply'] ? explode('|', $topic['last_reply']) : null;
                    ?>
                        <div class="topic-item">
                            <div class="topic-info">
                                <div class="topic-icon">
                                    <i class="fas fa-comment"></i>
                                </div>
                                <div class="topic-details">
                                    <h4>
                                        <?php if($topic['is_pinned']): ?>
                                            <span class="badge pinned">Pinned</span>
                                        <?php endif; ?>
                                        <?php if($topic['is_locked']): ?>
                                            <span class="badge locked">Locked</span>
                                        <?php endif; ?>
                                        <a href="topic.php?id=<?php echo $topic['id']; ?>">
                                            <?php echo htmlspecialchars($topic['title']); ?>
                                        </a>
                                    </h4>
                                    <div class="topic-meta">
                                        by <strong><?php echo htmlspecialchars($topic['username']); ?></strong> • 
                                        <?php echo date('M j, Y g:i A', strtotime($topic['created_at'])); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="topic-stats">
                                <strong><?php echo $topic['replies_count']; ?></strong>
                                Replies
                            </div>
                            <div class="topic-stats">
                                <strong><?php echo $topic['views']; ?></strong>
                                Views
                            </div>
                            <div class="last-post">
                                <?php if($last_reply): ?>
                                    by <?php echo htmlspecialchars($last_reply[0]); ?><br>
                                    <small><?php echo date('M j, g:i A', strtotime($last_reply[1])); ?></small>
                                <?php else: ?>
                                    <em>No replies</em>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?php if($total_pages > 1): ?>
                        <div class="pagination">
                            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                <a href="?id=<?php echo $forum_id; ?>&page=<?php echo $i; ?>" 
                                   class="<?php echo $i == $page ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="p-3 text-center">
                        <i class="fas fa-comments" style="font-size: 3rem; color: var(--text-secondary); margin-bottom: 1rem;"></i>
                        <h3>No topics yet</h3>
                        <p class="text-secondary">Be the first to start a discussion in this forum!</p>
                        <?php if($user): ?>
                            <a href="new_topic.php?forum_id=<?php echo $forum_id; ?>" class="btn btn-primary mt-2">
                                <i class="fas fa-plus"></i>Create First Topic
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <aside class="sidebar">
                <div class="widget">
                    <h3><i class="fas fa-info-circle"></i>Forum Info</h3>
                    <div class="stats-grid">
                        <div class="stat-item">
                            <strong><?php echo $total_topics; ?></strong>
                            <span>Topics</span>
                        </div>
                        <div class="stat-item">
                            <strong><?php echo $forum_info['posts_count']; ?></strong>
                            <span>Posts</span>
                        </div>
                    </div>
                </div>

                <?php if($user): ?>
                    <div class="widget">
                        <h3><i class="fas fa-tools"></i> Quick Actions</h3>
                        <a href="new_topic.php?forum_id=<?php echo $forum_id; ?>" class="btn btn-primary" style="width: 100%; margin-bottom: 0.5rem;">
                            <i class="fas fa-plus"></i> New Topic
                        </a>
                        <a href="search.php?forum=<?php echo $forum_id; ?>" class="btn btn-secondary" style="width: 100%;">
                            <i class="fas fa-search"></i> Search Forum
                        </a>
                    </div>
                <?php endif; ?>
            </aside>
        </div>
        
        <script src="assets/scripts/main.js"></script>
    </main>
</body>
</html>