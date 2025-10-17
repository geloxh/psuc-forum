<?php
    require_once 'includes/auth.php';
    require_once 'includes/forum.php';

    $auth = new Auth();
    $forum = new Forum();
    $user = $auth -> getCurrentUser();

    if (!$user) {
        header('Location: login.php');
        exit;
    }

    $post_id = $_GET['id'] ?? 0;
    $post = $forum -> getPost($post_id);

    if (!$post || ($user['id'] != $post['user_id'] && !$auth -> isAdmin())) {
        header('Location: index.php');
        exit;
    }

    // Delete Confirmation
    if ($_POST && isset($_POST['confirm_delete'])) {
        if ($forum -> deletePost($post_id)) {
            header('Location: topic.php?id=' . $post['topic_id'] . '&deleted=true');
        } else {
            header('Location: topic.php?id=' . $post['topic_id'] .'&error=delete_failed');
        }
    
        exit;
    }
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title> Delete Post - PSUC Forum</title>
        <link href="assets/stylesheets/main.css" rel="stylesheet">
        <link href="assets/stylesheets/forum.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    </head>
    <body>
        <div class="delete-container">
            <div class="delete-modal">
                <div class="delete-icon">
                    <i class="fas fa-trash-alt"></i>
                </div>

                <h2 class="delete-title">Delete Post</h2>

                <p class="delete-message">
                    Are you sure you want to delete this post? This action cannot be undone.
                </p>

                <div class="post-preview">
                    <div class="post-preview-content">
                        <?php echo nl2br(htmlspecialchars(substr($post['content'], 0, 200))); ?>
                        <?php if (strlen($post['content']) > 200): ?>...<?php endif; ?>
                    </div>
                </div>

                <div class="delete-actions">
                <a href="topic.php?id=<?php echo $post['topic_id']; ?>" class="btn-cancel">
                    <i class="fas fa-times"></i> Cancel
                </a>
                
                <form method="POST" style="display: inline;">
                    <button type="submit" name="confirm_delete" class="btn-delete">
                        <i class="fas fa-trash"></i> Delete Post
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>