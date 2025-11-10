-- Create documentation post for "Understanding My Practice Items"
-- WordPress table prefix: wp_
-- Category term_id: 233

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
    1,  -- post_author (change to your admin user ID if different)
    NOW(),  -- post_date
    UTC_TIMESTAMP(),  -- post_date_gmt
    '<h2>Overview</h2>
<p>My Practice Items is a personalized system that lets you create and organize the specific things you want to practice. Think of practice items as your personalized practice playlist - you can add items for scales, songs, exercises, or any musical concept you\'re working on.</p>

<h2>What Are Practice Items?</h2>
<p>Practice Items are customizable entries that represent what you\'re practicing. Each item can be:</p>
<ul>
<li>A specific song you\'re learning</li>
<li>A scale or exercise you\'re working on</li>
<li>A jazz piano concept or technique</li>
<li>A lesson from JazzEdge Academy</li>
<li>Anything else you want to track your practice for</li>
</ul>

<p>You can have up to <strong>6 active practice items</strong> at any time, giving you flexibility to focus on multiple areas while keeping your practice organized.</p>

<h2>Creating Practice Items</h2>
<p>There are two ways to create a practice item:</p>

<h3>1. Custom Practice Item</h3>
<p>Create your own practice item from scratch:</p>
<ol>
<li>Click the <strong>"Add Practice Item"</strong> button on your Practice Dashboard</li>
<li>Select <strong>"Custom"</strong> as the practice type</li>
<li>Enter a title (required, max 50 characters)</li>
<li>Optionally add a description (max 200 characters) explaining what you\'ll practice</li>
<li>Click <strong>"Add Practice Item"</strong> to save</li>
</ol>

<h3>2. From Lesson Favorites</h3>
<p>Create a practice item from a lesson you\'ve favorited:</p>
<ol>
<li>Click the <strong>"Add Practice Item"</strong> button</li>
<li>Select <strong>"From Favorites"</strong> as the practice type</li>
<li>Choose a lesson from your favorites list</li>
<li>The title will be automatically filled from the lesson name</li>
<li>Optionally add or edit the description</li>
<li>Click <strong>"Add Practice Item"</strong> to save</li>
</ol>

<p><strong>Note:</strong> If you don\'t see any favorites, visit lesson pages on the Academy site and add them to your favorites first, then return to create practice items.</p>

<h2>Managing Your Practice Items</h2>

<h3>Viewing Your Items</h3>
<p>Your practice items are displayed in a grid on the Practice Dashboard under the "My Practice Items" section. Each item card shows:</p>
<ul>
<li><strong>Item Name:</strong> The title of your practice item</li>
<li><strong>Last Practiced:</strong> When you last logged practice for this item</li>
<li><strong>Description:</strong> Your notes about what this item covers</li>
<li><strong>Action Buttons:</strong> Log Practice, Edit, and Delete options</li>
</ul>

<h3>Reordering Items</h3>
<p>You can drag and drop practice items to reorder them:</p>
<ol>
<li>Click and hold the drag handle (dots icon) in the top-left corner of any practice item card</li>
<li>Drag it to your desired position</li>
<li>Release to drop it in the new position</li>
<li>The order is automatically saved</li>
</ol>

<h3>Editing Practice Items</h3>
<p>To edit an existing practice item:</p>
<ol>
<li>Click the <strong>Edit</strong> button (pencil icon) on the practice item card</li>
<li>Modify the title or description</li>
<li>Click <strong>"Update Practice Item"</strong> to save your changes</li>
</ol>

<h3>Deleting Practice Items</h3>
<p>To remove a practice item:</p>
<ol>
<li>Click the <strong>Delete</strong> button (trash icon) on the practice item card</li>
<li>Confirm the deletion when prompted</li>
<li>The item will be removed from your list</li>
</ol>

<p><strong>Note:</strong> Deleting a practice item does not delete your practice history. All practice sessions logged for that item remain in your history.</p>

<h2>Logging Practice Sessions</h2>
<p>One of the main purposes of practice items is to track your practice sessions. Here\'s how to log practice:</p>

<ol>
<li>Click the <strong>"Log Practice"</strong> button on any practice item card</li>
<li>A modal window will open with practice logging options</li>
<li>Fill in the following information:
    <ul>
        <li><strong>Duration:</strong> How long you practiced (select from quick buttons like 5 min, 10 min, 15 min, 30 min, 45 min, 1 hour, or enter custom minutes)</li>
        <li><strong>How it went:</strong> Rate your practice session:
            <ul>
                <li>😊 Excellent (5/5)</li>
                <li>🙂 Good (4/5)</li>
                <li>😐 Okay (3/5)</li>
                <li>😕 Challenging (2/5)</li>
                <li>😞 Struggled (1/5)</li>
            </ul>
        </li>
        <li><strong>Improvement Detected:</strong> Check this box if you noticed improvement during your practice</li>
        <li><strong>Notes:</strong> Optional notes about your practice session</li>
    </ul>
</li>
<li>Click <strong>"Log Practice"</strong> or press Enter to save</li>
</ol>

<h3>What Happens When You Log Practice?</h3>
<p>When you log a practice session, several things happen automatically:</p>
<ul>
<li><strong>XP Earned:</strong> You earn Experience Points based on duration, sentiment, and whether you detected improvement</li>
<li><strong>Streak Updated:</strong> Your practice streak is maintained or updated</li>
<li><strong>Practice History:</strong> The session is recorded in your practice history</li>
<li><strong>Badge Progress:</strong> Your progress toward various achievement badges is updated</li>
<li><strong>Last Practiced Date:</strong> The practice item card shows when you last practiced</li>
</ul>

<h2>Practice Item Limits</h2>
<p>To keep your practice focused and manageable:</p>
<ul>
<li><strong>Maximum Active Items:</strong> You can have up to 6 active practice items at once</li>
<li><strong>Duplicate Names:</strong> You cannot have two active items with the same name</li>
<li><strong>Item Slots:</strong> The dashboard shows 6 slots - empty slots display "Add Practice Item" buttons</li>
</ul>

<p><strong>If you reach the limit:</strong> To add a new practice item when you have 6 already, you\'ll need to delete or deactivate an existing item first.</p>

<h2>Practice History</h2>
<p>All practice sessions logged for your practice items are tracked in your Practice History:</p>
<ul>
<li>Click the <strong>"Practice History"</strong> button in the Practice Items section header</li>
<li>View all your logged practice sessions</li>
<li>See details like date, duration, sentiment, improvement detected, and notes</li>
<li>Edit or delete individual practice sessions if needed</li>
</ul>

<h2>Tips for Effective Practice Item Management</h2>
<ul>
<li><strong>Be Specific:</strong> Create items for specific things you want to practice (e.g., "Autumn Leaves in C" rather than just "Jazz Standards")</li>
<li><strong>Use Descriptions:</strong> Add descriptions to remind yourself what each item focuses on</li>
<li><strong>Regular Updates:</strong> Keep your items current - delete items you\'ve mastered and add new challenges</li>
<li><strong>Prioritize:</strong> Use drag-and-drop to put your most important practice items at the top</li>
<li><strong>Log Consistently:</strong> Log practice sessions regularly to track your progress and maintain streaks</li>
<li><strong>Link to Lessons:</strong> If an item is created from a favorite, it will link back to the lesson page for easy access</li>
</ul>

<h2>Practice Items vs. Repertoire</h2>
<p>Your Practice Dashboard includes both <strong>Practice Items</strong> and <strong>Repertoire</strong>:</p>
<ul>
<li><strong>Practice Items:</strong> Things you\'re actively working on or learning (up to 6 items)</li>
<li><strong>Repertoire:</strong> Songs or pieces you\'ve learned and want to maintain (unlimited)</li>
</ul>

<p>Practice Items are for active learning and improvement, while Repertoire is for maintaining songs you already know.</p>

<h2>Frequently Asked Questions</h2>

<p><strong>Q: Can I have more than 6 practice items?</strong><br>
A: No, the limit is 6 active items. If you need to add a new item, delete or deactivate an existing one first.</p>

<p><strong>Q: What happens if I delete a practice item?</strong><br>
A: The item is removed from your list, but all practice sessions logged for that item remain in your practice history.</p>

<p><strong>Q: Can I change a practice item\'s name?</strong><br>
A: Yes, click the Edit button on any practice item to modify its name or description.</p>

<p><strong>Q: Do I have to log practice every day?</strong><br>
A: No, but logging practice regularly helps maintain your streak and earn more XP. You can log practice whenever you practice, even multiple times per day.</p>

<p><strong>Q: Can I log practice for the same item multiple times in one day?</strong><br>
A: Yes! You can log multiple practice sessions for the same item on the same day. Each session earns XP separately.</p>

<p><strong>Q: What\'s the difference between a custom item and one from favorites?</strong><br>
A: Custom items are created from scratch with your own title. Items from favorites are automatically linked to Academy lessons and include a link to view the lesson.</p>

<p><strong>Q: How do I see all my practice sessions?</strong><br>
A: Click the "Practice History" button in the Practice Items section header to view all logged sessions across all items.</p>

<p><strong>Q: Can I reorder my practice items?</strong><br>
A: Yes! Drag and drop practice items by their drag handle (dots icon) to reorder them. The order is saved automatically.</p>',  -- post_content
    'Understanding My Practice Items',  -- post_title
    'Learn how to create, manage, and use Practice Items on your Practice Dashboard. Create custom items or from favorites, log practice sessions, and track your progress.',  -- post_excerpt
    'publish',  -- post_status
    'closed',  -- comment_status
    'closed',  -- ping_status
    '',  -- post_password
    'understanding-my-practice-items',  -- post_name (slug)
    '',  -- to_ping
    '',  -- pinged
    NOW(),  -- post_modified
    UTC_TIMESTAMP(),  -- post_modified_gmt
    '',  -- post_content_filtered
    0,  -- post_parent
    '',  -- guid (will be updated after insert)
    0,  -- menu_order
    'jazzedge_doc',  -- post_type
    '',  -- post_mime_type
    0  -- comment_count
);

-- Get the post ID that was just inserted
SET @doc_post_id = LAST_INSERT_ID();

-- Update the GUID with the actual post ID
UPDATE `wp_posts` 
SET `guid` = CONCAT('https://jazzedge.academy/?post_type=jazzedge_doc&p=', @doc_post_id) 
WHERE `ID` = @doc_post_id;

-- Link the post to category term_id 233
-- First, get the term_taxonomy_id for category 233
SET @term_taxonomy_id = (
    SELECT `term_taxonomy_id` 
    FROM `wp_term_taxonomy` 
    WHERE `term_id` = 233 
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

-- Set featured meta (not featured)
INSERT INTO `wp_postmeta` (`post_id`, `meta_key`, `meta_value`) 
VALUES (@doc_post_id, '_jazzedge_doc_featured', 'no')
ON DUPLICATE KEY UPDATE `meta_value` = 'no';

