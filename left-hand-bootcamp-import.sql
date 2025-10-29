-- Insert Left Hand Bootcamp lesson and chapters into Collection ID 179

-- Insert Main Lesson
INSERT INTO wp_alm_lessons (collection_id, lesson_title, post_date, duration, slug, menu_order, membership_level, created_at, updated_at) VALUES
(179, 'Left Hand Bootcamp', '2025-07-07', 0, 'left-hand-bootcamp', 0, 3, NOW(), NOW());

-- Insert Chapters for Left Hand Bootcamp
INSERT INTO wp_alm_chapters (lesson_id, chapter_title, menu_order, bunny_url, vimeo_id, slug, created_at, updated_at)
SELECT l.ID, 'Boogie Left-Hand Accompaniment', 0, 'https://vz-0696d3da-4b7.b-cdn.net/081c6bd2-2020-4a53-b435-80da3b9a812b/playlist.m3u8', 0, 'boogie-left-hand-accompaniment', NOW(), NOW()
FROM wp_alm_lessons l WHERE l.slug = 'left-hand-bootcamp' AND l.collection_id = 179

UNION ALL

SELECT l.ID, 'Hand Coordination Techniques', 1, 'https://vz-0696d3da-4b7.b-cdn.net/6e04ce43-908a-4677-be6d-c29fe1edf233/playlist.m3u8', 0, 'hand-coordination-techniques', NOW(), NOW()
FROM wp_alm_lessons l WHERE l.slug = 'left-hand-bootcamp' AND l.collection_id = 179

UNION ALL

SELECT l.ID, 'Awesome Boogie Pattern', 2, 'https://vz-0696d3da-4b7.b-cdn.net/199fb390-019f-4980-a016-74f170fa021e/playlist.m3u8', 0, 'awesome-boogie-pattern', NOW(), NOW()
FROM wp_alm_lessons l WHERE l.slug = 'left-hand-bootcamp' AND l.collection_id = 179

UNION ALL

SELECT l.ID, 'Scale Chords', 3, 'https://vz-0696d3da-4b7.b-cdn.net/732315d3-ac7b-40a4-9094-3ab6ab924e64/playlist.m3u8', 0, 'scale-chords', NOW(), NOW()
FROM wp_alm_lessons l WHERE l.slug = 'left-hand-bootcamp' AND l.collection_id = 179;

