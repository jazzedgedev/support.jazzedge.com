-- ============================================================================
-- JazzEdge Practice Curriculum™ (JPC) Documentation - Complete SQL
-- WordPress table prefix: wp_
-- Category term_id: 27
-- 
-- This file creates 4 documentation posts:
-- 1. Introduction to JazzEdge Practice Curriculum™
-- 2. Working Through JPC Focuses and Keys
-- 3. Understanding JPC Resources: Sheet Music, Backing Tracks, and iReal Pro
-- 4. Tracking Your JPC Progress
-- ============================================================================

-- ============================================================================
-- DOCUMENT 1: Introduction to JazzEdge Practice Curriculum™
-- ============================================================================

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
    '<h2>What is JazzEdge Practice Curriculum™?</h2>
<p>JazzEdge Practice Curriculum™ (JPC) is a structured, systematic approach to learning jazz piano that guides you through essential concepts and techniques. Unlike free-form practice, JPC provides a clear path with specific focuses, organized by difficulty and musical concepts.</p>

<h2>Why Use JPC?</h2>
<p>JPC offers several advantages:</p>

<h3>Structured Learning Path</h3>
<p>Instead of wondering what to practice next, JPC provides a clear sequence of focuses that build upon each other. Each focus introduces new concepts while reinforcing previous learning.</p>

<h3>Comprehensive Coverage</h3>
<p>The curriculum covers all 12 keys, ensuring you develop fluency across the entire circle of fifths. This systematic approach builds complete musical understanding.</p>

<h3>Integrated Resources</h3>
<p>Each focus includes:</p>
<ul>
<li>Video lessons demonstrating the concepts</li>
<li>Sheet music (PDF downloads)</li>
<li>iReal Pro files for practice</li>
<li>MP3 backing tracks</li>
<li>Clear instructions and tempo suggestions</li>
</ul>

<h3>Progress Tracking</h3>
<p>JPC tracks your progress through each focus and key, providing visual feedback and motivation as you complete sections.</p>

<h3>Gamification</h3>
<p>Earn XP and gems as you progress:</p>
<ul>
<li><strong>25 XP</strong> for completing each key</li>
<li><strong>50 gems</strong> for completing all 12 keys in a focus</li>
</ul>

<h2>How JPC is Organized</h2>

<h3>Focuses</h3>
<p>JPC is organized into numbered focuses (like 1.10, 1.20, 2.10, etc.). Each focus represents a specific musical concept, technique, or pattern you\'ll learn.</p>

<h3>The 12 Keys</h3>
<p>Within each focus, you\'ll practice the concept in all 12 keys:</p>
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

<p>This systematic approach ensures you can apply what you learn in any key, not just familiar ones.</p>

<h3>Your Current Assignment</h3>
<p>At any time, you have one active focus and one active key within that focus. This keeps your practice focused and prevents overwhelm.</p>

<h2>Where to Find JPC</h2>
<p>JPC is accessed through the <strong>Foundational</strong> tab on your Practice Dashboard:</p>
<ol>
<li>Navigate to your Practice Dashboard</li>
<li>Click on the <strong>"Foundational"</strong> tab in the navigation menu</li>
<li>You\'ll see your current JPC assignment displayed prominently, including:</li>
   <ul>
   <li>Focus number and title</li>
   <li>Current key</li>
   <li>Suggested tempo</li>
   <li>Video lesson</li>
   <li>Resource downloads</li>
   <li>Mark Complete button</li>
   </ul>
</ol>

<p><strong>Note:</strong> The Foundational tab is where all JazzEdge Practice Curriculum™ content is located. This is separate from other practice items and lessons.</p>

<h2>Getting Started</h2>
<p>If you\'re new to JPC:</p>
<ol>
<li>Navigate to your Practice Dashboard</li>
<li>Click on the <strong>"Foundational"</strong> tab</li>
<li>Find your assigned focus (if you don\'t have one yet, you\'ll be assigned one automatically)</li>
<li>Watch the video lesson for your current key</li>
<li>Download the resources (sheet music, backing tracks, etc.)</li>
<li>Practice the material</li>
<li>Mark the key complete when ready</li>
<li>Move to the next key automatically</li>
</ol>

<h2>Progress Through JPC</h2>
<p>As you complete keys:</p>
<ul>
<li>Your progress is automatically tracked</li>
<li>You earn XP for each completed key</li>
<li>When all 12 keys are complete, you earn gems</li>
<li>The next key or focus is automatically assigned</li>
<li>You can view your overall progress in the "All Focuses Progress" table</li>
</ul>

<h2>Understanding the Focus Order</h2>
<p>Focuses are numbered sequentially (1.10, 1.20, 2.10, etc.). The numbering system:</p>
<ul>
<li>Indicates the order you should complete focuses</li>
<li>Groups related concepts together</li>
<li>Shows progression from foundational to advanced</li>
</ul>

<p><strong>Important:</strong> While you can jump ahead, we recommend completing focuses in order for best learning outcomes.</p>

<h2>JPC vs. Regular Lessons</h2>
<p>JPC complements regular Academy lessons:</p>
<ul>
<li><strong>Regular Lessons:</strong> Explore specific songs, techniques, or concepts</li>
<li><strong>JPC:</strong> Systematic practice curriculum ensuring comprehensive skill development</li>
</ul>

<p>Think of regular lessons as your exploration and JPC as your systematic training program.</p>

<h2>Frequently Asked Questions</h2>

<p><strong>Q: Do I have to complete all 12 keys before moving on?</strong><br>
A: Yes, completing all 12 keys in a focus is required before moving to the next focus. This ensures comprehensive understanding.</p>

<p><strong>Q: Can I skip ahead to later focuses?</strong><br>
A: The system assigns focuses sequentially. While you can review any focus, completing them in order provides the best learning foundation.</p>

<p><strong>Q: What if I want to practice a specific key more?</strong><br>
A> You can always review previous keys and focuses. Marking complete doesn\'t prevent you from practicing again.</p>

<p><strong>Q: How long does each key take?</strong><br>
A: It varies by student and focus. Take your time to truly understand each concept before moving on.</p>

<p><strong>Q: What happens when I complete all focuses?</strong><br>
A: You\'ll have completed the full curriculum! You can continue reviewing focuses or wait for new content to be added.</p>

<p><strong>Q: Can I see my overall JPC progress?</strong><br>
A: Yes, the "All Focuses Progress" table shows your completion status for all focuses and keys.</p>',
    'Introduction to JazzEdge Practice Curriculum™',
    'Learn what JazzEdge Practice Curriculum™ (JPC) is, how it\'s organized, and why it\'s an effective way to systematically learn jazz piano.',
    'publish',
    'closed',
    'closed',
    '',
    'introduction-jazzedge-practice-curriculum',
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

SET @doc_post_id = LAST_INSERT_ID();
UPDATE `wp_posts` SET `guid` = CONCAT('https://jazzedge.academy/?post_type=jazzedge_doc&p=', @doc_post_id) WHERE `ID` = @doc_post_id;
SET @term_taxonomy_id = (SELECT `term_taxonomy_id` FROM `wp_term_taxonomy` WHERE `term_id` = 27 AND `taxonomy` = 'jazzedge_doc_category' LIMIT 1);
INSERT INTO `wp_term_relationships` (`object_id`, `term_taxonomy_id`, `term_order`) VALUES (@doc_post_id, @term_taxonomy_id, 0) ON DUPLICATE KEY UPDATE `term_order` = 0;
UPDATE `wp_term_taxonomy` SET `count` = `count` + 1 WHERE `term_taxonomy_id` = @term_taxonomy_id;
INSERT INTO `wp_postmeta` (`post_id`, `meta_key`, `meta_value`) VALUES (@doc_post_id, '_jazzedge_doc_featured', 'no') ON DUPLICATE KEY UPDATE `meta_value` = 'no';

-- ============================================================================
-- DOCUMENT 2: Working Through JPC Focuses and Keys
-- ============================================================================

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
<p>This guide explains how to work through JazzEdge Practice Curriculum™ focuses and keys systematically, ensuring you get the most out of each practice session.</p>

<h2>Your Current Assignment</h2>
<p>To access your JPC assignment:</p>
<ol>
<li>Go to your Practice Dashboard</li>
<li>Click on the <strong>"Foundational"</strong> tab</li>
<li>Your current JPC assignment will be displayed</li>
</ol>

<p>The assignment shows:</p>

<ul>
<li><strong>Focus Number:</strong> The current focus you\'re working on (e.g., "FOCUS: 1.10")</li>
<li><strong>Focus Title:</strong> Description of what you\'re learning</li>
<li><strong>Current Key:</strong> Which of the 12 keys you\'re currently practicing</li>
<li><strong>Suggested Tempo:</strong> Recommended BPM for practice (this is just a suggestion - go slower if needed!)</li>
<li><strong>Video Lesson:</strong> Instructional video demonstrating the concept</li>
<li><strong>Resources:</strong> Downloadable materials for practice</li>
</ul>

<h2>Step-by-Step Process</h2>

<h3>1. Watch the Video Lesson</h3>
<p>Start by watching the video lesson for your current key:</p>
<ul>
<li>The video demonstrates the concept or technique</li>
<li>Pay attention to hand position, fingering, and musical concepts</li>
<li>Take notes if needed</li>
<li>Watch multiple times if necessary</li>
</ul>

<h3>2. Download Resources</h3>
<p>Download the practice materials:</p>

<h4>Sheet Music (PDF)</h4>
<p>Download the PDF sheet music for your current key. This shows:</p>
<ul>
<li>Notes and rhythms</li>
<li>Fingering suggestions</li>
<li>Musical notation</li>
</ul>

<h4>iReal Pro File</h4>
<p>If available, download the iReal Pro file:</p>
<ul>
<li>Import into the iReal Pro app</li>
<li>Use for practice with backing tracks</li>
<li>Adjust tempo and key as needed</li>
</ul>

<h4>MP3 Backing Track</h4>
<p>Download the MP3 backing track:</p>
<ul>
<li>Practice along with the track</li>
<li>Use it to develop timing and feel</li>
<li>Great for focused practice sessions</li>
</ul>

<h3>3. Practice the Material</h3>
<p>Now it\'s time to practice:</p>

<h4>Initial Practice</h4>
<ul>
<li>Start slowly, focusing on accuracy</li>
<li>Don\'t worry about tempo initially</li>
<li>Break difficult passages into smaller sections</li>
<li>Practice hands separately if needed</li>
</ul>

<h4>Building Up</h4>
<ul>
<li>Gradually increase tempo as you become comfortable</li>
<li>Work toward the suggested tempo, but don\'t rush</li>
<li>Focus on steady, consistent playing</li>
<li>Practice until you feel confident</li>
</ul>

<h4>Integration</h4>
<ul>
<li>Play along with backing tracks</li>
<li>Practice in different contexts</li>
<li>Ensure you understand the musical concept</li>
<li>Feel comfortable applying it</li>
</ul>

<h3>4. Mark the Key Complete</h3>
<p>When you\'re ready:</p>
<ol>
<li>Click the <strong>"Mark Complete"</strong> button</li>
<li>You\'ll earn 25 XP</li>
<li>The next key will automatically be assigned</li>
<li>Your progress updates immediately</li>
</ol>

<p><strong>Note:</strong> Mark complete when you feel you understand the concept, not necessarily when you\'ve mastered it perfectly. You can always review later.</p>

<h2>Progressing Through the 12 Keys</h2>
<p>Each focus contains 12 keys. Here\'s the typical order:</p>
<ol>
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
</ol>

<p>As you complete each key:</p>
<ul>
<li>You\'ll see your progress update in real-time</li>
<li>The "All Focuses Progress" table shows completed keys with checkmarks</li>
<li>You must complete all 12 keys before moving to the next focus</li>
</ul>

<h2>Completing a Focus</h2>
<p>When you complete all 12 keys in a focus:</p>
<ul>
<li><strong>50 Gems Reward:</strong> You\'ll earn 50 gems as a completion bonus</li>
<li><strong>Next Focus:</strong> The next focus is automatically assigned</li>
<li><strong>Progress Celebration:</strong> Your progress table shows the focus as complete</li>
<li><strong>Milestone Option:</strong> Some focuses may offer milestone submission opportunities</li>
</ul>

<h2>Understanding Tempo Suggestions</h2>
<p>Each focus includes a suggested tempo:</p>

<h3>It\'s Just a Suggestion</h3>
<p>The tempo is a guideline, not a requirement:</p>
<ul>
<li>Start slower if needed</li>
<li>Focus on accuracy over speed</li>
<li>Build up gradually</li>
<li>Don\'t rush to hit the tempo</li>
</ul>

<h3>When to Increase Tempo</h3>
<p>Increase tempo when:</p>
<ul>
<li>You can play accurately at current tempo</li>
<li>You feel comfortable with the material</li>
<li>You can maintain steady rhythm</li>
<li>You\'re ready for the next challenge</li>
</ul>

<h2>Reviewing Previous Material</h2>
<p>You can always review previous keys and focuses:</p>

<h3>Reviewing Keys</h3>
<ul>
<li>Access any completed key from the progress table</li>
<li>Re-watch videos</li>
<li>Re-download resources</li>
<li>Practice again as needed</li>
</ul>

<h3>Reviewing Focuses</h3>
<ul>
<li>Use the "All Focuses Progress" table</li>
<li>Click on any focus to review</li>
<li>Access all resources</li>
<li>Practice the entire focus again if desired</li>
</ul>

<h2>Tips for Success</h2>

<h3>Practice Regularly</h3>
<p>Consistent practice beats occasional marathon sessions:</p>
<ul>
<li>Practice a little each day</li>
<li>Use JPC as part of your regular routine</li>
<li>Maintain your practice streak</li>
<li>Build habits gradually</li>
</ul>

<h3>Don\'t Rush</h3>
<p>Take your time with each key:</p>
<ul>
<li>Mastery takes time</li>
<li>Quality over speed</li>
<li>Understand concepts deeply</li>
<li>Build strong foundations</li>
</ul>

<h3>Use All Resources</h3>
<p>Make the most of provided materials:</p>
<ul>
<li>Watch videos multiple times</li>
<li>Print sheet music for reference</li>
<li>Use backing tracks regularly</li>
<li>Import iReal Pro files for practice</li>
</ul>

<h3>Track Your Progress</h3>
<p>Monitor your advancement:</p>
<ul>
<li>Check the progress table regularly</li>
<li>Celebrate milestones</li>
<li>Note areas needing more practice</li>
<li>Review completed focuses periodically</li>
</ul>

<h2>What If I Get Stuck?</h2>
<p>If you\'re struggling with a key or focus:</p>

<ul>
<li><strong>Review the Video:</strong> Watch the lesson again</li>
<li><strong>Slow Down:</strong> Practice at a slower tempo</li>
<li><strong>Break It Down:</strong> Practice small sections</li>
<li><strong>Take Breaks:</strong> Don\'t burn out</li>
<li><strong>Review Basics:</strong> Ensure you understand previous focuses</li>
<li><strong>Ask for Help:</strong> Contact support or instructors</li>
</ul>

<h2>Frequently Asked Questions</h2>

<p><strong>Q: Can I skip a key?</strong><br>
A: No, all 12 keys must be completed in order to finish a focus. This ensures comprehensive understanding.</p>

<p><strong>Q: What if I mark a key complete by mistake?</strong><br>
A: You can always review that key again. The completion status helps track progress but doesn\'t prevent review.</p>

<p><strong>Q: How long should I spend on each key?</strong><br>
A: It varies. Take as long as you need to feel comfortable with the concept. Don\'t rush.</p>

<p><strong>Q: Can I practice multiple keys at once?</strong><br>
A: You have one active assignment at a time. However, you can review previous keys while working on your current one.</p>

<p><strong>Q: What happens if I don\'t complete all 12 keys?</strong><br>
A: You\'ll remain on your current focus until all keys are completed. The next focus won\'t be assigned until then.</p>

<p><strong>Q: Can I change my assignment?</strong><br>
A: Assignments progress automatically. If you need to adjust your progress, contact support for assistance.</p>',
    'Working Through JPC Focuses and Keys',
    'Learn the step-by-step process for working through JPC focuses and keys, from watching videos to marking complete and progressing through the curriculum.',
    'publish',
    'closed',
    'closed',
    '',
    'working-through-jpc-focuses-keys',
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

SET @doc_post_id = LAST_INSERT_ID();
UPDATE `wp_posts` SET `guid` = CONCAT('https://jazzedge.academy/?post_type=jazzedge_doc&p=', @doc_post_id) WHERE `ID` = @doc_post_id;
SET @term_taxonomy_id = (SELECT `term_taxonomy_id` FROM `wp_term_taxonomy` WHERE `term_id` = 27 AND `taxonomy` = 'jazzedge_doc_category' LIMIT 1);
INSERT INTO `wp_term_relationships` (`object_id`, `term_taxonomy_id`, `term_order`) VALUES (@doc_post_id, @term_taxonomy_id, 0) ON DUPLICATE KEY UPDATE `term_order` = 0;
UPDATE `wp_term_taxonomy` SET `count` = `count` + 1 WHERE `term_taxonomy_id` = @term_taxonomy_id;
INSERT INTO `wp_postmeta` (`post_id`, `meta_key`, `meta_value`) VALUES (@doc_post_id, '_jazzedge_doc_featured', 'no') ON DUPLICATE KEY UPDATE `meta_value` = 'no';

-- ============================================================================
-- DOCUMENT 3: Understanding JPC Resources: Sheet Music, Backing Tracks, and iReal Pro
-- ============================================================================

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
<p>Each JazzEdge Practice Curriculum™ focus includes downloadable resources to support your practice. Understanding how to use these resources effectively will enhance your learning experience.</p>

<h2>Types of Resources</h2>
<p>JPC provides three main types of resources for each focus:</p>
<ul>
<li><strong>Sheet Music (PDF)</strong> - Musical notation</li>
<li><strong>iReal Pro Files</strong> - Practice app files</li>
<li><strong>MP3 Backing Tracks</strong> - Audio practice tracks</li>
</ul>

<h2>Sheet Music (PDF)</h2>

<h3>What It Is</h3>
<p>PDF sheet music files contain the musical notation for each key in a focus. These show:</p>
<ul>
<li>Notes and rhythms</li>
<li>Key signatures</li>
<li>Fingering suggestions</li>
<li>Musical structure</li>
<li>Performance markings</li>
</ul>

<h3>How to Access</h3>
<p>Sheet music is available once you\'ve started a focus:</p>
<ol>
<li>Open your current focus assignment</li>
<li>Look for the <strong>"SHEET MUSIC"</strong> section</li>
<li>Click the download link</li>
<li>The PDF will download to your device</li>
</ol>

<h3>How to Use</h3>
<p>Use sheet music for:</p>
<ul>
<li><strong>Learning:</strong> Reading the notation while practicing</li>
<li><strong>Reference:</strong> Checking notes and rhythms</li>
<li><strong>Analysis:</strong> Understanding musical structure</li>
<li><strong>Printing:</strong> Print for hands-on practice</li>
<li><strong>Marking:</strong> Annotate with your own notes</li>
</ul>

<h3>Tips for Using Sheet Music</h3>
<ul>
<li>Print copies for practice sessions</li>
<li>Keep digital copies organized by focus</li>
<li>Compare sheet music with video demonstrations</li>
<li>Use it to understand music theory concepts</li>
<li>Reference it while practicing with backing tracks</li>
</ul>

<h2>iReal Pro Files</h2>

<h3>What It Is</h3>
<p>iReal Pro is a popular music practice app. JPC provides iReal Pro files (typically .html or .irb format) that contain:</p>
<ul>
<li>Chord progressions</li>
<li>Song arrangements</li>
<li>Practice loops</li>
<li>Metronome capabilities</li>
<li>Tempo adjustments</li>
</ul>

<h3>Getting iReal Pro</h3>
<p>To use iReal Pro files:</p>
<ol>
<li>Download the iReal Pro app on your device (iOS, Android, or Mac)</li>
<li>Purchase the app if required (one-time purchase)</li>
<li>Set up your account</li>
</ol>

<h3>How to Import</h3>
<p>Once you have iReal Pro:</p>
<ol>
<li>Download the iReal Pro file from your JPC focus</li>
<li>Open iReal Pro</li>
<li>Use the import function</li>
<li>Select the downloaded file</li>
<li>The song/arrangement will appear in your library</li>
</ol>

<h3>How to Use</h3>
<p>iReal Pro files are excellent for:</p>
<ul>
<li><strong>Practice:</strong> Play along with chord progressions</li>
<li><strong>Tempo Control:</strong> Adjust tempo to your comfort level</li>
<li><strong>Transposition:</strong> Change keys easily</li>
<li><strong>Looping:</strong> Repeat sections for focused practice</li>
<li><strong>Metronome:</strong> Practice with steady time</li>
</ul>

<h3>Benefits of iReal Pro</h3>
<ul>
<li>Highly customizable tempo and key</li>
<li>Professional-sounding backing tracks</li>
<li>Great for improvisation practice</li>
<li>Portable practice tool</li>
<li>Extensive song library capability</li>
</ul>

<h2>MP3 Backing Tracks</h2>

<h3>What It Is</h3>
<p>MP3 backing tracks are audio files you can play along with:</p>
<ul>
<li>Professional-quality recordings</li>
<li>Recorded at suggested tempo</li>
<li>Perfect for practice sessions</li>
<li>Playable on any device</li>
</ul>

<h3>How to Access</h3>
<p>Download MP3 backing tracks:</p>
<ol>
<li>Find the <strong>"MP3 Backing Track"</strong> section</li>
<li>Click the download link</li>
<li>Save the file to your device</li>
<li>Play it using any music player</li>
</ol>

<h3>How to Use</h3>
<p>Use backing tracks for:</p>
<ul>
<li><strong>Time Feel:</strong> Developing steady rhythm</li>
<li><strong>Performance Practice:</strong> Simulating real performance</li>
<li><strong>Improvisation:</strong> Practicing solos</li>
<li><strong>Concentration:</strong> Focusing on playing without distractions</li>
<li><strong>Fun:</strong> Making practice more enjoyable</li>
</ul>

<h3>Tips for Using Backing Tracks</h3>
<ul>
<li>Practice the material first without the track</li>
<li>Start at slower tempos if needed</li>
<li>Use headphones for better focus</li>
<li>Record yourself playing with the track</li>
<li>Practice until you feel comfortable</li>
</ul>

<h2>Using Resources Together</h2>
<p>For best results, use all resources in combination:</p>

<h3>Complete Practice Workflow</h3>
<ol>
<li><strong>Watch Video:</strong> Understand the concept</li>
<li><strong>Study Sheet Music:</strong> Learn the notation</li>
<li><strong>Practice Slowly:</strong> Without backing track initially</li>
<li><strong>Import to iReal Pro:</strong> (If using) Adjust tempo and practice</li>
<li><strong>Play with Backing Track:</strong> Develop feel and timing</li>
<li><strong>Review Video:</strong> Check technique and interpretation</li>
<li><strong>Repeat:</strong> Until comfortable</li>
</ol>

<h2>Accessing Resources</h2>

<h3>Current Focus</h3>
<p>Resources for your current focus are accessible through the Foundational tab:</p>
<ul>
<li>Go to Practice Dashboard → Foundational tab</li>
<li>Resources are displayed prominently with your current assignment</li>
<li>Easy download links for each resource type</li>
<li>Available immediately when you start a focus</li>
</ul>

<h3>Previous Focuses</h3>
<p>Resources from completed focuses:</p>
<ul>
<li>Available in the "All Focuses Progress" table</li>
<li>Downloadable once you\'ve started that focus</li>
<li>Accessible for review anytime</li>
</ul>

<h3>Organizing Downloads</h3>
<p>Keep your resources organized:</p>
<ul>
<li>Create folders by focus number</li>
<li>Label files clearly</li>
<li>Keep digital copies for easy access</li>
<li>Print sheet music as needed</li>
<li>Backup important files</li>
</ul>

<h2>Technical Requirements</h2>

<h3>PDF Files</h3>
<ul>
<li>Any PDF reader (built into most devices)</li>
<li>Printing capability recommended</li>
<li>No special software needed</li>
</ul>

<h3>iReal Pro Files</h3>
<ul>
<li>iReal Pro app (iOS, Android, or Mac)</li>
<li>App purchase may be required</li>
<li>Internet connection for initial download</li>
</ul>

<h3>MP3 Files</h3>
<ul>
<li>Any audio player</li>
<li>Mobile device, computer, or tablet</li>
<li>Headphones or speakers</li>
</ul>

<h2>Frequently Asked Questions</h2>

<p><strong>Q: Do I need all three resource types?</strong><br>
A: No, but using all three provides the most comprehensive practice experience. Start with what you have available.</p>

<p><strong>Q: Can I download resources multiple times?</strong><br>
A: Yes, download links remain available. You can download resources as many times as needed.</p>

<p><strong>Q: Do I need to buy iReal Pro?</strong><br>
A: iReal Pro files are optional. You can practice effectively with just sheet music and backing tracks.</p>

<p><strong>Q: Can I use resources offline?</strong><br>
A: Yes, once downloaded, all resources work offline. Download them when you have internet access.</p>

<p><strong>Q: Are resources available for all focuses?</strong><br>
A: Most focuses include all three resource types, but availability may vary. Check your current focus for available resources.</p>

<p><strong>Q: Can I share resources with others?</strong><br>
A: Resources are for your personal use as part of your membership. Sharing may violate terms of service.</p>

<p><strong>Q: What if a resource won\'t download?</strong><br>
A: Check your internet connection, try a different browser, or contact support if issues persist.</p>',
    'Understanding JPC Resources: Sheet Music, Backing Tracks, and iReal Pro',
    'Learn how to download and use JPC resources including PDF sheet music, MP3 backing tracks, and iReal Pro files to enhance your practice.',
    'publish',
    'closed',
    'closed',
    '',
    'understanding-jpc-resources-sheet-music-backing-tracks-ireal-pro',
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

SET @doc_post_id = LAST_INSERT_ID();
UPDATE `wp_posts` SET `guid` = CONCAT('https://jazzedge.academy/?post_type=jazzedge_doc&p=', @doc_post_id) WHERE `ID` = @doc_post_id;
SET @term_taxonomy_id = (SELECT `term_taxonomy_id` FROM `wp_term_taxonomy` WHERE `term_id` = 27 AND `taxonomy` = 'jazzedge_doc_category' LIMIT 1);
INSERT INTO `wp_term_relationships` (`object_id`, `term_taxonomy_id`, `term_order`) VALUES (@doc_post_id, @term_taxonomy_id, 0) ON DUPLICATE KEY UPDATE `term_order` = 0;
UPDATE `wp_term_taxonomy` SET `count` = `count` + 1 WHERE `term_taxonomy_id` = @term_taxonomy_id;
INSERT INTO `wp_postmeta` (`post_id`, `meta_key`, `meta_value`) VALUES (@doc_post_id, '_jazzedge_doc_featured', 'no') ON DUPLICATE KEY UPDATE `meta_value` = 'no';

-- ============================================================================
-- DOCUMENT 4: Tracking Your JPC Progress
-- ============================================================================

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
A: A completed focus will have checkmarks in all 12 key columns, indicating you\'ve finished that focus.</p>',
    'Tracking Your JPC Progress',
    'Learn how to read and understand the JPC progress table, track your advancement through focuses and keys, and use progress tracking to plan your practice.',
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

SET @doc_post_id = LAST_INSERT_ID();
UPDATE `wp_posts` SET `guid` = CONCAT('https://jazzedge.academy/?post_type=jazzedge_doc&p=', @doc_post_id) WHERE `ID` = @doc_post_id;
SET @term_taxonomy_id = (SELECT `term_taxonomy_id` FROM `wp_term_taxonomy` WHERE `term_id` = 27 AND `taxonomy` = 'jazzedge_doc_category' LIMIT 1);
INSERT INTO `wp_term_relationships` (`object_id`, `term_taxonomy_id`, `term_order`) VALUES (@doc_post_id, @term_taxonomy_id, 0) ON DUPLICATE KEY UPDATE `term_order` = 0;
UPDATE `wp_term_taxonomy` SET `count` = `count` + 1 WHERE `term_taxonomy_id` = @term_taxonomy_id;
INSERT INTO `wp_postmeta` (`post_id`, `meta_key`, `meta_value`) VALUES (@doc_post_id, '_jazzedge_doc_featured', 'no') ON DUPLICATE KEY UPDATE `meta_value` = 'no';

