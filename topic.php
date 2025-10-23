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

    <main class="container">
        <!-- Enhanced Topic Header -->
        <div class="topic-header-enhanced">
            <nav class="breadcrumb-nav">
                <a href="index.php"><i class="fas fa-home"></i> Forum</a>
                <i class="fas fa-chevron-right"></i>
                <a href="forum.php?id=<?php echo $topic['forum_id']; ?>"><?php echo htmlspecialchars($topic['forum_name']); ?></a>
                <i class="fas fa-chevron-right"></i>
                <span><?php echo htmlspecialchars($topic['title']); ?></span>
            </nav>
            
            <h1 class="topic-title-enhanced"><?php echo htmlspecialchars($topic['title']); ?></h1>
            
            <div class="topic-meta-enhanced">
                <div class="meta-item">
                    <i class="fas fa-user"></i>
                    <span>Started by <strong><?php echo htmlspecialchars($topic['username']); ?></strong></span>
                </div>
                <div class="meta-item">
                    <i class="fas fa-calendar"></i>
                    <span><?php echo date('M j, Y g:i A', strtotime($topic['created_at'])); ?></span>
                </div>
                <div class="meta-item">
                    <i class="fas fa-eye"></i>
                    <span><?php echo number_format($topic['views']); ?> views</span>
                </div>
            </div>
            
            <div class="topic-stats-bar">
                <div class="stat-badge">
                    <i class="fas fa-reply"></i>
                    <span><?php echo $total_posts; ?> replies</span>
                </div>
                <?php if($topic['votes_up'] > 0 || $topic['votes_down'] > 0): ?>
                <div class="stat-badge">
                    <i class="fas fa-thumbs-up"></i>
                    <span><?php echo $topic['votes_up']; ?> up, <?php echo $topic['votes_down']; ?> down</span>
                </div>
                <?php endif; ?>
            </div>
        </div>

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
                <div class="posts-container">

                    <!-- Original Topic Post -->
                    <div class="post" id="original-post">
                        <div class="post-author">
                            <img src="assets/avatars/<?php echo $topic['avatar']; ?>" alt="Avatar" onerror="this.src='assets/avatars/default.png'">
                            <h5><?php echo htmlspecialchars($topic['username']); ?></h5>
                            <div class="role"><?php echo ucfirst($topic['role'] ?? 'Member'); ?></div>
                            <div style="margin-top: 0.5rem; font-size: 0.8rem; color: var(--text-secondary);">
                                <i class="fas fa-star"></i> Reputation: <?php echo $topic['reputation'] ?? 0; ?>
                            </div>
                        </div>

                        <div class="post-content">
                            <div class="post-header">
                                <div class="post-date">
                                    <?php echo date('M j, Y g:i A', strtotime($topic['created_at'])); ?>
                                </div>
                                <div style="display: flex; gap: 0.5rem; align-items: center;">
                                    <span class="badge" style="background: var(--accent-gold); color: white; padding: 0.25rem 0.5rem; border-radius: 12px; font-size: 0.75rem;">
                                        <i class="fas fa-crown"></i> Original Post
                                    </span>
                                </div>
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
                    <?php if(!empty($posts)): ?>
                        <div style="margin: 2rem 0; padding: 1rem; background: rgba(59, 130, 246, 0.05); border-radius: 12px; text-align: center;">
                            <h3 style="margin: 0; color: var(--primary-blue); font-size: 1.2rem;">
                                <i class="fas fa-comments"></i> Replies (<?php echo count($posts); ?>)
                            </h3>
                        </div>
                    <?php endif; ?>
                    
                    <?php foreach($posts as $index => $post): ?>
                        <div class="post" id="post-<?php echo $post['id']; ?>">
                            <div class="post-author">
                                <img src="assets/avatars/<?php echo $post['avatar']; ?>" alt="Avatar" onerror="this.src='assets/avatars/default.png'">
                                <h5><?php echo htmlspecialchars($post['username']); ?></h5>
                                <div class="role"><?php echo ucfirst($post['role']); ?></div>
                                <div style="margin-top: 0.5rem; font-size: 0.8rem; color: var(--text-secondary);">
                                    <i class="fas fa-star"></i> Reputation: <?php echo $post['reputation'] ?? 0; ?>
                                </div>
                            </div>
                            <div class="post-content">
                                <div class="post-header">
                                    <div class="post-date">
                                        <?php echo date('M j, Y g:i A', strtotime($post['created_at'])); ?>
                                    </div>
                                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                                        <span class="badge" style="background: var(--secondary-blue); color: white; padding: 0.25rem 0.5rem; border-radius: 12px; font-size: 0.75rem;">
                                            #<?php echo $index + 1; ?>
                                        </span>
                                        <button class="btn btn-secondary" onclick="copyPostLink(<?php echo $post['id']; ?>)" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;">
                                            <i class="fas fa-link"></i>
                                        </button>
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

                </div>
                
                <!-- Reply Form -->
                <?php if($topic['is_locked']): ?>
                <div class="reply-form-enhanced text-center">
                    <div class="form-header" style="justify-content: center;">
                        <i class="fas fa-lock" style="background: var(--danger-color);"></i>
                        <h3>Topic Locked</h3>
                    </div>
                    <p class="text-secondary">This topic has been locked and no new replies can be posted.</p>
                </div>
                <?php elseif(!$user): ?>
                <div class="reply-form-enhanced text-center">
                    <div class="form-header" style="justify-content: center;">
                        <i class="fas fa-sign-in-alt" style="background: var(--secondary-blue);"></i>
                        <h3>Join the Discussion</h3>
                    </div>
                    <p style="margin-bottom: 1.5rem; color: var(--text-secondary);">Please login to participate in this discussion</p>
                    <div style="display: flex; gap: 1rem; justify-content: center;">
                        <a href="login.php" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                        <a href="register.php" class="btn btn-secondary">
                            <i class="fas fa-user-plus"></i> Register
                        </a>
                    </div>
                </div>
                <?php else: ?>
                <div class="reply-form-enhanced" id="reply-form">
                    <div class="form-header">
                        <i class="fas fa-reply"></i>
                        <h3>Post a Reply</h3>
                    </div>
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="create_post">
                        <div class="form-group">
                            <label for="content">
                                <i class="fas fa-edit"></i> Your Reply
                            </label>
                            <textarea name="content" id="content" class="form-control" rows="6" required placeholder="Share your thoughts, ask questions, or provide helpful information..."></textarea>
                            <div class="form-text">
                                Be respectful and constructive in your response
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="attachments">
                                <i class="fas fa-paperclip"></i> Attach Files (Optional)
                            </label>
                            <input type="file" id="attachments" name="attachments[]" class="form-control" multiple accept="image/*,video/*,.pdf,.doc,.docx,.txt,.zip,.rar">
                            <div class="form-text">
                                Supported formats: Images, Videos, Documents (Max 10MB each)
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-reply"></i> Post Reply
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="document.getElementById('content').value = '';">
                                <i class="fas fa-eraser"></i> Clear
                            </button>
                        </div>
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
        
        <!-- Floating Action Buttons -->
        <div class="floating-actions">
            <?php if($user && !$topic['is_locked']): ?>
            <a href="#reply-form" class="floating-btn reply-btn" title="Reply to Topic">
                <i class="fas fa-reply"></i>
            </a>
            <?php endif; ?>
            <a href="forum.php?id=<?php echo $topic['forum_id']; ?>" class="floating-btn back-btn" title="Back to Forum">
                <i class="fas fa-arrow-left"></i>
            </a>
            <button onclick="scrollToTop()" class="floating-btn" title="Scroll to Top">
                <i class="fas fa-arrow-up"></i>
            </button>
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