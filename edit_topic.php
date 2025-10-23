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

$error = '';

if($_POST) {
    try {
        if($forum->updateTopic($topic_id, $_POST['title'], $_POST['content'])) {
            header("Location: topic.php?id=$topic_id");
            exit;
        }
    } catch(Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Topic - PSUC Forum</title>
    <link rel="stylesheet" href="assets/stylesheets/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container">
        <div class="main-content">
            <div class="forum-content">
                <div class="p-3">
                    <nav style="margin-bottom: 1rem;">
                        <a href="index.php">Forum</a> > 
                        <a href="topic.php?id=<?php echo $topic_id; ?>"><?php echo htmlspecialchars($topic['title']); ?></a> > 
                        <strong>Edit</strong>
                    </nav>
                    
                    <h1><i class="fas fa-edit"></i> Edit Topic</h1>
                    
                    <?php if($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="form-group">
                            <label>Title</label>
                            <input type="text" name="title" class="form-control" required 
                                   value="<?php echo htmlspecialchars($topic['title']); ?>">
                        </div>
                        <div class="form-group">
                            <label>Content</label>
                            <textarea name="content" class="form-control" rows="10" required><?php echo htmlspecialchars($topic['content']); ?></textarea>
                        </div>
                        <div style="display: flex; gap: 1rem;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Changes
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