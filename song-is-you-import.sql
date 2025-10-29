-- Insert Song is You lesson and chapters into Collection ID 180

-- Insert Main Lesson
INSERT INTO wp_alm_lessons (collection_id, lesson_title, post_date, duration, slug, menu_order, membership_level, created_at, updated_at) VALUES
(180, 'Song is You', '2025-07-22', 0, 'song-is-you', 0, 3, NOW(), NOW());

-- Insert Chapters for Song is You
INSERT INTO wp_alm_chapters (lesson_id, chapter_title, menu_order, bunny_url, vimeo_id, slug, created_at, updated_at)
SELECT l.ID, 'Part 1 of 4', 0, 'https://vz-0696d3da-4b7.b-cdn.net/87741dbd-9768-41c9-a544-0bafb34be965/playlist.m3u8', 0, 'part-1-of-4', NOW(), NOW()
FROM wp_alm_lessons l WHERE l.slug = 'song-is-you' AND l.collection_id = 180

UNION ALL

SELECT l.ID, 'Part 2 of 4', 1, 'https://vz-0696d3da-4b7.b-cdn.net/827ecf5f-a878-41bd-9cc0-9a765ac20d71/playlist.m3u8', 0, 'part-2-of-4', NOW(), NOW()
FROM wp_alm_lessons l WHERE l.slug = 'song-is-you' AND l.collection_id = 180

UNION ALL

SELECT l.ID, 'Part 3 of 4', 2, 'https://vz-0696d3da-4b7.b-cdn.net/ef5019fb-e249-4e62-b6d4-74a46bb77b05/playlist.m3u8', 0, 'part-3-of-4', NOW(), NOW()
FROM wp_alm_lessons l WHERE l.slug = 'song-is-you' AND l.collection_id = 180

UNION ALL

SELECT l.ID, 'Part 4 of 4', 3, 'https://vz-0696d3da-4b7.b-cdn.net/4e26a759-6417-41cf-a489-1e18ec8bcc90/playlist.m3u8', 0, 'part-4-of-4', NOW(), NOW()
FROM wp_alm_lessons l WHERE l.slug = 'song-is-you' AND l.collection_id = 180;

