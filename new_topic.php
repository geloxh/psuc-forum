<?php
    require_once 'includes/auth.php';
    require_once 'includes/forum.php';

    $auth = new Auth();
    $forum = new Forum();
    $user = $auth->getCurrentUser();

    if (!$user) {
        header('Location: login.php');
        exit;
    }

    $forum_id = $_GET['id'] ?? 0;
    $error = '';

    // Handle new topic creation
    if ($_POST && $user) {
        try {
            $topic_id = $forum->createTopic($forum_id, $user['id'], $_POST['title'], $_POST['content']);
            if ($topic_id) {
                header("Location: topic.php?id=$topic_id");
                exit;
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Create New Topic - PSUC forum</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="assets/stylesheets/main.css" rel="stylesheet">
        <link href="assets/stylesheets/dark-theme.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    </head>
    <body>
        <?php
            include 'includes/header.php';
        ?>

        <main class="container">
            <div class="main-content">
                <h1>Create New Topic</h1>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="title">Topic Title</label>
                        <input type="text" id="title" name="title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="content">Content</label>
                        <textarea id="content" name="content" class="form-control" rows="10" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="attachments">Attachments</label>
                        <input type="file" id="attachments" name="attachments[]" class="form-control" multiple>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Topic
                    </button>
                </form>
            </div>
        </main>

        <script src="assets/scripts/main.js"></script>
    </body>
</html>