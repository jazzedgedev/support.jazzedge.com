-- SIMPLE VERSION - Update wp_alm_transcripts table to support multiple transcripts per lesson
-- Replace 'wp_' with your actual WordPress table prefix if different
-- 
-- This version is simpler but may show errors if indexes/columns already exist
-- If you get errors, use the update-transcripts-table.sql file instead

-- Step 1: Ensure chapter_id column exists (may already exist)
ALTER TABLE wp_alm_transcripts 
ADD COLUMN chapter_id int(11) DEFAULT 0 AFTER lesson_id;

-- Step 2: Add index on chapter_id if it doesn't exist
ALTER TABLE wp_alm_transcripts 
ADD INDEX chapter_id (chapter_id);

-- Step 3: Drop old unique constraint (may show error if it doesn't exist - that's OK)
ALTER TABLE wp_alm_transcripts 
DROP INDEX uniq_lesson_source;

-- Step 4: Add new unique constraint (lesson_id, chapter_id, source)
-- This allows multiple chapters per lesson, each with their own transcript
ALTER TABLE wp_alm_transcripts 
ADD UNIQUE KEY uniq_lesson_chapter_source (lesson_id, chapter_id, source);

-- Verify: Run this to check the structure:
-- SHOW CREATE TABLE wp_alm_transcripts;
-- 
-- You should see:
-- UNIQUE KEY `uniq_lesson_chapter_source` (`lesson_id`,`chapter_id`,`source`)

