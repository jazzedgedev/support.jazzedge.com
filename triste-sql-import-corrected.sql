-- Insert Lessons and Chapters for Collection ID 181
-- Structure: Song titles are lessons, Parts 1-4 are chapters

-- Insert Main Lessons (Song Titles)
INSERT INTO wp_alm_lessons (collection_id, lesson_title, post_date, duration, slug, menu_order, membership_level, created_at, updated_at) VALUES
(181, 'Triste', '2025-12-04', 0, 'triste', 0, 3, NOW(), NOW()),
(181, 'Darn That Dream', '2025-11-06', 0, 'darn-that-dream', 1, 3, NOW(), NOW()),
(181, 'A Foggy Day', '2025-10-02', 0, 'a-foggy-day', 2, 3, NOW(), NOW()),
(181, 'Angel Eyes', '2025-09-04', 0, 'angel-eyes', 3, 3, NOW(), NOW()),
(181, 'There Will Never Be Another You', '2025-08-07', 0, 'there-will-never-be-another-you', 4, 3, NOW(), NOW()),
(181, 'Here''s That Rainy Day', '2025-07-03', 0, 'heres-that-rainy-day', 5, 3, NOW(), NOW());

-- Insert Chapters for Triste
INSERT INTO wp_alm_chapters (lesson_id, chapter_title, menu_order, bunny_url, vimeo_id, slug, created_at, updated_at)
SELECT l.ID, 'Part 1', 0, 'https://vz-0696d3da-4b7.b-cdn.net/5133a487-732b-4249-b4c4-d7ff5b323ea0/playlist.m3u8', 0, 'part-1', NOW(), NOW()
FROM wp_alm_lessons l WHERE l.slug = 'triste' AND l.collection_id = 181

UNION ALL

SELECT l.ID, 'Part 2', 1, 'https://vz-0696d3da-4b7.b-cdn.net/a834c9d4-6887-4243-b593-ea68a6b12ae2/playlist.m3u8', 0, 'part-2', NOW(), NOW()
FROM wp_alm_lessons l WHERE l.slug = 'triste' AND l.collection_id = 181

UNION ALL

SELECT l.ID, 'Part 3', 2, 'https://vz-0696d3da-4b7.b-cdn.net/6ca091b6-409c-478e-886a-ea3458a84618/playlist.m3u8', 0, 'part-3', NOW(), NOW()
FROM wp_alm_lessons l WHERE l.slug = 'triste' AND l.collection_id = 181

UNION ALL

SELECT l.ID, 'Part 4', 3, 'https://vz-0696d3da-4b7.b-cdn.net/86d875e5-15fb-4540-88cd-1c144dded693/playlist.m3u8', 0, 'part-4', NOW(), NOW()
FROM wp_alm_lessons l WHERE l.slug = 'triste' AND l.collection_id = 181

UNION ALL

SELECT l.ID, 'Lesson Video', 4, 'https://vz-0696d3da-4b7.b-cdn.net/64d34e94-1d0e-44cc-979a-72ceb5dcfc6c/playlist.m3u8', 0, 'lesson-video', NOW(), NOW()
FROM wp_alm_lessons l WHERE l.slug = 'triste' AND l.collection_id = 181;

-- Insert Chapters for Darn That Dream
INSERT INTO wp_alm_chapters (lesson_id, chapter_title, menu_order, bunny_url, vimeo_id, slug, created_at, updated_at)
SELECT l.ID, 'Part 1', 0, 'https://vz-0696d3da-4b7.b-cdn.net/62023060-f5eb-456b-a6cf-28240693e049/playlist.m3u8', 0, 'part-1', NOW(), NOW()
FROM wp_alm_lessons l WHERE l.slug = 'darn-that-dream' AND l.collection_id = 181

UNION ALL

SELECT l.ID, 'Part 2', 1, 'https://vz-0696d3da-4b7.b-cdn.net/78e6e059-7a0d-4525-89cb-4cc23d991906/playlist.m3u8', 0, 'part-2', NOW(), NOW()
FROM wp_alm_lessons l WHERE l.slug = 'darn-that-dream' AND l.collection_id = 181

UNION ALL

SELECT l.ID, 'Part 3', 2, 'https://vz-0696d3da-4b7.b-cdn.net/e796e52d-ea9d-42ff-a728-bd7569693833/playlist.m3u8', 0, 'part-3', NOW(), NOW()
FROM wp_alm_lessons l WHERE l.slug = 'darn-that-dream' AND l.collection_id = 181

UNION ALL

SELECT l.ID, 'Part 4', 3, 'https://vz-0696d3da-4b7.b-cdn.net/8ee7791f-53db-4c68-a0b9-772f37f79702/playlist.m3u8', 0, 'part-4', NOW(), NOW()
FROM wp_alm_lessons l WHERE l.slug = 'darn-that-dream' AND l.collection_id = 181

UNION ALL

SELECT l.ID, 'Lesson Video', 4, 'https://vz-0696d3da-4b7.b-cdn.net/71ebf3e5-aaab-4e89-909b-060e4343be27/playlist.m3u8', 0, 'lesson-video', NOW(), NOW()
FROM wp_alm_lessons l WHERE l.slug = 'darn-that-dream' AND l.collection_id = 181;

-- Insert Chapters for A Foggy Day
INSERT INTO wp_alm_chapters (lesson_id, chapter_title, menu_order, bunny_url, vimeo_id, slug, created_at, updated_at)
SELECT l.ID, 'Part 1', 0, 'https://vz-0696d3da-4b7.b-cdn.net/b3c27368-4809-4dfd-8c92-5ec62cef8568/playlist.m3u8', 0, 'part-1', NOW(), NOW()
FROM wp_alm_lessons l WHERE l.slug = 'a-foggy-day' AND l.collection_id = 181

UNION ALL

SELECT l.ID, 'Part 2', 1, 'https://vz-0696d3da-4b7.b-cdn.net/ef1e544c-2c72-4ac9-81da-24dbe545b1d0/playlist.m3u8', 0, 'part-2', NOW(), NOW()
FROM wp_alm_lessons l WHERE l.slug = 'a-foggy-day' AND l.collection_id = 181

UNION ALL

SELECT l.ID, 'Part 3', 2, 'https://vz-0696d3da-4b7.b-cdn.net/611a63a3-ef5e-4978-8e6e-457961300124/playlist.m3u8', 0, 'part-3', NOW(), NOW()
FROM wp_alm_lessons l WHERE l.slug = 'a-foggy-day' AND l.collection_id = 181

UNION ALL

SELECT l.ID, 'Part 4', 3, 'https://vz-0696d3da-4b7.b-cdn.net/39df7caf-251c-4cbf-95dd-52299855c3dd/playlist.m3u8', 0, 'part-4', NOW(), NOW()
FROM wp_alm_lessons l WHERE l.slug = 'a-foggy-day' AND l.collection_id = 181

UNION ALL

SELECT l.ID, 'Lesson Video', 4, 'https://vz-0696d3da-4b7.b-cdn.net/eb0bc1c6-4ef1-4b74-9f30-55f821d41e42/playlist.m3u8', 0, 'lesson-video', NOW(), NOW()
FROM wp_alm_lessons l WHERE l.slug = 'a-foggy-day' AND l.collection_id = 181;

-- Insert Chapters for Angel Eyes
INSERT INTO wp_alm_chapters (lesson_id, chapter_title, menu_order, bunny_url, vimeo_id, slug, created_at, updated_at)
SELECT l.ID, 'Part 1', 0, 'https://vz-0696d3da-4b7.b-cdn.net/7b6220a2-b283-410b-baec-741188c8332e/playlist.m3u8', 0, 'part-1', NOW(), NOW()
FROM wp_alm_lessons l WHERE l.slug = 'angel-eyes' AND l.collection_id = 181

UNION ALL

SELECT l.ID, 'Part 2', 1, 'https://vz-0696d3da-4b7.b-cdn.net/aeac7673-a053-4aaf-bba1-55439db13c8f/playlist.m3u8', 0, 'part-2', NOW(), NOW()
FROM wp_alm_lessons l WHERE l.slug = 'angel-eyes' AND l.collection_id = 181

UNION ALL

SELECT l.ID, 'Part 3', 2, 'https://vz-0696d3da-4b7.b-cdn.net/4b781744-1d31-4a06-85b6-bfc6dabb5a0c/playlist.m3u8', 0, 'part-3', NOW(), NOW()
FROM wp_alm_lessons l WHERE l.slug = 'angel-eyes' AND l.collection_id = 181

UNION ALL

SELECT l.ID, 'Part 4', 3, 'https://vz-0696d3da-4b7.b-cdn.net/518dab92-be43-487b-9f53-11ed29991e4c/playlist.m3u8', 0, 'part-4', NOW(), NOW()
FROM wp_alm_lessons l WHERE l.slug = 'angel-eyes' AND l.collection_id = 181

UNION ALL

SELECT l.ID, 'Lesson Video', 4, 'https://vz-0696d3da-4b7.b-cdn.net/d793cd18-3bcd-475c-8290-c4bb39e88b55/playlist.m3u8', 0, 'lesson-video', NOW(), NOW()
FROM wp_alm_lessons l WHERE l.slug = 'angel-eyes' AND l.collection_id = 181;

-- Insert Chapters for There Will Never Be Another You
INSERT INTO wp_alm_chapters (lesson_id, chapter_title, menu_order, bunny_url, vimeo_id, slug, created_at, updated_at)
SELECT l.ID, 'Part 1', 0, 'https://vz-0696d3da-4b7.b-cdn.net/3a5a8e80-3858-4ed6-a0bf-6476c7ef1cd8/playlist.m3u8', 0, 'part-1', NOW(), NOW()
FROM wp_alm_lessons l WHERE l.slug = 'there-will-never-be-another-you' AND l.collection_id = 181

UNION ALL

SELECT l.ID, 'Part 2', 1, 'https://vz-0696d3da-4b7.b-cdn.net/c5518ed6-66c8-4301-a14d-853f5483bcb8/playlist.m3u8', 0, 'part-2', NOW(), NOW()
FROM wp_alm_lessons l WHERE l.slug = 'there-will-never-be-another-you' AND l.collection_id = 181

UNION ALL

SELECT l.ID, 'Part 3', 2, 'https://vz-0696d3da-4b7.b-cdn.net/be2c8914-4ec6-4240-9046-ef44edff433d/playlist.m3u8', 0, 'part-3', NOW(), NOW()
FROM wp_alm_lessons l WHERE l.slug = 'there-will-never-be-another-you' AND l.collection_id = 181

UNION ALL

SELECT l.ID, 'Part 4', 3, 'https://vz-0696d3da-4b7.b-cdn.net/7c6b3bf9-6880-4b9b-8503-bb2d42e0f55c/playlist.m3u8', 0, 'part-4', NOW(), NOW()
FROM wp_alm_lessons l WHERE l.slug = 'there-will-never-be-another-you' AND l.collection_id = 181

UNION ALL

SELECT l.ID, 'Lesson Video', 4, 'https://vz-0696d3da-4b7.b-cdn.net/63d0447b-f98d-449c-ab22-e198f2639a89/playlist.m3u8', 0, 'lesson-video', NOW(), NOW()
FROM wp_alm_lessons l WHERE l.slug = 'there-will-never-be-another-you' AND l.collection_id = 181;

-- Insert Chapters for Here's That Rainy Day
INSERT INTO wp_alm_chapters (lesson_id, chapter_title, menu_order, bunny_url, vimeo_id, slug, created_at, updated_at)
SELECT l.ID, 'Part 1', 0, 'https://vz-0696d3da-4b7.b-cdn.net/3fef1255-9eff-4fca-8c53-f52adebe93f7/playlist.m3u8', 0, 'part-1', NOW(), NOW()
FROM wp_alm_lessons l WHERE l.slug = 'heres-that-rainy-day' AND l.collection_id = 181

UNION ALL

SELECT l.ID, 'Part 2', 1, 'https://vz-0696d3da-4b7.b-cdn.net/12db0217-4397-4aae-8df6-86b7edfea06b/playlist.m3u8', 0, 'part-2', NOW(), NOW()
FROM wp_alm_lessons l WHERE l.slug = 'heres-that-rainy-day' AND l.collection_id = 181

UNION ALL

SELECT l.ID, 'Part 3', 2, 'https://vz-0696d3da-4b7.b-cdn.net/4ccc4439-aa2c-4c18-97e8-8f76820643f7/playlist.m3u8', 0, 'part-3', NOW(), NOW()
FROM wp_alm_lessons l WHERE l.slug = 'heres-that-rainy-day' AND l.collection_id = 181

UNION ALL

SELECT l.ID, 'Part 4', 3, 'https://vz-0696d3da-4b7.b-cdn.net/546f42ad-d84a-4e4e-b661-ea44b7f8070a/playlist.m3u8', 0, 'part-4', NOW(), NOW()
FROM wp_alm_lessons l WHERE l.slug = 'heres-that-rainy-day' AND l.collection_id = 181

UNION ALL

SELECT l.ID, 'Lesson Video', 4, 'https://vz-0696d3da-4b7.b-cdn.net/9d98dca5-02f3-425a-80d9-28792fb6e6d2/playlist.m3u8', 0, 'lesson-video', NOW(), NOW()
FROM wp_alm_lessons l WHERE l.slug = 'heres-that-rainy-day' AND l.collection_id = 181;

