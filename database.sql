CREATE DATABASE IF NOT EXISTS soulbalance_blog;
USE soulbalance_blog;

CREATE TABLE IF NOT EXISTS user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- BLOG POST TABLE (ENHANCED)
-- ============================================
CREATE TABLE IF NOT EXISTS blogPost (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content LONGTEXT NOT NULL,
    category VARCHAR(50) DEFAULT NULL,
    featured_image VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at),
    INDEX idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Contact Messages Table Schema
-- ============================================

CREATE TABLE IF NOT EXISTS contact_messages (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    subject VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('new', 'read', 'replied', 'archived') DEFAULT 'new',
    ip_address VARCHAR(45) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    replied_at DATETIME DEFAULT NULL,
    replied_by INT(11) DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    INDEX idx_subject (subject)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- BLOG RATINGS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS blog_ratings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    blog_post_id INT NOT NULL,
    user_id INT NOT NULL,
    rating TINYINT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (blog_post_id) REFERENCES blogPost(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_rating (blog_post_id, user_id),
    INDEX idx_blog_post (blog_post_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- BLOG PUBLIC RATINGS TABLE (for non-logged-in users)
-- ============================================
CREATE TABLE IF NOT EXISTS blog_ratings_public (
    id INT AUTO_INCREMENT PRIMARY KEY,
    blog_post_id INT NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    rating TINYINT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (blog_post_id) REFERENCES blogPost(id) ON DELETE CASCADE,
    UNIQUE KEY unique_ip_rating (blog_post_id, ip_address),
    INDEX idx_blog_post (blog_post_id),
    INDEX idx_ip_address (ip_address)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- ============================================
-- BLOG COMMENTS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS blog_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    blog_post_id INT NOT NULL,
    user_id INT NOT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (blog_post_id) REFERENCES blogPost(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
    INDEX idx_blog_post (blog_post_id),
    INDEX idx_user (user_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Sample Query to View Recent Messages
-- ============================================

-- View all new messages
SELECT 
    id,
    CONCAT(first_name, ' ', last_name) as full_name,
    email,
    phone,
    subject,
    LEFT(message, 100) as message_preview,
    created_at
FROM contact_messages 
WHERE status = 'new'
ORDER BY created_at DESC;

-- View all messages from a specific email
SELECT * FROM contact_messages 
WHERE email = 'example@email.com'
ORDER BY created_at DESC;

-- Count messages by status
SELECT 
    status,
    COUNT(*) as count
FROM contact_messages
GROUP BY status;

-- ============================================
-- Admin Functions (Optional)
-- ============================================

-- Mark message as read
-- UPDATE contact_messages SET status = 'read', updated_at = NOW() WHERE id = ?;

-- Mark message as replied
-- UPDATE contact_messages SET status = 'replied', replied_at = NOW(), replied_by = ? WHERE id = ?;

-- Add admin notes to a message
-- UPDATE contact_messages SET notes = ? WHERE id = ?;

-- Archive old messages (older than 6 months)
-- UPDATE contact_messages SET status = 'archived' WHERE created_at < DATE_SUB(NOW(), INTERVAL 6 MONTH) AND status != 'new';

-- ============================================
-- Performance Indexes
-- ============================================

-- Additional indexes for better query performance
-- CREATE INDEX idx_full_name ON contact_messages(first_name, last_name);
-- CREATE INDEX idx_created_status ON contact_messages(created_at, status);

-- ============================================
-- SAMPLE DATA (for testing)
-- Password for all test accounts: password123
-- ============================================




-- ============================================
-- CREATE UPLOADS DIRECTORY STRUCTURE
-- Note: These directories must be created manually
-- ============================================

-- uploads/
-- └── blogs/
--     └── .gitkeep

-- Make sure to set proper permissions:
-- chmod 755 uploads/blogs/

-- ============================================
-- VERIFICATION QUERIES
-- ============================================

-- Check if tables were created
-- SHOW TABLES;

-- View sample data
-- SELECT u.username, COUNT(bp.id) as post_count 
-- FROM user u 
-- LEFT JOIN blogPost bp ON u.id = bp.user_id 
-- GROUP BY u.id;

-- View all blog posts
-- SELECT bp.title, u.username, bp.category, bp.created_at 
-- FROM blogPost bp 
-- JOIN user u ON bp.user_id = u.id 
-- ORDER BY bp.created_at DESC;