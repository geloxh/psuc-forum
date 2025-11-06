<?php
    require_once 'includes/auth.php';
    require_once 'includes/forum.php';

    $auth = new Auth();
    $forum = new Forum();
    $user = $auth -> getCurrentUser();

    $forum_id = $_GET['id'] ?? 0;
    
    // Redirect if no forum ID provided
    if(!$forum_id || !is_numeric($forum_id)) {
        header('Location: index.php');
        exit;
    }
    
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
    <link rel="stylesheet" href="assets/stylesheets/new-topic.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .forum-page {
            background: #fafbfc;
            min-height: 100vh;
            padding: 1.5rem 1rem;
        }
        
        .forum-container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            border: 1px solid rgba(229, 231, 235, 0.3);
        }
        
        .forum-header {
            padding: 2rem;
            background: linear-gradient(135deg, #fafbfc 0%, #f8fafc 100%);
            border-bottom: 1px solid #f3f4f6;
        }
        
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
            color: #6b7280;
            padding: 0.75rem 1rem;
            background: rgba(248, 250, 252, 0.6);
            border-radius: 8px;
            border: 1px solid rgba(229, 231, 235, 0.3);
        }
        
        .breadcrumb a {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s ease;
        }
        
        .breadcrumb a:hover {
            color: #2563eb;
        }
        
        .breadcrumb i {
            font-size: 0.75rem;
            opacity: 0.6;
        }
        
        .forum-title-bar {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 2rem;
        }
        
        .forum-details h1 {
            font-size: 1.75rem;
            font-weight: 600;
            color: #111827;
            margin: 0 0 0.75rem 0;
            letter-spacing: -0.025em;
        }
        
        .forum-details p {
            color: #6b7280;
            margin: 0 0 1rem 0;
            line-height: 1.6;
            font-size: 0.95rem;
        }
        
        .forum-stats {
            display: flex;
            gap: 1.5rem;
            font-size: 0.875rem;
            color: #6b7280;
        }
        
        .forum-stats span {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.375rem 0.75rem;
            background: rgba(248, 250, 252, 0.8);
            border-radius: 6px;
            border: 1px solid rgba(229, 231, 235, 0.3);
            font-weight: 500;
        }
        
        .forum-stats i {
            color: #3b82f6;
            font-size: 0.8rem;
        }
        
        .new-topic-button {
             display: inline-flex;
             align-items: center;
             gap: 0.5rem;
             padding: 0.75rem 1.25rem;
             background: linear-gradient(135deg, #3b82f6, #2563eb);
             color: white;
             text-decoration: none;
             border-radius: 10px;
             font-weight: 600;
             font-size: 0.9rem;
             transition: all 0.3s ease;
             box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
             border: none;
             cursor: pointer;
        }
        
        .new-topic-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(59, 130, 246, 0.3);
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
        }

        .new-topic-button i {
            font-size: 0.85rem;
        }

        .topics-section {
            padding: 0;
        }
        
        .topics-list {
            display: flex;
            flex-direction: column;
        }
        
        .topic-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.75rem 2rem;
            border-bottom: 1px solid rgba(243, 244, 246, 0.6);
            transition: all 0.3s ease;
            position: relative;
        }
        
        .topic-row::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 3px;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .topic-row:hover {
            background: rgba(248, 250, 252, 0.8);
            transform: translateX(4px);
        }
        
        .topic-row:hover::before {
            opacity: 1;
        }
        
        .topic-row:last-child {
            border-bottom: none;
        }
        
        .topic-info {
            flex: 1;
            min-width: 0;
        }
        
        .topic-badges {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }
        
        .badge {
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }
        
        .badge.pinned {
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            color: white;
            box-shadow: 0 1px 3px rgba(251, 191, 36, 0.3);
        }
        
        .badge.locked {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            box-shadow: 0 1px 3px rgba(239, 68, 68, 0.3);
        }
        
        .topic-title {
            margin: 0 0 0.75rem 0;
            font-size: 1.1rem;
            font-weight: 500;
            line-height: 1.4;
        }
        
        .topic-title a {
            color: #111827;
            text-decoration: none;
            transition: color 0.2s ease;
        }
        
        .topic-title a:hover {
            color: #3b82f6;
        }
        
        .topic-meta {
            display: flex;
            gap: 1.25rem;
            font-size: 0.85rem;
            color: #6b7280;
        }
        
        .topic-meta span {
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }
        
        .topic-meta i {
            font-size: 0.75rem;
            opacity: 0.7;
        }
        
        .topic-meta strong {
            color: #3b82f6;
            font-weight: 500;
        }
        
        .topic-stats {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-shrink: 0;
        }
        
        .stat {
            text-align: center;
            min-width: 70px;
            padding: 0.5rem;
            background: rgba(248, 250, 252, 0.5);
            border-radius: 8px;
            border: 1px solid rgba(229, 231, 235, 0.3);
            transition: all 0.2s ease;
        }
        
        .stat:hover {
            background: rgba(59, 130, 246, 0.05);
            border-color: rgba(59, 130, 246, 0.2);
        }
        
        .stat strong {
            display: block;
            font-size: 1.125rem;
            font-weight: 600;
            color: #111827;
            margin-bottom: 0.125rem;
        }
        
        .stat span {
            font-size: 0.7rem;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 500;
        }
        
        .last-reply {
            text-align: center;
            font-size: 0.8rem;
            color: #6b7280;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.25rem;
            padding: 0.5rem;
            background: rgba(248, 250, 252, 0.5);
            border-radius: 6px;
            border: 1px solid rgba(229, 231, 235, 0.3);
        }
        
        .last-reply i {
            font-size: 0.7rem;
            opacity: 0.6;
        }
        
        .empty-state {
            text-align: center;
            padding: 5rem 2rem;
            color: #6b7280;
            background: linear-gradient(135deg, rgba(248, 250, 252, 0.5), rgba(241, 245, 249, 0.3));
            border-radius: 16px;
            margin: 2rem;
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            color: #d1d5db;
            opacity: 0.7;
        }
        
        .empty-state h3 {
            font-size: 1.5rem;
            font-weight: 500;
            color: #111827;
            margin: 0 0 0.75rem 0;
            letter-spacing: -0.025em;
        }
        
        .empty-state p {
            margin: 0 0 2.5rem 0;
            font-size: 1rem;
            line-height: 1.6;
        }

        .empty-state .new-topic-button {
            padding: 0.375rem 0.75rem;
            font-size: 0.75rem;
            gap: 0.375rem;
        }

        .empty-state .new-topic-button i {
            font-size: 0.7rem;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.75rem;
            padding: 2.5rem 2rem;
            border-top: 1px solid rgba(243, 244, 246, 0.6);
            background: rgba(248, 250, 252, 0.3);
        }
        
        .page-btn, .page-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.625rem 0.875rem;
            border: 1px solid rgba(209, 213, 219, 0.6);
            background: white;
            color: #374151;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }
        
        .page-btn:hover, .page-number:hover {
            background: #f8fafc;
            border-color: #3b82f6;
            color: #3b82f6;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.15);
        }
        
        .page-number.active {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            border-color: #3b82f6;
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
        }
        
        @media (max-width: 768px) {
            .forum-page {
                padding: 1rem 0.5rem;
            }
            
            .forum-header {
                padding: 1.5rem;
            }
            
            .forum-title-bar {
                flex-direction: column;
                gap: 1.5rem;
                align-items: stretch;
            }
            
            .forum-details h1 {
                font-size: 1.5rem;
            }
            
            .topic-row {
                flex-direction: column;
                align-items: stretch;
                gap: 1rem;
                padding: 1.5rem;
            }
            
            .topic-stats {
                justify-content: space-around;
                padding-top: 1rem;
                border-top: 1px solid #f3f4f6;
            }
            
            .pagination {
                flex-wrap: wrap;
                padding: 1.5rem;
            }
        }
        
        @media (max-width: 480px) {
            .forum-header {
                padding: 1rem;
            }
            
            .topic-row {
                padding: 1rem;
            }
            
            .topic-stats {
                gap: 1rem;
            }
            
            .stat {
                min-width: 50px;
            }
            
            .stat strong {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="forum-page">
        <div class="forum-container">
            <!-- Header Section -->
            <header class="forum-header">
                <nav class="breadcrumb">
                    <a href="index.php"><i class="fas fa-home"></i> Forum</a>
                    <i class="fas fa-chevron-right"></i>
                    <span><?php echo htmlspecialchars($forum_info['category_name']); ?></span>
                    <i class="fas fa-chevron-right"></i>
                    <span><?php echo htmlspecialchars($forum_info['name']); ?></span>
                </nav>
                
                <div class="forum-title-bar">
                    <div class="forum-details">
                        <h1><?php echo htmlspecialchars($forum_info['name']); ?></h1>
                        <p><?php echo htmlspecialchars($forum_info['description']); ?></p>
                        <div class="forum-stats">
                            <span><i class="fas fa-comments"></i> <?php echo $total_topics; ?> topics</span>
                            <span><i class="fas fa-reply"></i> <?php echo $forum_info['posts_count']; ?> posts</span>
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
                                            <span class="badge pinned"><i class="fas fa-thumbtack"></i> Pinned</span>
                                        <?php endif; ?>
                                        <?php if($topic['is_locked']): ?>
                                            <span class="badge locked"><i class="fas fa-lock"></i> Locked</span>
                                        <?php endif; ?>
                                    </div>
                                    <h3 class="topic-title">
                                        <a href="topic.php?id=<?php echo $topic['id']; ?>">
                                            <?php echo htmlspecialchars($topic['title']); ?>
                                        </a>
                                    </h3>
                                    <div class="topic-meta">
                                        <span><i class="fas fa-user"></i> by <strong><?php echo htmlspecialchars($topic['username']); ?></strong></span>
                                        <span><i class="fas fa-calendar"></i> <?php echo date('M j, Y', strtotime($topic['created_at'])); ?></span>
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
                                            <i class="fas fa-clock"></i>
                                            <span><?php echo date('M j', strtotime($last_reply[1])); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-comments empty-state-icon"></i>
                        <h3>No topics yet</h3>
                        <p>This forum is waiting for its first discussion. Start the conversation and help build this community!</p>
                        <?php if($user): ?>
                            <a href="new_topic.php?forum_id=<?php echo $forum_id; ?>" class="new-topic-button">
                                <i class="fas fa-plus"></i> Create First Topic
                            </a>
                        <?php else: ?>
                            <p><a href="login.php" style="color: #3b82f6; text-decoration: none;">Login</a> to start the first discussion</p>
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
    <script>
        // Add smooth animations for topic rows
        document.addEventListener('DOMContentLoaded', function() {
            const topicRows = document.querySelectorAll('.topic-row');
            topicRows.forEach((row, index) => {
                row.style.opacity = '0';
                row.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    row.style.transition = 'all 0.3s ease';
                    row.style.opacity = '1';
                    row.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>