<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

$auth = new Auth();
$user = $auth->getCurrentUser();

if (!$user) {
    header('Location: login.php');
    exit;
}

$database = new Database();
$conn = $database->getConnection();

// Handle sending message
if ($_POST && isset($_POST['send_message'])) {
    $query = "INSERT INTO messages (sender_id, receiver_id, subject, content) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->execute([$user['id'], $_POST['receiver_id'], $_POST['subject'], $_POST['content']]);
    $success = "Message sent successfully!";
}

// Handle marking as read
if (isset($_GET['read']) && $_GET['read']) {
    $read_query = "UPDATE messages SET is_read = 1 WHERE id = ? AND receiver_id = ?";
    $stmt = $conn->prepare($read_query);
    $stmt->execute([$_GET['read'], $user['id']]);
}

// Get current view
$view = $_GET['view'] ?? 'inbox';
$selected_message = $_GET['message'] ?? null;

// Get messages based on view
switch ($view) {
    case 'sent':
        $messages_query = "SELECT m.*, u.username as other_user 
                          FROM messages m 
                          JOIN users u ON m.receiver_id = u.id 
                          WHERE m.sender_id = ? 
                          ORDER BY m.created_at DESC";
        $stmt = $conn->prepare($messages_query);
        $stmt->execute([$user['id']]);
        break;
    default: // inbox
        $messages_query = "SELECT m.*, u.username as other_user 
                          FROM messages m 
                          JOIN users u ON m.sender_id = u.id 
                          WHERE m.receiver_id = ? 
                          ORDER BY m.created_at DESC";
        $stmt = $conn->prepare($messages_query);
        $stmt->execute([$user['id']]);
}
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get selected message details
$message_detail = null;
if ($selected_message) {
    $detail_query = "SELECT m.*, 
                     u1.username as sender_name, 
                     u2.username as receiver_name 
                     FROM messages m 
                     JOIN users u1 ON m.sender_id = u1.id 
                     JOIN users u2 ON m.receiver_id = u2.id 
                     WHERE m.id = ? AND (m.sender_id = ? OR m.receiver_id = ?)";
    $stmt = $conn->prepare($detail_query);
    $stmt->execute([$selected_message, $user['id'], $user['id']]);
    $message_detail = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Mark as read if it's received by current user
    if ($message_detail && $message_detail['receiver_id'] == $user['id'] && !$message_detail['is_read']) {
        $read_query = "UPDATE messages SET is_read = 1 WHERE id = ?";
        $stmt = $conn->prepare($read_query);
        $stmt->execute([$selected_message]);
        $message_detail['is_read'] = 1;
    }
}

// Get users for messaging
$users_query = "SELECT id, username FROM users WHERE id != ? ORDER BY username";
$stmt = $conn->prepare($users_query);
$stmt->execute([$user['id']]);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get unread count
$unread_query = "SELECT COUNT(*) as count FROM messages WHERE receiver_id = ? AND is_read = 0";
$stmt = $conn->prepare($unread_query);
$stmt->execute([$user['id']]);
$unread_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - PSUC Forum</title>
    <link rel="stylesheet" href="assets/stylesheets/main.css">
    <link rel="stylesheet" href="assets/stylesheets/messages.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="gmail-container">
        <!-- Sidebar -->
        <aside class="gmail-sidebar">
            <button class="compose-btn" onclick="toggleCompose()">
                <i class="fas fa-plus"></i>
                <span>Compose</span>
            </button>
            
            <nav class="sidebar-nav">
                <a href="?view=inbox" class="nav-item <?php echo $view === 'inbox' ? 'active' : ''; ?>">
                    <i class="fas fa-inbox"></i>
                    <span>Inbox</span>
                    <?php if ($unread_count > 0): ?>
                        <span class="count-badge"><?php echo $unread_count; ?></span>
                    <?php endif; ?>
                </a>
                <a href="?view=sent" class="nav-item <?php echo $view === 'sent' ? 'active' : ''; ?>">
                    <i class="fas fa-paper-plane"></i>
                    <span>Sent</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="gmail-main">
            <!-- Message List -->
            <div class="message-list <?php echo $selected_message ? 'hidden-mobile' : ''; ?>">
                <div class="list-header">
                    <h2>
                        <i class="fas fa-<?php echo $view === 'sent' ? 'paper-plane' : 'inbox'; ?>"></i>
                        <?php echo ucfirst($view); ?>
                    </h2>
                    <?php if (isset($success)): ?>
                        <div class="success-toast">
                            <i class="fas fa-check"></i>
                            <?php echo $success; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if (count($messages) > 0): ?>
                    <div class="messages-list">
                        <?php foreach ($messages as $message): ?>
                            <a href="?view=<?php echo $view; ?>&message=<?php echo $message['id']; ?>" 
                               class="message-row <?php echo !$message['is_read'] && $view === 'inbox' ? 'unread' : ''; ?> <?php echo $selected_message == $message['id'] ? 'selected' : ''; ?>">
                                <div class="message-checkbox">
                                    <input type="checkbox" onclick="event.stopPropagation()">
                                </div>
                                <div class="message-sender">
                                    <?php echo htmlspecialchars($message['other_user']); ?>
                                </div>
                                <div class="message-content">
                                    <span class="message-subject"><?php echo htmlspecialchars($message['subject']); ?></span>
                                    <span class="message-snippet"> - <?php echo substr(htmlspecialchars($message['content']), 0, 80); ?></span>
                                </div>
                                <div class="message-date">
                                    <?php echo date('M j', strtotime($message['created_at'])); ?>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h3>No messages in <?php echo $view; ?></h3>
                        <p>Your <?php echo $view; ?> is empty</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Message Detail -->
            <?php if ($message_detail): ?>
                <div class="message-detail">
                    <div class="detail-header">
                        <button class="back-btn" onclick="window.location.href='?view=<?php echo $view; ?>'">
                            <i class="fas fa-arrow-left"></i>
                        </button>
                        <div class="detail-actions">
                            <button class="action-btn" title="Reply">
                                <i class="fas fa-reply"></i>
                            </button>
                            <button class="action-btn" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="message-header">
                        <h1><?php echo htmlspecialchars($message_detail['subject']); ?></h1>
                        <div class="message-meta">
                            <div class="sender-info">
                                <strong><?php echo htmlspecialchars($message_detail['sender_name']); ?></strong>
                                <span>to <?php echo htmlspecialchars($message_detail['receiver_name']); ?></span>
                            </div>
                            <div class="message-time">
                                <?php echo date('M j, Y, g:i A', strtotime($message_detail['created_at'])); ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="message-body">
                        <?php echo nl2br(htmlspecialchars($message_detail['content'])); ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="no-selection">
                    <i class="fas fa-envelope-open"></i>
                    <h3>Select a message to read</h3>
                    <p>Choose a message from your <?php echo $view; ?> to view here</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Compose Modal -->
    <div class="compose-overlay" id="compose-overlay" onclick="toggleCompose()"></div>
    <div class="compose-modal" id="compose-modal">
        <div class="compose-header">
            <h3>New Message</h3>
            <button class="close-btn" onclick="toggleCompose()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form method="POST" class="compose-form">
            <div class="compose-field">
                <label>To</label>
                <select name="receiver_id" required>
                    <option value="">Select recipient</option>
                    <?php foreach ($users as $u): ?>
                        <option value="<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['username']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="compose-field">
                <label>Subject</label>
                <input type="text" name="subject" placeholder="Subject" required>
            </div>
            
            <div class="compose-field">
                <textarea name="content" placeholder="Compose your message..." required></textarea>
            </div>
            
            <div class="compose-actions">
                <button type="submit" name="send_message" class="send-btn">
                    <i class="fas fa-paper-plane"></i>
                    Send
                </button>
                <button type="button" class="cancel-btn" onclick="toggleCompose()">
                    Cancel
                </button>
            </div>
        </form>
    </div>

    <script src="assets/scripts/main.js"></script>
    <script src="assets/scripts/messages.js"></script>
</body>
</html>