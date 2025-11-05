<?php
    require_once __DIR__ . '/../config/database.php';

    class Forum {
        private $conn;
    
        public function __construct() {
            $database = new Database();
            $this -> conn = $database -> getConnection();
        }

        public function prepareQuery($query) {
            return $this -> conn -> prepare($query);
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
    
            $posts = $stmt -> fetchAll(PDO::FETCH_ASSOC);

            // Fetch attachments for each post
            foreach ($posts as $key => $post) {
                $attachment_query = "SELECT * FROM attachments WHERE post_id = ?";
                $attachment_stmt = $this -> conn -> prepare($attachment_query);
                $attachment_stmt -> execute([$post['id']]);
                $posts[$key]['attachments'] = $attachment_stmt -> fetchAll(PDO::FETCH_ASSOC);
            }
            return $posts;
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

            $this -> conn -> beginTransaction();

            try {
                // Create  topic
                $query = "INSERT INTO topics (forum_id, user_id, title, content) VALUES (?, ?, ?, ?)";
                $stmt = $this -> conn -> prepare($query);
                $stmt -> execute([$forum_id, $user_id, $title, $content]);
                $topic_id = $this -> conn -> lastInsertId();

                // Handle attachments directly for the topic
                if (isset($_FILES['attachments'])) {
                    $this -> handleTopicAttachments($topic_id, $user_id, $_FILES['attachments']);
                }

                $this -> updateForumStats($forum_id);
                $this -> createNotification($user_id, 'topic_created', 'Topic Created', "Your topic '$title' has been posted.", "topic.php?id=$topic_id");

                $this -> conn -> commit();
                return $topic_id;

            } catch (Exception $e) {
                $this -> conn -> rollBack();
                throw $e; // Re-throw the exception
            }
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
            $stmt = $this -> conn -> prepare($query);
            return $stmt -> execute([$title, $content, $topic_id]);
        }

        public function deleteTopic($topic_id) {
            $topic_query = "SELECT forum_id FROM topics WHERE id = ?";
            $stmt = $this -> conn -> prepare($topic_query);
            $stmt -> execute([$topic_id]);
            $topic = $stmt -> fetch(PDO::FETCH_ASSOC);

            if (!$topic) {
                return false; // Topic doesn't exist
            }
            $forum_id = $topic['forum_id'];

            // The database is set up with ON DELETE CASCADE(posts, votes, notifications) will be deleted automatically.
            $query = "DELETE FROM topics WHERE id = ?";
            $stmt = $this -> conn -> prepare($query);
        
            if ($stmt -> execute([$topic_id])) {
                // Update the stats for the parent forum
                $this -> updateForumStats($forum_id);
                return true; 
            }
            return false;
        }
    
        public function createPost($topic_id, $user_id, $content) {
            $content = trim($content);
            if (empty($content)) {
                throw new Exception('Post content cannot be empty.');
            }

            $this -> conn -> beginTransaction();

            try {
                $query = "INSERT INTO posts (topic_id, user_id, content) VALUES (?, ?, ?)";
                $stmt = $this -> conn -> prepare($query);
                $stmt -> execute([$topic_id, $user_id, $content]);
                $post_id = $this -> conn ->lastInsertId();

                // Handle file uploads
                if (isset($_FILES['attachments'])) {
                    $this -> handleAttachments($post_id, $user_id, $_FILES['attachments']);
                }

                $this -> updateTopicStats($topic_id);
                $this -> notifyTopicParticipants($topic_id, $user_id, 'New reply to your topic');

                $this -> conn -> commit();
                return $post_id;
            } catch (Exception $e) {
                $this -> conn -> rollBack();
                // Re-throw the exception to be caught by the calling script
                throw new Exception("Failed to create post: " . $e -> getMessage());
            }
            return false;
        }

        public function getPost($post_id) {
            $query = "SELECT p.*, u.username FROM posts p JOIN users u ON p.user_id = u.id WHERE p.id = ?";
            $stmt = $this -> conn -> prepare($query);
            $stmt -> execute([$post_id]);
            return $stmt -> fetch(PDO::FETCH_ASSOC);
        }

        public function getAttachments($post_id, $topic_id = null) {
            $query = "SELECT * FROM attachments WHERE post_id = ? OR topic_id = ?";
            $stmt = $this -> conn -> prepare($query);
            $stmt -> execute([$post_id, $topic_id]);
            return $stmt -> fetchAll(PDO::FETCH_ASSOC);
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
            $post_query = "SELECT topic_id FROM posts WHERE id = ?";
            $stmt = $this -> conn -> prepare($post_query);
            $stmt -> execute([$post_id]);
            $post = $stmt -> fetch(PDO::FETCH_ASSOC);

            if(!$post) {
                return false; // Post doesn't exist
            }

            $query = "DELETE FROM posts WHERE id =?";
            $stmt = $this -> conn -> prepare($query);
            if ($stmt -> execute([$post_id])) {
                $this -> updateTopicStats($post['topic_id']);
                return true;
            }
            return false;
        }

        public function handleAttachments($post_id, $user_id, $files) {
            $target_dir = __DIR__ . "/../uploads/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            foreach ($files['name'] as $key => $name) {
                if ($files['error'][$key] === UPLOAD_ERR_OK) {
                    $file_name = htmlspecialchars(basename($name));
                    $file_tmp = $files['tmp_name'][$key];
                    $file_size = $files['size'][$key];
                    $file_type = $files['type'][$key];

                    // Basic security: check file size (e.g., max 5MB) 
                    if ($file_size > 5000000) {
                        throw new Exception("File '$file_name' is too large.");
                    }

                    // Create a unique file path
                    $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'docx', 'txt', 'zip', 'rar'];

                    if (!in_array($file_extension, $allowed_extensions)) {
                        throw new Exception("File type for '$file_name' is not allowed.");
                    }

                    // Sanitize filename and create a unique name
                    $safe_filename = preg_replace('/[^A-Za-z0-9.\-_]/', '', pathinfo($file_name, PATHINFO_FILENAME));
                    $unique_filename = $safe_filename . '_' . uniqid() . '.' . $file_extension;
                    $target_path = $target_dir . $unique_filename;

                    if (move_uploaded_file($file_tmp, $target_path)) {
                        // Save attachment info to the database
                        $query = "INSERT INTO attachments (post_id, user_id, file_name, file_path, file_type, file_size) VALUES (?, ?, ?, ?, ?, ?)";
                        $stmt = $this -> conn -> prepare($query);
                        $stmt -> execute([
                            $post_id,
                            $user_id,
                            $file_name, // Original filename for display
                            'uploads/' . $unique_filename, // Stored filename
                            $file_type,
                            $file_size
                        ]);
                    } else {
                        throw new Exception("Failed to upload file '$file_name'.");
                    }
                }
            }
        }

        public function getTopicAttachments($topic_id) {
            $query = "SELECT * FROM attachments WHERE topic_id = ? ORDER BY uploaded_at DESC";
            $stmt = $this -> conn -> prepare($query);
            $stmt -> execute([$topic_id]);
            return $stmt -> fetchAll(PDO::FETCH_ASSOC);
        }

        public function deleteAttachment($attachment_id, $user_id) {
            // Get attachment info first
            $query = "SELECT * FROM attachments WHERE id = ? AND user_id = ?";
            $stmt = $this -> conn -> prepare($query);
            $stmt -> execute([$attachment_id, $user_id]);
            $attachment = $stmt -> fetch(PDO::FETCH_ASSOC);

            if(!$attachment) {
                return false;
            }

            // Delete file from database
            $query = "DELETE FROM attachments WHERE id = ? AND user_id = ?";
            $stmt = $this -> conn -> prepare($query);
            return $stmt -> execute([$attachment_id, $user_id]);
        }

        public function updateTopicWithAttachments($topic_id, $title, $content, $user_id) {
            $this -> conn -> beginTransaction();

            try {
                // Update topic
                $this -> updateTopic($topic_id, $title, $content);

                // Handle attachments
                if(isset($_FILES['attachments']) && !empty($_FILES['attachments']['name'][0])) {
                    $this -> handleTopicAttachments($topic_id, $user_id, $_FILES['attachments']);
                }

                $this -> conn -> commit();
                return true; 
            } catch(Exception $e) {
                $this -> conn -> rollBack();
                throw $e;
            }
        }

        public function handleTopicAttachments($topic_id, $user_id, $files) {
            $target_dir = __DIR__ . "/../uploads/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            foreach($files['name'] as $key => $name) {
                if ($files['error'][$key] === UPLOAD_ERR_OK) {
                    $file_name = htmlspecialchars(basename($name));
                    $file_tmp = $files['tmp_name'][$key];
                    $file_size = $files['size'][$key];
                    $file_type = $files['type'][$key];

                    if($file_size > 5000000) {
                        throw new Exception("File '$file_name' is too large.");
                    }

                    $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'docx', 'txt', 'zip', 'rar'];

                    if(!in_array($file_extension, $allowed_extensions)) {
                        throw new Exception("File type for '$file_name' is not allowed.");
                    }

                    $safe_filename = preg_replace('/[^A-Za-z0-9.\-_]/', '', pathinfo($file_name, PATHINFO_FILENAME));
                    $unique_filename = $safe_filename . '_' . uniqid() . '.' . $file_extension;
                    $target_path = $target_dir . $unique_filename;

                    if (move_uploaded_file($file_tmp, $target_path)) {
                        $query = "INSERT INTO attachments (topic_id, user_id, file_name, file_path, file_type, file_size) VALUES (?, ?, ?, ?, ?, ?)";
                        $stmt = $this -> conn -> prepare($query);
                        $stmt -> execute([
                        $topic_id,
                        $user_id,
                        $file_name,
                        'uploads/' . $unique_filename,
                        $file_type,
                        $file_size
                    ]);
                    } else {
                        throw new Exception("Failed to upload file '$file_name'.");
                    }
                }
            }
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



    
        private function handleAttachmentUpload($attachment, $user_id, $post_id = null, $topic_id = null) {
            $upload_dir = __DIR__ . '/../uploads/';
            $max_file_size = 5 * 1024 * 1024; // 5MB
            $allowed_types = ['image/jpeg', 'image/png', 'application/pdf', 'text/plain', 'application/zip'];

            $file_name = $attachment['name'];
            $file_size = $attachment['size'];
            $file_tmp = $attachment['tmp_name'];
            $file_type = $attachment['type'];

            if ($file_size > $max_file_size) {
                throw new Exception("File size exceeds the maximum limit of 5MB.");
            }

            if (!in_array($file_type, $allowed_types)) {
                throw new Exception("File type is not allowed.");
            }

            // Create a unique file name to prevent overwriting
            $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
            $unique_file_name = uniqid('', true) . '.' . $file_extension;
            $file_path = $upload_dir . $unique_file_name;

            if (move_uploaded_file($file_tmp, $file_path)) {
                $query = "INSERT INTO attachments (user_id, post_id, topic_id, file_name, file_path, file_size, file_type) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $this -> conn -> prepare($query);
                $stmt -> execute([$user_id, $post_id, $topic_id, $file_name, 'uploads/' . $unique_file_name, $file_size, $file_type]);
            } else {
                throw new Exception("Failed to move uploaded file.");
            }
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
        
            $up_stmt = $this -> conn -> prepare($up_query);
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