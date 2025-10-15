<?php
    require_once __DIR__ . '/auth.php';
    require_once __DIR__ . '/forum.php';
    require_once __DIR__ . '/../config/database.php';

    $auth = new Auth();
    $forum = new Forum();
    $user = $auth -> getCurrentUser();

    if (!$user) {
        header('Location: ../login.php');
        exit;
    }

    $forum_id = $_GET['forum_id'] ?? 0;
    $database = new Database();
    $conn = $database -> getConnection();

    // Get forum info
    $forum_query = "SELECT name FROM forums WHERE id = ?";
    $stmt = $conn -> prepare($forum_query);
    $stmt -> execute([$forum_id]);
    $forum_info = $stmt -> fetch(PDO::FETCH_ASSOC);

    if (!$forum_info) {
        header('Location: ../index.php');
        exit;
    }

    $error = '';
    $success = '';

    if ($_POST) {
        try {
            $topic_id = $forum -> createTopic($forum_id, $user['id'], $_POST['title'], $_POST['content']);
            if ($topic_id) {
                header('Location: ../topic.php?id=' . $topic_id);
                exit;
            } else {
                $error = 'Failed to create topic. Please try again.';
            }
        } catch (Exception $e) {
            $error = $e -> getMessage();
        }
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Create New Topic - PSUC forum</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="../assets/stylesheets/main.css" rel="stylesheet">
        <link href="../assets/stylesheets/dark-theme.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    </head>
    <body>
        <?php
            include 'header.php';
        ?>

        <main class="container">
            <div class="main-content">
                <div class="forum-content">
                    <div class="p-3">
                        <nav style="margin-bottom: 1rem;">
                            <a href="../index.php">Forum</a> >
                            <a href="../forum.php?id=<?php echo $forum_id; ?>"><?php echo htmlspecialchars($forum_info['name']); ?></a> >
                            <strong>New Topic</strong>
                        </nav>

                        <h1><i class="fas fa-plus"></i>Create New Topic</h1>
                        <p class="text-secondary">Start a new discussion in <?php echo htmlspecialchars($forum_info['name']); ?></p>

                        <?php if($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="POST" style="margin-top: 2rem;">
                            <div class="form-group">
                                <label>Topic Title</label>
                                <input type="text" name="title" class="form-control" placeholder="Enter a descriptive title for your topic" required maxlength="255">
                            </div>

                            <div class="form-group">
                                <label>Content</label>
                                <textarea name="content" class="form-control" rows="12" placeholder="Write your topic content here..." required></textarea>
                            </div>

                            <div style="display: flex; gap: 1rem;">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i>Create Topic
                                </button>
                                <a href="../forum.php?id=<?php echo $forum_id; ?>" class="btn btn-secondary">
                                    <i class="fas fa-times"></i>Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <aside class="sidebar">
                    <div class="widget">
                        <h3><i class="fas fa-lightbulb"></i>Postingg Guidelines</h3>
                        <ul style="padding-left: 1.5rem; line-height: 1.8;">
                            <li>Use a clear, descriptive title</li>
                            <li>Be respectful and professional</li>
                            <li>Stay on topic</li>
                            <li>Search before posting to avoid duplicates</li>
                            <li>Use proper grammar and spelling</li>
                        </ul>
                    </div>

                    <div class="widget">
                        <h3><i class="fas fa-info-circle"></i>Forum Rules</h3>
                        <p style="font-size: 0.9rem; line: 1.6;">
                            Please follow our community guidelines. Topics that violate our rules may be removed or locked by moderators.
                        </p>
                    </div>
                </aside>
            </div>
        </main>

        <script src="../assets/scripts/main.js"></script>
    </body>
</html>