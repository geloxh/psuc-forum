CREATE DATABASE IF NOT EXISTS psuc_forum;
USE psuc_forum;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    university VARCHAR(100) NOT NULL,
    role ENUM('admin', 'moderator', 'faculty', 'college student', 'other') DEFAULT 'other',
    avatar VARCHAR(255) DEFAULT 'default.png',
    reputation INT DEFAULT 0,
    status ENUM('active', 'banned', 'pending') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_active TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    icon VARCHAR(50) DEFAULT 'fas fa-folder',
    color VARCHAR(7) DEFAULT '#007bff',
    position INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE forums (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    position INT DEFAULT 0,
    topics_count INT DEFAULT 0,
    posts_count INT DEFAULT 0,
    last_post_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

CREATE TABLE topics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    forum_id INT,
    user_id INT,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    is_pinned BOOLEAN DEFAULT FALSE,
    is_locked BOOLEAN DEFAULT FALSE,
    views INT DEFAULT 0,
    replies_count INT DEFAULT 0,
    votes_up INT DEFAULT 0,
    votes_down INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (forum_id) REFERENCES forums(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    topic_id INT,
    user_id INT,
    content TEXT NOT NULL,
    votes_up INT DEFAULT 0,
    votes_down INT DEFAULT 0,
    is_solution BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (topic_id) REFERENCES topics(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE votes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    target_type ENUM('topic', 'post'),
    target_id INT,
    vote_type ENUM('up', 'down'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_vote (user_id, target_type, target_id)
);

CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT,
    receiver_id INT,
    subject VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT,
    url VARCHAR(255),
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE user_groups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    color VARCHAR(7) DEFAULT '#6c757d',
    permissions JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE user_group_members (
    user_id INT,
    group_id INT,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, group_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (group_id) REFERENCES user_groups(id) ON DELETE CASCADE
);

CREATE TABLE social_logins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    provider ENUM('facebook', 'google', 'twitter'),
    provider_id VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_social (provider, provider_id)
);

-- Insert default categories
INSERT INTO categories (name, description, icon, color) VALUES
('General Discussion', 'General topics for all PSUC members', 'fas fa-comments', '#007bff'),
('Academic', 'Academic discussions and resources', 'fas fa-graduation-cap', '#28a745'),
('Research', 'Research collaboration and sharing', 'fas fa-microscope', '#dc3545'),
('Events & Announcements', 'University events and official announcements', 'fas fa-calendar', '#ffc107'),
('Student Life', 'Campus life, activities, and student concerns', 'fas fa-users', '#17a2b8');

-- Insert default forums
INSERT INTO forums (category_id, name, description) VALUES
(1, 'Welcome & Introductions', 'Introduce yourself to the PSUC community'),
(1, 'General Chat', 'General discussions about anything'),
(1, 'Help & Support', 'Get help with forum usage and technical issues'),
(2, 'Course Discussions', 'Discuss courses, curriculum, and academic topics'),
(2, 'Study Groups', 'Form and join study groups'),
(2, 'Academic Resources', 'Share textbooks, notes, and study materials'),
(3, 'Research Projects', 'Share and collaborate on research projects'),
(3, 'Publications & Papers', 'Share published papers and research articles'),
(3, 'Research Opportunities', 'Post and find research opportunities'),
(4, 'University Events', 'Upcoming events and activities'),
(4, 'Official Announcements', 'Important announcements from administration'),
(4, 'News & Updates', 'Latest news and updates from PSUC institutions'),
(5, 'Campus Life', 'Discuss campus life and experiences'),
(5, 'Organizations & Clubs', 'Student organizations and club activities'),
(5, 'Career & Opportunities', 'Job opportunities, internships, and career advice');

-- Insert default user groups
INSERT INTO user_groups (name, description, color, permissions) VALUES
('Administrators', 'Full system access and management', '#dc3545', '{"manage_users": true, "manage_forums": true, "moderate_content": true, "system_settings": true}'),
('Moderators', 'Content moderation and topic management', '#ffc107', '{"moderate_content": true, "manage_topics": true, "pin_topics": true}'),
('Faculty Members', 'Teaching staff and faculty', '#28a745', '{"create_announcements": true, "pin_topics": true, "moderate_discussions": true}'),
('College Student', 'College student members', '#007bff', '{"create_topics": true, "reply_posts": true, "vote_content": true, "send_messages": true}'),
('Other', 'Other members', '#6c757d', '{"create_topics": true, "reply_posts": true, "vote_content": true, "send_messages": true}');