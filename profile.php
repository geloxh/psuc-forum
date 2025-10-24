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

    <main style="background: #f8fafc; min-height: 100vh; padding: 3rem 1rem;">
        <div style="max-width: 800px; margin: 0 auto;">
            <div style="background: white; border-radius: 24px; padding: 4rem 3rem; box-shadow: 0 8px 40px rgba(0,0,0,0.06); text-align: center; margin-bottom: 3rem;">
                <img src="assets/avatars/<?php echo $user['avatar']; ?>" alt="Avatar" 
                     style="width: 140px; height: 140px; border-radius: 50%; margin: 0 auto 2rem; border: 6px solid #f1f5f9; object-fit: cover;"
                     onerror="this.src='https://via.placeholder.com/140/007bff/ffffff?text=<?php echo strtoupper(substr($user['username'], 0, 1)); ?>'">
                <h1 style="font-size: 2.5rem; font-weight: 300; color: var(--text-primary); margin: 0 0 0.5rem 0; letter-spacing: -0.02em;">
                    <?php echo htmlspecialchars($user['full_name']); ?>
                </h1>
                <p style="color: var(--text-secondary); font-size: 1.1rem; margin-bottom: 2rem; font-weight: 400;">
                    @<?php echo htmlspecialchars($user['username']); ?>
                </p>
                <div style="display: inline-block; padding: 0.75rem 2rem; background: var(--primary-gradient); color: white; border-radius: 50px; font-size: 0.9rem; font-weight: 500; margin-bottom: 2rem;">
                    <?php echo ucfirst($user['role']); ?>
                </div>
                <div style="display: flex; justify-content: center; gap: 3rem; color: var(--text-secondary); font-size: 0.95rem;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-university"></i>
                        <span><?php echo htmlspecialchars($user['university']); ?></span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-calendar"></i>
                        <span>Joined <?php echo date('M Y', strtotime($user['created_at'])); ?></span>
                    </div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 2rem; margin-bottom: 3rem;">
                <div style="background: white; padding: 2.5rem 1.5rem; border-radius: 20px; text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,0.04); transition: transform 0.3s ease;" onmouseover="this.style.transform='translateY(-8px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div style="font-size: 3rem; font-weight: 200; color: var(--primary-blue); margin-bottom: 0.5rem; line-height: 1;">
                        <?php echo $stats['topics_created']; ?>
                    </div>
                    <div style="color: var(--text-secondary); font-size: 0.9rem; font-weight: 500;">Topics</div>
                </div>
                <div style="background: white; padding: 2.5rem 1.5rem; border-radius: 20px; text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,0.04); transition: transform 0.3s ease;" onmouseover="this.style.transform='translateY(-8px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div style="font-size: 3rem; font-weight: 200; color: var(--primary-blue); margin-bottom: 0.5rem; line-height: 1;">
                        <?php echo $stats['posts_made']; ?>
                    </div>
                    <div style="color: var(--text-secondary); font-size: 0.9rem; font-weight: 500;">Posts</div>
                </div>
                <div style="background: white; padding: 2.5rem 1.5rem; border-radius: 20px; text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,0.04); transition: transform 0.3s ease;" onmouseover="this.style.transform='translateY(-8px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div style="font-size: 3rem; font-weight: 200; color: var(--primary-blue); margin-bottom: 0.5rem; line-height: 1;">
                        <?php echo $stats['votes_cast']; ?>
                    </div>
                    <div style="color: var(--text-secondary); font-size: 0.9rem; font-weight: 500;">Votes</div>
                </div>
                <div style="background: white; padding: 2.5rem 1.5rem; border-radius: 20px; text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,0.04); transition: transform 0.3s ease;" onmouseover="this.style.transform='translateY(-8px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div style="font-size: 3rem; font-weight: 200; color: var(--primary-blue); margin-bottom: 0.5rem; line-height: 1;">
                        <?php echo $stats['messages_sent']; ?>
                    </div>
                    <div style="color: var(--text-secondary); font-size: 0.9rem; font-weight: 500;">Messages</div>
                </div>
            </div>

            <div style="background: white; border-radius: 20px; padding: 3rem; box-shadow: 0 4px 20px rgba(0,0,0,0.04); margin-bottom: 3rem;">
                <h2 style="font-size: 1.5rem; font-weight: 300; color: var(--text-primary); margin-bottom: 2rem; text-align: center;">Change Avatar</h2>
                <form action="upload_avatar.php" method="post" enctype="multipart/form-data">
                    <div style="border: 2px dashed #e2e8f0; border-radius: 16px; padding: 3rem; text-align: center; transition: all 0.3s ease; margin-bottom: 2rem;" onmouseover="this.style.borderColor='var(--secondary-blue)'; this.style.background='rgba(59, 130, 246, 0.02)'" onmouseout="this.style.borderColor='#e2e8f0'; this.style.background='transparent'">
                        <i class="fas fa-cloud-upload-alt" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 1rem;"></i>
                        <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">Choose a new avatar image</p>
                        <input type="file" name="avatar" id="avatar" class="form-control" required style="max-width: 300px; margin: 0 auto;">
                    </div>
                    <div style="text-align: center;">
                        <button type="submit" style="background: var(--primary-gradient); color: white; border: none; padding: 1rem 3rem; border-radius: 50px; font-weight: 500; cursor: pointer; transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 25px rgba(59, 130, 246, 0.3)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                            Upload Avatar
                        </button>
                    </div>
                </form>
            </div>

            <div style="background: white; border-radius: 20px; padding: 3rem; box-shadow: 0 4px 20px rgba(0,0,0,0.04);">
                <h2 style="font-size: 1.5rem; font-weight: 300; color: var(--text-primary); margin-bottom: 2rem; text-align: center;">Recent Topics</h2>
                <?php if(count($recent_topics) > 0): ?>
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <?php foreach($recent_topics as $topic): ?>
                            <div style="padding: 1.5rem; border-radius: 12px; background: #f8fafc; transition: all 0.3s ease;" onmouseover="this.style.background='white'; this.style.boxShadow='0 4px 15px rgba(0,0,0,0.08)'" onmouseout="this.style.background='#f8fafc'; this.style.boxShadow='none'">
                                <a href="topic.php?id=<?php echo $topic['id']; ?>" style="font-weight: 500; color: var(--text-primary); text-decoration: none; display: block; margin-bottom: 0.5rem;" onmouseover="this.style.color='var(--secondary-blue)'" onmouseout="this.style.color='var(--text-primary)'">
                                    <?php echo htmlspecialchars($topic['title']); ?>
                                </a>
                                <div style="font-size: 0.85rem; color: var(--text-secondary);">
                                    in <?php echo htmlspecialchars($topic['forum_name']); ?> • <?php echo date('M j, Y g:i A', strtotime($topic['created_at'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                        <i class="fas fa-comment-slash" style="font-size: 4rem; color: #e2e8f0; margin-bottom: 1rem;"></i>
                        <p>No topics created yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script src="assets/scripts/main.js"></script>
    <script>
        // Add responsive behavior for mobile
        if (window.innerWidth <= 768) {
            const statsGrid = document.querySelector('[style*="grid-template-columns: repeat(4, 1fr)"]');
            if (statsGrid) {
                statsGrid.style.gridTemplateColumns = 'repeat(2, 1fr)';
                statsGrid.style.gap = '1rem';
            }
            
            const userInfo = document.querySelector('[style*="display: flex; justify-content: center; gap: 3rem"]');
            if (userInfo) {
                userInfo.style.flexDirection = 'column';
                userInfo.style.gap = '1rem';
            }
        }
    </script>
</body>
</html>