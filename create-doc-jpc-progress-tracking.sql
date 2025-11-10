-- Create documentation post for "Tracking Your JPC Progress"
-- WordPress table prefix: wp_
-- Category term_id: 27

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
<p>Understanding how to track your progress through JazzEdge Practice Curriculum™ helps you see how far you\'ve come and what remains to be completed. The progress tracking system provides clear visual feedback on your journey through all focuses and keys.</p>

<h2>Where to View Your Progress</h2>
<p>Your JPC progress is visible in the Foundational tab:</p>
<ol>
<li>Navigate to your Practice Dashboard</li>
<li>Click on the <strong>"Foundational"</strong> tab</li>
<li>Scroll down to see the <strong>"All Focuses Progress"</strong> table</li>
</ol>

<h2>Understanding the Progress Table</h2>
<p>The "All Focuses Progress" table shows your completion status for all focuses and keys:</p>

<h3>Table Structure</h3>
<p>The table displays:</p>
<ul>
<li><strong>Focus ID:</strong> The focus number (1.10, 1.20, 2.10, etc.)</li>
<li><strong>Focus Title:</strong> Description of what each focus covers</li>
<li><strong>12 Key Columns:</strong> One column for each of the 12 keys:
    <ul>
    <li>C</li>
    <li>F</li>
    <li>G</li>
    <li>D</li>
    <li>B♭</li>
    <li>A</li>
    <li>E♭</li>
    <li>E</li>
    <li>A♭</li>
    <li>D♭</li>
    <li>F♯</li>
    <li>B</li>
    </ul>
</li>
<li><strong>Action Column:</strong> Options for each focus</li>
</ul>

<h3>Understanding Progress Indicators</h3>
<p>Each key cell shows different states:</p>

<h4>Completed Keys</h4>
<p>Completed keys are marked with:</p>
<ul>
<li>Checkmark (✓) icon</li>
<li>Green or filled background</li>
<li>Visual indication of completion</li>
</ul>

<h4>Current Key</h4>
<p>Your current active key shows:</p>
<ul>
<li>Different highlighting</li>
<li>Indicates where you should focus next</li>
<li>Located in your current focus</li>
</ul>

<h4>Upcoming Keys</h4>
<p>Keys not yet started show:</p>
<ul>
<li>Empty or unfilled cells</li>
<li>No checkmark</li>
<li>Ready for you to complete</li>
</ul>

<h3>Current Focus Highlighting</h3>
<p>Your current active focus is highlighted:</p>
<ul>
<li>Entire row may be highlighted</li>
<li>Makes it easy to find your current work</li>
<li>Shows focus number and title clearly</li>
</ul>

<h2>Reading Your Progress</h2>

<h3>Focus Completion</h3>
<p>To see if a focus is complete:</p>
<ul>
<li>Check if all 12 key columns have checkmarks</li>
<li>Completed focuses show progress across all keys</li>
<li>You can review completed focuses anytime</li>
</ul>

<h3>Overall Progress</h3>
<p>To see your overall JPC progress:</p>
<ul>
<li>Count how many focuses you\'ve completed (all 12 keys done)</li>
<li>Count how many focuses you\'ve started (any keys completed)</li>
<li>Count how many focuses remain untouched</li>
<li>Track your advancement through the curriculum</li>
</ul>

<h3>Key Completion Pattern</h3>
<p>As you complete keys, you\'ll see:</p>
<ul>
<li>Progress filling in horizontally across a focus</li>
<li>Checkmarks appearing as you complete each key</li>
<li>Visual representation of your advancement</li>
<li>Motivation to complete all 12 keys</li>
</ul>

<h2>Accessing Resources from Progress Table</h2>
<p>Once you\'ve started a focus, you can access its resources:</p>

<h3>Resource Downloads</h3>
<p>For focuses you\'ve started, you\'ll see:</p>
<ul>
<li>Download links for PDF sheet music</li>
<li>Download links for iReal Pro files</li>
<li>Download links for MP3 backing tracks</li>
<li>All accessible from the progress table</li>
</ul>

<h3>Viewing Focus Details</h3>
<p>You can review any focus:</p>
<ul>
<li>Click on focus titles or rows</li>
<li>Access video lessons</li>
<li>Download resources</li>
<li>Review completed material</li>
</ul>

<h2>Progress Tracking Features</h2>

<h3>Automatic Updates</h3>
<p>Your progress updates automatically when you:</p>
<ul>
<li>Mark a key complete</li>
<li>Complete all 12 keys in a focus</li>
<li>Move to the next focus</li>
</ul>

<h3>Persistent Progress</h3>
<p>Your progress is saved:</p>
<ul>
<li>Permanently stored in your account</li>
<li>Available across all devices</li>
<li>Never lost unless you reset it</li>
<li>Syncs automatically</li>
</ul>

<h3>Visual Feedback</h3>
<p>The progress table provides:</p>
<ul>
<li>At-a-glance view of your advancement</li>
<li>Clear completion indicators</li>
<li>Motivation to keep progressing</li>
<li>Easy identification of next steps</li>
</ul>

<h2>Understanding Milestone Submissions</h2>
<p>Some focuses may show milestone submission information:</p>

<h3>Grade Status</h3>
<p>If a focus has been graded:</p>
<ul>
<li>You\'ll see a grade badge (PASS or REDO)</li>
<li>Date when it was graded</li>
<li>Teacher notes (if provided)</li>
<li>Option to resubmit if needed</li>
</ul>

<h3>Submission Options</h3>
<p>For focuses with milestone options:</p>
<ul>
<li>"Get Graded" button to submit your work</li>
<li>Video submission capability</li>
<li>Teacher review and feedback</li>
<li>Graded status displayed in progress table</li>
</ul>

<h2>Using Progress to Plan Practice</h2>

<h3>Review Completed Focuses</h3>
<p>Use the progress table to:</p>
<ul>
<li>Identify focuses you\'ve completed</li>
<li>Review completed material</li>
<li>Refresh your memory on previous concepts</li>
<li>Practice focuses that need reinforcement</li>
</ul>

<h3>Focus on Current Work</h3>
<p>The progress table helps you:</p>
<ul>
<li>See exactly where you are</li>
<li>Identify your current focus</li>
<li>Know which key to work on next</li>
<li>Stay organized in your practice</li>
</ul>

<h3>Set Goals</h3>
<p>Use progress tracking to:</p>
<ul>
<li>Set completion goals</li>
<li>Track advancement over time</li>
<li>Celebrate milestones</li>
<li>Plan your practice schedule</li>
</ul>

<h2>Fixing Your Progress</h2>
<p>If your progress seems incorrect:</p>

<h3>Fix Progress Link</h3>
<p>You can use the "Fix my progress" link:</p>
<ul>
<li>Located below the Mark Complete button</li>
<li>Allows you to adjust your current assignment</li>
<li>Useful if you\'re assigned to the wrong focus or key</li>
<li>Helps correct any tracking issues</li>
</ul>

<h3>Contact Support</h3>
<p>If progress tracking isn\'t working correctly:</p>
<ul>
<li>Try refreshing the page</li>
<li>Check that you\'re logged in</li>
<li>Verify your membership is active</li>
<li>Contact support if issues persist</li>
</ul>

<h2>Tips for Tracking Progress</h2>

<h3>Check Regularly</h3>
<p>Review your progress:</p>
<ul>
<li>Before starting practice sessions</li>
<li>After completing keys</li>
<li>To plan your practice schedule</li>
<li>To celebrate achievements</li>
</ul>

<h3>Use as Motivation</h3>
<p>The progress table can:</p>
<ul>
<li>Show how far you\'ve come</li>
<li>Motivate you to complete focuses</li>
<li>Help you see patterns in your learning</li>
<li>Encourage consistent practice</li>
</ul>

<h3>Stay Organized</h3>
<p>Keep track of:</p>
<ul>
<li>Which focuses you\'ve completed</li>
<li>Which focuses you\'re currently working on</li>
<li>Which focuses you haven\'t started</li>
<li>Your overall curriculum completion</li>
</ul>

<h2>Frequently Asked Questions</h2>

<p><strong>Q: Why don\'t I see progress for all focuses?</strong><br>
A: Progress only shows for focuses you\'ve started. Focuses you haven\'t begun yet won\'t show completion markers.</p>

<p><strong>Q: Can I see my progress on mobile?</strong><br>
A: Yes, the progress table is accessible on mobile devices through the Foundational tab.</p>

<p><strong>Q: What if progress doesn\'t update after marking complete?</strong><br>
A: Try refreshing the page. If it still doesn\'t update, contact support.</p>

<p><strong>Q: Can I reset my progress?</strong><br>
A: Contact support if you need to reset your progress. The "Fix my progress" link can help adjust your current assignment.</p>

<p><strong>Q: How do I know which focus to work on next?</strong><br>
A: Your current focus is highlighted in the progress table. Complete all 12 keys in your current focus before moving to the next.</p>

<p><strong>Q: Can I skip ahead to later focuses?</strong><br>
A: Focuses are designed to be completed in order. Complete all 12 keys in your current focus first.</p>

<p><strong>Q: What does a completed focus look like?</strong><br>
A: A completed focus will have checkmarks in all 12 key columns, indicating you\'ve finished that focus.</p>',  -- post_content
    'Tracking Your JPC Progress',  -- post_title
    'Learn how to read and understand the JPC progress table, track your advancement through focuses and keys, and use progress tracking to plan your practice.',  -- post_excerpt
    'publish',
    'closed',
    'closed',
    '',
    'tracking-your-jpc-progress',
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

-- Link the post to category term_id 27
SET @term_taxonomy_id = (
    SELECT `term_taxonomy_id` 
    FROM `wp_term_taxonomy` 
    WHERE `term_id` = 27 
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

