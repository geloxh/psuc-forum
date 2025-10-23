<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/forum.php';
require_once __DIR__ . '/config/database.php';

$auth = new Auth();
$forum = new Forum();
$user = $auth->getCurrentUser();

if (!$user) {
    header('Location: login.php');
    exit;
}

// Determine forum_id from GET on page load, or POST on form submission
$forum_id = $_SERVER['REQUEST_METHOD'] === 'POST' ? ($_POST['forum_id'] ?? 0) : ($_GET['forum_id'] ?? 0);
$error = '';

$database = new Database();
$conn = $database->getConnection();

// Get forum info to validate it and for display
$forum_query = "SELECT name FROM forums WHERE id = ?";
$stmt = $conn->prepare($forum_query);
$stmt->execute([$forum_id]);
$forum_info = $stmt->fetch(PDO::FETCH_ASSOC);

// If no forum_id provided, show forum selection
if (!$forum_id) {
    $categories_query = "SELECT c.*, 
        (SELECT COUNT(*) FROM forums f WHERE f.category_id = c.id) as forum_count 
        FROM categories c ORDER BY c.position, c.name";
    $stmt = $conn->prepare($categories_query);
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $show_forum_selection = true;
} elseif (!$forum_info) {
    header('Location: index.php');
    exit;
}

// Handle new topic creation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // The $forum_id from POST is already validated by the check above
        $topic_id = $forum->createTopic($forum_id, $user['id'], $_POST['title'], $_POST['content']);
        if ($topic_id) {
            header("Location: topic.php?id=$topic_id");
            exit;
        } else {
            $error = 'Failed to create topic. Please try again.';
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Topic - PSUC Forum</title>
    <link rel="stylesheet" href="assets/stylesheets/main.css">
    <link rel="stylesheet" href="assets/stylesheets/dark-theme.css">
    <link rel="stylesheet" href="assets/stylesheets/media-preview.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container">
        <div class="main-content">
            <div class="forum-content">
                <div class="p-3">
                    <?php if(isset($show_forum_selection)): ?>
                        <h1><i class="fas fa-plus"></i> Create New Topic</h1>
                        <p class="text-secondary">Select a forum to start your discussion</p>
                        
                        <div style="display: grid; gap: 1.5rem;">
                            <?php
                            foreach($categories as $category):
                                $forums_query = "SELECT id, name, description FROM forums WHERE category_id = ? ORDER BY position, name";
                                $stmt = $conn->prepare($forums_query);
                                $stmt->execute([$category['id']]);
                                $forums = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            ?>
                                <div class="widget">
                                    <div class="widget-header">
                                        <div class="widget-icon" style="color: <?php echo $category['color']; ?>">
                                            <i class="<?php echo $category['icon']; ?>"></i>
                                        </div>
                                        <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                                    </div>
                                    <div style="display: grid; gap: 0.75rem;">
                                        <?php foreach($forums as $forum): ?>
                                            <a href="new_topic.php?forum_id=<?php echo $forum['id']; ?>" class="forum-selection-item">
                                                <div class="forum-selection-content">
                                                    <h4><?php echo htmlspecialchars($forum['name']); ?></h4>
                                                    <p><?php echo htmlspecialchars($forum['description']); ?></p>
                                                </div>
                                                <i class="fas fa-chevron-right"></i>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <nav style="margin-bottom: 1rem;">
                            <a href="index.php">Forum</a> >
                            <a href="forum.php?id=<?php echo $forum_id; ?>"><?php echo htmlspecialchars($forum_info['name']); ?></a> >
                            <strong>New Topic</strong>
                        </nav>

                        <h1><i class="fas fa-plus"></i> Create New Topic</h1>
                        <p class="text-secondary">Start a new discussion in <?php echo htmlspecialchars($forum_info['name']); ?></p>
                    <?php endif; ?>

                    <?php if(!isset($show_forum_selection)): ?>
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <div class="topic-creation-form">
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="forum_id" value="<?php echo $forum_id; ?>">
                                
                                <div class="form-group">
                                    <label for="title"><i class="fas fa-heading"></i> Topic Title</label>
                                    <input type="text" id="title" name="title" class="form-control" 
                                           placeholder="Enter a descriptive title for your topic" 
                                           required maxlength="255" 
                                           value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>">
                                    <small class="form-text">Choose a clear, descriptive title that summarizes your topic</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="content"><i class="fas fa-edit"></i> Content</label>
                                    <textarea id="content" name="content" class="form-control" rows="12" 
                                              placeholder="Write your topic content here..." required><?php echo htmlspecialchars($_POST['content'] ?? ''); ?></textarea>
                                    <small class="form-text">Provide detailed information about your topic. You can format text using line breaks.</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="attachments"><i class="fas fa-paperclip"></i> Attachments (Optional)</label>
                                    <input type="file" id="attachments" name="attachments[]" class="form-control-file" multiple accept="image/*,video/*,.pdf,.doc,.docx,.txt,.zip,.rar">
                                    <small class="form-text">Supported formats: JPG, PNG, PDF, DOC, TXT, ZIP (Max 5MB each)</small>
                                </div>
                                
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane"></i> Create Topic
                                    </button>
                                    <a href="forum.php?id=<?php echo $forum_id; ?>" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Back to Forum
                                    </a>
                                </div>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <aside class="sidebar">
                <?php if(!isset($show_forum_selection)): ?>
                    <div class="widget">
                        <h3><i class="fas fa-info-circle"></i> Posting Guidelines</h3>
                        <ul style="padding-left: 1.5rem; line-height: 1.8;">
                            <li>Use a clear, descriptive title</li>
                            <li>Provide detailed information</li>
                            <li>Be respectful and constructive</li>
                            <li>Search before posting duplicates</li>
                            <li>Stay on topic for the forum</li>
                        </ul>
                    </div>

                    <div class="widget">
                        <h3><i class="fas fa-lightbulb"></i> Tips for Success</h3>
                        <div style="font-size: 0.9rem; line-height: 1.6;">
                            <p><strong>Good titles:</strong> "Need help with PHP database connection" or "Research collaboration opportunity in AI"</p>
                            <p><strong>Avoid:</strong> "Help!" or "Question" - be specific!</p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="widget">
                        <h3><i class="fas fa-compass"></i> Choose Your Forum</h3>
                        <p>Select the most appropriate forum for your topic to ensure it reaches the right audience.</p>
                    </div>

                    <div class="widget">
                        <h3><i class="fas fa-users"></i> Community Guidelines</h3>
                        <ul style="padding-left: 1.5rem; line-height: 1.8; font-size: 0.9rem;">
                            <li>Respect all community members</li>
                            <li>Post in the appropriate forum</li>
                            <li>Use clear, descriptive titles</li>
                            <li>No spam or duplicate posts</li>
                        </ul>
                    </div>
                <?php endif; ?>
            </aside>
        </div>
    </main>

    <script src="assets/scripts/main.js"></script>
    <script src="assets/scripts/media-preview.js"></script>
</body>
</html>