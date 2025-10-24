<?php
    require_once 'includes/auth.php';

    $auth = new Auth();
    $user = $auth -> getCurrentUser();

    if(!$user) {
        header('Location: login.php');
        exit;
    }

    $database = new Database();
    $conn = $database -> getConnection();

    // Handle sending message
    if($_POST && isset($_POST['send_message'])) {
        $query = "INSERT INTO messages (sender_id, receiver_id, subject, content) VALUES (?, ?, ?, ?)";
        $stmt = $conn -> prepare($query);
        $stmt -> execute([$user['id'], $_POST['receiver_id'], $_POST['subject'], $_POST['content']]);
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
    $stmt = $conn -> prepare($messages_query);
    $stmt -> execute([$user['id'], $user['id']]);
    $messages = $stmt -> fetchAll(PDO::FETCH_ASSOC);

    // Get users for messaging
    $users_query = "SELECT id, username FROM users WHERE id != ? ORDER BY username";
    $stmt = $conn -> prepare($users_query);
    $stmt -> execute([$user['id']]);
    $users = $stmt -> fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - PSUC Forum</title>
    <link rel="stylesheet" href="assets/stylesheets/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="messages-page">
        <div class="messages-container">
            <!-- Header -->
            <header class="messages-header">
                <h1>Messages</h1>
                <?php
                $unread_query = "SELECT COUNT(*) as count FROM messages WHERE receiver_id = ? AND is_read = 0";
                $stmt = $conn->prepare($unread_query);
                $stmt->execute([$user['id']]);
                $unread_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                ?>
                <div class="message-stats">
                    <span><?php echo count($messages); ?> total</span>
                    <?php if($unread_count > 0): ?>
                        <span class="unread-badge"><?php echo $unread_count; ?> unread</span>
                    <?php endif; ?>
                </div>
            </header>

            <?php if(isset($success)): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <div class="messages-layout">
                <!-- Messages List -->
                <div class="messages-list">
                    <div class="messages-list-header">
                        <h2>Your Messages</h2>
                        <button class="compose-btn" onclick="toggleCompose()">
                            <i class="fas fa-plus"></i>
                            New Message
                        </button>
                    </div>
                    
                    <?php if(count($messages) > 0): ?>
                        <div class="messages-scroll">
                            <?php foreach($messages as $message): ?>
                                <div class="message-item <?php echo !$message['is_read'] && $message['receiver_id'] == $user['id'] ? 'unread' : ''; ?>">
                                    <div class="message-header">
                                        <h3><?php echo htmlspecialchars($message['subject']); ?></h3>
                                        <?php if(!$message['is_read'] && $message['receiver_id'] == $user['id']): ?>
                                            <span class="new-badge">New</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="message-meta">
                                        <?php if($message['sender_id'] == $user['id']): ?>
                                            <span>To: <strong><?php echo htmlspecialchars($message['receiver_name']); ?></strong></span>
                                        <?php else: ?>
                                            <span>From: <strong><?php echo htmlspecialchars($message['sender_name']); ?></strong></span>
                                        <?php endif; ?>
                                        <time><?php echo date('M j, Y', strtotime($message['created_at'])); ?></time>
                                    </div>
                                    <p class="message-preview">
                                        <?php echo substr(htmlspecialchars($message['content']), 0, 120) . '...'; ?>
                                    </p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-messages">
                            <i class="fas fa-inbox"></i>
                            <h3>No messages yet</h3>
                            <p>Start a conversation with other members!</p>
                            <button class="compose-btn" onclick="toggleCompose()">
                                <i class="fas fa-plus"></i>
                                Send First Message
                            </button>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Compose Form -->
                <div class="compose-form" id="compose-form">
                    <div class="compose-header">
                        <h2>New Message</h2>
                        <button class="close-btn" onclick="toggleCompose()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <form method="POST" class="message-form">
                        <div class="form-field">
                            <label for="receiver_id">To</label>
                            <select name="receiver_id" id="receiver_id" required>
                                <option value="">Select recipient</option>
                                <?php foreach($users as $u): ?>
                                    <option value="<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['username']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-field">
                            <label for="subject">Subject</label>
                            <input type="text" name="subject" id="subject" placeholder="Enter message subject" required>
                        </div>
                        
                        <div class="form-field">
                            <label for="content">Message</label>
                            <textarea name="content" id="content" rows="8" placeholder="Write your message here..." required></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" name="send_message" class="send-btn">
                                <i class="fas fa-paper-plane"></i>
                                Send Message
                            </button>
                            <button type="button" class="cancel-btn" onclick="toggleCompose()">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script src="assets/scripts/main.js"></script>
    <script>
        function toggleCompose() {
            const form = document.getElementById('compose-form');
            form.classList.toggle('active');
        }
    </script>
    
</body>
</html>