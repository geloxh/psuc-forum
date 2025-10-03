<?php
    require_once 'includes/auth.php';
    require_once 'includes/forum.php';

    $auth = new Auth();
    $forum = new Forum();
    $user = $auth->getCurrentUser();

    $topic_id = $_GET['id'] ?? 0;
    $page = $_GET['page'] ?? 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;

    $topic = $forum->getTopic($topic_id);
    if(!$topic) {
        header('Location: index.php');
        exit;
    }

    $posts = $forum->getPosts($topic_id, $limit, $offset);

    // Handle new post
    if($_POST && $user) {
        if($forum->createPost($topic_id, $user['id'], $_POST['content'])) {
            header("Location: topic.php?id=$topic_id");
            exit;
        }
    }

    // Handle voting
    if($_GET['action'] == 'vote' && $user) {
        $forum->vote($user['id'], $_GET['type'], $_GET['target_id'], $_GET['vote']);
        header("Location: topic.php?id=$topic_id");
        exit;
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($topic['title']); ?> - PSUC Forum</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/dark-theme.css">
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
                        <a href="forum.php?id=<?php echo $topic['forum_id']; ?>"><?php echo htmlspecialchars($topic['forum_name']); ?></a> > 
                        <strong><?php echo htmlspecialchars($topic['title']); ?></strong>
                    </nav>
                    
                    <h1><?php echo htmlspecialchars($topic['title']); ?></h1>
                    <div class="topic-meta">
                        Started by <strong><?php echo htmlspecialchars($topic['username']); ?></strong> • 
                        <?php echo date('M j, Y g:i A', strtotime($topic['created_at'])); ?> • 
                        <?php echo $topic['views']; ?> views
                    </div>
                </div>

                <!-- Original Topic Post -->
                <div class="post">
                    <div class="post-author">
                        <img src="assets/avatars/<?php echo $topic['avatar']; ?>" alt="Avatar" onerror="this.src='assets/avatars/default.png'">
                        <h5><?php echo htmlspecialchars($topic['username']); ?></h5>
                        <div class="role"><?php echo ucfirst($topic['role'] ?? 'Member'); ?></div>
                        <div style="margin-top: 0.5rem; font-size: 0.8rem; color: var(--text-secondary);">
                            Reputation: <?php echo $topic['reputation']; ?>
                        </div>
                    </div>
                    <div class="post-content">
                        <div class="post-header">
                            <div class="post-date">
                                <?php echo date('M j, Y g:i A', strtotime($topic['created_at'])); ?>
                            </div>
                        </div>
                        <div class="post-body">
                            <?php echo nl2br(htmlspecialchars($topic['content'])); ?>
                        </div>
                        <div class="post-actions">
                            <?php if($user): ?>
                                <div class="vote-buttons">
                                    <a href="?id=<?php echo $topic_id; ?>&action=vote&type=topic&target_id=<?php echo $topic['id']; ?>&vote=up" 
                                       class="vote-btn">
                                        <i class="fas fa-thumbs-up"></i> <?php echo $topic['votes_up']; ?>
                                    </a>
                                    <a href="?id=<?php echo $topic_id; ?>&action=vote&type=topic&target_id=<?php echo $topic['id']; ?>&vote=down" 
                                       class="vote-btn">
                                        <i class="fas fa-thumbs-down"></i> <?php echo $topic['votes_down']; ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Replies -->
                <?php foreach($posts as $post): ?>
                    <div class="post">
                        <div class="post-author">
                            <img src="assets/avatars/<?php echo $post['avatar']; ?>" alt="Avatar" onerror="this.src='assets/avatars/default.png'">
                            <h5><?php echo htmlspecialchars($post['username']); ?></h5>
                            <div class="role"><?php echo ucfirst($post['role']); ?></div>
                            <div style="margin-top: 0.5rem; font-size: 0.8rem; color: var(--text-secondary);">
                                Reputation: <?php echo $post['reputation']; ?>
                            </div>
                        </div>
                        <div class="post-content">
                            <div class="post-header">
                                <div class="post-date">
                                    <?php echo date('M j, Y g:i A', strtotime($post['created_at'])); ?>
                                </div>
                            </div>
                            <div class="post-body">
                                <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                            </div>
                            <div class="post-actions">
                                <?php if($user): ?>
                                    <div class="vote-buttons">
                                        <a href="?id=<?php echo $topic_id; ?>&action=vote&type=post&target_id=<?php echo $post['id']; ?>&vote=up" 
                                           class="vote-btn">
                                            <i class="fas fa-thumbs-up"></i> <?php echo $post['votes_up']; ?>
                                        </a>
                                        <a href="?id=<?php echo $topic_id; ?>&action=vote&type=post&target_id=<?php echo $post['id']; ?>&vote=down" 
                                           class="vote-btn">
                                            <i class="fas fa-thumbs-down"></i> <?php echo $post['votes_down']; ?>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <!-- Reply Form -->
                <?php if($user && !$topic['is_locked']): ?>
                    <div class="p-3" style="border-top: 1px solid var(--border-color);">
                        <h3>Post Reply</h3>
                        <form method="POST">
                            <div class="form-group">
                                <textarea name="content" class="form-control" rows="6" placeholder="Write your reply..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-reply"></i> Post Reply
                            </button>
                        </form>
                    </div>
                <?php elseif($topic['is_locked']): ?>
                    <div class="p-3 text-center" style="border-top: 1px solid var(--border-color);">
                        <i class="fas fa-lock" style="font-size: 2rem; color: var(--danger-color); margin-bottom: 1rem;"></i>
                        <h3>Topic Locked</h3>
                        <p class="text-secondary">This topic has been locked and no new replies can be posted.</p>
                    </div>
                <?php elseif(!$user): ?>
                    <div class="p-3 text-center" style="border-top: 1px solid var(--border-color);">
                        <p>Please <a href="login.php">login</a> to post a reply.</p>
                    </div>
                <?php endif; ?>
            </div>

            <aside class="sidebar">
                <div class="widget">
                    <h3><i class="fas fa-info-circle"></i> Topic Info</h3>
                    <div class="stats-grid">
                        <div class="stat-item">
                            <strong><?php echo count($posts); ?></strong>
                            <span>Replies</span>
                        </div>
                        <div class="stat-item">
                            <strong><?php echo $topic['views']; ?></strong>
                            <span>Views</span>
                        </div>
                    </div>
                </div>

                <?php if($user): ?>
                    <div class="widget">
                        <h3><i class="fas fa-tools"></i> Quick Actions</h3>
                        <a href="new_topic.php?forum_id=<?php echo $topic['forum_id']; ?>" class="btn btn-primary" style="width: 100%; margin-bottom: 0.5rem;">
                            <i class="fas fa-plus"></i> New Topic
                        </a>
                        <a href="forum.php?id=<?php echo $topic['forum_id']; ?>" class="btn btn-secondary" style="width: 100%;">
                            <i class="fas fa-arrow-left"></i> Back to Forum
                        </a>
                    </div>
                <?php endif; ?>
            </aside>
        </div>
    </main>

    <script>
        function toggleTheme() {
            const body = document.body;
            const icon = document.getElementById('themeIcon');
            
            if (body.classList.contains('dark-theme')) {
                body.classList.remove('dark-theme');
                icon.className = 'fas fa-moon';
                localStorage.setItem('theme', 'light');
            } else {
                body.classList.add('dark-theme');
                icon.className = 'fas fa-sun';
                localStorage.setItem('theme', 'dark');
            }
        }

        function toggleDropdown() {
            document.getElementById('userDropdown').classList.toggle('show');
        }

        // Load saved theme
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme');
            const icon = document.getElementById('themeIcon');
            
            if (savedTheme === 'dark') {
                document.body.classList.add('dark-theme');
                if(icon) icon.className = 'fas fa-sun';
            }
        });

        // Close dropdown when clicking outside
        window.onclick = function(event) {
            if (!event.target.matches('.user-menu a')) {
                var dropdown = document.getElementById('userDropdown');
                if (dropdown && dropdown.classList.contains('show')) {
                    dropdown.classList.remove('show');
                }
            }
        }
    </script>
</body>
</html>