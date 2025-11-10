-- Create documentation post for "Lesson Video Player: Basic Controls and Navigation"
-- WordPress table prefix: wp_
-- Category term_id: 232

-- Insert the post into wp_posts
INSERT INTO `wp_posts` (
    `post_author`,
    `post_date`,
    `post_date_gmt`,
    `post_content`,
    `post_title`,
    `post_excerpt`,
    `post_status`,
    `comment_status`,
    `ping_status`,
    `post_password`,
    `post_name`,
    `to_ping`,
    `pinged`,
    `post_modified`,
    `post_modified_gmt`,
    `post_content_filtered`,
    `post_parent`,
    `guid`,
    `menu_order`,
    `post_type`,
    `post_mime_type`,
    `comment_count`
) VALUES (
    1,
    NOW(),
    UTC_TIMESTAMP(),
    '<h2>Overview</h2>
<p>The lesson video player is your primary tool for watching and learning from JazzEdge Academy video lessons. Understanding how to use the player controls and navigate between chapters will help you get the most out of your learning experience.</p>

<h2>Video Player Layout</h2>
<p>When you open a lesson, you\'ll see:</p>
<ul>
<li><strong>Video Player:</strong> Large video player at the top showing the current chapter</li>
<li><strong>Lesson Title:</strong> The lesson name and current chapter name displayed above the video</li>
<li><strong>Progress Badge:</strong> Shows your overall completion percentage for the lesson</li>
<li><strong>Action Buttons:</strong> Mark Complete and Save as Favorite buttons below the video</li>
<li><strong>Progress Bar:</strong> Visual progress indicator showing chapters completed</li>
<li><strong>Chapter List:</strong> Sidebar showing all chapters in the lesson</li>
</ul>

<h2>Basic Video Controls</h2>
<p>The video player includes standard playback controls:</p>

<h3>Play/Pause</h3>
<p>Click the center of the video or the play/pause button to start or pause playback. You can also use the spacebar on your keyboard when the video is focused.</p>

<h3>Volume Control</h3>
<p>Adjust the volume using the volume slider in the control bar. Click the speaker icon to mute/unmute. You can also use the up/down arrow keys to adjust volume.</p>

<h3>Seek/Scrub</h3>
<p>Click anywhere on the progress bar to jump to a specific point in the video. You can also drag the progress indicator to scrub through the video.</p>

<h3>Fullscreen</h3>
<p>Click the fullscreen icon in the bottom-right corner of the player to enter fullscreen mode. Press Escape or click the fullscreen icon again to exit. On most keyboards, you can also press F11 for fullscreen.</p>

<h3>Playback Speed</h3>
<p>Many video players allow you to adjust playback speed. Look for a speed control (often labeled "1x", "1.25x", "1.5x", etc.) in the player controls. This is useful for:</p>
<ul>
<li>Slowing down to catch difficult passages (0.75x or 0.5x)</li>
<li>Speeding up through familiar content (1.25x or 1.5x)</li>
<li>Reviewing at different speeds to reinforce learning</li>
</ul>

<h2>Navigating Between Chapters</h2>
<p>Lessons are organized into chapters. Here\'s how to navigate:</p>

<h3>Using the Chapter List</h3>
<p>The chapter list appears on the left side of the lesson page:</p>
<ul>
<li><strong>Current Chapter:</strong> Highlighted with a play icon (▶) and marked as "Current"</li>
<li><strong>Completed Chapters:</strong> Show a checkmark (✓) and "Completed" badge</li>
<li><strong>Upcoming Chapters:</strong> Show chapter numbers</li>
<li><strong>Locked Chapters:</strong> Chapters you don\'t have access to (if applicable)</li>
</ul>

<p>To switch chapters:</p>
<ol>
<li>Click on any chapter title in the chapter list</li>
<li>The video will load the new chapter</li>
<li>The page will automatically scroll to show the video player</li>
</ol>

<h3>Using URL Parameters</h3>
<p>You can also navigate directly to a chapter by adding <code>?c=chapter-slug</code> to the lesson URL. This is useful for bookmarking specific chapters or sharing links.</p>

<h2>Understanding Progress Tracking</h2>
<p>The lesson page tracks your progress in two ways:</p>

<h3>Overall Progress Badge</h3>
<p>At the top-right of the video player, you\'ll see a percentage badge showing how much of the lesson you\'ve completed (e.g., "45% Complete"). This updates as you mark chapters complete.</p>

<h3>Progress Bar</h3>
<p>Below the video player, there\'s a visual progress bar showing:</p>
<ul>
<li>Completed chapters (filled portion)</li>
<li>Remaining chapters (unfilled portion)</li>
<li>Text showing "Progress: X of Y chapters completed"</li>
</ul>

<h2>Video Player Features</h2>

<h3>Keyboard Shortcuts</h3>
<p>While the video player is focused, you can use:</p>
<ul>
<li><strong>Spacebar:</strong> Play/Pause</li>
<li><strong>Arrow Left:</strong> Rewind 10 seconds</li>
<li><strong>Arrow Right:</strong> Fast forward 10 seconds</li>
<li><strong>Arrow Up:</strong> Increase volume</li>
<li><strong>Arrow Down:</strong> Decrease volume</li>
<li><strong>M:</strong> Mute/Unmute</li>
<li><strong>F:</strong> Enter/Exit fullscreen</li>
<li><strong>0-9:</strong> Jump to percentage of video (0 = start, 9 = 90%)</li>
</ul>

<h3>Mobile Viewing</h3>
<p>The video player is fully responsive and adapts to mobile devices:</p>
<ul>
<li>Video automatically resizes to fit your screen</li>
<li>Touch controls work for play/pause and seeking</li>
<li>Fullscreen mode works on mobile devices</li>
<li>Chapter list is accessible via scrolling</li>
</ul>

<h2>Tips for Effective Learning</h2>
<ul>
<li><strong>Take Notes:</strong> Use the notes section or your own notebook while watching</li>
<li><strong>Practice Along:</strong> Pause frequently to practice what you just learned</li>
<li><strong>Review Chapters:</strong> Re-watch chapters as needed to reinforce concepts</li>
<li><strong>Use Playback Speed:</strong> Slow down difficult sections, speed up review</li>
<li><strong>Watch in Order:</strong> Complete chapters sequentially for best learning flow</li>
<li><strong>Take Breaks:</strong> Don\'t try to complete entire lessons in one sitting</li>
</ul>

<h2>Frequently Asked Questions</h2>

<p><strong>Q: Can I download videos to watch offline?</strong><br>
A: Videos are streamed and not available for download. You need an internet connection to watch lessons.</p>

<p><strong>Q: Why does the video take time to load?</strong><br>
A: The player may buffer briefly when starting or changing chapters. This is normal and ensures smooth playback.</p>

<p><strong>Q: Can I watch videos on my phone or tablet?</strong><br>
A: Yes! The video player works on all devices including smartphones and tablets.</p>

<p><strong>Q: What if a video won\'t play?</strong><br>
A: Check your internet connection, try refreshing the page, or clear your browser cache. If problems persist, contact support.</p>

<p><strong>Q: Does the video remember where I left off?</strong><br>
A: Your progress is tracked by chapter completion. Use the chapter list to jump back to where you were.</p>

<p><strong>Q: Can I skip ahead to later chapters?</strong><br>
A: Yes, you can click any chapter in the list to jump to it. However, we recommend watching in order for best learning.</p>',  -- post_content
    'Lesson Video Player: Basic Controls and Navigation',  -- post_title
    'Learn how to use the lesson video player controls, navigate between chapters, and make the most of your video learning experience.',  -- post_excerpt
    'publish',
    'closed',
    'closed',
    '',
    'lesson-video-player-basic-controls-navigation',
    '',
    '',
    NOW(),
    UTC_TIMESTAMP(),
    '',
    0,
    '',
    0,
    'jazzedge_doc',
    '',
    0
);

-- Get the post ID that was just inserted
SET @doc_post_id = LAST_INSERT_ID();

-- Update the GUID with the actual post ID
UPDATE `wp_posts` 
SET `guid` = CONCAT('https://jazzedge.academy/?post_type=jazzedge_doc&p=', @doc_post_id) 
WHERE `ID` = @doc_post_id;

-- Link the post to category term_id 232
SET @term_taxonomy_id = (
    SELECT `term_taxonomy_id` 
    FROM `wp_term_taxonomy` 
    WHERE `term_id` = 232 
    AND `taxonomy` = 'jazzedge_doc_category' 
    LIMIT 1
);

-- Insert the relationship
INSERT INTO `wp_term_relationships` (`object_id`, `term_taxonomy_id`, `term_order`) 
VALUES (@doc_post_id, @term_taxonomy_id, 0)
ON DUPLICATE KEY UPDATE `term_order` = 0;

-- Update the term count
UPDATE `wp_term_taxonomy` 
SET `count` = `count` + 1 
WHERE `term_taxonomy_id` = @term_taxonomy_id;

-- Set featured meta
INSERT INTO `wp_postmeta` (`post_id`, `meta_key`, `meta_value`) 
VALUES (@doc_post_id, '_jazzedge_doc_featured', 'no')
ON DUPLICATE KEY UPDATE `meta_value` = 'no';

