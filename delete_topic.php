<?php
    require_once 'includes/auth.php';
    require_once 'includes/forum.php';

    // Start session if not already started to handle CSRF tokens
    if (session_status() == PHP_SESSION_NONE) session_start();

    $auth = new Auth();
    $forum = new Forum();
    $user = $auth -> getCurrentUser();

    if (!$user) {
        header('Location: login.php');
        exit;
    }

    $topic_id = $_GET['id'] ?? 0;
    // Fetch the topic to get its details before deleting
    $topic = $forum -> getTopic($topic_id);

    // Security check: ensure user is the author or an admin
    if (!$topic || ($user['id'] != $topic['user_id'] && !$auth -> isAdmin())) {
        header('Location: index.php');
        exit;
    }

    // Handle Delete Confirmation
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            header('Location: topic.php?id=' . $topic_id . '&error=csrf_failed');
            exit;
        }

        $forum_id = $topic['forum_id'];

        if ($forum -> deleteTopic($topic_id)) {
            header('Location: forum.php?id=' . $forum_id . '&status=topic_deleted');
        } else {
            header('Location: topic.php?id=' . $topic_id . '&error=delete_failed');
        }
        exit;
    }

    // Generate and store CSRF token for the form
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Delete Topic - PSUC Forum</title>
        <link href="assets/stylesheets/main.css" rel="stylesheet">
        <link href="assets/stylesheets/forum.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    </head>
    <body>
        <div class="delete-container">
            <div class="delete-modal">
                <div class="delete-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>

                <h2 class="delete-title">Delete Topic</h2>

                <p class="delete-message">
                    Are you sure you want to delete this topic? This will permanently delete the topic and all its replies. This action cannot be undone.
                </p>

                <div class="post-preview">
                    <strong><?php echo htmlspecialchars($topic['title']); ?></strong>
                </div>

                <div class="delete-actions">
                    <a href="topic.php?id=<?php echo $topic['id']; ?>" class="btn-cancel"><i class="fas fa-times"></i> Cancel</a>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <button type="submit" name="confirm_delete" class="btn-delete"><i class="fas fa-trash"></i> Delete Topic</button>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>
