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
<html lange="en">
    <head>
        <title>Edit Post - PSUC Forum</title>
        <link href="assets/stylesheets/main.css" rel="stylesheet">
    </head>
    <body>
        <?php
            include 'includes/header.php';
        ?>
        <main class="container">
            <h2>Edit Post</h2>
            <form method="POST">
                <textarea name="content" rows="6" required><?php echo htmlspecialchars($post['content']); ?></textarea>
                <button type="submit" class="btn btn-primary">Update Post</button>
                <a href="topic.php?id=<?php echo $post['topic_id']; ?>" class="btn btn0=-secondary">Cancel</a>
            </form>
        </main>
    </body>
</html>