-- Clear Chapter Transcription Jobs - SQL Version
-- Run this via phpMyAdmin, MySQL command line, or WP-CLI: wp db query < clear-transcription-jobs-sql.sql

-- Step 1: Clear all pending and processing jobs (reset to pending)
UPDATE wp_alm_transcription_jobs 
SET status = 'pending', 
    started_at = NULL, 
    completed_at = NULL,
    attempts = 0,
    message = ''
WHERE status IN ('pending', 'processing');

-- Step 2: Optional - Delete all jobs completely (uncomment if needed)
-- DELETE FROM wp_alm_transcription_jobs;

-- Step 3: Show current status
SELECT 
    status,
    COUNT(*) as count
FROM wp_alm_transcription_jobs
GROUP BY status;

