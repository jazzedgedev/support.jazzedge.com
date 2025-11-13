-- Fix total_minutes calculation by summing all practice sessions
-- This will update the total_minutes field in jph_user_stats based on actual practice sessions

-- Update total_minutes for all users based on their practice sessions
UPDATE wp_jph_user_stats us
INNER JOIN (
    SELECT 
        user_id,
        SUM(duration_minutes) as calculated_total_minutes
    FROM wp_jph_practice_sessions
    GROUP BY user_id
) ps ON us.user_id = ps.user_id
SET us.total_minutes = ps.calculated_total_minutes;

-- Verify the results
SELECT 
    us.user_id,
    u.user_login,
    COALESCE(us.display_name, u.display_name, u.user_login) as leaderboard_name,
    us.total_sessions,
    us.total_minutes,
    (SELECT SUM(duration_minutes) FROM wp_jph_practice_sessions WHERE user_id = us.user_id) as calculated_from_sessions,
    CASE 
        WHEN us.total_minutes = (SELECT SUM(duration_minutes) FROM wp_jph_practice_sessions WHERE user_id = us.user_id) 
        THEN '✓ Match' 
        ELSE '✗ Mismatch' 
    END as status
FROM wp_jph_user_stats us
INNER JOIN wp_users u ON us.user_id = u.ID
WHERE us.show_on_leaderboard = 1 
    AND us.total_xp > 0
ORDER BY us.total_xp DESC;

