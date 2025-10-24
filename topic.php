<?php
    require_once 'includes/auth.php';
    require_once 'includes/forum.php';

    $auth = new Auth();
    $forum = new Forum();
    $user = $auth -> getCurrentUser();

    $topic_id = $_GET['id'] ?? 0;
    $page = $_GET['page'] ?? 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;

    $error = '';

    $topic = $forum -> getTopic($topic_id);

    if(!$topic) {
        header('Location: index.php');
        exit;
    }

    $posts = $forum -> getPosts($topic_id, $limit, $offset);
    $total_posts = $forum -> getPostCount($topic_id);

    // Handle new post creation
    if ($_POST && isset($_POST['action']) && $_POST['action'] == 'create_post' && $user) {
        try {
            $post_id = $forum->createPost($topic_id, $user['id'], $_POST['content']);
            if ($post_id) {
                // Redirect to the last page to see the new post
                $last_page = ceil(($total_posts + 1) / $limit);
                header("Location: topic.php?id=$topic_id&page=$last_page#post-$post_id");
                exit;
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }

    // Handle voting
    if (isset($_GET['action']) && $_GET['action'] == 'vote' && $user) {
        $forum->vote($user['id'], $_GET['type'], $_GET['target_id'], $_GET['vote']); // Corrected header
        header("Location: topic.php?id=$topic_id&page=$page");
        exit;
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($topic['title']); ?> - PSUC Forum</title>
    <link rel="stylesheet" href="assets/stylesheets/main.css">
    <link rel="stylesheet" href="assets/stylesheets/media-preview.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .topic-header-enhanced {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.05), rgba(245, 158, 11, 0.05));
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            border: 1px solid rgba(59, 130, 246, 0.1);
            position: relative;
            overflow: hidden;
        }
        .topic-header-enhanced::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gold-gradient);
        }
        .breadcrumb-nav {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            color: var(--text-secondary);
        }
        .breadcrumb-nav a {
            color: var(--secondary-blue);
            text-decoration: none;
            transition: color 0.3s ease;
        }
        .breadcrumb-nav a:hover {
            color: var(--primary-blue);
        }
        .topic-title-enhanced {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 1rem;
            line-height: 1.3;
        }
        .topic-meta-enhanced {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            flex-wrap: wrap;
        }
        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: white;
            border-radius: 20px;
            font-size: 0.85rem;
            color: var(--text-secondary);
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .meta-item i {
            color: var(--secondary-blue);
        }
        .topic-stats-bar {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
            flex-wrap: wrap;
        }
        .stat-badge {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: var(--primary-gradient);
            color: white;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        .floating-actions {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            z-index: 100;
        }
        .floating-btn {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: var(--primary-gradient);
            color: white;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            transition: all 0.3s ease;
            text-decoration: none;
        }
        .floating-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
        }
        .floating-btn.reply-btn {
            background: var(--success-color);
        }
        .floating-btn.back-btn {
            background: var(--text-secondary);
        }
        .posts-container {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        .reply-form-enhanced {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: var(--card-shadow);
            border: 1px solid rgba(59, 130, 246, 0.1);
            position: relative;
            overflow: hidden;
            margin-top: 2rem;
        }
        .reply-form-enhanced::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--success-color);
        }
        .form-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
        }
        .form-header i {
            width: 40px;
            height: 40px;
            background: var(--success-color);
            color: white;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }
        .form-header h3 {
            margin: 0;
            color: var(--text-primary);
            font-size: 1.3rem;
            font-weight: 600;
        }
        @media (max-width: 768px) {
            .floating-actions {
                bottom: 1rem;
                right: 1rem;
            }
            .floating-btn {
                width: 48px;
                height: 48px;
                font-size: 1rem;
            }
            .topic-title-enhanced {
                font-size: 1.5rem;
            }
            .topic-meta-enhanced {
                gap: 1rem;
            }
            .meta-item {
                font-size: 0.8rem;
                padding: 0.4rem 0.8rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="topic-page">
        <div class="topic-container">
            <!-- Topic Header -->
            <header class="topic-header">
                <nav class="breadcrumb">
                    <a href="index.php">Forum</a>
                    <span>/</span>
                    <a href="forum.php?id=<?php echo $topic['forum_id']; ?>"><?php echo htmlspecialchars($topic['forum_name']); ?></a>
                    <span>/</span>
                    <span><?php echo htmlspecialchars($topic['title']); ?></span>
                </nav>
                
                <h1><?php echo htmlspecialchars($topic['title']); ?></h1>
                
                <div class="topic-meta">
                    <span>by <strong><?php echo htmlspecialchars($topic['username']); ?></strong></span>
                    <span><?php echo date('M j, Y', strtotime($topic['created_at'])); ?></span>
                    <span><?php echo number_format($topic['views']); ?> views</span>
                    <span><?php echo $total_posts; ?> replies</span>
                </div>
            </header>

            <!-- Posts Section -->
            <div class="posts-section">
                <?php if(isset($_GET['status']) && $_GET['status'] == 'post_deleted'): ?>
                    <div class="alert alert-success">
                        The post has been successfully deleted.
                    </div>
                <?php endif; ?>
                <?php if(isset($_GET['error'])): ?>
                    <div class="alert alert-danger">
                        There was an error deleting the item. Please try again.
                    </div>
                <?php endif; ?>
                <div class="posts-list">

                    <!-- Original Topic Post -->
                    <article class="post original-post" id="original-post">
                        <div class="post-author">
                            <img src="assets/avatars/<?php echo $topic['avatar']; ?>" alt="Avatar" onerror="this.src='assets/avatars/default.png'">
                            <div class="author-info">
                                <h4><?php echo htmlspecialchars($topic['username']); ?></h4>
                                <span class="role"><?php echo ucfirst($topic['role'] ?? 'Member'); ?></span>
                            </div>
                        </div>
                        <div class="post-content">
                            <div class="post-header">
                                <span class="post-badge original">Original Post</span>
                                <time><?php echo date('M j, Y g:i A', strtotime($topic['created_at'])); ?></time>
                            </div>
                            <div class="post-body">
                                <?php echo nl2br(htmlspecialchars($topic['content'])); ?>
                            </div>

                        <?php
                            $topic_attachments = $forum->getAttachments($posts[0]['id'] ?? 0); // The first post is the topic content
                        ?>
                        <?php if (!empty($topic_attachments)): ?>
                            <div class="attachments">
                                <strong>Attachments:</strong>
                                <ul>
                                    <?php foreach ($topic_attachments as $attachment): ?>
                                        <li>
                                            <a href="<?php echo htmlspecialchars($attachment['file_path']); ?>" download="<?php echo htmlspecialchars($attachment['file_name']); ?>">
                                                <i class="fas fa-paperclip"></i> <?php echo htmlspecialchars($attachment['file_name']); ?>
                                            </a>
                                            (<?php echo round($attachment['file_size'] / 1024, 2); ?> KB)
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                            <?php if($user): ?>
                                <div class="post-actions">
                                    <div class="vote-buttons">
                                        <a href="?id=<?php echo $topic_id; ?>&action=vote&type=topic&target_id=<?php echo $topic['id']; ?>&vote=up" class="vote-btn">
                                            <i class="fas fa-thumbs-up"></i> <?php echo $topic['votes_up']; ?>
                                        </a>
                                        <a href="?id=<?php echo $topic_id; ?>&action=vote&type=topic&target_id=<?php echo $topic['id']; ?>&vote=down" class="vote-btn">
                                            <i class="fas fa-thumbs-down"></i> <?php echo $topic['votes_down']; ?>
                                        </a>
                                    </div>
                                    <div class="action-buttons">
                                        <button onclick="shareTopic()" class="action-btn">
                                            <i class="fas fa-share-alt"></i> Share
                                        </button>
                                        <?php if ($user['id'] == $topic['user_id'] || $auth -> isAdmin()): ?>
                                            <a href="edit_topic.php?id=<?php echo $topic['id']; ?>" class="action-btn">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </article>

                    <?php if(!empty($posts)): ?>
                        <div class="replies-header">
                            <h3>Replies (<?php echo count($posts); ?>)</h3>
                        </div>
                    <?php endif; ?>
                    
                    <?php foreach($posts as $index => $post): ?>
                        <article class="post" id="post-<?php echo $post['id']; ?>">
                            <div class="post-author">
                                <img src="assets/avatars/<?php echo $post['avatar']; ?>" alt="Avatar" onerror="this.src='assets/avatars/default.png'">
                                <div class="author-info">
                                    <h4><?php echo htmlspecialchars($post['username']); ?></h4>
                                    <span class="role"><?php echo ucfirst($post['role']); ?></span>
                                </div>
                            </div>
                            <div class="post-content">
                                <div class="post-header">
                                    <span class="post-badge reply">#<?php echo $index + 1; ?></span>
                                    <time><?php echo date('M j, Y g:i A', strtotime($post['created_at'])); ?></time>
                                </div>
                                <div class="post-body">
                                    <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                                </div>

                                    <?php
                                        $attachments = $post['attachments']; // Attachments are already fetched in getPosts()
                                    ?>
                                    <?php if (!empty($attachments)): ?>
                                        <div class="attachments">
                                            <strong>Attachments:</strong>
                                            <ul>
                                                <?php foreach ($attachments as $attachment): ?>
                                                    <li>
                                                        <?php if (strpos($attachment['file_type'], 'image/') === 0): ?>
                                                            <div class="media-preview-item">
                                                                <img src="<?php echo htmlspecialchars($attachment['file_path']); ?>" alt="<?php echo htmlspecialchars($attachment['file_name']); ?>">
                                                            </div>
                                                        <?php elseif (strpos($attachment['file_type'], 'video/') === 0): ?>
                                                            <div class="media-preview-item">
                                                                <video controls>
                                                                    <source src="<?php echo htmlspecialchars($attachment['file_path']); ?>" type="<?php echo htmlspecialchars($attachment['file_type']); ?>">
                                                                </video>
                                                            </div>
                                                        <?php else: ?>
                                                            <a href="<?php echo htmlspecialchars($attachment['file_path']); ?>" download="<?php echo htmlspecialchars($attachment['file_name']); ?>">
                                                                <i class="fas fa-paperclip"></i> <?php echo htmlspecialchars($attachment['file_name']); ?>
                                                            </a>
                                                            (<?php echo round($attachment['file_size'] / 1024, 2); ?> KB)
                                                        <?php endif; ?>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>

                                <?php if($user): ?>
                                    <div class="post-actions">
                                        <div class="vote-buttons">
                                            <a href="?id=<?php echo $topic_id; ?>&action=vote&type=post&target_id=<?php echo $post['id']; ?>&vote=up" class="vote-btn">
                                                <i class="fas fa-thumbs-up"></i> <?php echo $post['votes_up']; ?>
                                            </a>
                                            <a href="?id=<?php echo $topic_id; ?>&action=vote&type=post&target_id=<?php echo $post['id']; ?>&vote=down" class="vote-btn">
                                                <i class="fas fa-thumbs-down"></i> <?php echo $post['votes_down']; ?>
                                            </a>
                                        </div>
                                        <div class="action-buttons">
                                            <button onclick="sharePost(<?php echo $post['id']; ?>)" class="action-btn">
                                                <i class="fas fa-share-alt"></i> Share
                                            </button>
                                            <?php if ($user['id'] == $post['user_id'] || $auth->isAdmin()): ?>
                                                <a href="edit_post.php?id=<?php echo $post['id']; ?>" class="action-btn">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>

                </div>
                
                </div>
                
                <!-- Reply Form -->
                <?php if($topic['is_locked']): ?>
                    <div class="reply-form locked">
                        <div class="locked-message">
                            <i class="fas fa-lock"></i>
                            <h3>Topic Locked</h3>
                            <p>This topic has been locked and no new replies can be posted.</p>
                        </div>
                    </div>
                <?php elseif(!$user): ?>
                    <div class="reply-form login-required">
                        <div class="login-message">
                            <h3>Join the Discussion</h3>
                            <p>Please login to participate in this discussion</p>
                            <div class="login-buttons">
                                <a href="login.php" class="login-btn">Login</a>
                                <a href="register.php" class="register-btn">Register</a>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <form method="POST" enctype="multipart/form-data" class="reply-form" id="reply-form">
                        <input type="hidden" name="action" value="create_post">
                        <h3>Post a Reply</h3>
                        <?php if ($error): ?>
                            <div class="error-message">
                                <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        <div class="form-field">
                            <textarea name="content" id="content" rows="6" required placeholder="Share your thoughts, ask questions, or provide helpful information..."></textarea>
                        </div>
                        <div class="form-field">
                            <input type="file" id="attachments" name="attachments[]" multiple accept="image/*,video/*,.pdf,.doc,.docx,.txt,.zip,.rar">
                            <small>Attach files (optional): Images, Videos, Documents (Max 10MB each)</small>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="submit-btn">
                                <i class="fas fa-reply"></i> Post Reply
                            </button>
                            <button type="button" class="clear-btn" onclick="document.getElementById('content').value = '';">
                                Clear
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
            
            <!-- Pagination -->
            <?php $total_pages = ceil($total_posts / $limit); ?>
            <?php if ($total_pages > 1): ?>
                <nav class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?id=<?php echo $topic_id; ?>&page=<?php echo $i; ?>" class="page-number <?php echo $i == $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </nav>
            <?php endif; ?>
        </div>
        
        <!-- Quick Actions -->
        <div class="quick-actions">
            <?php if($user && !$topic['is_locked']): ?>
                <a href="#reply-form" class="quick-btn reply">
                    <i class="fas fa-reply"></i>
                </a>
            <?php endif; ?>
            <a href="forum.php?id=<?php echo $topic['forum_id']; ?>" class="quick-btn back">
                <i class="fas fa-arrow-left"></i>
            </a>
        </div>
    </main>

    <script src="assets/scripts/main.js"></script>
    <script src="assets/scripts/media-preview.js"></script>
    <script>
        function shareTopic() {
            if (navigator.share) {
                navigator.share({
                    title: document.title,
                    text: 'Check out this topic on PSUC Forum!',
                    url: window.location.href
                }).catch(console.error);
            } else {
                navigator.clipboard.writeText(window.location.href).then(function() {
                    showNotification('Topic URL copied to clipboard!', 'success');
                }, function(err) {
                    showNotification('Could not copy URL.', 'error');
                });
            }
        }

        function sharePost(postId) {
            const url = window.location.href.split('#')[0] + '#post-' + postId;
            if (navigator.share) {
                navigator.share({
                    title: document.title,
                    text: 'Check out this post on PSUC Forum!',
                    url: url
                }).catch(console.error);
            } else {
                navigator.clipboard.writeText(url).then(function() {
                    showNotification('Post URL copied to clipboard!', 'success');
                }, function(err) {
                    showNotification('Could not copy URL.', 'error');
                });
            }
        }
        
        function copyPostLink(postId) {
            const url = window.location.href.split('#')[0] + '#post-' + postId;
            navigator.clipboard.writeText(url).then(function() {
                showNotification('Post link copied!', 'success');
            }, function(err) {
                showNotification('Could not copy link.', 'error');
            });
        }
        
        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }
        
        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'}`;
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 1000;
                min-width: 300px;
                padding: 1rem;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                animation: slideIn 0.3s ease;
            `;
            notification.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i> ${message}`;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 3000);
        }
        
        // Add CSS animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
        `;
        document.head.appendChild(style);
        
        // Smooth scroll to anchors
        document.addEventListener('DOMContentLoaded', function() {
            const hash = window.location.hash;
            if (hash) {
                setTimeout(() => {
                    const element = document.querySelector(hash);
                    if (element) {
                        element.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        element.style.animation = 'highlight 2s ease';
                    }
                }, 100);
            }
        });
        
        // Add highlight animation
        const highlightStyle = document.createElement('style');
        highlightStyle.textContent = `
            @keyframes highlight {
                0% { background-color: rgba(59, 130, 246, 0.2); }
                50% { background-color: rgba(59, 130, 246, 0.1); }
                100% { background-color: transparent; }
            }
        `;
        document.head.appendChild(highlightStyle);
    </script>
</body>
</html>