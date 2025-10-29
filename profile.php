<?php
    require_once 'includes/auth.php';

    $auth = new Auth();
    $user = $auth->getCurrentUser();

    if(!$user) {
        header('Location: login.php');
        exit;
    }

    $database = new Database();
    $conn = $database->getConnection();

    // Get user statistics
    $stats_query = "SELECT 
        (SELECT COUNT(*) FROM topics WHERE user_id = ?) as topics_created,
        (SELECT COUNT(*) FROM posts WHERE user_id = ?) as posts_made,
        (SELECT COUNT(*) FROM votes WHERE user_id = ?) as votes_cast,
        (SELECT COUNT(*) FROM messages WHERE sender_id = ?) as messages_sent";

    $stmt = $conn -> prepare($stats_query);
    $stmt -> execute([$user['id'], $user['id'], $user['id'], $user['id']]);
    $stats = $stmt -> fetch(PDO::FETCH_ASSOC);

    // Get recent topics
    $recent_topics_query = "SELECT t.*, f.name as forum_name FROM topics t 
                        JOIN forums f ON t.forum_id = f.id 
                        WHERE t.user_id = ? ORDER BY t.created_at DESC LIMIT 5";
    $stmt = $conn -> prepare($recent_topics_query);
    $stmt -> execute([$user['id']]);
    $recent_topics = $stmt -> fetchAll(PDO::FETCH_ASSOC);

?>

<?php
    if(!isset($_SESSION)) { 
        session_start(); 
    }

    if(isset($_SESSION['upload_error'])) {
        echo '<div class="alert alert-danger">' . $_SESSION['upload_error'] . '</div>';
        unset($_SESSION['upload_error']);
    }
    
    if(isset($_SESSION['upload_success'])) {
        echo '<div class="alert alert-success">' . $_SESSION['upload_success'] . '</div>';
        unset($_SESSION['upload_success']);
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - PSUC Forum</title>
    <link rel="stylesheet" href="assets/stylesheets/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main style="background: #fafafa; min-height: 100vh; padding: 2rem 1rem;">
        <div style="max-width: 600px; margin: 0 auto;">
            <!-- Profile Card -->
            <div style="background: white; border-radius: 12px; padding: 2rem; box-shadow: 0 2px 8px rgba(0,0,0,0.04); margin-bottom: 1.5rem;">
                <div style="display: flex; align-items: center; gap: 1.5rem; margin-bottom: 1.5rem;">
                    <img src="assets/avatars/<?php echo $user['avatar']; ?>" alt="Avatar" 
                         style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 3px solid #f1f5f9;"
                         onerror="this.src='https://via.placeholder.com/80/007bff/ffffff?text=<?php echo strtoupper(substr($user['username'], 0, 1)); ?>'">
                    <div>
                        <h1 style="font-size: 1.5rem; font-weight: 600; color: #1a202c; margin: 0 0 0.25rem 0;">
                            <?php echo htmlspecialchars($user['full_name']); ?>
                        </h1>
                        <p style="color: #718096; font-size: 0.9rem; margin: 0 0 0.5rem 0;">@<?php echo htmlspecialchars($user['username']); ?></p>
                        <span style="background: #3182ce; color: white; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.75rem; font-weight: 500;">
                            <?php echo ucfirst($user['role']); ?>
                        </span>
                    </div>
                </div>
                <div style="display: flex; gap: 2rem; color: #718096; font-size: 0.85rem; padding-top: 1rem; border-top: 1px solid #e2e8f0;">
                    <div><i class="fas fa-university" style="margin-right: 0.5rem;"></i><?php echo htmlspecialchars($user['university']); ?></div>
                    <div><i class="fas fa-calendar" style="margin-right: 0.5rem;"></i>Joined <?php echo date('M Y', strtotime($user['created_at'])); ?></div>
                </div>
            </div>

            <!-- Stats -->
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1.5rem;">
                <div style="background: white; padding: 1.5rem 1rem; border-radius: 12px; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
                    <div style="font-size: 1.5rem; font-weight: 700; color: #3182ce; margin-bottom: 0.25rem;"><?php echo $stats['topics_created']; ?></div>
                    <div style="color: #718096; font-size: 0.75rem;">Topics</div>
                </div>
                <div style="background: white; padding: 1.5rem 1rem; border-radius: 12px; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
                    <div style="font-size: 1.5rem; font-weight: 700; color: #3182ce; margin-bottom: 0.25rem;"><?php echo $stats['posts_made']; ?></div>
                    <div style="color: #718096; font-size: 0.75rem;">Posts</div>
                </div>
                <div style="background: white; padding: 1.5rem 1rem; border-radius: 12px; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
                    <div style="font-size: 1.5rem; font-weight: 700; color: #3182ce; margin-bottom: 0.25rem;"><?php echo $stats['votes_cast']; ?></div>
                    <div style="color: #718096; font-size: 0.75rem;">Votes</div>
                </div>
                <div style="background: white; padding: 1.5rem 1rem; border-radius: 12px; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
                    <div style="font-size: 1.5rem; font-weight: 700; color: #3182ce; margin-bottom: 0.25rem;"><?php echo $stats['messages_sent']; ?></div>
                    <div style="color: #718096; font-size: 0.75rem;">Messages</div>
                </div>
            </div>

            <!-- Avatar Upload -->
            <div style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: 0 2px 8px rgba(0,0,0,0.04); margin-bottom: 1.5rem;">
                <h3 style="font-size: 1rem; font-weight: 600; color: #1a202c; margin: 0 0 1rem 0;">Change Avatar</h3>
                <form action="upload_avatar.php" method="post" enctype="multipart/form-data" style="display: flex; gap: 1rem; align-items: end;">
                    <input type="file" name="avatar" id="avatar" required style="flex: 1; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.85rem;">
                    <button type="submit" style="background: #3182ce; color: white; border: none; padding: 0.5rem 1rem; border-radius: 6px; font-size: 0.85rem; cursor: pointer;">Upload</button>
                </form>
            </div>

            <!-- Recent Topics -->
            <?php if(count($recent_topics) > 0): ?>
            <div style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
                <h3 style="font-size: 1rem; font-weight: 600; color: #1a202c; margin: 0 0 1rem 0;">Recent Topics</h3>
                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <?php foreach($recent_topics as $topic): ?>
                        <div style="padding: 1rem; border-radius: 8px; background: #f7fafc; border-left: 3px solid #3182ce;">
                            <a href="topic.php?id=<?php echo $topic['id']; ?>" style="font-weight: 500; color: #1a202c; text-decoration: none; display: block; margin-bottom: 0.25rem; font-size: 0.9rem;">
                                <?php echo htmlspecialchars($topic['title']); ?>
                            </a>
                            <div style="font-size: 0.75rem; color: #718096;">
                                <?php echo htmlspecialchars($topic['forum_name']); ?> • <?php echo date('M j, Y', strtotime($topic['created_at'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <script src="assets/scripts/main.js"></script>
    <script>
        // Mobile responsive adjustments
        if (window.innerWidth <= 768) {
            const statsGrid = document.querySelector('[style*="grid-template-columns: repeat(4, 1fr)"]');
            if (statsGrid) {
                statsGrid.style.gridTemplateColumns = 'repeat(2, 1fr)';
            }
            
            const profileInfo = document.querySelector('[style*="display: flex; gap: 2rem"]');
            if (profileInfo) {
                profileInfo.style.flexDirection = 'column';
                profileInfo.style.gap = '1rem';
            }
            
            const uploadForm = document.querySelector('form[style*="display: flex"]');
            if (uploadForm) {
                uploadForm.style.flexDirection = 'column';
                uploadForm.style.alignItems = 'stretch';
            }
        }
    </script>
</body>
</html>