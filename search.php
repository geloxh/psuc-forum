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
    <link rel="stylesheet" href="assets/stylesheets/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .search-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 3rem 1rem;
        }
        .search-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        .search-icon {
            width: 80px;
            height: 80px;
            background: var(--primary-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: white;
            font-size: 2rem;
        }
        .search-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0 0 0.5rem 0;
        }
        .search-subtitle {
            color: var(--text-secondary);
            font-size: 1.1rem;
            margin: 0;
        }
        .search-form {
            position: relative;
            max-width: 600px;
            margin: 0 auto 3rem;
        }
        .search-input {
            width: 100%;
            padding: 1.25rem 1.5rem 1.25rem 3.5rem;
            border: 2px solid var(--border-color);
            border-radius: 50px;
            font-size: 1.1rem;
            background: white;
            transition: all 0.3s ease;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        .search-input:focus {
            outline: none;
            border-color: var(--secondary-blue);
            box-shadow: 0 4px 20px rgba(59, 130, 246, 0.2);
        }
        .search-form i {
            position: absolute;
            left: 1.25rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            font-size: 1.2rem;
        }
        .search-btn {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            background: var(--primary-gradient);
            border: none;
            border-radius: 50px;
            padding: 0.75rem 1.5rem;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .search-btn:hover {
            transform: translateY(-50%) scale(1.05);
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
        }
        .results-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
        }
        .results-count {
            color: var(--text-secondary);
            font-size: 0.95rem;
        }
        .result-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            border: 1px solid rgba(229, 231, 235, 0.6);
            transition: all 0.3s ease;
        }
        .result-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.12);
        }
        .result-type {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.25rem 0.75rem;
            background: rgba(59, 130, 246, 0.1);
            color: var(--secondary-blue);
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            margin-bottom: 1rem;
        }
        .result-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin: 0 0 1rem 0;
        }
        .result-title a {
            color: var(--text-primary);
            text-decoration: none;
            transition: color 0.3s ease;
        }
        .result-title a:hover {
            color: var(--secondary-blue);
        }
        .result-content {
            color: var(--text-secondary);
            line-height: 1.6;
            margin-bottom: 1rem;
        }
        .result-meta {
            display: flex;
            align-items: center;
            gap: 1rem;
            font-size: 0.9rem;
            color: var(--text-secondary);
        }
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
        }
        .empty-icon {
            width: 120px;
            height: 120px;
            background: rgba(107, 114, 128, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            color: var(--text-secondary);
            font-size: 3rem;
        }
        .empty-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0 0 1rem 0;
        }
        .empty-text {
            color: var(--text-secondary);
            font-size: 1.1rem;
        }
        @media (max-width: 768px) {
            .search-container { padding: 2rem 1rem; }
            .search-title { font-size: 2rem; }
            .search-input { padding: 1rem 1.25rem 1rem 3rem; }
            .result-card { padding: 1.5rem; }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container">
        <div class="search-container">
            <div class="search-header">
                <div class="search-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h1 class="search-title">Search Forum</h1>
                <p class="search-subtitle">Find topics, posts, and discussions across PSUC communities</p>
            </div>

            <form method="GET" class="search-form">
                <i class="fas fa-search"></i>
                <input type="text" name="q" class="search-input" 
                       placeholder="Search topics, posts, and discussions..." 
                       value="<?php echo htmlspecialchars($query); ?>" autofocus>
                <button type="submit" class="search-btn">
                    Search
                </button>
            </form>

            <?php if($query): ?>
                <div class="results-header">
                    <h2>Results for "<?php echo htmlspecialchars($query); ?>"</h2>
                    <span class="results-count"><?php echo count($results); ?> results found</span>
                </div>

                <?php if(count($results) > 0): ?>
                    <?php foreach($results as $result): ?>
                        <div class="result-card">
                            <div class="result-type">
                                <?php if($result['type'] == 'topic'): ?>
                                    <i class="fas fa-comment"></i> Topic
                                <?php else: ?>
                                    <i class="fas fa-reply"></i> Post
                                <?php endif; ?>
                            </div>
                            <h3 class="result-title">
                                <a href="topic.php?id=<?php echo $result['id']; ?>">
                                    <?php echo htmlspecialchars($result['title']); ?>
                                </a>
                            </h3>
                            <p class="result-content">
                                <?php echo substr(strip_tags($result['content']), 0, 200) . '...'; ?>
                            </p>
                            <div class="result-meta">
                                <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($result['username']); ?></span>
                                <span><i class="fas fa-folder"></i> <?php echo htmlspecialchars($result['forum_name']); ?></span>
                                <span><i class="fas fa-clock"></i> <?php echo date('M j, Y', strtotime($result['created_at'])); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-search-minus"></i>
                        </div>
                        <h3 class="empty-title">No results found</h3>
                        <p class="empty-text">Try different keywords or check your spelling</p>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3 class="empty-title">Start Your Search</h3>
                    <p class="empty-text">Enter keywords above to search through topics and posts</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script src="assets/scripts/main.js"></script>
</body>
</html>