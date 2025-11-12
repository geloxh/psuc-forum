<?php
    require_once 'includes/auth.php';
    require_once 'config/database.php';

    $auth = new Auth();
    $user = $auth -> getCurrentUser();

    if (!$user) {
        header('Location: login.php');
        exit;
    }

    $database = new Database();
    $conn = $database -> getConnection();

    if (!$conn) {
        die("Database connection failed.");
    }

    // Handle sending message
    if ($_POST && isset($_POST['send_message'])) {
        try {
             $query = "INSERT INTO messages (sender_id, receiver_id, subject, content) VALUES (?, ?, ?, ?)";
             $stmt = $conn -> prepare($query);
             $result = $stmt -> execute([$user['id'], $_POST['receiver_id'], $_POST['subject'], $_POST['content']]);

             if ($result) {
                header('Location: messages.php?view' . ($_GET['view'] ?? 'inbox') . '&success=1');
                exit;
             } 
        } catch (PDOException $e) {
            $error = "Failed to send message. Please reload the page.";
        }
    }

    $view = $_GET['view'] ?? 'inbox';
    $selected_message = $_GET['message'] ?? null;

    // Get messages
    switch ($view) {
        case 'sent':
            $messages_query = "SELECT m.*, u.username as other_user
                            FROM messages m
                            JOIN users u ON m.receiver_id = u.id
                            WHERE m.sender_id = ?
                            ORDER BY m.created_at DESC";
            $stmt = $conn -> prepare($messages_query);
            $stmt -> execute([$user['id']]);
            break;
        default: // Inbox
            $messages_query = "SELECT m.*, u.username as other_user
                            FROM messages m
                            JOIN users u ON m.sender_id = u.id
                            WHERE m.receiver_id = ?
                            ORDER BY m.created_at DESC";
            $stmt = $conn -> prepare($messages_query);
            $stmt -> execute([$user['id']]);
            break;
    }
    $messages = $stmt -> fetchAll(PDO::FETCH_ASSOC);

    // Get selected message
    $message_detail = null;
    if ($selected_message) {
        $detail_query = "SELECT m.*,
                        ul.username as sender_name,
                        u2.username as receiver_name
                        FROM messages m
                        JOIN users ul ON m.sender_id = ul.id
                        JOIN users u2 ON m.receiver_id = u2.id
                        WHERE m.id = ? AND (m.sender_id = ? OR m.receiver_id = ?)";

        $stmt = $conn -> prepare($detail_query);
        $stmt -> execute([$selected_message, $user['id'], $user['id']]);
        $message_detail = $stmt -> fetch(PDO::FETCH_ASSOC);

        if ($message_detail && $message_detail['receiver_id'] == $user['id'] && !$message_detail['is_read']) {
            $read_query = "UPDATE messages SET is_read = 1 WHERE id = ?";
            $stmt = $conn -> prepare($read_query);
            $stmt -> execute([$selected_message]);
            $message_detail['is_read'] = 1;
        }
    }

    // Get users for messaging
    $users_query = "SELECT id, username FROM users WHERE id != ? ORDER BY username";
    $stmt = $conn -> prepare($users_query);
    $stmt -> execute([$user['id']]);
    $users = $stmt -> fetchAll(PDO::FETCH_ASSOC);

    // Get unread count
    $unread_query = "SELECT COUNT(*) as count FROM messages WHERE receiver_id = ? AND is_read = 0";
    $stmt = $conn -> prepare($unread_query);
    $stmt -> execute([$user['id']]);
    $unread_count = $stmt -> fetch(PDO::FETCH_ASSOC)['count'];
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
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="messages-container">
        <!-- Sidebar -->
        <nav class="messages-nav">
            <button class="compose-btn" onclick="toggleCompose()">
                <i class="fas fa-edit"></i>
                New Message
            </button>
            
            <div class="nav-links">
                <a href="?view=inbox" class="nav-link <?php echo $view === 'inbox' ? 'active' : ''; ?>">
                    <i class="fas fa-inbox"></i>
                    Inbox
                    <?php if ($unread_count > 0): ?>
                        <span class="badge"><?php echo $unread_count; ?></span>
                    <?php endif; ?>
                </a>
                <a href="?view=sent" class="nav-link <?php echo $view === 'sent' ? 'active' : ''; ?>">
                    <i class="fas fa-paper-plane"></i>
                    Sent
                </a>
            </div>
        </nav>

        <!-- Messages List -->
        <div class="messages-list <?php echo $selected_message ? 'hidden-mobile' : ''; ?>">
            <div class="list-header">
                <h2><?php echo ucfirst($view); ?></h2>
                <?php if (isset($_GET['success'])): ?>
                    <div class="success-msg">Message sent!</div>
                <?php endif; ?>
            </div>

            <div class="search-box">
                <input type="text" class="search-input" placeholder="Search messages..." id="messageSearch">
            </div>

            
            <?php if (count($messages) > 0): ?>
                <div class="message-items">
                    <?php foreach ($messages as $message): ?>
                        <a href="?view=<?php echo $view; ?>&message=<?php echo $message['id']; ?>" 
                           class="message-item <?php echo !$message['is_read'] && $view === 'inbox' ? 'unread' : ''; ?> <?php echo $selected_message == $message['id'] ? 'active' : ''; ?>">
                            <div class="message-header">
                                <div class="message-from">
                                    <?php echo htmlspecialchars($message['other_user']); ?>
                                </div>
                                <div class="message-time">
                                    <?php echo date('M j', strtotime($message['created_at'])); ?>
                                </div>
                            </div>
                            <div class="message-subject">
                                <?php echo htmlspecialchars($message['subject']); ?>
                            </div>
                            <div class="message-preview">
                                <?php echo substr(htmlspecialchars($message['content']), 0, 60) . '...'; ?>
                            </div>      
                        </a>
                    <?php endforeach; ?>
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
                
                <div class="message-content">
                    <h1><?php echo htmlspecialchars($message_detail['subject']); ?></h1>
                    <div class="message-meta">
                        <span class="from">From: <strong><?php echo htmlspecialchars($message_detail['sender_name']); ?></strong></span>
                        <span class="date"><?php echo date('M j, Y g:i A', strtotime($message_detail['created_at'])); ?></span>
                    </div>
                    <div class="message-body">
                        <?php echo nl2br(htmlspecialchars($message_detail['content'])); ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Compose Modal -->
    <div class="compose-overlay" id="compose-overlay"></div>
    <div class="compose-modal" id="compose-modal">
        <div class="modal-header">
            <h3>New Message</h3>
            <button class="close-btn" onclick="toggleCompose()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form method="POST" class="compose-form">
            <select name="receiver_id" required>
                <option value="">Select recipient...</option>
                <?php foreach ($users as $u): ?>
                    <option value="<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['username']); ?></option>
                <?php endforeach; ?>
            </select>
            
            <input type="text" name="subject" placeholder="Subject" required>
            
            <textarea name="content" placeholder="Write your message..." required></textarea>
            
            <div class="form-actions">
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

    <script>
        function toggleCompose() {
    const overlay = document.getElementById('compose-overlay');
    const modal = document.getElementById('compose-modal');
    
    overlay.classList.toggle('active');
    modal.classList.toggle('active');
}

// Search functionality
document.getElementById('messageSearch')?.addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const messages = document.querySelectorAll('.message-item');
    
    messages.forEach(message => {
        const text = message.textContent.toLowerCase();
        message.style.display = text.includes(searchTerm) ? 'block' : 'none';
    });
});

// Auto-hide success message
setTimeout(() => {
    const successMsg = document.querySelector('.success-msg');
    if (successMsg) successMsg.style.display = 'none';
}, 3000);
    </script>
</body>
</html>