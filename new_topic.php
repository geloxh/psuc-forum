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
    <link rel="stylesheet" href="assets/stylesheets/media-preview.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="new-topic-page">
        <div class="topic-container">
            <?php if(isset($show_forum_selection)): ?>
                <!-- Forum Selection -->
                <header class="page-header">
                    <h1>Create New Topic</h1>
                    <p>Choose a forum to start your discussion</p>
                </header>

                <div class="forum-selection">
                    <?php
                    foreach($categories as $category):
                        $forums_query = "SELECT id, name, description FROM forums WHERE category_id = ? ORDER BY position, name";
                        $stmt = $conn->prepare($forums_query);
                        $stmt->execute([$category['id']]);
                        $forums = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                        <div class="category-section">
                            <h2 class="category-title">
                                <i class="<?php echo $category['icon']; ?>" style="color: <?php echo $category['color']; ?>"></i>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </h2>
                            <div class="forums-grid">
                                <?php foreach($forums as $forum): ?>
                                    <a href="new_topic.php?forum_id=<?php echo $forum['id']; ?>" class="forum-card">
                                        <h3><?php echo htmlspecialchars($forum['name']); ?></h3>
                                        <p><?php echo htmlspecialchars($forum['description']); ?></p>
                                        <i class="fas fa-arrow-right"></i>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <!-- Topic Creation Form -->
                <header class="page-header">
                    <nav class="breadcrumb">
                        <a href="index.php">Forum</a>
                        <span>/</span>
                        <a href="forum.php?id=<?php echo $forum_id; ?>"><?php echo htmlspecialchars($forum_info['name']); ?></a>
                        <span>/</span>
                        <span>New Topic</span>
                    </nav>
                    <h1>Create New Topic</h1>
                    <p>Start a new discussion in <?php echo htmlspecialchars($forum_info['name']); ?></p>
                </header>

                <?php if ($error): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" class="topic-form">
                    <input type="hidden" name="forum_id" value="<?php echo $forum_id; ?>">
                    
                    <div class="form-field">
                        <label for="title">Topic Title</label>
                        <input type="text" id="title" name="title" 
                               placeholder="Enter a clear, descriptive title" 
                               required maxlength="255" 
                               value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-field">
                        <label for="content">Content</label>
                        <textarea id="content" name="content" rows="12" 
                                  placeholder="Write your topic content here..." 
                                  required><?php echo htmlspecialchars($_POST['content'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-field">
                        <label for="attachments">Attachments (Optional)</label>
                        <input type="file" id="attachments" name="attachments[]" 
                               multiple accept="image/*,video/*,.pdf,.doc,.docx,.txt,.zip,.rar">
                        <small>Supported: JPG, PNG, PDF, DOC, TXT, ZIP (Max 5MB each)</small>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="submit-btn">
                            <i class="fas fa-paper-plane"></i>
                            Create Topic
                        </button>
                        <a href="forum.php?id=<?php echo $forum_id; ?>" class="cancel-btn">
                            Cancel
                        </a>
                    </div>
                </form>

                <div class="guidelines">
                    <h3>Posting Guidelines</h3>
                    <ul>
                        <li>Use a clear, descriptive title</li>
                        <li>Provide detailed information</li>
                        <li>Be respectful and constructive</li>
                        <li>Search before posting duplicates</li>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script src="assets/scripts/main.js"></script>
    <script src="assets/scripts/media-preview.js"></script>
</body>
</html>