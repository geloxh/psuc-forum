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
    $stmt = $conn -> prepare($forum_query);
    $stmt -> execute([$forum_id]);
    $forum_info = $stmt -> fetch(PDO::FETCH_ASSOC);

    // If no forum_id provided, show forum selection
    if (!$forum_id) {
        $categories_query = "SELECT c.*, 
            (SELECT COUNT(*) FROM forums f WHERE f.category_id = c.id) as forum_count 
            FROM categories c ORDER BY c.position, c.name";
        $stmt = $conn -> prepare($categories_query);
        $stmt -> execute();
        $categories = $stmt -> fetchAll(PDO::FETCH_ASSOC);
    
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
    <link rel="stylesheet" href="assets/stylesheets/new-topic.css">
    <link rel="stylesheet" href="assets/stylesheets/media-preview.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="new-topic-page">
        <div class="container">
            <?php if(isset($show_forum_selection)): ?>
                <!-- Forum Selection -->
                <div class="page-header">
                    <h1><i class="fas fa-plus-circle"></i> Create New Topic</h1>
                    <p>Choose a forum to start your discussion</p>
                </div>

                <div class="forum-selection">
                    <?php
                    foreach($categories as $category):
                        $forums_query = "SELECT id, name, description FROM forums WHERE category_id = ? ORDER BY position, name";
                        $stmt = $conn->prepare($forums_query);
                        $stmt->execute([$category['id']]);
                        $forums = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        if(count($forums) > 0):
                    ?>
                        <div class="category-section">
                            <h2 class="category-title">
                                <i class="<?php echo $category['icon']; ?>" style="color: <?php echo $category['color']; ?>"></i>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </h2>
                            <div class="forums-grid">
                                <?php foreach($forums as $forum): ?>
                                    <a href="new_topic.php?forum_id=<?php echo $forum['id']; ?>" class="forum-card">
                                        <div class="forum-content">
                                            <h3><?php echo htmlspecialchars($forum['name']); ?></h3>
                                            <p><?php echo htmlspecialchars($forum['description']); ?></p>
                                        </div>
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; endforeach; ?>
                </div>
            <?php else: ?>
                <!-- Topic Creation Form -->
                <div class="page-header">
                    <nav class="breadcrumb">
                        <a href="index.php"><i class="fas fa-home"></i></a>
                        <i class="fas fa-chevron-right"></i>
                        <a href="forum.php?id=<?php echo $forum_id; ?>"><?php echo htmlspecialchars($forum_info['name']); ?></a>
                        <i class="fas fa-chevron-right"></i>
                        <span>New Topic</span>
                    </nav>
                    <h1><i class="fas fa-edit"></i> Create New Topic</h1>
                    <p>in <?php echo htmlspecialchars($forum_info['name']); ?></p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span><?php echo $error; ?></span>
                    </div>
                <?php endif; ?>

                <div class="form-container">
                    <form method="POST" enctype="multipart/form-data" class="topic-form">
                        <input type="hidden" name="forum_id" value="<?php echo $forum_id; ?>">
                        
                        <div class="form-group">
                            <label for="title">Topic Title</label>
                            <input type="text" id="title" name="title" class="form-input"
                                   placeholder="What would you like to discuss?" 
                                   required maxlength="255" 
                                   value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="content">Content</label>
                            <textarea id="content" name="content" rows="10" class="form-textarea"
                                      placeholder="Share your thoughts, ask questions, or start a discussion..." 
                                      required><?php echo htmlspecialchars($_POST['content'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="attachments">Attachments <span class="optional">(Optional)</span></label>
                            <div class="file-input-wrapper">
                                <input type="file" id="attachments" name="attachments[]" 
                                       multiple accept="image/*,video/*,.pdf,.doc,.docx,.txt,.zip,.rar">
                                <div class="file-input-text">
                                    <i class="fas fa-paperclip"></i>
                                    <span>Choose files or drag and drop</span>
                                </div>
                            </div>
                            <small class="form-hint">Supported: Images, PDFs, Documents, Archives (Max 5MB each)</small>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i>
                                Create Topic
                            </button>
                            <a href="forum.php?id=<?php echo $forum_id; ?>" class="btn btn-secondary">
                                <i class="fas fa-times"></i>
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>

                <div class="guidelines-card">
                    <h3><i class="fas fa-lightbulb"></i> Posting Guidelines</h3>
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
    <script src="assets/scripts/new-topic.js"></script>
    <script src="assets/scripts/media-preview.js"></script>
</body>
</html>