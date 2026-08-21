-- Migration for Blog Redesign
USE blog_app;

-- Topics table
CREATE TABLE IF NOT EXISTS topic (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    slug VARCHAR(60) NOT NULL UNIQUE,
    icon VARCHAR(10) DEFAULT '📝',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add new columns to blogPost
ALTER TABLE blogPost 
    ADD COLUMN IF NOT EXISTS cover_image VARCHAR(500) DEFAULT NULL AFTER content,
    ADD COLUMN IF NOT EXISTS excerpt VARCHAR(500) DEFAULT NULL AFTER cover_image,
    ADD COLUMN IF NOT EXISTS topic_id INT DEFAULT NULL AFTER excerpt,
    ADD COLUMN IF NOT EXISTS views INT DEFAULT 0 AFTER topic_id,
    ADD COLUMN IF NOT EXISTS reading_time INT DEFAULT 1 AFTER views;

-- Likes table
CREATE TABLE IF NOT EXISTS post_like (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    post_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_like (user_id, post_id),
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES blogPost(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bookmarks table
CREATE TABLE IF NOT EXISTS bookmark (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    post_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_bookmark (user_id, post_id),
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES blogPost(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Newsletter table
CREATE TABLE IF NOT EXISTS newsletter (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add bio and avatar to user
ALTER TABLE user
    ADD COLUMN IF NOT EXISTS bio TEXT DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS avatar VARCHAR(500) DEFAULT NULL;

-- Seed default topics
INSERT IGNORE INTO topic (name, slug, icon) VALUES
('Lifestyle', 'lifestyle', '🌿'),
('Productivity', 'productivity', '⚡'),
('Mental Health', 'mental-health', '🧠'),
('Relationships', 'relationships', '💕'),
('Self Growth', 'self-growth', '🌱'),
('Dreams', 'dreams', '✨'),
('Favourites', 'favourites', '💖'),
('Random Thoughts', 'random-thoughts', '💭');
