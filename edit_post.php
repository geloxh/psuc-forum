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

    if ($_POST) {
        try {
            if ($forum -> updatePost($post_id, $_POST['content'])) {
                header('Location: topic.php?id=' . $post['topic_id'] . '#post-' . $post_id);
                exit;
            }
        } catch (Exception $e) {
            $error = $e -> getMessage();
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Edit Post - PSUC Forum</title>
        <link href="assets/stylesheets/main.css" rel="stylesheet">
        <link href="assets/stylesheets/dark-theme.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    </head>
    <body>
        <?php include 'includes/header.php'; ?>

        <main class="container">
            <div class="main-content">
                <div class="forum-content">
                    <div class="p-3">
                        <h1><i class="fas fa-edit"></i> Edit Post</h1>

                        <?php if(isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="POST" style="margin-top: 2rem;">
                            <div class="form-group">
                                <label>Content</label>
                                <textarea name="content" class="form-control" rows="10" required><?php echo htmlspecialchars($post['content']); ?></textarea>
                            </div>

                            <div style="display: flex; gap: 1rem;">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Post
                                </button>
                                <a href="topic.php?id=<?php echo $post['topic_id']; ?>#post-<?php echo $post_id; ?>" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </body>
</html>