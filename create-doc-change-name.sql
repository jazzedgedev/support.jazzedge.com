-- Create documentation post for "How to Change Your Name on the Dashboard"
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
<p>Your display name appears on your Practice Dashboard and is used throughout the Jazzedge Academy site, including on leaderboards and in your profile. You can change this name at any time directly from your dashboard.</p>

<h2>How to Change Your Name</h2>
<p>Follow these simple steps to update your display name:</p>

<ol>
<li><strong>Navigate to your Practice Dashboard</strong><br>
If you\'re not already there, go to your Practice Dashboard by clicking on "Your Practice Dashboard" in the navigation menu.</li>

<li><strong>Locate the Edit Button</strong><br>
Look for the small edit icon (pencil icon) next to the "Your Practice Dashboard" heading at the top of the page. This edit button appears when you hover over the dashboard title area.</li>

<li><strong>Click the Edit Button</strong><br>
Click the edit icon to open the display name modal window.</li>

<li><strong>Enter Your New Name</strong><br>
In the modal that appears, you\'ll see a text field with your current display name. Type your new desired name in this field.</li>

<li><strong>Save Your Changes</strong><br>
Click the "Save Name" button to save your changes. You can also press Enter while in the text field to quickly save.</li>

<li><strong>Confirm the Update</strong><br>
You\'ll see a confirmation message that your display name has been updated successfully, and the modal will close automatically. Your dashboard will refresh to show your new name.</li>
</ol>

<h2>Important Notes</h2>
<ul>
<li>Your display name is separate from your WordPress account username - changing your display name does not affect your login credentials.</li>
<li>The display name you set will appear on leaderboards and in your profile across the site.</li>
<li>You can change your display name as many times as you\'d like.</li>
<li>The name field has character limits for appropriate display length.</li>
</ul>

<h2>Troubleshooting</h2>
<p><strong>I don\'t see the edit button</strong><br>
Make sure you\'re logged in and viewing your own Practice Dashboard. The edit button appears as a small icon next to the dashboard title when you hover over that area.</p>

<p><strong>The name didn\'t save</strong><br>
Check that you clicked the "Save Name" button or pressed Enter. If you still have issues, try refreshing the page and attempting again. If problems persist, contact support.</p>

<p><strong>I want to revert to my original name</strong><br>
Simply click the edit button again and change your name back to your original display name or WordPress username.</p>',  -- post_content
    'How to Change Your Name on the Dashboard',  -- post_title
    'Learn how to update your display name on your Practice Dashboard. Your display name appears on leaderboards and throughout the Jazzedge Academy site.',  -- post_excerpt
    'publish',  -- post_status
    'closed',  -- comment_status
    'closed',  -- ping_status
    '',  -- post_password
    'how-to-change-your-name-on-the-dashboard',  -- post_name (slug)
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

