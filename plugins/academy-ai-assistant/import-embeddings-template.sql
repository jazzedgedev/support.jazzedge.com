-- Template for importing transcript embeddings
-- Replace 'wp_' with your actual WordPress table prefix if different
-- This is a template - you'll need to add your actual data

SET NAMES utf8mb4;

-- Make sure the table exists first (run create-embeddings-table.sql if needed)

-- Example import format:
-- INSERT INTO `wp_alm_transcript_embeddings` 
-- (`transcript_id`, `segment_index`, `embedding`, `segment_text`, `start_time`, `end_time`, `created_at`) 
-- VALUES
-- (1, 0, '[0.056844150000000003,0.031415217000000002,...]', 'This is the end of the video.', 0, 3, '2025-12-07 08:29:02'),
-- (1, 1, '[0.012345,0.023456,...]', 'Thank you for watching.', 3, 6, '2025-12-07 08:29:02'),
-- ... (add more rows as needed)

-- Notes:
-- - transcript_id: Links to wp_alm_transcripts.ID
-- - segment_index: Index of segment within the transcript (0, 1, 2, ...)
-- - embedding: JSON array of 1536 floats (must be valid JSON)
-- - segment_text: The actual text of the segment
-- - start_time: Start time in seconds (float)
-- - end_time: End time in seconds (float)
-- - created_at: Timestamp when embedding was created

-- To import your data:
-- 1. Make sure the table structure is correct (run create-embeddings-table.sql)
-- 2. Format your data as INSERT statements following the pattern above
-- 3. Run the SQL in phpMyAdmin or your database tool

