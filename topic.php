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

    // Get the first post ID for the topic (original post)
    $first_post_query = "SELECT id FROM posts WHERE topic_id = ? ORDER BY created_at ASC LIMIT 1";
    $stmt = $forum -> prepareQuery($first_post_query);
    $stmt -> execute([$topic_id]);
    $first_post = $stmt -> fetch(PDO::FETCH_ASSOC);
    $original_post_id = $first_post['id'] ?? 0;

    // Handle new post creation
    if ($_POST && isset($_POST['action']) && $_POST['action'] == 'create_post' && $user) {
        try {
            $post_id = $forum -> createPost($topic_id, $user['id'], $_POST['content']);
            if ($post_id) {
                $new_total_posts  = $forum -> getPostCount($topic_id);
                $last_page = ceil($new_total_posts / $limit);
                header("Location: topic.php?id=$topic_id&page=$last_page#post-$post_id");
                exit;
            }
        } catch (Exception $e) {
            $error = $e -> getMessage();
        }
    }

    // Handle voting
    if (isset($_GET['action']) && $_GET['action'] == 'vote' && $user) {
        $forum -> vote($user['id'], $_GET['type'], $_GET['target_id'], $_GET['vote']);
        header("Location: topic.php?id=$topic_id&page=$page");
        exit;
    }

    // Handle delete action
    if (isset($_GET['action']) && $_GET['action'] == 'delete' && $user) {
        if (isset($_GET['type']) && isset($_GET['target_id'])) {
            if ($_GET['type'] == 'topic' && ($user['id'] == $topic['user_id'] || $auth->isAdmin())) {
                if ($forum->deleteTopic($_GET['target_id'])) {
                    header("Location: forum.php?id=" . $topic['forum_id']);
                    exit;
                }
            } elseif ($_GET['type'] == 'post' && isset($_GET['owner_id']) && ($user['id'] == $_GET['owner_id'] || $auth->isAdmin())) {
                
                if ($forum->deletePost($_GET['target_id'])) {
                header("Location: topic.php?id=$topic_id&page=$page");
                exit;
                }
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($topic['title']); ?> - PSUC Forum</title>
    <link rel="stylesheet" href="assets/stylesheets/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .topic-page { max-width: 900px; margin: 0 auto; padding: 2rem 1rem; }
        
        .topic-header { 
            background: white; 
            border-radius: 12px; 
            padding: 2rem; 
            margin-bottom: 2rem; 
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            border: 1px solid #f1f5f9;
        }
        
        .breadcrumb { 
            display: flex; 
            align-items: center; 
            gap: 0.5rem; 
            margin-bottom: 1.5rem; 
            font-size: 0.85rem; 
            color: #64748b; 
        }
        
        .breadcrumb a { color: #3b82f6; text-decoration: none; }
        .breadcrumb a:hover { text-decoration: underline; }
        
        .topic-header h1 { 
            font-size: 1.75rem; 
            font-weight: 600; 
            color: #1e293b; 
            margin-bottom: 1rem; 
            line-height: 1.3; 
        }
        
        .topic-meta { 
            display: flex; 
            gap: 1.5rem; 
            font-size: 0.85rem; 
            color: #64748b; 
            flex-wrap: wrap; 
        }
        
        .topic-meta strong { color: #3b82f6; }
        
        /* Threading Styles */
        .posts-section { 
            margin-bottom: 2rem;
            position: relative;
        }
        
        .post { 
            background: white; 
            border-radius: 12px; 
            padding: 1.5rem; 
            margin-bottom: 1.5rem; 
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            border: 1px solid #f1f5f9;
        }
        
        .post-author { 
            display: flex; 
            align-items: center; 
            gap: 0.75rem; 
            margin-bottom: 1rem; 
            padding-bottom: 1rem; 
            border-bottom: 1px solid #f1f5f9; 
        }
        
        .post-author img { 
            width: 40px; 
            height: 40px; 
            border-radius: 50%; 
            object-fit: cover; 
        }
        
        .author-info h4 { 
            margin: 0; 
            font-size: 0.9rem; 
            font-weight: 600; 
            color: #1e293b; 
        }
        
        .author-info .role { 
            font-size: 0.75rem; 
            color: #64748b; 
            text-transform: uppercase; 
            letter-spacing: 0.5px; 
        }
        
        .post-header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 1rem; 
        }
        
        .post-badge { 
            background: #f1f5f9; 
            color: #475569; 
            padding: 0.25rem 0.5rem; 
            border-radius: 4px; 
            font-size: 0.75rem; 
            font-weight: 500; 
        }
        
        .post-badge.original { background: #dbeafe; color: #1d4ed8; }
        .post-badge.reply { background: #f0fdf4; color: #166534; }
        
        .post-body { 
            color: #374151; 
            line-height: 1.6; 
            margin-bottom: 1rem; 
        }

        /* Original Post Styles  */
        #original-post {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border: 2px solid #e0f2fe;
            position: relative;
        }
        #original-post::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #3b82f6, #1d4ed8);
            border-radius: 12px 12px 0 0;
        }

        /* Reply Post Styles */
        .post:not(#original-post) {
            position: relative;
            margin-left: 3rem;
            margin-bottom: 1.5rem;
            padding: 1.25rem;
            background: #fafbfc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        /* Threading Connection Lines */
        .post:not(#original-post)::before {
            content: '';
            position: absolute;
            left: -3rem;
            top: 0;
            width: 3px;
            height: 100%;
            background: linear-gradient(180deg, #cbd5e1, #e2e8f0);
            border-radius: 2px;
        }

        .post:not(#original-post)::after {
            content: '';
            position: absolute;
            left: -3rem;
            top: 2.5rem;
            width: .51rem;
            height: 3px;
            background: linear-gradient(90deg, #cbd5e1, #e2e8f0);
            border-radius: 2px;
        }

        /* Reply Indicator */
        .post:not(#original-post) .post-badge .reply {
            background(135deg, #10b981, #059669);
            color: white;
            font-weight: 600;
            padding: 0.rem 0.6rem;
            border-radius: 6px;
            font-size: 0.75rem;
            box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2);
        }

        /* Author Section for Replies */
        .post:not(#original-post) .post-author {
            margin-bottom: 0.75rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .post:not(#original-post) .post-author img {
            width: 36px;
            height: 36px;
            border: 2px solid #e2e8f0;
        }

        .post:not(#original-post) .author-info h4 {
            font-size: 0.9rem;
            color: #1e293b;
        }

        .post:not(#original-post) .author-info .role {
            font-size: 0.7rem;
            color: #64748b;
            background: #f1f5f9;
            padding: 0.1rem 0.4rem;
            border-radius: 4px;
            display: inline-block;
            margin-top: 0.2rem;
        }

        /* Post Content Styling */
        .post:not(#original-post) .post-body {
            font-size: 0.9rem;
            line-height: 1.6;
            color: #374151;
            margin-bottom: 1rem;
        }

        /* Hover Effects */
        .post:not(#original-post):hover {
            background: white;
            border-color: #3b82f6;
            transform: translateX(4px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
            transition: all 0.2s ease;
        }

        .post:not(#original-post):hover::before {
            background: linear-gradient(180deg, #3b82f6, #1d4ed8);
            width: 4px;
        }

        .post:not(#original-post):hover::after {
            background: linear-gradient(90deg, #3b82f6, #1d4ed8);
            height: 4px;
        }

        /* Post Actions for Replies */
        .post:not(#original-post) .post-actions {
            padding-top: 0.75rem;
            border-top: 1px solid #e2e8f0;
        }

        .post:not(#original-post) .vote-btn,
        .post:not(#original-post) .action-btn {
            font-size: 0.75rem;
            padding: 0.4rem 0.5rem;
        }

        /* Mobile Responsive Threading */
        @media (max-width: 768px) {
            .post:not(#original-post) {
                margin-left: 2rem;
                padding: 1rem;
            }

            .post:not(#original-post)::before {
                left: -2rem;
                width: 3px;
            }

            .post:not(3original-post)::after {
                left: -1.5rem;
                width: 1rem;
                height: 2px;
            }

            .post:not(#original-post):hover {
                transform: translateY(2px);
            }
        }

        @media (max-width: 480px) {
            .post:not(#original-post) {
                margin-left: 1.5rem;
                padding: 0.75rem;
            }

            .post:not(#original-post)::before {
                left: -1.5rem;
            }

            .post:not(#original-post)::after {
                left: -1.5rem;
                width: 0.75rem;
            }
        }

        
        /* File Attachments Styles */
        .attachments { 
            margin: 1rem 0; 
            padding: 1rem; 
            background: #f8fafc; 
            border-radius: 8px; 
            border: 1px solid #e2e8f0; 
        }
        
        .attachments-header { 
            display: flex; 
            align-items: center; 
            gap: 0.5rem; 
            margin-bottom: 0.75rem; 
            font-weight: 600; 
            color: #374151; 
            font-size: 0.9rem; 
        }
        
        .media-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); 
            gap: 0.75rem; 
        }
        
        .media-item { 
            position: relative; 
            border-radius: 8px; 
            overflow: hidden; 
            background: white; 
            border: 1px solid #e2e8f0; 
            cursor: pointer; 
            transition: all 0.2s; 
        }
        
        .media-item:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 4px 12px rgba(0,0,0,0.1); 
        }
        
        .media-item img { 
            width: 100%; 
            height: 150px; 
            object-fit: cover; 
            display: block; 
        }
        
        .media-item video { 
            width: 100%; 
            height: 150px; 
            object-fit: cover; 
        }
        
        .file-item { 
            display: flex; 
            align-items: center; 
            gap: 0.75rem; 
            padding: 0.75rem; 
            background: white; 
            border: 1px solid #e2e8f0; 
            border-radius: 8px; 
            text-decoration: none; 
            color: #374151; 
            transition: all 0.2s; 
        }
        
        .file-item:hover { 
            background: #f8fafc; 
            border-color: #3b82f6; 
        }
        
        .file-icon { 
            width: 40px; 
            height: 40px; 
            background: #3b82f6; 
            color: white; 
            border-radius: 8px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            font-size: 1.2rem; 
        }
        
        .file-info { flex: 1; }
        .file-name { font-weight: 500; margin-bottom: 0.25rem; }
        .file-size { font-size: 0.8rem; color: #64748b; }

        /* Modal for image preview */
        .modal { 
            display: none; 
            position: fixed; 
            z-index: 1000; 
            left: 0; 
            top: 0; 
            width: 100%; 
            height: 100%; 
            background: rgba(0,0,0,0.9); 
        }
        
        .modal-content { 
            position: absolute; 
            top: 50%; 
            left: 50%; 
            transform: translate(-50%, -50%); 
            max-width: 90%; 
            max-height: 90%; 
        }
        
        .modal img, .modal video { 
            width: 100%; 
            height: auto; 
            border-radius: 8px; 
        }
        
        .modal-close { 
            position: absolute; 
            top: 20px; 
            right: 30px; 
            color: white; 
            font-size: 2rem; 
            cursor: pointer; 
        }
        
        .post-actions { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            padding-top: 1rem; 
            border-top: 1px solid #f1f5f9; 
        }
        
        .vote-buttons { display: flex; gap: 0.5rem; }
        
        .vote-btn { 
            display: flex; 
            align-items: center; 
            gap: 0.25rem; 
            padding: 0.5rem 0.75rem; 
            background: #f8fafc; 
            border: 1px solid #e2e8f0; 
            border-radius: 6px; 
            color: #64748b; 
            text-decoration: none; 
            font-size: 0.8rem; 
            transition: all 0.2s; 
        }
        
        .vote-btn:hover { background: #f1f5f9; color: #3b82f6; }
        
        .action-buttons { display: flex; gap: 0.5rem; }
        
        .action-btn { 
            padding: 0.5rem 0.75rem; 
            background: transparent; 
            border: 1px solid #e2e8f0; 
            border-radius: 6px; 
            color: #64748b; 
            text-decoration: none; 
            font-size: 0.8rem; 
            cursor: pointer; 
            transition: all 0.2s; 
        }
        
        .action-btn:hover { background: #f8fafc; color: #3b82f6; }
        
        .reply-form { 
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px; 
            padding: 1rem; 
            box-shadow: none;
        }
        
        .reply-form h3 { 
            margin: 0 0 0.75rem; 
            font-size: 0.09rem; 
            font-weight: 500; 
            color: #64748b; 
        }
        
        .form-field { margin-bottom: 0.75rem; }
        
        .form-field textarea { 
            width: 100%; 
            padding: 0.5rem; 
            border: 1px solid #e2e8f0; 
            border-radius: 6px; 
            font-family: inherit;
            font-size: 0.9rem;
            resize: vertical; 
            min-height: 80px;
            background: white;
        }
        
        .form-field textarea:focus { 
            outline: none; 
            border-color: #3b82f6; 
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1); 
        }

        .form-field input[type="file"] {
            font-size: 0.8rem;
            padding: 0.25rem;
        }

        .form-field small {
            font-size: 0.75rem;
            color: #64748b;
            display: block;
            margin-top: 0.25rem;
        }
        
        .form-actions { 
            display: flex; 
            gap: 0.5rem;
            align-items: center;
        }
        
        .submit-btn { 
            background: #3b82f6; 
            color: white; 
            border: none; 
            padding: 0.5rem 1rem; 
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 500; 
            cursor: pointer; 
            transition: all 0.2s; 
        }
        
        .submit-btn:hover { 
            background: #2563eb; 
        }
        
        .clear-btn { 
            background: transparent; 
            color: #64748b; 
            border: 1px solid #e2e8f0; 
            padding: 0.5rem 1rem; 
            border-radius: 6px;
            font-size: 0.85rem;
            cursor: pointer; 
            transition: all 0.2s; 
        }
        
        .clear-btn:hover { 
            background: #f1f5f9; 
        }
        
        .pagination { 
            display: flex; 
            justify-content: center; 
            gap: 0.5rem; 
            margin-top: 2rem; 
        }
        
        .page-number { 
            padding: 0.5rem 0.75rem; 
            border: 1px solid #e2e8f0; 
            border-radius: 6px; 
            color: #64748b; 
            text-decoration: none; 
            transition: all 0.2s; 
        }
        
        .page-number:hover, .page-number.active { 
            background: #3b82f6; 
            color: white; 
            border-color: #3b82f6; 
        }
        
        .quick-actions { 
            position: fixed; 
            bottom: 2rem; 
            right: 2rem; 
            display: flex; 
            flex-direction: column; 
            gap: 0.75rem; 
        }
        
        .quick-btn { 
            width: 48px; 
            height: 48px; 
            border-radius: 50%; 
            background: #3b82f6; 
            color: white; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            text-decoration: none; 
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3); 
            transition: all 0.2s; 
        }
        
        .quick-btn:hover { transform: scale(1.1); }
        .quick-btn.back { background: #64748b; }
        
        @media (max-width: 768px) {
            .topic-page { padding: 1rem 0.5rem; }
            .topic-header, .post, .reply-form { padding: 1rem; }
            .topic-header h1 { font-size: 1.5rem; }
            .topic-meta { gap: 1rem; }
            .post-actions { flex-direction: column; gap: 1rem; align-items: flex-start; }
            .quick-actions { bottom: 1rem; right: 1rem; }
            .media-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="topic-page">
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
                <span><?php echo number_format($topic['views']); ?>views</span>
                <span><?php echo $total_posts; ?>replies</span>
            </div>
        </header>

        <!-- Posts Section -->
        <div class="posts-section">
            <!-- Original Topic Post -->
            <article class="post" id="original-post">
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
                    
                        // Get attachments for the topic (check both topic_id and first post)
                        $topic_attachments_query = "SELECT * FROM attachments WHERE (topic_id = ? OR post_id = ?) ORDER BY uploaded_at DESC";
                        $topic_attachments_stmt = $forum -> prepareQuery($topic_attachments_query);
                        $topic_attachments_stmt -> execute([$topic_id, $original_post_id]);
                        $topic_attachments = $topic_attachments_stmt -> fetchAll(PDO::FETCH_ASSOC);

                    ?>
                    <?php if (!empty($topic_attachments)): ?>
                        <div class="attachments">
                            <div class="attachments-header">
                                <i class="fas fa-paperclip"></i>
                                Attachments (<?php echo count($topic_attachments); ?>)
                            </div>
                            <div class="media-grid">
                                <?php foreach ($topic_attachments as $attachment): ?>
                                    <?php if (strpos($attachment['file_type'], 'image/') === 0): ?>
                                        <div class="media-item" onclick="openModal('<?php echo htmlspecialchars($attachment['file_path']); ?>', 'image')">
                                            <img src="<?php echo htmlspecialchars($attachment['file_path']); ?>" alt="<?php echo htmlspecialchars($attachment['file_name']); ?>">
                                        </div>
                                    <?php elseif (strpos($attachment['file_type'], 'video/') === 0): ?>
                                        <div class="media-item" onclick="openModal('<?php echo htmlspecialchars($attachment['file_path']); ?>', 'video')">
                                            <video>
                                                <source src="<?php echo htmlspecialchars($attachment['file_path']); ?>" type="<?php echo htmlspecialchars($attachment['file_type']); ?>">
                                            </video>
                                        </div>
                                    <?php else: ?>
                                        <a href="<?php echo htmlspecialchars($attachment['file_path']); ?>" download="<?php echo htmlspecialchars($attachment['file_name']); ?>" class="file-item">
                                            <div class="file-icon">
                                                <i class="fas fa-file"></i>
                                            </div>
                                            <div class="file-info">
                                                <div class="file-name"><?php echo htmlspecialchars($attachment['file_name']); ?></div>
                                                <div class="file-size"><?php echo round($attachment['file_size'] / 1024, 2); ?> KB</div>
                                            </div>
                                        </a>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
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
                                <?php if ($user['id'] == $topic['user_id'] || $auth->isAdmin()): ?>
                                    <a href="edit_topic.php?id=<?php echo $topic['id']; ?>" class="action-btn">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="?id=<?php echo $topic_id; ?>&action=delete&type=topic&target_id=<?php echo $topic['id']; ?>"  
                                        class="action-btn" onclick="return confirm('Delete this post? This cannot be undone.')">
                                            <i class="fas fa-trash"></i> Delete
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </article>

            <!-- Replies -->
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

                        <?php if (!empty($post['attachments'])): ?>
                            <div class="attachments">
                                <div class="attachments-header">
                                    <i class="fas fa-paperclip"></i>
                                    Attachments (<?php echo count($post['attachments']); ?>)
                                </div>
                                <div class="media-grid">
                                    <?php foreach ($post['attachments'] as $attachment): ?>
                                        <?php if (strpos($attachment['file_type'], 'image/') === 0): ?>
                                            <div class="media-item" onclick="openModal('<?php echo htmlspecialchars($attachment['file_path']); ?>', 'image')">
                                                <img src="<?php echo htmlspecialchars($attachment['file_path']); ?>" alt="<?php echo htmlspecialchars($attachment['file_name']); ?>">
                                            </div>
                                        <?php elseif (strpos($attachment['file_type'], 'video/') === 0): ?>
                                            <div class="media-item" onclick="openModal('<?php echo htmlspecialchars($attachment['file_path']); ?>', 'video')">
                                                <video>
                                                    <source src="<?php echo htmlspecialchars($attachment['file_path']); ?>" type="<?php echo htmlspecialchars($attachment['file_type']); ?>">
                                                </video>
                                            </div>
                                        <?php else: ?>
                                            <a href="<?php echo htmlspecialchars($attachment['file_path']); ?>" download="<?php echo htmlspecialchars($attachment['file_name']); ?>" class="file-item">
                                                <div class="file-icon">
                                                    <i class="fas fa-file"></i>
                                                </div>
                                                <div class="file-info">
                                                    <div class="file-name"><?php echo htmlspecialchars($attachment['file_name']); ?></div>
                                                    <div class="file-size"><?php echo round($attachment['file_size'] / 1024, 2); ?> KB</div>
                                                </div>
                                            </a>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
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
                                        <a href="?id=<?php echo $topic_id; ?>&action=delete&type=post&target_id=<?php echo $post['id']; ?>&owner_id=<?php echo $post['user_id']; ?>"
                                            class="action-btn" onclick="return confirm('Delete this topic? This cannot be undone.')">
                                                <i class="fas fa-trash"></i>Delete
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        
        <!-- Reply Form -->
        <?php if($topic['is_locked']): ?>
            <div class="reply-form">
                <h3>🔒 Topic Locked</h3>
                <p>This topic has been locked and no new replies can be posted.</p>
            </div>
        <?php elseif(!$user): ?>
            <div class="reply-form">
                <h3>Join the Discussion</h3>
                <p>Please <a href="login.php">login</a> or <a href="register.php">register</a> to participate in this discussion.</p>
            </div>
        <?php else: ?>
            <form method="POST" enctype="multipart/form-data" class="reply-form" id="reply-form">
                <input type="hidden" name="action" value="create_post">
                <h3>Post a Reply</h3>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <div class="form-field">
                    <textarea name="content" placeholder="Share your thoughts..." required></textarea>
                </div>
                <div class="form-field">
                    <input type="file" name="attachments[]" multiple accept="image/*,video/*,.pdf,.doc,.docx,.txt,.zip,.rar">
                    <small>Attach files (optional): Images, Videos, Documents (Max 5MB each)</small>
                </div>
                <div class="form-actions">
                    <button type="submit" class="submit-btn">
                        <i class="fas fa-reply"></i> Post Reply
                    </button>
                    <button type="button" class="clear-btn" onclick="this.form.reset()">Clear</button>
                </div>
            </form>
        <?php endif; ?>
        
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
    </main>
    
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

    <!-- Modal for media preview -->
    <div id="mediaModal" class="modal">
        <span class="modal-close" onclick="closeModal()">&times;</span>
        <div class="modal-content" id="modalContent"></div>
    </div>

    <script>
        function openModal(filePath, type) {
            const modal = document.getElementById('mediaModal');
            const modalContent = document.getElementById('modalContent');
            
            if (type === 'image') {
                modalContent.innerHTML = `<img src="${filePath}" alt="Preview">`;
            } else if (type === 'video') {
                modalContent.innerHTML = `<video controls autoplay><source src="${filePath}"></video>`;
            }
            
            modal.style.display = 'block';
        }

        function closeModal() {
            document.getElementById('mediaModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('mediaModal');
            if (event.target === modal) {
                closeModal();
            }
        }

        function shareTopic() {
            if (navigator.share) {
                navigator.share({
                    title: document.title,
                    url: window.location.href
                });
            } else {
                navigator.clipboard.writeText(window.location.href);
            }
        }

        function sharePost(postId) {
            const url = window.location.href.split('#')[0] + '#post-' + postId;
            if (navigator.share) {
                navigator.share({
                    title: document.title,
                    url: url
                });
            } else {
                navigator.clipboard.writeText(url);
            }
        }
    </script>
</body>
</html>
