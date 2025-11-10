-- Create documentation post for "Tracking Your Progress: Marking Chapters Complete"
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
<p>Marking chapters complete is an important part of tracking your learning progress. It helps you see how far you\'ve come, organize your study sessions, and pick up where you left off.</p>

<h2>What Does "Mark Complete" Mean?</h2>
<p>Marking a chapter complete tells the system you\'ve finished watching and practicing that chapter. This:</p>
<ul>
<li>Updates your overall lesson progress percentage</li>
<li>Shows a checkmark (✓) next to completed chapters</li>
<li>Updates your progress bar</li>
<li>Helps you track which chapters you\'ve covered</li>
<li>Can unlock access to subsequent lessons or content (depending on lesson structure)</li>
</ul>

<h2>How to Mark a Chapter Complete</h2>
<p>After watching a chapter video, follow these steps:</p>

<ol>
<li><strong>Watch the Chapter:</strong> Complete watching the chapter video</li>
<li><strong>Find the Mark Complete Button:</strong> Look below the video player for the "Mark Complete" button (usually on the left side)</li>
<li><strong>Click the Button:</strong> Click "Mark Complete" to mark the chapter as finished</li>
<li><strong>Confirmation:</strong> The button will change to show it\'s been marked complete</li>
</ol>

<p><strong>Note:</strong> You can mark a chapter complete even if you haven\'t mastered everything. Think of it as "I\'ve reviewed this chapter" rather than "I\'ve perfected this material."</p>

<h2>Understanding Progress Indicators</h2>

<h3>Progress Badge</h3>
<p>At the top-right of the video player, you\'ll see a percentage badge showing your overall lesson completion. This updates automatically when you mark chapters complete:</p>
<ul>
<li><strong>0%:</strong> No chapters completed</li>
<li><strong>25%:</strong> 1 of 4 chapters completed</li>
<li><strong>50%:</strong> Halfway through the lesson</li>
<li><strong>100%:</strong> All chapters completed</li>
</ul>

<h3>Progress Bar</h3>
<p>Below the video player, a visual progress bar shows:</p>
<ul>
<li><strong>Filled Section:</strong> Represents completed chapters (green/orange)</li>
<li><strong>Unfilled Section:</strong> Represents remaining chapters (gray)</li>
<li><strong>Text Label:</strong> Shows "Progress: X of Y chapters completed"</li>
</ul>

<p>The progress bar fills from left to right as you complete chapters.</p>

<h3>Chapter List Indicators</h3>
<p>In the chapter list sidebar, you\'ll see visual indicators:</p>
<ul>
<li><strong>✓ Checkmark:</strong> Chapter is completed</li>
<li><strong>▶ Play Icon:</strong> Current chapter you\'re viewing</li>
<li><strong>Number:</strong> Upcoming chapters not yet completed</li>
<li><strong>"Completed" Badge:</strong> Text indicator on completed chapters</li>
<li><strong>"Current" Badge:</strong> Text indicator on the chapter you\'re viewing</li>
</ul>

<h2>Why Track Your Progress?</h2>
<p>Tracking progress offers several benefits:</p>

<h3>Motivation</h3>
<p>Seeing your progress percentage increase provides visual feedback and motivation to continue learning.</p>

<h3>Organization</h3>
<p>You can quickly see which chapters you\'ve covered and which ones you still need to review or complete.</p>

<h3>Resume Learning</h3>
<p>When you return to a lesson, you can easily see where you left off and continue from there.</p>

<h3>Learning Path</h3>
<p>Some lessons may require completing previous chapters before accessing new content. Marking complete ensures proper progression.</p>

<h2>Can I Unmark a Chapter?</h2>
<p>If you mark a chapter complete by mistake, you can typically:</p>
<ul>
<li>Click the "Mark Complete" button again (if it changes to "Mark Incomplete" or similar)</li>
<li>Or the chapter will remain marked complete - you can always re-watch it</li>
</ul>

<p><strong>Note:</strong> The exact behavior depends on the lesson implementation. Re-watching a chapter doesn\'t affect your completion status.</p>

<h2>Progress Tracking Tips</h2>

<h3>Mark Complete After Review</h3>
<p>Consider marking a chapter complete after you\'ve:</p>
<ul>
<li>Watched the entire video</li>
<li>Understood the main concepts</li>
<li>Practiced the material (if applicable)</li>
<li>Reviewed any provided resources</li>
</ul>

<h3>Don\'t Rush</h3>
<p>Don\'t feel pressured to mark chapters complete quickly. Take your time to understand the material before moving on.</p>

<h3>Revisit Completed Chapters</h3>
<p>You can always re-watch completed chapters. Marking complete doesn\'t lock you out - it just tracks your progress.</p>

<h3>Track Across Devices</h3>
<p>Your progress is saved to your account, so you can mark chapters complete on one device and see the progress on another.</p>

<h2>Progress vs. Mastery</h2>
<p>It\'s important to understand the difference:</p>

<ul>
<li><strong>Progress:</strong> Indicates you\'ve reviewed the chapter material</li>
<li><strong>Mastery:</strong> Means you\'ve fully understood and can apply the concepts</li>
</ul>

<p>Don\'t worry if you mark a chapter complete but still need to practice. You can always return to review and practice more. Progress tracking is about organization, not perfection.</p>

<h2>Frequently Asked Questions</h2>

<p><strong>Q: Do I have to mark chapters complete?</strong><br>
A: No, it\'s optional. However, it helps track your progress and organize your learning.</p>

<p><strong>Q: What happens if I skip marking a chapter complete?</strong><br>
A: Your progress won\'t update, but you can still watch all chapters. The progress tracking is just for your organization.</p>

<p><strong>Q: Can I mark multiple chapters complete at once?</strong><br>
A: No, you need to mark each chapter individually. This ensures you\'ve actually reviewed each one.</p>

<p><strong>Q: Will marking complete affect my ability to re-watch?</strong><br>
A: No, you can re-watch any chapter at any time, whether it\'s marked complete or not.</p>

<p><strong>Q: Does progress sync across devices?</strong><br>
A: Yes, your progress is saved to your account and will appear on any device where you\'re logged in.</p>

<p><strong>Q: What if I want to start over?</strong><br>
A> You can re-watch chapters in any order. While you can\'t always reset completion status, you can review chapters as needed.</p>

<p><strong>Q: Does completion unlock new content?</strong><br>
A: It depends on the lesson structure. Some lessons may require completing previous chapters before accessing new ones.</p>',  -- post_content
    'Tracking Your Progress: Marking Chapters Complete',  -- post_title
    'Learn how to mark chapters complete, understand progress indicators, and track your learning journey through JazzEdge Academy lessons.',  -- post_excerpt
    'publish',
    'closed',
    'closed',
    '',
    'tracking-progress-marking-chapters-complete',
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

