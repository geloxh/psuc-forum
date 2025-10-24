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

    // Mark notification as read
    if(isset($_GET['action']) && $_GET['action'] == 'read' && isset($_GET['id'])) {
        $forum -> markNotificationRead($_GET['id'], $user['id']);
        if(isset($_GET['url']) && $_GET['url']) {
            header('Location: ' . urldecode($_GET['url']));
            exit;
        }
    }

    $notifications = $forum -> getNotifications($user['id'], 50);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - PSUC Forum</title>
    <link rel="stylesheet" href="assets/stylesheets/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main style="background: #f8fafc; min-height: 100vh; padding: 3rem 1rem;">
        <div style="max-width: 800px; margin: 0 auto;">
            <div style="text-align: center; margin-bottom: 3rem;">
                <div style="width: 80px; height: 80px; background: var(--primary-gradient); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; color: white; font-size: 2rem;">
                    <i class="fas fa-bell"></i>
                </div>
                <h1 style="font-size: 2.5rem; font-weight: 300; color: var(--text-primary); margin: 0 0 0.5rem 0; letter-spacing: -0.02em;">Notifications</h1>
                <p style="color: var(--text-secondary); font-size: 1.1rem; margin: 0;">Stay updated with your forum activity</p>
            </div>

            <?php if(count($notifications) > 0): ?>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <?php foreach($notifications as $notification): ?>
                        <div style="background: white; border-radius: 16px; padding: 2rem; box-shadow: 0 4px 20px rgba(0,0,0,0.04); transition: all 0.3s ease; <?php echo !$notification['is_read'] ? 'border-left: 4px solid var(--secondary-blue);' : ''; ?>" 
                             onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 25px rgba(0,0,0,0.08)'" 
                             onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 20px rgba(0,0,0,0.04)'">
                            <div style="display: flex; justify-content: space-between; align-items: start; gap: 1.5rem;">
                                <div style="flex: 1;">
                                    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                                        <div style="width: 40px; height: 40px; background: rgba(59, 130, 246, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--secondary-blue); flex-shrink: 0;">
                                            <i class="fas fa-<?php echo $notification['type'] == 'welcome' ? 'hand-wave' : ($notification['type'] == 'reply' ? 'reply' : 'info-circle'); ?>"></i>
                                        </div>
                                        <div>
                                            <h3 style="font-size: 1.1rem; font-weight: 600; color: var(--text-primary); margin: 0 0 0.25rem 0;">
                                                <?php echo htmlspecialchars($notification['title']); ?>
                                            </h3>
                                            <?php if(!$notification['is_read']): ?>
                                                <span style="display: inline-block; padding: 0.25rem 0.75rem; background: var(--secondary-blue); color: white; border-radius: 20px; font-size: 0.75rem; font-weight: 500;">New</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <p style="margin: 0 0 1rem 0; color: var(--text-secondary); line-height: 1.6; font-size: 0.95rem;">
                                        <?php echo htmlspecialchars($notification['content']); ?>
                                    </p>
                                    <div style="color: var(--text-secondary); font-size: 0.85rem;">
                                        <i class="fas fa-clock"></i> <?php echo date('M j, Y g:i A', strtotime($notification['created_at'])); ?>
                                    </div>
                                </div>
                                <?php if($notification['url']): ?>
                                    <a href="notifications.php?action=read&id=<?php echo $notification['id']; ?>&url=<?php echo urlencode($notification['url']); ?>" 
                                       style="background: var(--primary-gradient); color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 25px; text-decoration: none; font-weight: 500; transition: all 0.3s ease; flex-shrink: 0;"
                                       onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 15px rgba(59, 130, 246, 0.3)'"
                                       onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                                        View
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div style="background: white; border-radius: 20px; padding: 4rem 3rem; box-shadow: 0 4px 20px rgba(0,0,0,0.04); text-align: center;">
                    <div style="width: 100px; height: 100px; background: rgba(107, 114, 128, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 2rem; color: var(--text-secondary); font-size: 3rem;">
                        <i class="fas fa-bell-slash"></i>
                    </div>
                    <h3 style="font-size: 1.5rem; font-weight: 500; color: var(--text-primary); margin: 0 0 1rem 0;">All Caught Up!</h3>
                    <p style="color: var(--text-secondary); font-size: 1rem; margin: 0; line-height: 1.6;">You have no new notifications. New activity will appear here when it happens.</p>
                </div>
            <?php endif; ?>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-top: 3rem;">
                <div style="background: white; border-radius: 16px; padding: 2rem; box-shadow: 0 4px 20px rgba(0,0,0,0.04);">
                    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
                        <div style="width: 50px; height: 50px; background: rgba(245, 158, 11, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--accent-gold);">
                            <i class="fas fa-cog"></i>
                        </div>
                        <h3 style="font-size: 1.2rem; font-weight: 500; color: var(--text-primary); margin: 0;">Settings</h3>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer; padding: 0.5rem; border-radius: 8px; transition: background 0.3s ease;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                            <input type="checkbox" checked style="width: 18px; height: 18px;"> 
                            <span style="font-size: 0.9rem; color: var(--text-primary);">New replies to my topics</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer; padding: 0.5rem; border-radius: 8px; transition: background 0.3s ease;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                            <input type="checkbox" checked style="width: 18px; height: 18px;"> 
                            <span style="font-size: 0.9rem; color: var(--text-primary);">Private messages</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer; padding: 0.5rem; border-radius: 8px; transition: background 0.3s ease;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                            <input type="checkbox" style="width: 18px; height: 18px;"> 
                            <span style="font-size: 0.9rem; color: var(--text-primary);">Weekly digest</span>
                        </label>
                    </div>
                </div>

                <div style="background: white; border-radius: 16px; padding: 2rem; box-shadow: 0 4px 20px rgba(0,0,0,0.04);">
                    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
                        <div style="width: 50px; height: 50px; background: rgba(59, 130, 246, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--secondary-blue);">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3 style="font-size: 1.2rem; font-weight: 500; color: var(--text-primary); margin: 0;">Your Activity</h3>
                    </div>
                    <?php
                    $database = new Database();
                    $conn = $database->getConnection();
                    $activity_query = "SELECT 
                        (SELECT COUNT(*) FROM topics WHERE user_id = ?) as topics_created,
                        (SELECT COUNT(*) FROM posts WHERE user_id = ?) as posts_made,
                        (SELECT COUNT(*) FROM votes WHERE user_id = ?) as votes_cast";
                    $stmt = $conn->prepare($activity_query);
                    $stmt->execute([$user['id'], $user['id'], $user['id']]);
                    $activity = $stmt->fetch(PDO::FETCH_ASSOC);
                    ?>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div style="text-align: center; padding: 1rem; background: #f8fafc; border-radius: 8px;">
                            <div style="font-size: 1.5rem; font-weight: 600; color: var(--primary-blue); margin-bottom: 0.25rem;"><?php echo $activity['topics_created']; ?></div>
                            <div style="font-size: 0.8rem; color: var(--text-secondary);">Topics</div>
                        </div>
                        <div style="text-align: center; padding: 1rem; background: #f8fafc; border-radius: 8px;">
                            <div style="font-size: 1.5rem; font-weight: 600; color: var(--primary-blue); margin-bottom: 0.25rem;"><?php echo $activity['posts_made']; ?></div>
                            <div style="font-size: 0.8rem; color: var(--text-secondary);">Posts</div>
                        </div>
                        <div style="text-align: center; padding: 1rem; background: #f8fafc; border-radius: 8px;">
                            <div style="font-size: 1.5rem; font-weight: 600; color: var(--primary-blue); margin-bottom: 0.25rem;"><?php echo $activity['votes_cast']; ?></div>
                            <div style="font-size: 0.8rem; color: var(--text-secondary);">Votes</div>
                        </div>
                        <div style="text-align: center; padding: 1rem; background: #f8fafc; border-radius: 8px;">
                            <div style="font-size: 1.5rem; font-weight: 600; color: var(--primary-blue); margin-bottom: 0.25rem;"><?php echo $user['reputation']; ?></div>
                            <div style="font-size: 0.8rem; color: var(--text-secondary);">Reputation</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="assets/scripts/main.js"></script>
    <script>
        // Add responsive behavior for mobile
        if (window.innerWidth <= 768) {
            const gridContainer = document.querySelector('[style*="grid-template-columns: 1fr 1fr"]');
            if (gridContainer) {
                gridContainer.style.gridTemplateColumns = '1fr';
                gridContainer.style.gap = '1rem';
            }
        }
    </script>
</body>
</html>