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
    <style>
        .messages-page {
            background: #fafbfc;
            min-height: 100vh;
            padding: 1.5rem 1rem;
        }
        
        .messages-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            border: 1px solid rgba(229, 231, 235, 0.3);
        }
        
        .messages-header {
            padding: 2rem 2rem 1.5rem;
            border-bottom: 1px solid #f3f4f6;
            background: linear-gradient(135deg, #fafbfc 0%, #f8fafc 100%);
        }
        
        .messages-header h1 {
            font-size: 1.75rem;
            font-weight: 600;
            color: #111827;
            margin: 0 0 0.75rem 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .messages-header h1::before {
            content: '💬';
            font-size: 1.5rem;
        }
        
        .message-stats {
            display: flex;
            gap: 1rem;
            font-size: 0.875rem;
            color: #6b7280;
        }
        
        .unread-badge {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-weight: 500;
            font-size: 0.75rem;
        }
        
        .success-message {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 2rem;
            background: linear-gradient(135deg, #f0fdf4, #dcfce7);
            color: #166534;
            border-left: 4px solid #22c55e;
            font-weight: 500;
        }
        
        .messages-content {
            padding: 2rem;
        }
        
        .messages-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .messages-actions h2 {
            font-size: 1.25rem;
            font-weight: 600;
            color: #111827;
            margin: 0;
        }
        
        .compose-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 500;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.2);
        }
        
        .compose-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(59, 130, 246, 0.3);
        }
        
        .messages-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        
        .message-item {
            padding: 1.5rem;
            border: 1px solid #f3f4f6;
            border-radius: 12px;
            background: #fafbfc;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
        }
        
        .message-item:hover {
            background: white;
            border-color: #e5e7eb;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        
        .message-item.unread {
            background: linear-gradient(135deg, #eff6ff, #dbeafe);
            border-color: #3b82f6;
            border-left: 4px solid #3b82f6;
        }
        
        .message-item.unread::before {
            content: '';
            position: absolute;
            top: 1rem;
            right: 1rem;
            width: 8px;
            height: 8px;
            background: #3b82f6;
            border-radius: 50%;
        }
        
        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 0.75rem;
        }
        
        .message-header h3 {
            font-size: 1.1rem;
            font-weight: 600;
            color: #111827;
            margin: 0;
            line-height: 1.4;
        }
        
        .new-badge {
            background: #3b82f6;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 8px;
            font-size: 0.7rem;
            font-weight: 500;
        }
        
        .message-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
            font-size: 0.875rem;
            color: #6b7280;
        }
        
        .message-meta strong {
            color: #3b82f6;
            font-weight: 600;
        }
        
        .message-preview {
            color: #6b7280;
            font-size: 0.9rem;
            line-height: 1.5;
            margin: 0;
        }
        
        .empty-messages {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 4rem 2rem;
            text-align: center;
            color: #6b7280;
        }
        
        .empty-messages i {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            color: #d1d5db;
            opacity: 0.7;
        }
        
        .empty-messages h3 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #111827;
            margin: 0 0 0.75rem 0;
        }
        
        .empty-messages p {
            margin: 0 0 2rem 0;
            font-size: 1rem;
            line-height: 1.6;
        }
        
        .compose-form {
            position: fixed;
            top: 0;
            right: -100%;
            width: 100%;
            max-width: 500px;
            height: 100vh;
            background: white;
            box-shadow: -4px 0 20px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            transition: right 0.3s ease;
            overflow-y: auto;
        }
        
        .compose-form.active {
            right: 0;
        }
        
        .compose-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 2rem;
            border-bottom: 1px solid #f3f4f6;
            background: #fafbfc;
        }
        
        .compose-header h2 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #111827;
            margin: 0;
        }
        
        .close-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #f3f4f6;
            color: #6b7280;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }
        
        .close-btn:hover {
            background: #e5e7eb;
            color: #111827;
        }
        
        .message-form {
            padding: 2rem;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        
        .form-field {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .form-field label {
            font-weight: 600;
            color: #111827;
            font-size: 0.875rem;
        }
        
        .form-field select,
        .form-field input,
        .form-field textarea {
            padding: 0.875rem;
            border: 2px solid #f3f4f6;
            border-radius: 12px;
            font-size: 1rem;
            font-family: inherit;
            transition: all 0.2s ease;
            background: #fafbfc;
        }
        
        .form-field select:focus,
        .form-field input:focus,
        .form-field textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            background: white;
        }
        
        .form-field textarea {
            resize: vertical;
            min-height: 150px;
            line-height: 1.6;
        }
        
        .form-actions {
            display: flex;
            gap: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #f3f4f6;
        }
        
        .send-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.875rem 1.5rem;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            flex: 1;
        }
        
        .send-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(59, 130, 246, 0.3);
        }
        
        .cancel-btn {
            padding: 0.875rem 1rem;
            background: #f9fafb;
            color: #6b7280;
            border: 2px solid #f3f4f6;
            border-radius: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .cancel-btn:hover {
            background: #f3f4f6;
            color: #111827;
            border-color: #e5e7eb;
        }
        
        .compose-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        
        .compose-overlay.active {
            opacity: 1;
            visibility: visible;
        }
        
        @media (max-width: 768px) {
            .messages-page {
                padding: 1rem 0.5rem;
            }
            
            .messages-header {
                padding: 1.5rem;
            }
            
            .messages-header h1 {
                font-size: 1.5rem;
            }
            
            .messages-content {
                padding: 1.5rem;
            }
            
            .message-item {
                padding: 1rem;
            }
            
            .message-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
            
            .message-meta {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.25rem;
            }
            
            .compose-form {
                max-width: 100%;
            }
        }
        
        @media (max-width: 480px) {
            .messages-header,
            .messages-content {
                padding: 1rem;
            }
            
            .messages-actions {
                flex-direction: column;
                gap: 1rem;
                align-items: stretch;
            }
            
            .compose-btn {
                justify-content: center;
            }
            
            .form-actions {
                flex-direction: column;
            }
        }
    </style>
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
                    <span><?php echo count($messages); ?> total messages</span>
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

            <div class="messages-content">
                <div class="messages-actions">
                    <h2>Your Conversations</h2>
                    <button class="compose-btn" onclick="toggleCompose()">
                        <i class="fas fa-plus"></i>
                        New Message
                    </button>
                </div>
                
                <?php if(count($messages) > 0): ?>
                    <div class="messages-list">
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
                                    <time><?php echo date('M j, Y \a\t g:i A', strtotime($message['created_at'])); ?></time>
                                </div>
                                <p class="message-preview">
                                    <?php echo substr(htmlspecialchars($message['content']), 0, 120) . (strlen($message['content']) > 120 ? '...' : ''); ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-messages">
                        <i class="fas fa-inbox"></i>
                        <h3>No messages yet</h3>
                        <p>Start a conversation with other members of the community!</p>
                        <button class="compose-btn" onclick="toggleCompose()">
                            <i class="fas fa-plus"></i>
                            Send First Message
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Compose Form Overlay -->
    <div class="compose-overlay" id="compose-overlay" onclick="toggleCompose()"></div>
    
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

    <script src="assets/scripts/main.js"></script>
    <script>
        function toggleCompose() {
            const form = document.getElementById('compose-form');
            const overlay = document.getElementById('compose-overlay');
            
            form.classList.toggle('active');
            overlay.classList.toggle('active');
            
            // Prevent body scroll when compose form is open
            if (form.classList.contains('active')) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        }
        
        // Close compose form when pressing Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const form = document.getElementById('compose-form');
                if (form.classList.contains('active')) {
                    toggleCompose();
                }
            }
        });
        
        // Add smooth animations for message items
        document.addEventListener('DOMContentLoaded', function() {
            const messageItems = document.querySelectorAll('.message-item');
            messageItems.forEach((item, index) => {
                item.style.opacity = '0';
                item.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    item.style.transition = 'all 0.3s ease';
                    item.style.opacity = '1';
                    item.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
    
</body>
</html>