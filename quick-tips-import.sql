-- Insert Quick Tips as lessons with chapters into collection_id 182
-- Each lesson has a single chapter with the same name as the lesson

-- Insert lessons
INSERT INTO wp_alm_lessons (collection_id, lesson_title, post_date, duration, slug, menu_order, membership_level, created_at, updated_at) VALUES
(182, 'Are You Scared By This Progression?', '2024-07-16', 0, 'are-you-scared-by-this-progression', 0, 1, NOW(), NOW()),
(182, 'How To Play Bill Evans Jazz Chords (Beginner Tutorial)', '2024-05-17', 0, 'how-to-play-bill-evans-jazz-chords-beginner-tutorial', 1, 2, NOW(), NOW()),
(182, 'Slick Jazz Piano Intro Tutorial', '2024-05-16', 0, 'slick-jazz-piano-intro-tutorial', 2, 0, NOW(), NOW()),
(182, 'Jazz Piano Intro Tutorial - See Why I Use This Intro for Standards', '2024-04-20', 0, 'jazz-piano-intro-tutorial-see-why-i-use-this-intro-for-standards', 3, 3, NOW(), NOW()),
(182, 'Massive Minor 11th Chords for Jazz Piano', '2024-04-10', 0, 'massive-minor-11th-chords-for-jazz-piano', 4, 2, NOW(), NOW()),
(182, 'Minor 7th Chord Clusters for Jazz Piano', '2024-03-26', 0, 'minor-7th-chord-clusters-for-jazz-piano', 5, 2, NOW(), NOW()),
(182, 'Rock the House with This Fire Blues Lick!', '2024-03-08', 0, 'rock-the-house-with-this-fire-blues-lick', 6, 3, NOW(), NOW()),
(182, 'Jazz Piano Technique HACK!', '2024-03-08', 0, 'jazz-piano-technique-hack', 7, 3, NOW(), NOW()),
(182, 'Wish I Learned This Berklee 2-5-1 Hack Sooner!', '2024-03-07', 0, 'wish-i-learned-this-berklee-2-5-1-hack-sooner', 8, 2, NOW(), NOW()),
(182, 'Play This FAST Oscar Peterson Lick (The Easier Way)', '2024-03-06', 0, 'play-this-fast-oscar-peterson-lick-the-easier-way', 9, 3, NOW(), NOW()),
(182, 'Keith Jarrett Turnaround - The Easy Version', '2024-02-29', 0, 'keith-jarrett-turnaround-the-easy-version', 10, 2, NOW(), NOW()),
(182, 'Randy Newman Slow Blues Intro', '2024-02-18', 0, 'randy-newman-slow-blues-intro', 11, 2, NOW(), NOW());

-- Insert chapters (one per lesson with same title and YouTube links)
-- Extract YouTube IDs and store in youtube_id column
INSERT INTO wp_alm_chapters (lesson_id, chapter_title, menu_order, bunny_url, vimeo_id, youtube_id, slug, created_at, updated_at)
SELECT 
    l.ID, 
    l.lesson_title, 
    0,
    '' as bunny_url,
    0 as vimeo_id,
    CASE l.slug
        WHEN 'are-you-scared-by-this-progression' THEN 'qQF'
        WHEN 'how-to-play-bill-evans-jazz-chords-beginner-tutorial' THEN 'dSi'
        WHEN 'slick-jazz-piano-intro-tutorial' THEN 'pNI'
        WHEN 'jazz-piano-intro-tutorial-see-why-i-use-this-intro-for-standards' THEN '1-x'
        WHEN 'massive-minor-11th-chords-for-jazz-piano' THEN 'xtz'
        WHEN 'minor-7th-chord-clusters-for-jazz-piano' THEN 'Yte'
        WHEN 'rock-the-house-with-this-fire-blues-lick' THEN '3Ru'
        WHEN 'jazz-piano-technique-hack' THEN 'H0D'
        WHEN 'wish-i-learned-this-berklee-2-5-1-hack-sooner' THEN 'aTa'
        WHEN 'play-this-fast-oscar-peterson-lick-the-easier-way' THEN 'io1'
        WHEN 'keith-jarrett-turnaround-the-easy-version' THEN '4WO'
        WHEN 'randy-newman-slow-blues-intro' THEN 'FSd'
        ELSE ''
    END as youtube_id,
    l.slug,
    NOW(),
    NOW()
FROM wp_alm_lessons l 
WHERE l.collection_id = 182 
AND l.slug IN (
    'are-you-scared-by-this-progression',
    'how-to-play-bill-evans-jazz-chords-beginner-tutorial',
    'slick-jazz-piano-intro-tutorial',
    'jazz-piano-intro-tutorial-see-why-i-use-this-intro-for-standards',
    'massive-minor-11th-chords-for-jazz-piano',
    'minor-7th-chord-clusters-for-jazz-piano',
    'rock-the-house-with-this-fire-blues-lick',
    'jazz-piano-technique-hack',
    'wish-i-learned-this-berklee-2-5-1-hack-sooner',
    'play-this-fast-oscar-peterson-lick-the-easier-way',
    'keith-jarrett-turnaround-the-easy-version',
    'randy-newman-slow-blues-intro'
);

