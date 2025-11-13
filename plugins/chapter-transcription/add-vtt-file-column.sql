-- Add vtt_file column to wp_alm_transcripts table
-- This stores the path/URL to the VTT file for use with fvplayer
-- Replace 'wp_' with your actual WordPress table prefix if different

-- Step 1: Add vtt_file column if it doesn't exist
ALTER TABLE wp_alm_transcripts 
ADD COLUMN vtt_file varchar(255) DEFAULT NULL AFTER content;

-- Verify: Run this to check the structure:
-- SHOW CREATE TABLE wp_alm_transcripts;
-- 
-- You should see the vtt_file column after content

