<?php
require_once 'includes/auth.php';
require_once 'includes/forum.php';

$auth = new Auth();
$forum = new Forum();
$user = $auth->getCurrentUser();

$query = $_GET['q'] ?? '';
$results = [];

if($query) {
    $results = $forum->search($query);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search - PSUC Forum</title>
    <!-- ===== CSS ===== -->
    <link rel="stylesheet" href="assets/stylesheets/main.css">
    <link rel="stylesheet" href="assets/stylesheets/dark-theme.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php 
        include 'includes/header.php'; 
    ?>

    <main class="container">
        <div class="main-content">
            <div class="forum-content">
                <div class="p-3">
                    <h1><i class="fas fa-search"></i> Search Forum</h1>
                    
                    <form method="GET" class="search-form">
                        <input type="text" name="q" class="form-control" placeholder="Search topics and posts..." 
                               value="<?php echo htmlspecialchars($query); ?>" autofocus>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </form>
                </div>

                <?php if($query): ?>
                    <div class="p-3" style="border-top: 1px solid var(--border-color);">
                        <h3>Search Results for "<?php echo htmlspecialchars($query); ?>"</h3>
                        <p class="text-secondary"><?php echo count($results); ?> results found</p>
                    </div>

                    <?php if(count($results) > 0): ?>
                        <?php foreach($results as $result): ?>
                            <div class="forum-item">
                                <div class="forum-info">
                                    <h4>
                                        <?php if($result['type'] == 'topic'): ?>
                                            <i class="fas fa-comment text-primary"></i>
                                            <a href="topic.php?id=<?php echo $result['id']; ?>">
                                                <?php echo htmlspecialchars($result['title']); ?>
                                            </a>
                                        <?php else: ?>
                                            <i class="fas fa-reply text-secondary"></i>
                                            <a href="topic.php?id=<?php echo $result['id']; ?>">
                                                Re: <?php echo htmlspecialchars($result['title']); ?>
                                            </a>
                                        <?php endif; ?>
                                    </h4>
                                    <p><?php echo substr(strip_tags($result['content']), 0, 200) . '...'; ?></p>
                                    <div class="topic-meta">
                                        by <strong><?php echo htmlspecialchars($result['username']); ?></strong> in 
                                        <strong><?php echo htmlspecialchars($result['forum_name']); ?></strong> • 
                                        <?php echo date('M j, Y g:i A', strtotime($result['created_at'])); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="p-3 text-center">
                            <i class="fas fa-search" style="font-size: 3rem; color: var(--text-secondary); margin-bottom: 1rem;"></i>
                            <h3>No results found</h3>
                            <p class="text-secondary">Try different keywords or check your spelling.</p>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="p-3 text-center">
                        <i class="fas fa-search" style="font-size: 3rem; color: var(--text-secondary); margin-bottom: 1rem;"></i>
                        <h3>Search the Forum</h3>
                        <p class="text-secondary">Enter keywords to search through topics and posts.</p>
                    </div>
                <?php endif; ?>
            </div>

            <aside class="sidebar">
                <div class="widget">
                    <h3><i class="fas fa-lightbulb"></i> Search Tips</h3>
                    <ul style="padding-left: 1.5rem; line-height: 1.8;">
                        <li>Use specific keywords</li>
                        <li>Try different word combinations</li>
                        <li>Search for exact phrases using quotes</li>
                        <li>Use shorter, more general terms</li>
                    </ul>
                </div>

                <div class="widget">
                    <h3><i class="fas fa-tags"></i> Popular Topics</h3>
                    <?php
                    $database = new Database();
                    $conn = $database->getConnection();
                    $popular_query = "SELECT title, id, views FROM topics ORDER BY views DESC LIMIT 5";
                    $stmt = $conn->prepare($popular_query);
                    $stmt->execute();
                    $popular_topics = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    <?php foreach($popular_topics as $topic): ?>
                        <div class="mb-2">
                            <a href="topic.php?id=<?php echo $topic['id']; ?>" style="text-decoration: none;">
                                <?php echo htmlspecialchars($topic['title']); ?>
                            </a>
                            <small class="text-secondary d-block"><?php echo $topic['views']; ?> views</small>
                        </div>
                    <?php endforeach; ?>
                </div>
            </aside>
        </div>
    
        <script src="assets/scripts/main.js"></script>
    </main>
</body>
</html>