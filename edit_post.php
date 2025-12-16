<?php
    require_once 'includes/auth.php';
    require_once 'includes/forum.php';

    $auth = new Auth();
    $forum = new Forum();
    $user = $auth -> getCurrentUser();

    if(!$user) {
        header('Location: login.php');
        exit;
    }

    $post_id = $_GET['id'] ?? 0;
    $post = $forum -> getPost($post_id);

    if(!$post || ($post['user_id'] != $user['id'] && !$auth -> isAdmin())) {
        header('Location: index.php');
        exit;
    }

    $error = '';

    if($_POST) {
        try {
            if($forum -> updatePost($post_id, $_POST['content'])) {
                // Get topic_id to redirect back
                $database = new Database();
                $conn = $database -> getConnection();
                $topic_query = "SELECT topic_id FROM posts WHERE id = ?";
                $stmt = $conn -> prepare($topic_query);
                $stmt -> execute([$post_id]);
                $topic_id = $stmt -> fetch(PDO::FETCH_ASSOC)['topic_id'];
            
                header("Location: topic.php?id=$topic_id");
                exit;
            }
        } catch(Exception $e) {
            $error = $e -> getMessage();
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Post - PSUC Forum</title>
    <link rel="stylesheet" href="assets/stylesheets/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .edit-page {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        .edit-header { 
            background: white; 
            border-radius: 12px; 
            padding: 2rem; 
            margin-bottom: 2rem; 
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            border: 1px solid #f1f5f9;
        }
        
        .edit-header h1 { 
            display: flex; 
            align-items: center; 
            gap: 0.75rem; 
            font-size: 1.5rem; 
            font-weight: 600; 
            color: #1e293b; 
            margin: 0; 
        }
        
        .edit-header .icon { 
            width: 40px; 
            height: 40px; 
            background: #3b82f6; 
            color: white; 
            border-radius: 10px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            font-size: 1.1rem; 
        }
        
        .edit-form { 
            background: white; 
            border-radius: 12px; 
            padding: 2rem; 
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            border: 1px solid #f1f5f9;
        }
        
        .form-group { 
            margin-bottom: 1.5rem; 
        }
        
        .form-label { 
            display: block; 
            margin-bottom: 0.5rem; 
            font-weight: 500; 
            color: #374151; 
            font-size: 0.9rem; 
        }
        
        .form-textarea { 
            width: 100%; 
            min-height: 200px; 
            padding: 1rem; 
            border: 1px solid #e2e8f0; 
            border-radius: 8px; 
            font-family: inherit; 
            font-size: 0.95rem; 
            line-height: 1.6; 
            resize: vertical; 
            transition: all 0.2s; 
        }
        
        .form-textarea:focus { 
            outline: none; 
            border-color: #3b82f6; 
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); 
        }
        
        .form-actions { 
            display: flex; 
            gap: 0.75rem; 
            padding-top: 1rem; 
            border-top: 1px solid #f1f5f9; 
        }
        
        .btn { 
            display: inline-flex; 
            align-items: center; 
            gap: 0.5rem; 
            padding: 0.75rem 1.5rem; 
            border-radius: 8px; 
            font-weight: 500; 
            text-decoration: none; 
            cursor: pointer; 
            transition: all 0.2s; 
            border: none; 
            font-size: 0.9rem; 
        }
        
        .btn-primary { 
            background: #3b82f6; 
            color: white; 
        }
        
        .btn-primary:hover { 
            background: #2563eb; 
            transform: translateY(-1px); 
        }
        
        .btn-secondary { 
            background: #f8fafc; 
            color: #64748b; 
            border: 1px solid #e2e8f0; 
        }
        
        .btn-secondary:hover { 
            background: #f1f5f9; 
            color: #475569; 
        }
        
        .alert { 
            padding: 1rem; 
            border-radius: 8px; 
            margin-bottom: 1.5rem; 
            border: 1px solid transparent; 
        }
        
        .alert-danger { 
            background: #fef2f2; 
            color: #dc2626; 
            border-color: #fecaca; 
        }
        
        .breadcrumb { 
            display: flex; 
            align-items: center; 
            gap: 0.5rem; 
            margin-bottom: 1.5rem; 
            font-size: 0.85rem; 
            color: #64748b; 
        }
        
        .breadcrumb a { 
            color: #3b82f6; 
            text-decoration: none; 
        }
        
        .breadcrumb a:hover { 
            text-decoration: underline; 
        }
        
        .post-preview { 
            background: #f8fafc; 
            border: 1px solid #e2e8f0; 
            border-radius: 8px; 
            padding: 1rem; 
            margin-bottom: 1rem; 
        }
        
        .preview-label { 
            font-size: 0.8rem; 
            color: #64748b; 
            margin-bottom: 0.5rem; 
            font-weight: 500; 
        }
        
        .preview-content { 
            color: #374151; 
            line-height: 1.6; 
        }
        
        @media (max-width: 768px) {
            .edit-page { 
                padding: 1rem; 
            }
            
            .edit-header, .edit-form { 
                padding: 1.5rem; 
            }
            
            .edit-header h1 { 
                font-size: 1.25rem; 
            }
            
            .form-actions { 
                flex-direction: column; 
            }
            
            .btn { 
                justify-content: center; 
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="edit-page">
        <!-- Header -->
        <div class="edit-header">
            <nav class="breadcrumb">
                <a href="index.php">Forum</a>
                <span>/</span>
                <a href="javascript:history.back()">Topic</a>
                <span>/</span>
                <span>Edit Post</span>
            </nav>
            
            <h1>
                <div class="icon">
                    <i class="fas fa-edit"></i>
                </div>
                Edit Post
            </h1>
        </div>

        <!-- Edit Form -->
        <div class="edit-form">
            <?php if($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" id="editForm">
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-comment"></i>
                        Post Content
                    </label>
                    <textarea 
                        name="content" 
                        class="form-textarea" 
                        placeholder="Edit your post content..."
                        required
                        oninput="updatePreview()"
                    ><?php echo htmlspecialchars($post['content']); ?></textarea>
                </div>

                <div class="form-group">
                    <div class="preview-label">
                        <i class="fas fa-eye"></i>
                        Preview
                    </div>
                    <div class="post-preview">
                        <div class="preview-content" id="previewContent">
                            <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Save Changes
                    </button>
                    <a href="javascript:history.back()" class="btn btn-secondary">
                        <i class="fas fa-times"></i>
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </main>
    
    <script>
        function updatePreview() {
            const content = document.querySelector('textarea[name="content"]').value;
            const preview = document.getElementById('previewContent');
            preview.innerHTML = content.replace(/\n/g, '<br>');
        }

        // Auto-resize textarea
        const textarea = document.querySelector('.form-textarea');
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.max(200, this.scrollHeight) + 'px';
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 's') {
                e.preventDefault();
                document.getElementById('editForm').submit();
            }
            if (e.key === 'Escape') {
                history.back();
            }
        });

        // Warn before leaving with unsaved changes
        let originalContent = document.querySelector('textarea[name="content"]').value;
        window.addEventListener('beforeunload', function(e) {
            const currentContent = document.querySelector('textarea[name="content"]').value;
            if (currentContent !== originalContent) {
                e.preventDefault();
                e.returnValue = '';
            }
        });

        // Remove warning when form is submitted
        document.getElementById('editForm').addEventListener('submit', function() {
            window.removeEventListener('beforeunload', arguments.callee);
        });
    </script>

    <script src="assets/scripts/main.js"></script>
</body>
</html>