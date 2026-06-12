-- ========== Love Site Database Setup ==========
-- Copy and paste this entire script into phpMyAdmin SQL tab and run it

-- Create database
CREATE DATABASE IF NOT EXISTS lovesite_db;
USE lovesite_db;

-- Create dates table
CREATE TABLE IF NOT EXISTS dates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date_value DATE NOT NULL,
    time_value TIME NULL,
    label VARCHAR(255) NULL,
    note TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create letters table
CREATE TABLE IF NOT EXISTS letters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample letters
INSERT INTO letters (title, content) VALUES
(
    'The Day I Knew',
    'I remember exactly when it happened. We were just sitting there, doing nothing special, and you laughed at something silly on your phone. And in that moment, with your hair catching the light and that genuine smile on your face, I realized I didn''t want to spend a single day without you. It wasn''t some grand, dramatic moment—it was quiet and simple and absolutely certain. You weren''t trying to convince me or prove anything. You were just being you, and somehow that was everything. I love you more every single day since.'
),
(
    'For a Hard Day',
    'Hey, I know today has been rough. I know you''re tired and frustrated and maybe you''re wondering if things will get better. I want you to know that you''re stronger than this moment feels right now. I see how hard you try, how much you care, how you keep going even when it would be so much easier to quit. That resilience, that heart—that''s why I love you. These hard days don''t define you. You will get through this, and I''ll be right here beside you, whenever you need me. You''re not alone in this. You''ve got me, always.'
),
(
    'Just Because',
    'There''s no special occasion today, no reason I *have* to tell you this except that it''s true and I wanted you to know. I love the way you hum when you''re cooking. I love how you care about people even when they don''t deserve it. I love your weird sense of humor and the random thoughts you share at midnight. I love your hands, your voice, the way you scrunch your nose when you''re concentrating. I love that you''re here, that you chose me, that we get to do this life together. So I''m telling you now: you make me happy. Not in a perfect, fairy-tale way, but in a real, messy, everyday kind of way that means everything. I hope this makes you smile today. I love you.'
);
