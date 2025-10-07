-- SQL to add practice sessions for user 6756 spanning over a year
-- This will help test the analytics dashboard with comprehensive data

-- First, let's add some practice sessions going back 15 months (450+ days)
-- We'll create sessions with realistic patterns: more frequent recent sessions, varied durations, and sentiment scores

INSERT INTO `wp_jph_practice_sessions` (`user_id`, `practice_item_id`, `duration_minutes`, `sentiment_score`, `improvement_detected`, `notes`, `ai_analysis`, `xp_earned`, `session_hash`, `created_at`) VALUES

-- Recent sessions (last 30 days) - more frequent
(6756, 1, 30, 4, 1, 'Great session today!', NULL, 30, MD5(CONCAT(6756, NOW(), RAND())), DATE_SUB(NOW(), INTERVAL 1 DAY)),
(6756, 1, 45, 5, 1, 'Feeling confident', NULL, 45, MD5(CONCAT(6756, NOW(), RAND())), DATE_SUB(NOW(), INTERVAL 2 DAY)),
(6756, 1, 20, 3, 0, 'Short but focused', NULL, 20, MD5(CONCAT(6756, NOW(), RAND())), DATE_SUB(NOW(), INTERVAL 3 DAY)),
(6756, 1, 60, 4, 1, 'Long productive session', NULL, 60, MD5(CONCAT(6756, NOW(), RAND())), DATE_SUB(NOW(), INTERVAL 4 DAY)),
(6756, 1, 25, 4, 1, 'Good progress', NULL, 25, MD5(CONCAT(6756, NOW(), RAND())), DATE_SUB(NOW(), INTERVAL 5 DAY)),
(6756, 1, 35, 3, 0, 'Challenging but worth it', NULL, 35, MD5(CONCAT(6756, NOW(), RAND())), DATE_SUB(NOW(), INTERVAL 6 DAY)),
(6756, 1, 40, 5, 1, 'Breakthrough moment!', NULL, 40, MD5(CONCAT(6756, NOW(), RAND())), DATE_SUB(NOW(), INTERVAL 7 DAY)),
(6756, 1, 15, 2, 0, 'Quick warm-up', NULL, 15, MD5(CONCAT(6756, NOW(), RAND())), DATE_SUB(NOW(), INTERVAL 8 DAY)),
(6756, 1, 50, 4, 1, 'Solid practice', NULL, 50, MD5(CONCAT(6756, NOW(), RAND())), DATE_SUB(NOW(), INTERVAL 9 DAY)),
(6756, 1, 30, 3, 1, 'Working on technique', NULL, 30, MD5(CONCAT(6756, NOW(), RAND())), DATE_SUB(NOW(), INTERVAL 10 DAY)),

-- 30-60 days ago
(6756, 1, 25, 4, 1, 'Consistent practice', NULL, 25, MD5(CONCAT(6756, NOW(), RAND())), DATE_SUB(NOW(), INTERVAL 15 DAY)),
(6756, 1, 45, 3, 0, 'Tough session', NULL, 45, MD5(CONCAT(6756, NOW(), RAND())), DATE_SUB(NOW(), INTERVAL 20 DAY)),
(6756, 1, 35, 4, 1, 'Getting better', NULL, 35, MD5(CONCAT(6756, NOW(), RAND())), DATE_SUB(NOW(), INTERVAL 25 DAY)),
(6756, 1, 20, 2, 0, 'Short session', NULL, 20, MD5(CONCAT(6756, NOW(), RAND())), DATE_SUB(NOW(), INTERVAL 30 DAY)),
(6756, 1, 55, 5, 1, 'Excellent practice', NULL, 55, MD5(CONCAT(6756, NOW(), RAND())), DATE_SUB(NOW(), INTERVAL 35 DAY)),
(6756, 1, 30, 3, 1, 'Steady progress', NULL, 30, MD5(CONCAT(6756, NOW(), RAND())), DATE_SUB(NOW(), INTERVAL 40 DAY)),
(6756, 1, 40, 4, 0, 'Good but challenging', NULL, 40, MD5(CONCAT(6756, NOW(), RAND())), DATE_SUB(NOW(), INTERVAL 45 DAY)),
(6756, 1, 25, 3, 1, 'Focused practice', NULL, 25, MD5(CONCAT(6756, NOW(), RAND())), DATE_SUB(NOW(), INTERVAL 50 DAY)),
(6756, 1, 50, 4, 1, 'Long session, good results', NULL, 50, MD5(CONCAT(6756, NOW(), RAND())), DATE_SUB(NOW(), INTERVAL 55 DAY)),
(6756, 1, 15, 2, 0, 'Quick practice', NULL, 15, MD5(CONCAT(6756, NOW(), RAND())), DATE_SUB(NOW(), INTERVAL 60 DAY)),

-- 60-90 days ago
(6756, 1, 35, 3, 1, 'Building consistency', NULL, 35, MD5(CONCAT(6756, NOW(), RAND())), DATE_SUB(NOW(), INTERVAL 65 DAY)),
(6756, 1, 45, 4, 0, 'Solid work', NULL, 45, MD5(CONCAT(6756, NOW(), RAND())), DATE_SUB(NOW(), INTERVAL 70 DAY)),
(6756, 1, 20, 2, 1, 'Short but effective', NULL, 20, MD5(CONCAT(6756, NOW(), RAND())), DATE_SUB(NOW(), INTERVAL 75 DAY)),
(6756, 1, 60, 5, 1, 'Marathon session!', NULL, 60, MD5(CONCAT(6756, NOW(), RAND())), DATE_SUB(NOW(), INTERVAL 80 DAY)),
(6756, 1, 30, 3, 0, 'Average session', NULL, 30, MD5(CONCAT(6756, NOW(), RAND())), DATE_SUB(NOW(), INTERVAL 85 DAY)),
(6756, 1, 40, 4, 1, 'Good progress today', NULL, 40, MD5(CONCAT(6756, NOW(), RAND())), DATE_SUB(NOW(), INTERVAL 90 DAY)),

-- 90-180 days ago (less frequent)
(6756, 1, 25, 3, 1, 'Getting back into it', NULL, 25, MD5(CONCAT(6756, NOW(), RAND())), DATE_SUB(NOW(), INTERVAL 100 DAY)),
(6756, 1, 50, 4, 0, 'Long practice session', NULL, 50, MD5(CONCAT(6756, NOW(), RAND())), DATE_SUB(NOW(), INTERVAL 110 DAY)),
(6756, 1, 35, 2, 0, 'Struggling a bit', NULL, 35, MD5(CONCAT(6756, NOW(), RAND())), DATE_SUB(NOW(), INTERVAL 120 DAY)),
(6756, 1, 20, 3, 1, 'Short but sweet', NULL, 20, MD5(CONCAT(6756, NOW(), RAND())), DATE_SUB(NOW(), INTERVAL 130 DAY)),
(6756, 1, 45, 4, 1, 'Back on track', NULL, 45, MD5(CONCAT(6756, NOW(), RAND())), DATE_SUB(NOW(), INTERVAL 140 DAY)),
(6756, 1, 30, 3, 0, 'Steady practice', NULL, 30, MD5(CONCAT(6756, NOW(), RAND())), DATE_SUB(NOW(), INTERVAL 150 DAY)),
(6756, 1, 40, 4, 1, 'Good session', NULL, 40, MD5(CONCAT(6756, NOW(), RAND())), DATE_SUB(NOW(), INTERVAL 160 DAY)),
(6756, 1, 25, 2, 1, 'Quick practice', NULL, 25, MD5(CONCAT(6756, NOW(), RAND())), DATE_SUB(NOW(), INTERVAL 170 DAY)),
(6756, 1, 55, 5, 1, 'Excellent day!', NULL, 55, MD5(CONCAT(6756, NOW(), RAND())), DATE_SUB(NOW(), INTERVAL 180 DAY)),

-- 180-365 days ago (sparse, early days)
(6756, 1, 15, 1, 0, 'First attempts', NULL, 15, MD5(CONCAT(6756, NOW(), RAND())), DATE_SUB(NOW(), INTERVAL 200 DAY)),
(6756, 1, 30, 2, 1, 'Learning the basics', NULL, 30, MD5(CONCAT(6756, NOW(), RAND())), DATE_SUB(NOW(), INTERVAL 220 DAY)),
(6756, 1, 20, 2, 0, 'Early struggles', NULL, 20, MD5(CONCAT(6756, NOW(), RAND())), DATE_SUB(NOW(), INTERVAL 240 DAY)),
(6756, 1, 35, 3, 1, 'Starting to get it', NULL, 35, MD5(CONCAT(6756, NOW(), RAND())), DATE_SUB(NOW(), INTERVAL 260 DAY)),
(6756, 1, 25, 2, 1, 'Building foundation', NULL, 25, MD5(CONCAT(6756, NOW(), RAND())), DATE_SUB(NOW(), INTERVAL 280 DAY)),
(6756, 1, 40, 3, 0, 'Longer sessions now', NULL, 40, MD5(CONCAT(6756, NOW(), RAND())), DATE_SUB(NOW(), INTERVAL 300 DAY)),
(6756, 1, 30, 3, 1, 'Consistency building', NULL, 30, MD5(CONCAT(6756, NOW(), RAND())), DATE_SUB(NOW(), INTERVAL 320 DAY)),
(6756, 1, 45, 4, 1, 'Major breakthrough!', NULL, 45, MD5(CONCAT(6756, NOW(), RAND())), DATE_SUB(NOW(), INTERVAL 340 DAY)),
(6756, 1, 20, 2, 0, 'Off day', NULL, 20, MD5(CONCAT(6756, NOW(), RAND())), DATE_SUB(NOW(), INTERVAL 360 DAY)),
(6756, 1, 50, 4, 1, 'Great progress', NULL, 50, MD5(CONCAT(6756, NOW(), RAND())), DATE_SUB(NOW(), INTERVAL 380 DAY)),
(6756, 1, 35, 3, 1, 'Steady improvement', NULL, 35, MD5(CONCAT(6756, NOW(), RAND())), DATE_SUB(NOW(), INTERVAL 400 DAY)),
(6756, 1, 25, 2, 1, 'Early days', NULL, 25, MD5(CONCAT(6756, NOW(), RAND())), DATE_SUB(NOW(), INTERVAL 420 DAY)),
(6756, 1, 40, 3, 0, 'Learning curve', NULL, 40, MD5(CONCAT(6756, NOW(), RAND())), DATE_SUB(NOW(), INTERVAL 440 DAY)),
(6756, 1, 30, 3, 1, 'First real progress', NULL, 30, MD5(CONCAT(6756, NOW(), RAND())), DATE_SUB(NOW(), INTERVAL 460 DAY)),

-- Add some sessions with different practice items (if they exist)
(6756, 2, 30, 4, 1, 'Working on scales', NULL, 30, MD5(CONCAT(6756, NOW(), RAND())), DATE_SUB(NOW(), INTERVAL 12 DAY)),
(6756, 2, 25, 3, 0, 'Scale practice', NULL, 25, MD5(CONCAT(6756, NOW(), RAND())), DATE_SUB(NOW(), INTERVAL 18 DAY)),
(6756, 2, 35, 4, 1, 'Scale mastery', NULL, 35, MD5(CONCAT(6756, NOW(), RAND())), DATE_SUB(NOW(), INTERVAL 25 DAY)),
(6756, 2, 20, 2, 1, 'Quick scales', NULL, 20, MD5(CONCAT(6756, NOW(), RAND())), DATE_SUB(NOW(), INTERVAL 32 DAY)),
(6756, 2, 45, 5, 1, 'Scale breakthrough!', NULL, 45, MD5(CONCAT(6756, NOW(), RAND())), DATE_SUB(NOW(), INTERVAL 38 DAY)),

-- Add some sessions with different sentiment patterns
(6756, 1, 15, 1, 0, 'Really tough day', NULL, 15, MD5(CONCAT(6756, NOW(), RAND())), DATE_SUB(NOW(), INTERVAL 14 DAY)),
(6756, 1, 60, 5, 1, 'Perfect session!', NULL, 60, MD5(CONCAT(6756, NOW(), RAND())), DATE_SUB(NOW(), INTERVAL 16 DAY)),
(6756, 1, 30, 1, 0, 'Frustrating practice', NULL, 30, MD5(CONCAT(6756, NOW(), RAND())), DATE_SUB(NOW(), INTERVAL 22 DAY)),
(6756, 1, 40, 5, 1, 'Amazing progress!', NULL, 40, MD5(CONCAT(6756, NOW(), RAND())), DATE_SUB(NOW(), INTERVAL 28 DAY)),

-- Add some sessions with no improvement detected
(6756, 1, 25, 3, 0, 'Plateau day', NULL, 25, MD5(CONCAT(6756, NOW(), RAND())), DATE_SUB(NOW(), INTERVAL 11 DAY)),
(6756, 1, 35, 2, 0, 'Struggling with technique', NULL, 35, MD5(CONCAT(6756, NOW(), RAND())), DATE_SUB(NOW(), INTERVAL 17 DAY)),
(6756, 1, 20, 2, 0, 'Not feeling it today', NULL, 20, MD5(CONCAT(6756, NOW(), RAND())), DATE_SUB(NOW(), INTERVAL 23 DAY)),
(6756, 1, 45, 3, 0, 'Long session, no breakthrough', NULL, 45, MD5(CONCAT(6756, NOW(), RAND())), DATE_SUB(NOW(), INTERVAL 29 DAY));

-- Update user stats to reflect the new practice sessions
UPDATE `wp_jph_user_stats` 
SET 
    `total_sessions` = (SELECT COUNT(*) FROM `wp_jph_practice_sessions` WHERE `user_id` = 6756),
    `total_minutes` = (SELECT SUM(`duration_minutes`) FROM `wp_jph_practice_sessions` WHERE `user_id` = 6756),
    `total_xp` = (SELECT SUM(`xp_earned`) FROM `wp_jph_practice_sessions` WHERE `user_id` = 6756),
    `last_practice_date` = (SELECT MAX(`created_at`) FROM `wp_jph_practice_sessions` WHERE `user_id` = 6756),
    `updated_at` = NOW()
WHERE `user_id` = 6756;
