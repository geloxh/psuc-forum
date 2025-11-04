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

    $topic_id = $_GET['id'] ?? 0;
    $topic = $forum -> getTopic($topic_id);

    if(!$topic || ($topic['user_id'] != $user['id'] && !$auth -> isAdmin())) {
        header('Location: index.php');
        exit;
    }

    // Get existing attachments
    $attachments = $forum -> getAttachments(null, $topic_id);
    $error = '';
    $success = '';

    // Handle attachments deletion
    if(isset($_POST['delete_attachment'])) {
        $attachment_id = $_POST['attachment_id'];
        if($forum -> deleteAttachment($attachment_id, $user['id'])) {
            $success = 'Attachment deleted successfully.';
            $attachments = $forum -> getAttachments(null, $topic_id);
        } else {
            $error = 'Failed to delete attachment.';
        }
    }

    // Handle for submission
    if(isset($_POST['update_topic'])) {
        try{
            $forum -> updateTopicWithAttachments($topic_id, $_POST['title'], $_POST['content'], $user['id']);
            header("Location: topic.php?id=$topic_id");
            exit;
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
    <title>Edit Topic - PSUC Forum</title>
    <link rel="stylesheet" href="assets/stylesheets/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, sans-serif; background: #f8f9fa; color: #333; line-height: 1.6; }
        .container { max-width: 800px; margin: 2rem auto; padding: 0 1rem; }
        .card { background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); padding: 2rem; margin-bottom: 1rem; }
        .breadcrumb { font-size: 0.9rem; color: #666; margin-bottom: 1.5rem; }
        .breadcrumb a { color: #007bff; text-decoration: none; }
        h1 { font-size: 1.5rem; margin-bottom: 1.5rem; color: #333; }
        .form-group { margin-bottom: 1.5rem; }
        label { display: block; margin-bottom: 0.5rem; font-weight: 500; color: #555; }
        input, textarea { width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem; transition: border-color 0.2s; }
        input:focus, textarea:focus { outline: none; border-color: #007bff; box-shadow: 0 0 0 2px rgba(0,123,255,0.25); }
        textarea { resize: vertical; min-height: 200px; }
        .btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; border: none; border-radius: 4px; font-size: 0.9rem; font-weight: 500; text-decoration: none; cursor: pointer; transition: all 0.2s; }
        .btn-primary { background: #007bff; color: white; }
        .btn-primary:hover { background: #0056b3; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-secondary:hover { background: #545b62; }
        .btn-danger { background: #dc3545; color: white; padding: 0.25rem 0.5rem; font-size: 0.8rem; }
        .btn-danger:hover { background: #c82333; }
        .actions { display: flex; gap: 1rem; margin-top: 2rem; }
        .alert { padding: 1rem; border-radius: 4px; margin-bottom: 1rem; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .attachments { margin-top: 1rem; }
        .attachment-item { display: flex; align-items: center; justify-content: space-between; padding: 0.75rem; background: #f8f9fa; border-radius: 4px; margin-bottom: 0.5rem; }
        .attachment-info { display: flex; align-items: center; gap: 0.5rem; }
        .file-icon { color: #6c757d; }
        .file-upload { border: 2px dashed #ddd; border-radius: 4px; padding: 2rem; text-align: center; background: #fafafa; transition: border-color 0.2s; }
        .file-upload:hover { border-color: #007bff; }
        .file-upload input { display: none; }
        .upload-text { color: #666; margin-top: 0.5rem; font-size: 0.9rem; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="breadcrumb">
                <a href="index.php">Forum</a> > 
                <a href="topic.php?id=<?php echo $topic_id; ?>"><?php echo htmlspecialchars($topic['title']); ?></a> > 
                Edit
            </div>
            
            <h1>Edit Topic</h1>
            
            <?php if($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" required value="<?php echo htmlspecialchars($topic['title']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="content">Content</label>
                    <textarea id="content" name="content" required><?php echo htmlspecialchars($topic['content']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Current Attachments</label>
                    <div class="attachments">
                        <?php if(empty($attachments)): ?>
                            <p style="color: #666; font-style: italic;">No attachments</p>
                        <?php else: ?>
                            <?php foreach($attachments as $attachment): ?>
                                <div class="attachment-item">
                                    <div class="attachment-info">
                                        <i class="fas fa-file file-icon"></i>
                                        <span><?php echo htmlspecialchars($attachment['file_name']); ?></span>
                                        <small style="color: #666;">(<?php echo number_format($attachment['file_size'] / 1024, 1); ?> KB)</small>
                                    </div>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="attachment_id" value="<?php echo $attachment['id']; ?>">
                                        <button type="submit" name="delete_attachment" class="btn btn-danger" onclick="return confirm('Delete this attachment?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Add New Attachments</label>
                    <div class="file-upload" onclick="document.getElementById('attachments').click()">
                        <i class="fas fa-cloud-upload-alt" style="font-size: 2rem; color: #007bff;"></i>
                        <div class="upload-text">Click to select files or drag and drop</div>
                        <input type="file" id="attachments" name="attachments[]" multiple accept=".jpg,.jpeg,.png,.gif,.pdf,.docx,.txt,.zip,.rar">
                    </div>
                </div>
                
                <div class="actions">
                    <button type="submit" name="update_topic" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                    <a href="topic.php?id=<?php echo $topic_id; ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // File upload drag and drop
        const fileUpload = document.querySelector('.file-upload');
        const fileInput = document.getElementById('attachments');
        
        fileUpload.addEventListener('dragover', (e) => {
            e.preventDefault();
            fileUpload.style.borderColor = '#007bff';
        });
        
        fileUpload.addEventListener('dragleave', () => {
            fileUpload.style.borderColor = '#ddd';
        });
        
        fileUpload.addEventListener('drop', (e) => {
            e.preventDefault();
            fileUpload.style.borderColor = '#ddd';
            fileInput.files = e.dataTransfer.files;
        });
        
        fileInput.addEventListener('change', () => {
            const files = fileInput.files;
            if(files.length > 0) {
                const uploadText = document.querySelector('.upload-text');
                uploadText.textContent = `${files.length} file(s) selected`;
            }
        });
    </script>
</body>
</html>