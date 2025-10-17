<?php
    require_once __DIR__ . '/../config/database.php';

class Forum {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this -> conn = $database -> getConnection();
    }
    
    public function getCategories() {
        $query = "SELECT * FROM categories ORDER BY position, name";
        $stmt = $this -> conn -> prepare($query);
        $stmt -> execute();
        return $stmt -> fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getForumsByCategory($category_id) {
        $query = "SELECT f.*, 
                  (SELECT COUNT(*) FROM topics WHERE forum_id = f.id) as topics_count,
                  (SELECT COUNT(*) FROM posts p JOIN topics t ON p.topic_id = t.id WHERE t.forum_id = f.id) as posts_count,
                  (SELECT CONCAT(u.username, '|', t.title, '|', p.created_at) 
                   FROM posts p 
                   JOIN topics t ON p.topic_id = t.id 
                   JOIN users u ON p.user_id = u.id 
                   WHERE t.forum_id = f.id 
                   ORDER BY p.created_at DESC LIMIT 1) as last_post
                  FROM forums f WHERE category_id = ? ORDER BY position, name";
        $stmt = $this -> conn -> prepare($query);
        $stmt -> execute([$category_id]);
        return $stmt -> fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getTopics($forum_id, $limit = 20, $offset = 0) {
        $query = "SELECT t.*, u.username, u.avatar,
                  (SELECT COUNT(*) FROM posts WHERE topic_id = t.id) as replies_count,
                  (SELECT CONCAT(u2.username, '|', p.created_at) 
                   FROM posts p 
                   JOIN users u2 ON p.user_id = u2.id 
                   WHERE p.topic_id = t.id 
                   ORDER BY p.created_at DESC LIMIT 1) as last_reply
                  FROM topics t 
                  JOIN users u ON t.user_id = u.id 
                  WHERE forum_id = ? 
                  ORDER BY is_pinned DESC, updated_at DESC 
                  LIMIT ? OFFSET ?";
        $stmt = $this -> conn -> prepare($query);
        $stmt -> bindValue(1, $forum_id, PDO::PARAM_INT);
        $stmt -> bindValue(2, (int) $limit, PDO::PARAM_INT);
        $stmt -> bindValue(3, (int) $offset, PDO::PARAM_INT);
        $stmt -> execute();
        return $stmt -> fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getTopic($topic_id) {
        $query = "SELECT t.*, u.username, u.avatar, u.reputation, u.role, f.name as forum_name
                  FROM topics t 
                  JOIN users u ON t.user_id = u.id 
                  JOIN forums f ON t.forum_id = f.id 
                  WHERE t.id = ?";
        $stmt = $this -> conn -> prepare($query);
        $stmt -> execute([$topic_id]);
        
        if($stmt -> rowCount() > 0) {
            $this -> incrementViews('topic', $topic_id);
            return $stmt -> fetch(PDO::FETCH_ASSOC);
        }
        return null;
    }
    
    public function getPosts($topic_id, $limit = 10, $offset = 0) {
        $query = "SELECT p.*, u.username, u.avatar, u.reputation, u.role 
                  FROM posts p 
                  JOIN users u ON p.user_id = u.id 
                  WHERE topic_id = ? 
                  ORDER BY created_at ASC 
                  LIMIT ? OFFSET ?";
        $stmt = $this -> conn -> prepare($query);
        $stmt -> bindValue(1, $topic_id, PDO::PARAM_INT);
        $stmt -> bindValue(2, $limit, PDO::PARAM_INT);
        $stmt -> bindValue(3, $offset, PDO::PARAM_INT);
        $stmt -> execute();
    
        return $stmt -> fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getPostCount($topic_id) {
        $query = "SELECT COUNT(*) as count FROM posts WHERE topic_id = ?";
        $stmt = $this -> conn -> prepare($query);
        $stmt -> execute([$topic_id]);
        return $stmt -> fetch(PDO::FETCH_ASSOC)['count'];
    }

    public function createTopic($forum_id, $user_id, $title, $content) {
        $title = trim($title);
        $content = trim($content);

        if (empty($title)) {
            throw new Exception('Topic title cannot be empty.');
        }

        if (strlen($title) > 255) {
            throw new Exception('Topic title cannot exceed 255 characters.');
        }

        if (empty($content)) {
            throw new Exception('Topic content cannot be empty.');
        }

        $query = "INSERT INTO topics (forum_id, user_id, title, content) VALUES (?, ?, ?, ?)";
        $stmt = $this -> conn -> prepare($query);
        if($stmt -> execute([$forum_id, $user_id, $title, $content])) {
            $topic_id = $this -> conn -> lastInsertId();
            $this -> updateForumStats($forum_id);
            $this -> createNotification($user_id, 'topic_created', 'Topic Created', "Your topic '$title' has been posted.", "topic.php?id=$topic_id");
            return $topic_id;
        }
        return false;
    }

    public function updateTopic($topic_id, $title, $content) {
        $title = trim($title);
        $content = trim($content);

        if (empty($title)) {
            throw new Exception('Topic title cannot be empty.');
        }
        if (strlen($title) > 255) {
            throw new Exception('Topic title cannot exceed 255 characters.');
        }
        if (empty($content)) {
            throw new Exception('Topic content cannot be empty.');
        }

        $query = "UPDATE topics SET title = ?, content = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$title, $content, $topic_id]);
    }

    public function deleteTopic($topic_id) {
        // We can implement a soft delete later if needed by adding an 'is_deleted' flag.
        // For now, we will perform a hard delete.
        // The database is set up with ON DELETE CASCADE, so posts, votes, etc. will be deleted.
        
        $query = "DELETE FROM topics WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        if ($stmt->execute([$topic_id])) {
            return true;
        }
        return false;
    }
    
    public function createPost($topic_id, $user_id, $content) {
        $content = trim($content);
        if (empty($content)) {
            throw new Exception('Post content cannot be empty.');
        }

        $query = "INSERT INTO posts (topic_id, user_id, content) VALUES (?, ?, ?)";
        $stmt = $this -> conn -> prepare($query);
        if($stmt -> execute([$topic_id, $user_id, $content])) {
            $this -> updateTopicStats($topic_id);
            $this -> notifyTopicParticipants($topic_id, $user_id, 'New reply to your topic');
            return $this -> conn -> lastInsertId();
        }
        return false;
    }

    public function getPost($post_id) {
        $query = "SELECT p.*, u.username FROM posts p JOIN users u ON p.user_id = u.id WHERE p.id = ?";
        $stmt = $this -> conn -> prepare($query);
        $stmt -> execute([$post_id]);
        return $stmt -> fetch(PDO::FETCH_ASSOC);
    }

    public function updatePost($post_id, $content) {
        $content = trim($content);
        if (empty($content)) {
            throw new Exception('Post content cannot be empty.');
        }
        $query = "UPDATE posts SET content = ? WHERE id = ?";
        $stmt = $this -> conn -> prepare($query);
        return $stmt -> execute([$content, $post_id]);
    }

    public function deletePost($post_id) {
        $query = "DELETE FROM posts WHERE id =?";
        $stmt = $this -> conn -> prepare($query);
        return $stmt -> execute([$post_id]);
    }
    
    public function vote($user_id, $target_type, $target_id, $vote_type) {
        $check_query = "SELECT vote_type FROM votes WHERE user_id = ? AND target_type = ? AND target_id = ?";
        $check_stmt = $this -> conn -> prepare($check_query);
        $check_stmt -> execute([$user_id, $target_type, $target_id]);
        
        if($check_stmt -> rowCount() > 0) {
            $existing_vote = $check_stmt -> fetch(PDO::FETCH_ASSOC);
            if($existing_vote['vote_type'] == $vote_type) {
                $delete_query = "DELETE FROM votes WHERE user_id = ? AND target_type = ? AND target_id = ?";
                $delete_stmt = $this -> conn -> prepare($delete_query);
                $delete_stmt -> execute([$user_id, $target_type, $target_id]);
            } else {
                $update_query = "UPDATE votes SET vote_type = ? WHERE user_id = ? AND target_type = ? AND target_id = ?";
                $update_stmt = $this -> conn -> prepare($update_query);
                $update_stmt -> execute([$vote_type, $user_id, $target_type, $target_id]);
            }
        } else {
            $insert_query = "INSERT INTO votes (user_id, target_type, target_id, vote_type) VALUES (?, ?, ?, ?)";
            $insert_stmt = $this -> conn -> prepare($insert_query);
            $insert_stmt -> execute([$user_id, $target_type, $target_id, $vote_type]);
        }
        
        $this -> updateVoteCounts($target_type, $target_id);
        $this -> updateUserReputation($target_type, $target_id, $vote_type);
        return true;
    }
    
    // Search Function
    public function search($query, $limit = 20) {
        $search_query = "SELECT 'topic' as type, t.id, t.title as title, t.content, t.created_at, u.username, f.name as forum_name
                        FROM topics t 
                        JOIN users u ON t.user_id = u.id 
                        JOIN forums f ON t.forum_id = f.id 
                        WHERE t.title LIKE ? OR t.content LIKE ?
                        UNION ALL
                        SELECT 'post' as type, p.id, t.title, p.content, p.created_at, u.username, f.name as forum_name
                        FROM posts p 
                        JOIN topics t ON p.topic_id = t.id 
                        JOIN users u ON p.user_id = u.id 
                        JOIN forums f ON t.forum_id = f.id 
                        WHERE p.content LIKE ?
                        ORDER BY created_at DESC 
                        LIMIT ?";
        $search_term = "%$query%";
        $stmt = $this -> conn -> prepare($search_query);
        $stmt -> bindValue(1, $search_term, PDO::PARAM_STR);
        $stmt -> bindValue(2, $search_term, PDO::PARAM_STR);
        $stmt -> bindValue(3, $search_term, PDO::PARAM_STR);
        $stmt -> bindValue(4, $limit, PDO::PARAM_INT);
        $stmt -> execute();

        return $stmt -> fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getNotifications($user_id, $limit = 10) {
        $query = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?";
        $stmt = $this -> conn -> prepare($query);
        $stmt -> bindValue(1, $user_id, PDO::PARAM_INT);
        $stmt -> bindValue(2, $limit, PDO::PARAM_INT);
        $stmt -> execute();

        return $stmt -> fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function markNotificationRead($notification_id, $user_id) {
        $query = "UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?";
        $stmt = $this -> conn -> prepare($query);
        return $stmt -> execute([$notification_id, $user_id]);
    }
    
    private function incrementViews($type, $id) {
        $table = $type == 'topic' ? 'topics' : 'posts';
        $query = "UPDATE $table SET views = views + 1 WHERE id = ?";
        $stmt = $this -> conn -> prepare($query);
        $stmt -> execute([$id]);
    }
    
    private function updateVoteCounts($target_type, $target_id) {
        $up_query = "SELECT COUNT(*) as count FROM votes WHERE target_type = ? AND target_id = ? AND vote_type = 'up'";
        $down_query = "SELECT COUNT(*) as count FROM votes WHERE target_type = ? AND target_id = ? AND vote_type = 'down'";
        
        $up_stmt = $this ->  conn -> prepare($up_query);
        $up_stmt -> execute([$target_type, $target_id]);
        $up_count = $up_stmt -> fetch(PDO::FETCH_ASSOC)['count'];
        
        $down_stmt = $this -> conn -> prepare($down_query);
        $down_stmt -> execute([$target_type, $target_id]);
        $down_count = $down_stmt -> fetch(PDO::FETCH_ASSOC)['count'];
        
        $table = $target_type == 'topic' ? 'topics' : 'posts';
        $update_query = "UPDATE $table SET votes_up = ?, votes_down = ? WHERE id = ?";
        $update_stmt = $this -> conn -> prepare($update_query);
        $update_stmt -> execute([$up_count, $down_count, $target_id]);
    }
    
    private function updateUserReputation($target_type, $target_id, $vote_type) {
        $table = $target_type == 'topic' ? 'topics' : 'posts';
        $user_query = "SELECT user_id FROM $table WHERE id = ?";
        $stmt = $this -> conn -> prepare($user_query);
        $stmt -> execute([$target_id]);
        $user_id = $stmt -> fetch(PDO::FETCH_ASSOC)['user_id'];
        
        $reputation_change = $vote_type == 'up' ? 1 : -1;
        $update_query = "UPDATE users SET reputation = reputation + ? WHERE id = ?";
        $stmt = $this -> conn -> prepare($update_query);
        $stmt -> execute([$reputation_change, $user_id]);
    }
    
    private function updateForumStats($forum_id) {
        $query = "UPDATE forums SET 
                  topics_count = (SELECT COUNT(*) FROM topics WHERE forum_id = ?),
                  posts_count = (SELECT COUNT(*) FROM posts p JOIN topics t ON p.topic_id = t.id WHERE t.forum_id = ?)
                  WHERE id = ?";
        $stmt = $this -> conn -> prepare($query);
        $stmt -> execute([$forum_id, $forum_id, $forum_id]);
    }
    
    private function updateTopicStats($topic_id) {
        $query = "UPDATE topics SET 
                  replies_count = (SELECT COUNT(*) FROM posts WHERE topic_id = ?),
                  updated_at = NOW()
                  WHERE id = ?";
        $stmt = $this -> conn -> prepare($query);
        $stmt -> execute([$topic_id, $topic_id]);
    }
    
    private function createNotification($user_id, $type, $title, $content, $url = null) {
        $query = "INSERT INTO notifications (user_id, type, title, content, url) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this -> conn -> prepare($query);
        $stmt -> execute([$user_id, $type, $title, $content, $url]);
    }
    
    private function notifyTopicParticipants($topic_id, $sender_id, $message) {
        $query = "SELECT DISTINCT user_id FROM posts WHERE topic_id = ? AND user_id != ?
                  UNION
                  SELECT user_id FROM topics WHERE id = ? AND user_id != ?";
        $stmt = $this -> conn -> prepare($query);
        $stmt -> execute([$topic_id, $sender_id, $topic_id, $sender_id]);
        $participants = $stmt -> fetchAll(PDO::FETCH_ASSOC);
        
        foreach($participants as $participant) {
            $this -> createNotification($participant['user_id'], 'reply', 'New Reply', $message, "topic.php?id=$topic_id");
        }
    }
}
?>