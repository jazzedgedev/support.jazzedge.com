-- Create the correct transcript embeddings table
-- This matches the structure expected by chapter-transcription plugin
-- Replace 'wp_' with your actual WordPress table prefix if different

SET NAMES utf8mb4;

-- Drop the incorrect table if it exists (wp_alm_lesson_embeddings)
DROP TABLE IF EXISTS `wp_alm_lesson_embeddings`;

-- Create the correct table structure
CREATE TABLE IF NOT EXISTS `wp_alm_transcript_embeddings` (
    `transcript_id` int(11) NOT NULL,
    `segment_index` int(11) NOT NULL,
    `embedding` longtext NOT NULL COMMENT 'JSON array of floats (1536 dimensions for text-embedding-3-small)',
    `segment_text` text,
    `start_time` float DEFAULT NULL COMMENT 'Start time in seconds',
    `end_time` float DEFAULT NULL COMMENT 'End time in seconds',
    `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`transcript_id`, `segment_index`),
    KEY `transcript_id` (`transcript_id`),
    KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Verify: Run this to check the structure:
-- SHOW CREATE TABLE wp_alm_transcript_embeddings;

