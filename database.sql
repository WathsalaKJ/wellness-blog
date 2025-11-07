-- ============================================
-- SOULBALANCE WELLNESS BLOG - DATABASE SETUP
-- Enhanced with featured image support
-- ============================================

-- Create database
CREATE DATABASE IF NOT EXISTS soulbalance_blog;
USE soulbalance_blog;

-- ============================================
-- USER TABLE
-- ============================================
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

-- Insert test users
INSERT INTO user (username, email, password, role) VALUES 
('yogateacher', 'yoga@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('nutritionist', 'nutrition@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('wellness_coach', 'coach@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');

-- Insert sample blog posts
INSERT INTO blogPost (user_id, title, content, category) VALUES 
(1, 'The Benefits of Morning Yoga', 
'Starting your day with yoga can transform your life in remarkable ways. Research shows that a consistent morning yoga practice offers numerous physical and mental health benefits.

Physical Benefits:
• Improved flexibility and strength
• Better posture and balance
• Enhanced cardiovascular health
• Boosted immune system
• Increased energy throughout the day

Mental Benefits:
• Reduced stress and anxiety
• Enhanced mental clarity
• Improved focus and concentration
• Better emotional regulation
• Deeper sense of inner peace

How to Start:
1. Begin with just 10-15 minutes each morning
2. Choose simple poses like Cat-Cow, Downward Dog, and Child''s Pose
3. Focus on your breath - inhale deeply, exhale slowly
4. Be consistent - practice at the same time each day
5. Listen to your body and don''t push too hard

Remember, the goal isn''t perfection but progress. Each day you show up on your mat is a victory. Start tomorrow morning and feel the difference within a week!', 
'Yoga Practices'),

(2, '10 Superfoods for Optimal Health', 
'Nutrition is the foundation of wellness. These ten superfoods are packed with nutrients that can dramatically improve your health, energy, and longevity.

1. Leafy Greens (Spinach, Kale, Chard)
Rich in vitamins A, C, K, and minerals. Add to smoothies, salads, or sauté as a side dish.

2. Berries (Blueberries, Strawberries, Acai)
Loaded with antioxidants that fight inflammation and support brain health.

3. Nuts and Seeds (Almonds, Walnuts, Chia)
Excellent source of healthy fats, protein, and fiber. Perfect for snacking.

4. Fatty Fish (Salmon, Mackerel, Sardines)
High in omega-3 fatty acids for heart and brain health.

5. Whole Grains (Quinoa, Oats, Brown Rice)
Provide sustained energy and essential nutrients.

6. Legumes (Lentils, Chickpeas, Black Beans)
Plant-based protein powerhouse with fiber.

7. Avocados
Healthy fats for heart health and nutrient absorption.

8. Greek Yogurt
Probiotics for gut health and protein for muscle.

9. Green Tea
Antioxidants and gentle caffeine for focus.

10. Dark Chocolate (70%+ cacao)
Rich in antioxidants - enjoy in moderation!

Incorporate these foods into your daily diet for maximum benefits. Your body will thank you!', 
'Nutrition'),

(1, 'Meditation for Beginners: A Complete Guide', 
'Meditation is one of the most powerful tools for mental wellness. If you''re new to meditation, this guide will help you start your practice with confidence.

What is Meditation?
Meditation is the practice of training your mind to focus and redirect thoughts. It''s not about stopping thoughts but observing them without judgment.

Benefits of Daily Meditation:
• Reduced stress and anxiety
• Improved emotional health
• Enhanced self-awareness
• Lengthened attention span
• Better sleep quality
• Reduced age-related memory loss

How to Start:
1. Choose a Quiet Space - Find a peaceful spot where you won''t be disturbed
2. Start Small - Begin with just 5 minutes daily
3. Get Comfortable - Sit in a chair or cross-legged on a cushion
4. Focus on Your Breath - Notice the sensation of breathing
5. Acknowledge Thoughts - When your mind wanders, gently return to your breath
6. Be Consistent - Practice at the same time each day

Common Mistakes to Avoid:
• Expecting immediate results
• Trying to force your mind to be blank
• Giving up after a few days
• Comparing yourself to others

Simple Techniques for Beginners:
• Breath Awareness: Focus on the natural rhythm of your breathing
• Body Scan: Notice sensations throughout your body
• Loving-Kindness: Send positive thoughts to yourself and others
• Guided Meditation: Use apps or YouTube videos

Remember, meditation is a practice, not a destination. Be patient with yourself and celebrate small wins!', 
'Meditation'),

(3, 'Creating a Balanced Daily Wellness Routine', 
'A well-rounded wellness routine encompasses physical health, mental clarity, and emotional balance. Here''s how to create a sustainable daily practice.

Morning Routine (6:00 AM - 8:00 AM):
• Wake up at a consistent time
• Drink a glass of water with lemon
• 10-15 minutes of gentle yoga or stretching
• 5-10 minutes of meditation
• Healthy breakfast with protein and greens
• Set intentions for the day

Midday Practices (12:00 PM - 2:00 PM):
• Mindful lunch break away from screens
• Short walk outside for fresh air
• Breathing exercises for stress relief
• Stay hydrated throughout the day

Evening Routine (7:00 PM - 9:00 PM):
• Light dinner 3 hours before bed
• Gentle movement or yoga
• Journaling or gratitude practice
• Digital detox 1 hour before sleep
• Relaxing tea or warm bath
• 10-15 minutes of reading

Key Principles:
• Consistency over intensity
• Listen to your body
• Adjust as seasons change
• Include activities you enjoy
• Make it sustainable, not perfect

Weekly Enhancements:
• One long nature walk
• Meal prep session
• Deep cleaning or organizing
• Social connection with loved ones
• Try something new

Remember: Your wellness routine should energize you, not drain you. Start with one or two habits and build from there!', 
'Wellness'),

(2, 'The Power of Mindful Eating', 
'Mindful eating transforms your relationship with food. It''s not a diet - it''s a practice of awareness and gratitude.

What is Mindful Eating?
Paying full attention to the experience of eating - the colors, smells, textures, flavors, and how food makes you feel.

Benefits:
• Better digestion
• Natural portion control
• Reduced stress around food
• Greater satisfaction from meals
• Weight management
• Improved relationship with food

How to Practice:
1. Eliminate Distractions - Turn off TV, put away phone
2. Engage Your Senses - Notice colors, aromas, textures
3. Eat Slowly - Put your fork down between bites
4. Chew Thoroughly - Aim for 20-30 chews per bite
5. Notice Hunger Cues - Eat when hungry, stop when satisfied
6. Express Gratitude - Appreciate the food and those who prepared it

Mindful Eating Exercise:
Try the "Raisin Meditation":
• Take one raisin
• Observe it as if you''ve never seen one
• Notice the texture, color, and smell
• Place it in your mouth without chewing
• Notice the taste and texture
• Slowly chew and notice changes
• Swallow mindfully

Apply this awareness to every meal!

Tips for Busy Schedules:
• Start with one mindful meal per week
• Practice during snack time
• Do a 5-minute mindful eating exercise
• Set a reminder on your phone

Transform your eating habits by bringing consciousness to each bite!', 
'Mindfulness');

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