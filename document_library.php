<?php

    require_once 'includes/auth.php';
    require_once 'config/database.php';

    $auth = new Auth();
    $user = $auth -> getCurrentUser();

    class Document {
        private $conn;
        private $table_name = "documents";

        public function __construct($db) {
            $this -> conn = $db;
        }

        public function create($title, $description, $filePath, $userId) {
            $query = "INSERT INTO " . $this -> table_name . " (title, description, file_path, uploaded_by (?, ?, ?, ?";
            $stmt = $this -> conn -> prepare($query);
            return $stmt -> execute([$title, $description, $filePath, $userID]);
        }

        public function read() {
            $query = "SELECT d.*, u.username FROM " . $this -> table_name . " d JOIN users u ON d.uploaded_by = u.id ORDER BY d.created_at DESC";
            $stmt = $this -> conn -> prepare($query);
            $stmt -> execute();
            return $stmt;
        }

        public function update($id, $title, $description) {
            $query = "UPDATE " . $this -> table_name . " SET title = ?, description = ? WHERE id = ?";
            $stmt = $this -> conn -> prepare($query);
            return $stmt -> execute([$title, $description, $id]);
        }

        public function delete($id) {
            $query = "DELETE FROM " . $this -> table_name . " WHERE id = ?";
            $stmt = $this -> conn -> prepare($query);
            return $stmt -> execute([$id]);
        }
    }

    $database = new Database();
    $db = $database -> getConnection();
    $document = new Document($db);

    $is_admin = ($user && $user['role'] == 'admin');

    if ($is_admin && $_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['add_document'])) {
            $title = $_POST['title'];
            $description = $_POST['description'];

            // File Upload Handling
            $target_dir = "uploads/documents/";
            if (!is_dir($target_dir)) {
                mkdir;
            }
        }
    }


?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Library - PSUC Forum</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, sans-serif; background: #fafafa; }
        .header { background: white; padding: 1rem 0; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .container { max-width: 800px; margin: 0 auto; padding: 0 1rem; }
        .logo { display: flex; align-items: center; gap: 1rem; text-decoration: none; color: #333; }
        .logo img { height: 50px; border-radius: 8px; }
        .content { padding: 3rem 0; }
        h1 { font-size: 2rem; margin-bottom: 2rem; color: #333; }
        .doc-grid { display: grid; gap: 1rem; }
        .doc-item { background: white; padding: 1.5rem; border-radius: 8px; display: flex; align-items: center; gap: 1rem; }
        .doc-icon { width: 40px; height: 40px; background: #4299e1; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; }
        .doc-info h3 { margin-bottom: 0.5rem; }
        .doc-info p { color: #666; font-size: 0.9rem; }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="container">
        <div class="content">
            <h1>Document Library</h1>
            <div class="doc-grid">
                <div class="doc-item">
                    <div class="doc-icon">📄</div>
                    <div class="doc-info">
                        <h3>Student Handbook</h3>
                        <p>Complete guide for students - policies, procedures, and academic requirements</p>
                    </div>
                </div>
                <div class="doc-item">
                    <div class="doc-icon">📋</div>
                    <div class="doc-info">
                        <h3>Enrollment Forms</h3>
                        <p>Registration and enrollment forms for all academic programs</p>
                    </div>
                </div>
                <div class="doc-item">
                    <div class="doc-icon">📊</div>
                    <div class="doc-info">
                        <h3>Research Guidelines</h3>
                        <p>Guidelines and templates for thesis and research projects</p>
                    </div>
                </div>
                <div class="doc-item">
                    <div class="doc-icon">🎓</div>
                    <div class="doc-info">
                        <h3>Graduation Requirements</h3>
                        <p>Complete checklist and requirements for graduation</p>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>