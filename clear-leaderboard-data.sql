-- Clear Leaderboard Data Script
-- This script resets leaderboard-specific statistics without affecting other user data
-- Use with caution - backup your database first!

-- ==========================================
-- STEP 1: Reset leaderboard statistics
-- ==========================================
-- This resets all stats that affect leaderboard rankings to zero/defaults
-- Does NOT delete user records, practice items, or other data

UPDATE wp_jph_user_stats 
SET 
    total_xp = 0,
    current_level = 1,
    current_streak = 0,
    longest_streak = 0,
    total_sessions = 0,
    total_minutes = 0,
    badges_earned = 0,
    last_practice_date = NULL
WHERE 1=1;  -- Update all users

-- ==========================================
-- STEP 2: Clear leaderboard display names (Optional)
-- ==========================================
-- Uncomment the line below if you also want to clear custom display names

-- UPDATE wp_jph_user_stats SET display_name = NULL WHERE display_name IS NOT NULL;

-- ==========================================
-- STEP 3: Reset leaderboard visibility (Optional)
-- ==========================================
-- Uncomment the line below if you want to reset all users to show on leaderboard

-- UPDATE wp_jph_user_stats SET show_on_leaderboard = 1 WHERE show_on_leaderboard = 0;

-- ==========================================
-- STEP 4: Clear leaderboard cache
-- ==========================================
-- WordPress stores cache as transients in the options table
-- This clears all leaderboard-related cache entries

DELETE FROM wp_options 
WHERE option_name LIKE '_transient%aph_cache_leaderboard%'
   OR option_name LIKE '_transient_timeout%aph_cache_leaderboard%';

-- ==========================================
-- STEP 5: Clear practice sessions (Optional)
-- ==========================================
-- Uncomment if you also want to clear practice session history
-- This will remove all practice session records

-- DELETE FROM wp_jph_practice_sessions;

-- ==========================================
-- STEP 6: Clear user badges (Optional)
-- ==========================================
-- Uncomment if you also want to clear earned badges
-- Note: This clears badge awards, not badge definitions

-- DELETE FROM wp_jph_user_badges;

-- ==========================================
-- VERIFICATION QUERIES
-- ==========================================
-- Run these to verify the data was cleared:

-- Check stats are reset:
-- SELECT user_id, total_xp, current_level, current_streak, total_sessions, total_minutes, badges_earned 
-- FROM wp_jph_user_stats 
-- LIMIT 10;

-- Check leaderboard is empty:
-- SELECT COUNT(*) as users_with_xp 
-- FROM wp_jph_user_stats 
-- WHERE total_xp > 0;

-- Check cache is cleared:
-- SELECT COUNT(*) as cached_entries 
-- FROM wp_options 
-- WHERE option_name LIKE '%leaderboard%';

