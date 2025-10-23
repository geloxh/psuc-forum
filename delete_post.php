<?php
require_once 'includes/auth.php';
require_once 'includes/forum.php';

$auth = new Auth();
$forum = new Forum();
$user = $auth->getCurrentUser();

if(!$user) {
    header('Location: login.php');
    exit;
}

$post_id = $_GET['id'] ?? 0;
$post = $forum->getPost($post_id);

if(!$post || ($post['user_id'] != $user['id'] && !$auth->isAdmin())) {
    header('Location: index.php');
    exit;
}

// Get topic_id for redirect
$database = new Database();
$conn = $database->getConnection();
$topic_query = "SELECT topic_id FROM posts WHERE id = ?";
$stmt = $conn->prepare($topic_query);
$stmt->execute([$post_id]);
$topic_id = $stmt->fetch(PDO::FETCH_ASSOC)['topic_id'];

if($_POST && isset($_POST['confirm'])) {
    if($forum->deletePost($post_id)) {
        header("Location: topic.php?id=$topic_id&status=post_deleted");
        exit;
    } else {
        $error = 'Failed to delete post';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Post - PSUC Forum</title>
    <link rel="stylesheet" href="assets/stylesheets/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container">
        <div class="main-content">
            <div class="forum-content">
                <div class="p-3">
                    <h1><i class="fas fa-trash text-danger"></i> Delete Post</h1>
                    
                    <?php if(isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <div class="alert alert-warning">
                        <strong>Warning!</strong> This action cannot be undone.
                    </div>
                    
                    <div style="background: var(--card-bg); padding: 1rem; border-radius: 8px; margin: 1rem 0;">
                        <p><strong>Post by:</strong> <?php echo htmlspecialchars($post['username']); ?></p>
                        <div style="background: #f8f9fa; padding: 1rem; border-radius: 4px; margin-top: 0.5rem;">
                            <?php echo nl2br(htmlspecialchars(substr($post['content'], 0, 200))); ?>
                            <?php if(strlen($post['content']) > 200): ?>...<?php endif; ?>
                        </div>
                    </div>
                    
                    <form method="POST">
                        <div style="display: flex; gap: 1rem;">
                            <button type="submit" name="confirm" value="1" class="btn btn-danger">
                                <i class="fas fa-trash"></i> Yes, Delete Post
                            </button>
                            <a href="topic.php?id=<?php echo $topic_id; ?>" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script src="assets/scripts/main.js"></script>
</body>
</html>