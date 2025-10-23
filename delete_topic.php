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

$topic_id = $_GET['id'] ?? 0;
$topic = $forum->getTopic($topic_id);

if(!$topic || ($topic['user_id'] != $user['id'] && !$auth->isAdmin())) {
    header('Location: index.php');
    exit;
}

if($_POST && isset($_POST['confirm'])) {
    if($forum->deleteTopic($topic_id)) {
        header('Location: forum.php?id=' . $topic['forum_id']);
        exit;
    } else {
        $error = 'Failed to delete topic';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Topic - PSUC Forum</title>
    <link rel="stylesheet" href="assets/stylesheets/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container">
        <div class="main-content">
            <div class="forum-content">
                <div class="p-3">
                    <h1><i class="fas fa-trash text-danger"></i> Delete Topic</h1>
                    
                    <?php if(isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <div class="alert alert-warning">
                        <strong>Warning!</strong> This action cannot be undone. All posts in this topic will also be deleted.
                    </div>
                    
                    <div style="background: var(--card-bg); padding: 1rem; border-radius: 8px; margin: 1rem 0;">
                        <h4><?php echo htmlspecialchars($topic['title']); ?></h4>
                        <p class="text-secondary">
                            Created by <?php echo htmlspecialchars($topic['username']); ?> on 
                            <?php echo date('M j, Y g:i A', strtotime($topic['created_at'])); ?>
                        </p>
                    </div>
                    
                    <form method="POST">
                        <div style="display: flex; gap: 1rem;">
                            <button type="submit" name="confirm" value="1" class="btn btn-danger">
                                <i class="fas fa-trash"></i> Yes, Delete Topic
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