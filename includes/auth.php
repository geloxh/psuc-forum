<?php

    session_start();
    require_once __DIR__ . '/../config/database.php';

    class Auth {
        private $conn;
    
        public function __construct() {
            $database = new Database();
            $this -> conn = $database -> getConnection();
        }
    
        public function register($username, $email, $password, $full_name, $university, $role) {

            $query = "INSERT INTO users (username, email, password, full_name, university, role) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this -> conn -> prepare($query);
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
            if($stmt -> execute([$username, $email, $hashed_password, $full_name, $university, $role])) {
                $this -> createNotification($this -> conn -> lastInsertId(), 'welcome', 'Welcome to PSUC Forum!', 'Thank you for joining our community.');
                return true;
            }
            return false;
        }
    
        public function login($username, $password) {
            $query = "SELECT id, username, email, password, role, full_name FROM users WHERE (username = ? OR email = ?) AND status = 'active'";
            $stmt = $this -> conn -> prepare($query);
            $stmt -> execute([$username, $username]);
        
            if($stmt -> rowCount() > 0) {
                $user = $stmt -> fetch(PDO::FETCH_ASSOC);
                if(password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['full_name'] = $user['full_name'];
                
                    $this -> updateLastActive($user['id']);
                    return true;
                }
            }
            return false;
        }
    
        public function logout() {
            session_destroy();
            return true;
        }
    
        public function isLoggedIn() {
            return isset($_SESSION['user_id']);
        }
    
        public function getCurrentUser() {
            if(isset($_SESSION['user_id'])) {
                $query = "SELECT id, username, email, full_name, role, university, avatar, status, reputation, created_at FROM users WHERE id = ?";
                $stmt = $this -> conn -> prepare($query);
                $stmt -> execute([$_SESSION['user_id']]);
                return $stmt -> fetch(PDO::FETCH_ASSOC);
            }
            return null;
        }

        public function updateAvatar($user_id, $avatar_filename) {
            $query = "UPDATE users SET avatar = ? WHERE id = ?";
            $stmt = $this -> conn -> prepare($query);
            $stmt -> execute([$avatar_filename, $user_id]);
        }

        public function isAdmin() {
            $user = $this -> getCurrentUser();
            return $user && $user['role'] === 'admin';
        }
    
        public function hasPermission($permission) {
            $user = $this -> getCurrentUser();
            if(!$user) return false;
        
            $permissions = [
                'admin' => ['manage_users', 'manage_forums', 'moderate_content', 'system_settings'],
                'moderator' => ['moderate_content', 'manage_topics', 'pin_topics'],
                'faculty' => ['create_announcements', 'pin_topics', 'moderate_discussions'],
                'college student' => ['create_topics', 'reply_posts', 'vote_content', 'send_messages'],
                'other' => ['create_topics', 'reply_posts', 'vote_content', 'send_messages']
            ];
        
            return in_array($permission, $permissions[$user['role']] ?? []);
        }
    
        private function updateLastActive($user_id) {
            $query = "UPDATE users SET last_active = NOW() WHERE id = ?";
            $stmt = $this -> conn -> prepare($query);
            $stmt -> execute([$user_id]);
        }
    
        private function createNotification($user_id, $type, $title, $content, $url = null) {
            $query = "INSERT INTO notifications (user_id, type, title, content, url) VALUES (?, ?, ?, ?, ?)";
            $stmt = $this -> conn -> prepare($query);
            $stmt -> execute([$user_id, $type, $title, $content, $url]);
        }

        public function emailExists($email) {
            $query = "SELECT id FROM users WHERE email = ?";
            $stmt = $this -> conn -> prepare($query);
            $stmt -> execute([$email]);
            return $stmt -> rowCount() > 0;
        }
    }
?>