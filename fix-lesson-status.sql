-- Fix lesson post status from 'future' to 'publish'
-- This will make the permalink work correctly

-- Update the post status for lessons in collection 181
UPDATE wp_posts p
INNER JOIN wp_alm_lessons l ON p.ID = l.post_id
SET p.post_status = 'publish'
WHERE l.collection_id = 181;

-- Check the results
SELECT 
    l.ID AS lesson_id,
    l.lesson_title,
    p.ID AS post_id,
    p.post_title,
    p.post_name,
    p.post_status,
    p.post_type
FROM wp_alm_lessons l
JOIN wp_posts p ON l.post_id = p.ID
WHERE l.collection_id = 181;

