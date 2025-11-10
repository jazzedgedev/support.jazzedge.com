-- Create documentation post for "Saving Favorites and Accessing Lesson Resources"
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
<p>JazzEdge Academy lessons include helpful features like saving favorites and accessing lesson resources. These tools help you organize your learning and access supporting materials like sheet music, backing tracks, and other resources.</p>

<h2>Saving Lessons as Favorites</h2>
<p>The "Save as Favorite" feature allows you to bookmark lessons you want to return to or prioritize in your practice.</p>

<h3>How to Save a Favorite</h3>
<ol>
<li><strong>Open a Lesson:</strong> Navigate to any lesson page</li>
<li><strong>Find the Button:</strong> Look for the "Save as Favorite" button below the video player (usually on the right side)</li>
<li><strong>Click to Save:</strong> Click the button with the star icon</li>
<li><strong>Confirmation:</strong> The button will change to indicate it\'s been saved</li>
</ol>

<h3>Where to Find Your Favorites</h3>
<p>Once saved, favorites appear in several places:</p>
<ul>
<li><strong>Practice Dashboard:</strong> Your favorites may appear in your practice items list</li>
<li><strong>Favorites List:</strong> You can access all favorites from your account menu</li>
<li><strong>Quick Access:</strong> Favorites are easily accessible for quick review</li>
</ul>

<h3>Using Favorites for Practice</h3>
<p>Favorites can be used to:</p>
<ul>
<li><strong>Create Practice Items:</strong> Convert favorites into practice items on your dashboard</li>
<li><strong>Quick Review:</strong> Quickly access lessons you want to revisit</li>
<li><strong>Organize Learning:</strong> Keep track of lessons you\'re actively working on</li>
<li><strong>Build Your Repertoire:</strong> Mark songs and lessons you want to maintain</li>
</ul>

<h2>Lesson Resources</h2>
<p>Many lessons include downloadable resources to support your learning. These appear in the Resources section of the lesson page.</p>

<h3>Types of Resources</h3>
<p>Common resources include:</p>

<h4>Sheet Music</h4>
<p>PDF files of the sheet music covered in the lesson. Available for download and printing.</p>

<h4>Backing Tracks</h4>
<p>Audio files you can use to practice along with the lesson material. Often available in multiple formats.</p>

<h4>iReal Pro Files</h4>
<p>Files compatible with the iReal Pro app, containing chord progressions and arrangements.</p>

<h4>YouTube Links</h4>
<p>Additional video content or demonstrations related to the lesson.</p>

<h4>Other Supporting Materials</h4>
<p>Various files, links, or additional content that supports the lesson learning objectives.</p>

<h3>Accessing Resources</h3>
<p>To access lesson resources:</p>
<ol>
<li><strong>Navigate to Resources Section:</strong> Scroll down on the lesson page to find the Resources section</li>
<li><strong>Browse Available Resources:</strong> View the list of resources available for the lesson</li>
<li><strong>Download or Access:</strong> Click on any resource to download or access it</li>
<li><strong>Check Membership Level:</strong> Some resources may require specific membership levels</li>
</ol>

<h3>Resource Icons</h3>
<p>Resources are typically marked with icons indicating their type:</p>
<ul>
<li><strong>🎵 Music Note:</strong> Sheet music</li>
<li><strong>🥁 Drum:</strong> Backing tracks</li>
<li><strong>⚙️ Sliders:</strong> iReal Pro files</li>
<li><strong>▶ Play:</strong> YouTube videos</li>
<li><strong>⬇ Download:</strong> Other downloadable files</li>
</ul>

<h2>Downloading Resources</h2>
<p>Most resources can be downloaded directly:</p>

<h3>Download Process</h3>
<ol>
<li>Click on the resource you want</li>
<li>The download will start automatically, or you\'ll be redirected to a download page</li>
<li>Save the file to your device</li>
<li>Use the resource for practice as needed</li>
</ol>

<h3>Download Tips</h3>
<ul>
<li><strong>Save in Organized Folders:</strong> Create folders for different lessons or types of resources</li>
<li><strong>Use Descriptive Names:</strong> Rename files if needed to keep them organized</li>
<li><strong>Backup Important Files:</strong> Keep backups of resources you use frequently</li>
<li><strong>Check File Formats:</strong> Ensure you have compatible software/apps for the file types</li>
</ul>

<h2>Membership Level Requirements</h2>
<p>Some resources may be restricted based on your membership level:</p>

<ul>
<li><strong>Free Members:</strong> May have access to basic resources</li>
<li><strong>Studio Members:</strong> Access to most lesson resources</li>
<li><strong>Premier Members:</strong> Full access to all resources</li>
</ul>

<p>If you don\'t have access to a resource, you\'ll see a message indicating which membership level is required.</p>

<h2>Best Practices</h2>

<h3>For Favorites</h3>
<ul>
<li><strong>Be Selective:</strong> Save favorites for lessons you\'re actively working on</li>
<li><strong>Review Regularly:</strong> Periodically review your favorites and remove ones you\'ve completed</li>
<li><strong>Use for Practice:</strong> Convert favorites into practice items for focused practice</li>
<li><strong>Build Your Library:</strong> Build a collection of favorites for different topics or skill levels</li>
</ul>

<h3>For Resources</h3>
<ul>
<li><strong>Download Early:</strong> Download resources when you start a lesson</li>
<li><strong>Organize Files:</strong> Keep downloaded resources organized by lesson or topic</li>
<li><strong>Use During Practice:</strong> Reference resources while practicing, not just while watching</li>
<li><strong>Print Sheet Music:</strong> Consider printing sheet music for hands-on practice</li>
<li><strong>Test Backing Tracks:</strong> Try backing tracks at different tempos or keys</li>
</ul>

<h2>Mobile Access</h2>
<p>Favorites and resources work on mobile devices:</p>
<ul>
<li><strong>Favorites:</strong> Access and manage favorites from mobile browsers</li>
<li><strong>Resources:</strong> Download resources directly to mobile devices</li>
<li><strong>Mobile-Friendly:</strong> All features are optimized for mobile viewing</li>
<li><strong>Cloud Storage:</strong> Consider using cloud storage for accessing resources across devices</li>
</ul>

<h2>Troubleshooting</h2>

<h3>Favorites Not Saving</h3>
<p>If favorites aren\'t saving:</p>
<ul>
<li>Make sure you\'re logged in</li>
<li>Check your internet connection</li>
<li>Try refreshing the page</li>
<li>Clear your browser cache</li>
<li>Try a different browser</li>
</ul>

<h3>Resources Won\'t Download</h3>
<p>If resources won\'t download:</p>
<ul>
<li>Check your membership level meets requirements</li>
<li>Verify your internet connection</li>
<li>Check browser download settings</li>
<li>Try right-clicking and "Save As"</li>
<li>Disable pop-up blockers</li>
<li>Check available storage space</li>
</ul>

<h3>Can\'t Access Favorites</h3>
<p>If you can\'t find your favorites:</p>
<ul>
<li>Make sure you\'re logged into the correct account</li>
<li>Check your account menu or dashboard</li>
<li>Look for a "Favorites" or "My Favorites" section</li>
<li>Verify the lesson was actually saved as favorite</li>
</ul>

<h2>Frequently Asked Questions</h2>

<p><strong>Q: How many favorites can I save?</strong><br>
A: There\'s typically no limit, but keeping your favorites list manageable helps with organization.</p>

<p><strong>Q: Can I remove a favorite?</strong><br>
A: Yes, you can usually remove favorites by clicking the favorite button again or from your favorites list.</p>

<p><strong>Q: Are resources available forever?</strong><br>
A: As long as you have access to the lesson, resources should remain available. Download them for permanent access.</p>

<p><strong>Q: Can I share resources with others?</strong><br>
A: Resources are for your personal use as part of your membership. Sharing may violate terms of service.</p>

<p><strong>Q: Do I need special software for resources?</strong><br>
A> Most resources are standard formats (PDF, MP3, etc.). Some formats like iReal Pro require specific apps.</p>

<p><strong>Q: Can I access favorites offline?</strong><br>
A: Favorites are linked to online lessons. Download resources if you need offline access to materials.</p>

<p><strong>Q: Will favorites sync across devices?</strong><br>
A: Yes, favorites are saved to your account and will appear on any device where you\'re logged in.</p>',  -- post_content
    'Saving Favorites and Accessing Lesson Resources',  -- post_title
    'Learn how to save lessons as favorites, download lesson resources like sheet music and backing tracks, and organize your learning materials.',  -- post_excerpt
    'publish',
    'closed',
    'closed',
    '',
    'saving-favorites-accessing-lesson-resources',
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

