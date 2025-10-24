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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="forum-page">
        <div class="forum-container">
            <!-- Header Section -->
            <header class="forum-header">
                <nav class="breadcrumb">
                    <a href="index.php">Forum</a>
                    <span>/</span>
                    <span><?php echo htmlspecialchars($forum_info['category_name']); ?></span>
                    <span>/</span>
                    <span><?php echo htmlspecialchars($forum_info['name']); ?></span>
                </nav>
                
                <div class="forum-title-bar">
                    <div class="forum-details">
                        <h1><?php echo htmlspecialchars($forum_info['name']); ?></h1>
                        <p><?php echo htmlspecialchars($forum_info['description']); ?></p>
                        <div class="forum-stats">
                            <span><?php echo $total_topics; ?> topics</span>
                            <span><?php echo $forum_info['posts_count']; ?> posts</span>
                        </div>
                    </div>
                    <?php if($user): ?>
                        <a href="new_topic.php?forum_id=<?php echo $forum_id; ?>" class="new-topic-button">
                            <i class="fas fa-plus"></i>
                            New Topic
                        </a>
                    <?php endif; ?>
                </div>
            </header>

            <!-- Topics List -->
            <div class="topics-section">
                <?php if(count($topics) > 0): ?>
                    <div class="topics-list">
                        <?php foreach($topics as $topic): 
                            $last_reply = $topic['last_reply'] ? explode('|', $topic['last_reply']) : null;
                        ?>
                            <article class="topic-row">
                                <div class="topic-info">
                                    <div class="topic-badges">
                                        <?php if($topic['is_pinned']): ?>
                                            <span class="badge pinned">Pinned</span>
                                        <?php endif; ?>
                                        <?php if($topic['is_locked']): ?>
                                            <span class="badge locked">Locked</span>
                                        <?php endif; ?>
                                    </div>
                                    <h3 class="topic-title">
                                        <a href="topic.php?id=<?php echo $topic['id']; ?>">
                                            <?php echo htmlspecialchars($topic['title']); ?>
                                        </a>
                                    </h3>
                                    <div class="topic-meta">
                                        <span>by <?php echo htmlspecialchars($topic['username']); ?></span>
                                        <span><?php echo date('M j, Y', strtotime($topic['created_at'])); ?></span>
                                    </div>
                                </div>
                                <div class="topic-stats">
                                    <div class="stat">
                                        <strong><?php echo $topic['replies_count']; ?></strong>
                                        <span>replies</span>
                                    </div>
                                    <div class="stat">
                                        <strong><?php echo number_format($topic['views']); ?></strong>
                                        <span>views</span>
                                    </div>
                                    <?php if($last_reply): ?>
                                        <div class="last-reply">
                                            <span><?php echo date('M j', strtotime($last_reply[1])); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-comments"></i>
                        <h3>No topics yet</h3>
                        <p>Be the first to start a discussion!</p>
                        <?php if($user): ?>
                            <a href="new_topic.php?forum_id=<?php echo $forum_id; ?>" class="new-topic-button">
                                <i class="fas fa-plus"></i> Create First Topic
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if($total_pages > 1): ?>
                    <nav class="pagination">
                        <?php if($page > 1): ?>
                            <a href="?id=<?php echo $forum_id; ?>&page=<?php echo $page-1; ?>" class="page-btn prev">
                                <i class="fas fa-chevron-left"></i> Previous
                            </a>
                        <?php endif; ?>
                        <?php for($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?id=<?php echo $forum_id; ?>&page=<?php echo $i; ?>" 
                               class="page-number <?php echo $i == $page ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        <?php if($page < $total_pages): ?>
                            <a href="?id=<?php echo $forum_id; ?>&page=<?php echo $page+1; ?>" class="page-btn next">
                                Next <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </main>
    <script src="assets/scripts/main.js"></script>
</body>
</html>