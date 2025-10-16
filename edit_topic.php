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

    $topic_id = $_GET['id'] ?? 0;

    // Fetch the topic to edit
    $topic = $forum->getTopic($topic_id);

    // Security check: ensure user is the author or an admin
    if (!$topic || ($topic['user_id'] != $user['id'] && !$auth->isAdmin())) {
        header('Location: index.php');
        exit;
    }

    $error = '';
    $success = '';

    if ($_POST) {
        try {
            $title = $_POST['title'];
            $content = $_POST['content'];

            if ($forum->updateTopic($topic_id, $title, $content)) {
                // Redirect back to the topic page after successful edit
                header('Location: topic.php?id=' . $topic_id);
                exit;
            } else {
                $error = 'Failed to update topic. Please try again.';
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Edit Topic - PSUC Forum</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
                        <nav style="margin-bottom: 1rem;">
                            <a href="index.php">Forum</a> >
                            <a href="forum.php?id=<?php echo $topic['forum_id']; ?>"><?php echo htmlspecialchars($topic['forum_name']); ?></a> >
                            <a href="topic.php?id=<?php echo $topic['id']; ?>"><?php echo htmlspecialchars($topic['title']); ?></a> >
                            <strong>Edit Topic</strong>
                        </nav>

                        <h1><i class="fas fa-edit"></i> Edit Topic</h1>

                        <?php if($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="POST" style="margin-top: 2rem;">
                            <div class="form-group">
                                <label>Topic Title</label>
                                <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($topic['title']); ?>" required maxlength="255">
                            </div>

                            <div class="form-group">
                                <label>Content</label>
                                <textarea name="content" class="form-control" rows="12" required><?php echo htmlspecialchars($topic['content']); ?></textarea>
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

                <aside class="sidebar">
                    <div class="widget">
                        <h3><i class="fas fa-lightbulb"></i>Editing Guidelines</h3>
                        <ul style="padding-left: 1.5rem; line-height: 1.8;">
                            <li>Ensure your title remains descriptive.</li>
                            <li>Clearly mark major edits if the topic has replies.</li>
                            <li>Do not change the fundamental subject of the topic.</li>
                            <li>All edits must still follow community rules.</li>
                        </ul>
                    </div>
                </aside>
            </div>
        </main>

        <script src="assets/scripts/main.js"></script>
    </body>
</html>