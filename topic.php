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
    <link rel="stylesheet" href="assets/stylesheets/dark-theme.css">
    <link rel="stylesheet" href="assets/stylesheets/media-preview.css">
    <link rel="stylesheet" href="assets/stylesheets/topic-redesign.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container">
        <div class="topic-page">
            <div class="topic-header">
                <nav class="breadcrumb">
                    <a href="index.php">Forum</a> > 
                    <a href="forum.php?id=<?php echo $topic['forum_id']; ?>"><?php echo htmlspecialchars($topic['forum_name']); ?></a> > 
                    <strong><?php echo htmlspecialchars($topic['title']); ?></strong>
                </nav>
                
                <div class="topic-title-section">
                    <h1><?php echo htmlspecialchars($topic['title']); ?></h1>
                    <div class="topic-stats">
                        <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($topic['username']); ?></span>
                        <span><i class="fas fa-calendar"></i> <?php echo date('M j, Y g:i A', strtotime($topic['created_at'])); ?></span>
                        <span><i class="fas fa-eye"></i> <?php echo $topic['views']; ?> views</span>
                        <span><i class="fas fa-reply"></i> <?php echo $total_posts; ?> replies</span>
                    </div>
                </div>
            </div>
            
            <div class="posts-container">
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

                <!-- Original Topic Post -->
                <div class="main-post">
                    <div class="post-header">
                        <img src="assets/avatars/<?php echo $topic['avatar']; ?>" alt="Avatar" onerror="this.src='assets/avatars/default.png'" class="avatar">
                        <div class="author-info">
                            <h4><?php echo htmlspecialchars($topic['username']); ?></h4>
                            <span class="role"><?php echo ucfirst($topic['role'] ?? 'Member'); ?></span>
                        </div>
                        <div class="post-date">
                            <?php echo date('M j, Y g:i A', strtotime($topic['created_at'])); ?>
                        </div>
                    </div>
                    
                    <div class="post-content">
                        <?php echo nl2br(htmlspecialchars($topic['content'])); ?>
                    </div>

                        <?php
                            $topic_attachments = $forum->getAttachments($posts[0]['id'] ?? 0); // The first post is the topic content
                        ?>
                    <?php if (!empty($topic_attachments)): ?>
                        <div class="media-preview-container">
                            <?php foreach ($topic_attachments as $attachment): ?>
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
                                    <div class="media-preview-item">
                                        <div class="pdf-preview">
                                            <i class="fas fa-file-pdf"></i>
                                            <span><?php echo htmlspecialchars($attachment['file_name']); ?></span>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="post-actions">
                        <?php if($user): ?>
                            <a href="?id=<?php echo $topic_id; ?>&action=vote&type=topic&target_id=<?php echo $topic['id']; ?>&vote=up" class="action-btn">
                                <i class="fas fa-thumbs-up"></i> <?php echo $topic['votes_up']; ?>
                            </a>
                            <a href="?id=<?php echo $topic_id; ?>&action=vote&type=topic&target_id=<?php echo $topic['id']; ?>&vote=down" class="action-btn">
                                <i class="fas fa-thumbs-down"></i> <?php echo $topic['votes_down']; ?>
                            </a>
                            <button class="action-btn" onclick="shareTopic()">
                                <i class="fas fa-share-alt"></i> Share
                            </button>
                            <?php if ($user['id'] == $topic['user_id'] || $auth -> isAdmin()): ?>
                                <a href="edit_topic.php?id=<?php echo $topic['id']; ?>" class="action-btn">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Replies -->
                <?php foreach($posts as $post): ?>
                    <div class="reply-post" id="post-<?php echo $post['id']; ?>">
                        <div class="post-header">
                            <img src="assets/avatars/<?php echo $post['avatar']; ?>" alt="Avatar" onerror="this.src='assets/avatars/default.png'" class="avatar">
                            <div class="author-info">
                                <h5><?php echo htmlspecialchars($post['username']); ?></h5>
                                <span class="role"><?php echo ucfirst($post['role']); ?></span>
                            </div>
                            <div class="post-date">
                                <?php echo date('M j, Y g:i A', strtotime($post['created_at'])); ?>
                            </div>
                        </div>
                        
                        <div class="post-content">
                            <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                        </div>

                        <?php if (!empty($post['attachments'])): ?>
                            <div class="media-preview-container">
                                <?php foreach ($post['attachments'] as $attachment): ?>
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
                                        <div class="media-preview-item">
                                            <div class="pdf-preview">
                                                <i class="fas fa-file-pdf"></i>
                                                <span><?php echo htmlspecialchars($attachment['file_name']); ?></span>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="post-actions">
                            <?php if($user): ?>
                                <a href="?id=<?php echo $topic_id; ?>&action=vote&type=post&target_id=<?php echo $post['id']; ?>&vote=up" class="action-btn">
                                    <i class="fas fa-thumbs-up"></i> <?php echo $post['votes_up']; ?>
                                </a>
                                <a href="?id=<?php echo $topic_id; ?>&action=vote&type=post&target_id=<?php echo $post['id']; ?>&vote=down" class="action-btn">
                                    <i class="fas fa-thumbs-down"></i> <?php echo $post['votes_down']; ?>
                                </a>
                                <?php if ($user['id'] == $post['user_id'] || $auth->isAdmin()): ?>
                                    <a href="edit_post.php?id=<?php echo $post['id']; ?>" class="action-btn">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <!-- Reply Form -->
                <?php if($topic['is_locked']): ?>
                    <div class="locked-message">
                        <i class="fas fa-lock"></i>
                        <h3>Topic Locked</h3>
                        <p>This topic has been locked and no new replies can be posted.</p>
                    </div>
                <?php elseif(!$user): ?>
                    <div class="login-prompt">
                        <p>Please <a href="login.php">login</a> to post a reply.</p>
                    </div>
                <?php else: ?>
                    <div class="reply-form">
                        <h3>Post a Reply</h3>
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="create_post">
                            <textarea name="content" class="form-control" rows="4" required placeholder="Write your reply..."></textarea>
                            <div class="form-actions">
                                <input type="file" id="attachments" name="attachments[]" multiple accept="image/*,video/*,.pdf,.doc,.docx,.txt,.zip,.rar" style="display: none;">
                                <button type="button" class="attach-btn" onclick="document.getElementById('attachments').click()">
                                    <i class="fas fa-paperclip"></i> Attach Files
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-reply"></i> Post Reply
                                </button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php $total_pages = ceil($total_posts / $limit); ?>
            <?php if ($total_pages > 1): ?>
                <div class="pagination-wrapper">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?id=<?php echo $topic_id; ?>&page=<?php echo $i; ?>" class="page-btn <?php echo $i == $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
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