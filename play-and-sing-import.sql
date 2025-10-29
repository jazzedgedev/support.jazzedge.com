-- Insert Play & Sing lesson and chapters into Collection ID 178

-- Insert Main Lesson
INSERT INTO wp_alm_lessons (collection_id, lesson_title, post_date, duration, slug, menu_order, membership_level, created_at, updated_at) VALUES
(178, 'Play & Sing', '2025-07-07', 0, 'play-and-sing', 0, 3, NOW(), NOW());

-- Insert Chapters for Play & Sing
INSERT INTO wp_alm_chapters (lesson_id, chapter_title, menu_order, bunny_url, vimeo_id, slug, created_at, updated_at)
SELECT l.ID, 'Focus on Right Hand Chords', 0, 'https://vz-0696d3da-4b7.b-cdn.net/c9753a43-7811-456f-b810-fda357d5d9b0/playlist.m3u8', 0, 'focus-on-right-hand-chords', NOW(), NOW()
FROM wp_alm_lessons l WHERE l.slug = 'play-and-sing' AND l.collection_id = 178

UNION ALL

SELECT l.ID, 'Adding The Left Hand', 1, 'https://vz-0696d3da-4b7.b-cdn.net/abfa39a6-5283-4bda-92cf-c42fbb47b2fa/playlist.m3u8', 0, 'adding-the-left-hand', NOW(), NOW()
FROM wp_alm_lessons l WHERE l.slug = 'play-and-sing' AND l.collection_id = 178

UNION ALL

SELECT l.ID, 'Adding the 9th & Country Piano', 2, 'https://vz-0696d3da-4b7.b-cdn.net/7f2e4682-f145-4888-8e55-37650ef1a4c1/playlist.m3u8', 0, 'adding-the-9th-country-piano', NOW(), NOW()
FROM wp_alm_lessons l WHERE l.slug = 'play-and-sing' AND l.collection_id = 178

UNION ALL

SELECT l.ID, 'Tying it All Together', 3, 'https://vz-0696d3da-4b7.b-cdn.net/e259bd45-781f-4ffd-afe9-d42f9abe5cd9/playlist.m3u8', 0, 'tying-it-all-together', NOW(), NOW()
FROM wp_alm_lessons l WHERE l.slug = 'play-and-sing' AND l.collection_id = 178;

