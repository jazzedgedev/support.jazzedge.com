-- Update wp_alm_transcripts table to support multiple transcripts per lesson (one per chapter)
-- This allows each chapter to have its own transcript
-- 
-- IMPORTANT: Replace 'wp_' with your actual WordPress table prefix if different
-- Run this in phpMyAdmin, Adminer, or your database management tool

-- Step 1: Check if chapter_id column exists, add if it doesn't
-- (Run this first to check: SHOW COLUMNS FROM wp_alm_transcripts LIKE 'chapter_id';)
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'wp_alm_transcripts' 
    AND COLUMN_NAME = 'chapter_id'
);

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE wp_alm_transcripts ADD COLUMN chapter_id int(11) DEFAULT 0 AFTER lesson_id, ADD KEY chapter_id (chapter_id)',
    'SELECT "chapter_id column already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Step 2: Drop the old unique constraint (lesson_id, source) if it exists
SET @index_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.STATISTICS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'wp_alm_transcripts' 
    AND INDEX_NAME = 'uniq_lesson_source'
);

SET @sql = IF(@index_exists > 0,
    'ALTER TABLE wp_alm_transcripts DROP INDEX uniq_lesson_source',
    'SELECT "uniq_lesson_source index does not exist" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Step 3: Add new unique constraint (lesson_id, chapter_id, source)
-- This allows multiple chapters per lesson, each with their own transcript
SET @new_index_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.STATISTICS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'wp_alm_transcripts' 
    AND INDEX_NAME = 'uniq_lesson_chapter_source'
);

SET @sql = IF(@new_index_exists = 0,
    'ALTER TABLE wp_alm_transcripts ADD UNIQUE KEY uniq_lesson_chapter_source (lesson_id, chapter_id, source)',
    'SELECT "uniq_lesson_chapter_source index already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verify the changes
-- Run this to check the table structure:
-- SHOW CREATE TABLE wp_alm_transcripts;
-- 
-- You should see:
-- UNIQUE KEY `uniq_lesson_chapter_source` (`lesson_id`,`chapter_id`,`source`)

