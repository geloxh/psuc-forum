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
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container">
        <div class="topic-layout">
            <aside class="left-sidebar">
                <div class="widget">
                    <div class="widget-header">
                        <div class="widget-icon">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <h3>Topic Info</h3>
                    </div>
                    <div class="topic-info-details">
                        <div class="info-item">
                            <div class="info-icon"><i class="fas fa-user"></i></div>
                            <div class="info-content">
                                <span class="info-label">Started by</span>
                                <span class="info-value"><?php echo htmlspecialchars($topic['username']); ?></span>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-icon"><i class="fas fa-calendar"></i></div>
                            <div class="info-content">
                                <span class="info-label">Created</span>
                                <span class="info-value"><?php echo date('M j, Y', strtotime($topic['created_at'])); ?></span>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-icon"><i class="fas fa-reply"></i></div>
                            <div class="info-content">
                                <span class="info-label">Replies</span>
                                <span class="info-value"><?php echo $total_posts; ?></span>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-icon"><i class="fas fa-eye"></i></div>
                            <div class="info-content">
                                <span class="info-label">Views</span>
                                <span class="info-value"><?php echo number_format($topic['views']); ?></span>
                            </div>
                        </div>
                        <?php if($topic['votes_up'] > 0 || $topic['votes_down'] > 0): ?>
                        <div class="info-item">
                            <div class="info-icon"><i class="fas fa-thumbs-up"></i></div>
                            <div class="info-content">
                                <span class="info-label">Votes</span>
                                <span class="info-value"><?php echo $topic['votes_up']; ?> up, <?php echo $topic['votes_down']; ?> down</span>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </aside>
            
            <div class="forum-content">
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
                            Reputation: <?php echo $topic['reputation'] ?? 0; ?>
                        </div>
                    </div>

                    <div class="post-content">
                        <div class="post-header">
                            <div class="post-date">
                                <?php echo date('M j, Y g:i A', strtotime($topic['created_at'])); ?>
                            </div>
                        </div>
                        <div class="post-body">
                            <?php echo nl2br(htmlspecialchars($topic['content'])); // Security: Escape output ?>
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
                                <div class="post-actions-buttons">
                                    <button class="btn btn-secondary" onclick="shareTopic()">
                                        <i class="fas fa-share-alt"></i>Share
                                    </button>
                                    <?php if ($user['id'] == $topic['user_id'] || $auth -> isAdmin()): ?>
                                        <a href="edit_topic.php?id=<?php echo $topic['id']; ?>" class="btn btn-secondary">
                                            <i class="fas fa-edit"></i>Edit
                                        </a>
                                        <a href="delete_topic.php?id=<?php echo $topic['id']; ?>" class="btn btn-danger">
                                            <i class="fas fa-trash"></i>Delete
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Replies -->
                    <?php foreach($posts as $post): ?>
                        <div class="post" id="post-<?php echo $post['id']; ?>">
                            <div class="post-author">
                                <img src="assets/avatars/<?php echo $post['avatar']; ?>" alt="Avatar" onerror="this.src='assets/avatars/default.png'">
                                <h5><?php echo htmlspecialchars($post['username']); ?></h5>
                                <div class="role"><?php echo ucfirst($post['role']); ?></div>
                                    <div style="margin-top: 0.5rem; font-size: 0.8rem; color: var(--text-secondary);">
                                        Reputation: <?php echo $post['reputation'] ?? 0; ?>
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
                                            <div class="post-actions-buttons">
                                                <button class="btn btn-secondary" onclick="sharePost(<?php echo $post['id']; ?>)">
                                                    <i class="fas fa-share-alt"></i>Share
                                                </button>
                                                <?php if ($user['id'] == $post['user_id'] || $auth->isAdmin()): ?>
                                                    <a href="edit_post.php?id=<?php echo $post['id']; ?>" class="btn btn-secondary">
                                                        <i class="fas fa-edit"></i>Edit
                                                    </a>
                                                    <a href="delete_post.php?id=<?php echo $post['id']; ?>" class="btn btn-danger">
                                                        <i class="fas fa-trash"></i>Delete
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                        </div>
                    <?php endforeach; ?>

                    <!-- Reply Form -->
                    <?php if($topic['is_locked']): ?>
                    <div class="p-3 text-center" style="border-top: 1px solid var(--border-color);">
                        <i class="fas fa-lock" style="font-size: 2rem; color: var(--danger-color); margin-bottom: 1rem;"></i>
                        <h3>Topic Locked</h3>
                        <p class="text-secondary">This topic has been locked and no new replies can be posted.</p>
                    </div>
                    <?php elseif(!$user): ?>
                    <div class="p-3 text-center" style="border-top: 1px solid var(--border-color);">
                        <p>Please <a href="login.php">login</a> to post a reply.</p>
                    </div>
                    <?php else: // User is logged in and topic is not locked ?>
                        <div class="p-3" style="border-top: 1px solid var(--border-color);">
                            <h3>Post a Reply</h3>
                            <?php if ($error): ?>
                                <div class="alert alert-danger"><?php echo $error; ?></div>
                            <?php endif; ?>
                            <form method="POST" enctype="multipart/form-data" class="reply-form">
                                <input type="hidden" name="action" value="create_post">
                                <div class="form-group">
                                    <textarea name="content" class="form-control" rows="5" required placeholder="Write your reply..."></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="attachments">Attach Files</label>
                                    <input type="file" id="attachments" name="attachments[]" class="form-control-file" multiple accept="image/*,video/*,.pdf,.doc,.docx,.txt,.zip,.rar">
                                </div>
                                <button type="submit" class="btn btn-primary"><i class="fas fa-reply"></i> Post Reply</button>
                            </form>
                        </div>
                <?php endif; ?>
            </div>
            
            <aside class="right-sidebar">
                <?php $total_pages = ceil($total_posts / $limit); ?>
                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?id=<?php echo $topic_id; ?>&page=<?php echo $i; ?>" class="<?php echo $i == $page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>

                <?php if($user): ?>
                    <div class="widget">
                        <div class="widget-header">
                            <div class="widget-icon">
                                <i class="fas fa-bolt"></i>
                            </div>
                            <h3>Quick Actions</h3>
                        </div>
                        <div class="quick-actions-list">
                            <a href="new_topic.php?forum_id=<?php echo $topic['forum_id']; ?>" class="action-item">
                                <div class="action-icon"><i class="fas fa-plus"></i></div>
                                <div class="action-content">
                                    <span class="action-title">New Topic</span>
                                    <span class="action-desc">Start a new discussion</span>
                                </div>
                            </a>
                            <a href="forum.php?id=<?php echo $topic['forum_id']; ?>" class="action-item">
                                <div class="action-icon"><i class="fas fa-arrow-left"></i></div>
                                <div class="action-content">
                                    <span class="action-title">Back to Forum</span>
                                    <span class="action-desc">Return to <?php echo htmlspecialchars($topic['forum_name']); ?></span>
                                </div>
                            </a>
                            <button onclick="shareTopic()" class="action-item">
                                <div class="action-icon"><i class="fas fa-share-alt"></i></div>
                                <div class="action-content">
                                    <span class="action-title">Share Topic</span>
                                    <span class="action-desc">Share this discussion</span>
                                </div>
                            </button>
                            <?php if($user['id'] == $topic['user_id'] || $auth->isAdmin()): ?>
                            <a href="edit_topic.php?id=<?php echo $topic_id; ?>" class="action-item">
                                <div class="action-icon"><i class="fas fa-edit"></i></div>
                                <div class="action-content">
                                    <span class="action-title">Edit Topic</span>
                                    <span class="action-desc">Modify this topic</span>
                                </div>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="widget">
                        <div class="widget-header">
                            <div class="widget-icon">
                                <i class="fas fa-sign-in-alt"></i>
                            </div>
                            <h3>Join Discussion</h3>
                        </div>
                        <div style="text-align: center; padding: 1rem 0;">
                            <p style="margin-bottom: 1rem; color: var(--text-secondary);">Login to participate in this discussion</p>
                            <a href="login.php" class="btn btn-primary" style="width: 100%; margin-bottom: 0.5rem;">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </a>
                            <a href="register.php" class="btn btn-secondary" style="width: 100%;">
                                <i class="fas fa-user-plus"></i> Register
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </aside>
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
                // Fallback for browsers that don't support the Web Share API
                navigator.clipboard.writeText(window.location.href).then(function() {
                    alert('Topic URL copied to clipboard!');
                }, function(err) {
                    alert('Could not copy URL.');
                });
            }
        }

        function sharePost(postId) {
            const url = window.location.href + '#post-' + postId;
            if (navigator.share) {
                navigator.share({
                    title: document.title,
                    text: 'Check out this post on PSUC Forum!',
                    url: url
                }).catch(console.error);
            } else {
                navigator.clipboard.writeText(url).then(function() {
                    alert('Post URL copied to clipboard!');
                }, function(err) {
                    alert('Could not copy URL.');
                });
            }
        }
    </script>
</body>
</html>