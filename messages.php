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

// Handle sending message
if($_POST && isset($_POST['send_message'])) {
    $query = "INSERT INTO messages (sender_id, receiver_id, subject, content) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->execute([$user['id'], $_POST['receiver_id'], $_POST['subject'], $_POST['content']]);
    $success = "Message sent successfully!";
}

// Get messages
$messages_query = "SELECT m.*, 
                   u1.username as sender_name, 
                   u2.username as receiver_name 
                   FROM messages m 
                   JOIN users u1 ON m.sender_id = u1.id 
                   JOIN users u2 ON m.receiver_id = u2.id 
                   WHERE m.sender_id = ? OR m.receiver_id = ? 
                   ORDER BY m.created_at DESC";
$stmt = $conn->prepare($messages_query);
$stmt->execute([$user['id'], $user['id']]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get users for messaging
$users_query = "SELECT id, username FROM users WHERE id != ? ORDER BY username";
$stmt = $conn->prepare($users_query);
$stmt->execute([$user['id']]);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - PSUC Forum</title>
    <link rel="stylesheet" href="assets/stylesheets/main.css">
    <link rel="stylesheet" href="assets/stylesheets/dark-theme.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container">
        <div class="main-content">
            <div class="forum-content">
                <div class="p-3">
                    <h1><i class="fas fa-envelope"></i> Private Messages</h1>
                    
                    <?php if(isset($success)): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-top: 2rem;">
                        <!-- Send Message -->
                        <div>
                            <h3>Send New Message</h3>
                            <form method="POST">
                                <div class="form-group">
                                    <label>To</label>
                                    <select name="receiver_id" class="form-control" required>
                                        <option value="">Select recipient</option>
                                        <?php foreach($users as $u): ?>
                                            <option value="<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['username']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Subject</label>
                                    <input type="text" name="subject" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Message</label>
                                    <textarea name="content" class="form-control" rows="6" required></textarea>
                                </div>
                                <button type="submit" name="send_message" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i> Send Message
                                </button>
                            </form>
                        </div>
                        
                        <!-- Message List -->
                        <div>
                            <h3>Your Messages</h3>
                            <?php if(count($messages) > 0): ?>
                                <div style="max-height: 500px; overflow-y: auto;">
                                    <?php foreach($messages as $message): ?>
                                        <div class="message-item" style="padding: 1rem; border: 1px solid var(--border-color); border-radius: 8px; margin-bottom: 1rem; <?php echo !$message['is_read'] && $message['receiver_id'] == $user['id'] ? 'background: rgba(59, 130, 246, 0.1);' : ''; ?>">
                                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                                                <strong><?php echo htmlspecialchars($message['subject']); ?></strong>
                                                <?php if(!$message['is_read'] && $message['receiver_id'] == $user['id']): ?>
                                                    <span class="badge" style="background: var(--primary-color);">New</span>
                                                <?php endif; ?>
                                            </div>
                                            <div style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 0.5rem;">
                                                <?php if($message['sender_id'] == $user['id']): ?>
                                                    To: <?php echo htmlspecialchars($message['receiver_name']); ?>
                                                <?php else: ?>
                                                    From: <?php echo htmlspecialchars($message['sender_name']); ?>
                                                <?php endif; ?>
                                                • <?php echo date('M j, Y g:i A', strtotime($message['created_at'])); ?>
                                            </div>
                                            <p style="margin: 0; font-size: 0.9rem;">
                                                <?php echo substr(htmlspecialchars($message['content']), 0, 100) . '...'; ?>
                                            </p>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center" style="padding: 2rem;">
                                    <i class="fas fa-inbox" style="font-size: 3rem; color: var(--text-secondary); margin-bottom: 1rem;"></i>
                                    <h4>No messages yet</h4>
                                    <p class="text-secondary">Start a conversation with other members!</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <aside class="sidebar">
                <div class="widget">
                    <h3><i class="fas fa-info-circle"></i> Message Info</h3>
                    <?php
                    $unread_query = "SELECT COUNT(*) as count FROM messages WHERE receiver_id = ? AND is_read = 0";
                    $stmt = $conn->prepare($unread_query);
                    $stmt->execute([$user['id']]);
                    $unread_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                    ?>
                    <div class="stats-grid">
                        <div class="stat-item">
                            <strong><?php echo count($messages); ?></strong>
                            <span>Total Messages</span>
                        </div>
                        <div class="stat-item">
                            <strong><?php echo $unread_count; ?></strong>
                            <span>Unread</span>
                        </div>
                    </div>
                </div>

                <div class="widget">
                    <h3><i class="fas fa-users"></i> Active Members</h3>
                    <?php
                    $active_query = "SELECT username FROM users WHERE last_active > DATE_SUB(NOW(), INTERVAL 1 HOUR) AND id != ? ORDER BY last_active DESC LIMIT 5";
                    $stmt = $conn->prepare($active_query);
                    $stmt->execute([$user['id']]);
                    $active_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    <?php foreach($active_users as $active_user): ?>
                        <div class="mb-1">
                            <span class="badge" style="background: var(--success-color);">●</span>
                            <?php echo htmlspecialchars($active_user['username']); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </aside>
        </div>
    </main>

    <script>
        function toggleTheme() {
            const body = document.body;
            const icon = document.getElementById('themeIcon');
            
            if (body.classList.contains('dark-theme')) {
                body.classList.remove('dark-theme');
                icon.className = 'fas fa-moon';
                localStorage.setItem('theme', 'light');
            } else {
                body.classList.add('dark-theme');
                icon.className = 'fas fa-sun';
                localStorage.setItem('theme', 'dark');
            }
        }

        function toggleDropdown() {
            document.getElementById('userDropdown').classList.toggle('show');
        }

        // Load saved theme
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme');
            const icon = document.getElementById('themeIcon');
            
            if (savedTheme === 'dark') {
                document.body.classList.add('dark-theme');
                if(icon) icon.className = 'fas fa-sun';
            }
        });

        // Close dropdown when clicking outside
        window.onclick = function(event) {
            if (!event.target.matches('.user-menu a')) {
                var dropdown = document.getElementById('userDropdown');
                if (dropdown && dropdown.classList.contains('show')) {
                    dropdown.classList.remove('show');
                }
            }
        }
    </script>
</body>
</html>