-- Add vtt_segments column to wp_alm_transcripts table
-- This stores timestamped segments extracted from VTT files as JSON
-- Replace 'wp_' with your actual WordPress table prefix if different

-- Step 1: Add vtt_segments column if it doesn't exist
ALTER TABLE wp_alm_transcripts 
ADD COLUMN vtt_segments longtext DEFAULT NULL AFTER content;

-- Verify: Run this to check the structure:
-- SHOW CREATE TABLE wp_alm_transcripts;
-- 
-- You should see the vtt_segments column after content

