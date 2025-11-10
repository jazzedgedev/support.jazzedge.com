-- Create documentation post for "Video Player Troubleshooting Guide"
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
<p>This troubleshooting guide helps you resolve common issues with the lesson video player. Follow these steps to fix most problems you might encounter.</p>

<h2>Video Won\'t Play</h2>

<h3>Check Your Internet Connection</h3>
<p>Video playback requires a stable internet connection:</p>
<ul>
<li>Test your internet speed (should be at least 2 Mbps for standard quality)</li>
<li>Try loading other websites to verify connectivity</li>
<li>Restart your router if connection is unstable</li>
<li>Switch to a wired connection if using Wi-Fi</li>
</ul>

<h3>Check Browser Compatibility</h3>
<p>Ensure you\'re using a supported browser:</p>
<ul>
<li><strong>Recommended:</strong> Chrome, Firefox, Safari, or Edge (latest versions)</li>
<li><strong>Update Your Browser:</strong> Use the latest version for best compatibility</li>
<li><strong>Try a Different Browser:</strong> If one browser doesn\'t work, try another</li>
<li><strong>Disable Extensions:</strong> Some browser extensions can interfere with video playback</li>
</ul>

<h3>Clear Browser Cache</h3>
<p>Cached data can sometimes cause playback issues:</p>
<ol>
<li>Open your browser settings</li>
<li>Find "Clear browsing data" or "Clear cache"</li>
<li>Select "Cached images and files"</li>
<li>Clear the cache</li>
<li>Refresh the lesson page</li>
</ol>

<h3>Disable Browser Extensions</h3>
<p>Some extensions can block video playback:</p>
<ul>
<li>Ad blockers</li>
<li>Privacy extensions</li>
<li>Video downloaders</li>
<li>Script blockers</li>
</ul>
<p>Try disabling extensions temporarily to see if one is causing the issue.</p>

<h2>Video Buffering or Stuttering</h2>

<h3>Check Internet Speed</h3>
<p>Slow connections cause buffering:</p>
<ul>
<li>Run a speed test (speedtest.net)</li>
<li>Close other bandwidth-intensive applications</li>
<li>Pause other videos or downloads</li>
<li>Reduce video quality if available</li>
</ul>

<h3>Reduce Browser Load</h3>
<p>Too many open tabs can slow performance:</p>
<ul>
<li>Close unnecessary browser tabs</li>
<li>Close other applications using internet</li>
<li>Restart your browser</li>
<li>Restart your computer if needed</li>
</ul>

<h3>Check Device Performance</h3>
<p>Older devices may struggle with video playback:</p>
<ul>
<li>Close unnecessary applications</li>
<li>Check available memory/storage</li>
<li>Update your operating system</li>
<li>Try on a different device</li>
</ul>

<h2>Video Player Not Loading</h2>

<h3>JavaScript Enabled</h3>
<p>Videos require JavaScript:</p>
<ul>
<li>Check that JavaScript is enabled in your browser</li>
<li>Look for JavaScript errors in browser console (F12)</li>
<li>Allow JavaScript for jazzedge.academy domain</li>
</ul>

<h3>Page Refresh</h3>
<p>Sometimes a simple refresh helps:</p>
<ul>
<li>Press F5 or Ctrl+R (Cmd+R on Mac) to refresh</li>
<li>Try a hard refresh: Ctrl+Shift+R (Cmd+Shift+R on Mac)</li>
<li>Clear page cache and reload</li>
</ul>

<h3>Check Membership Access</h3>
<p>Verify you have access to the lesson:</p>
<ul>
<li>Confirm you\'re logged in</li>
<li>Check your membership level meets requirements</li>
<li>Verify the lesson is available for your membership tier</li>
<li>Check if your membership is active</li>
</ul>

<h2>Chapters Not Loading</h2>

<h3>URL Parameters</h3>
<p>If a specific chapter won\'t load:</p>
<ul>
<li>Try accessing the lesson without the chapter parameter</li>
<li>Navigate to chapters using the chapter list</li>
<li>Clear the URL parameter and start from the beginning</li>
</ul>

<h3>Refresh the Page</h3>
<p>Sometimes chapter data needs to reload:</p>
<ul>
<li>Refresh the page</li>
<li>Navigate away and back to the lesson</li>
<li>Clear cache and reload</li>
</ul>

<h2>Progress Not Updating</h2>

<h3>Logged In Status</h3>
<p>Progress tracking requires you to be logged in:</p>
<ul>
<li>Verify you\'re logged in</li>
<li>Check if your session expired (log out and back in)</li>
<li>Try marking complete again after refreshing</li>
</ul>

<h3>Browser Storage</h3>
<p>Progress may be stored locally:</p>
<ul>
<li>Check browser settings allow local storage</li>
<li>Clear site data and try again</li>
<li>Don\'t use private/incognito mode for progress tracking</li>
</ul>

<h2>Mobile-Specific Issues</h2>

<h3>Mobile Browser</h3>
<p>On mobile devices:</p>
<ul>
<li>Use the latest version of Safari (iOS) or Chrome (Android)</li>
<li>Ensure mobile data is enabled if not on Wi-Fi</li>
<li>Check data usage limits</li>
<li>Try Wi-Fi instead of mobile data</li>
</ul>

<h3>Mobile Performance</h3>
<p>Improve mobile playback:</p>
<ul>
<li>Close other apps</li>
<li>Restart your device</li>
<li>Update your mobile browser</li>
<li>Check available storage space</li>
</ul>

<h2>Fullscreen Issues</h2>

<h3>Browser Permissions</h3>
<p>If fullscreen won\'t activate:</p>
<ul>
<li>Click the fullscreen button in the player controls</li>
<li>Try pressing F11 (Windows) or Cmd+Ctrl+F (Mac)</li>
<li>Allow fullscreen permissions if prompted</li>
<li>Try a different browser</li>
</ul>

<h3>Screen Resolution</h3>
<p>Fullscreen may behave differently:</p>
<ul>
<li>Check your screen resolution settings</li>
<li>Try adjusting browser zoom level</li>
<li>Update graphics drivers</li>
</ul>

<h2>Audio Issues</h2>

<h3>No Sound</h3>
<p>If video plays but no audio:</p>
<ul>
<li>Check your device volume</li>
<li>Check browser/computer volume settings</li>
<li>Verify video player volume isn\'t muted</li>
<li>Try headphones or different speakers</li>
<li>Check system audio settings</li>
</ul>

<h3>Poor Audio Quality</h3>
<p>If audio is distorted or choppy:</p>
<ul>
<li>Check internet connection stability</li>
<li>Reduce other network usage</li>
<li>Try a different device</li>
<li>Check audio driver settings</li>
</ul>

<h2>Access Denied Messages</h2>

<h3>Membership Level</h3>
<p>If you see "Access Denied" or upgrade messages:</p>
<ul>
<li>Verify your membership level</li>
<li>Check if your membership is active</li>
<li>Confirm the lesson is available for your tier</li>
<li>Try logging out and back in</li>
<li>Contact support if membership should grant access</li>
</ul>

<h3>Login Issues</h3>
<p>If access seems restricted:</p>
<ul>
<li>Ensure you\'re logged in</li>
<li>Check you\'re using the correct account</li>
<li>Verify account status</li>
<li>Try logging out and back in</li>
</ul>

<h2>General Troubleshooting Steps</h2>

<h3>Standard Checklist</h3>
<p>Try these steps in order:</p>
<ol>
<li><strong>Refresh the Page:</strong> Simple refresh (F5) or hard refresh (Ctrl+Shift+R)</li>
<li><strong>Clear Cache:</strong> Clear browser cache and cookies for the site</li>
<li><strong>Check Internet:</strong> Verify stable internet connection</li>
<li><strong>Update Browser:</strong> Use latest browser version</li>
<li><strong>Disable Extensions:</strong> Temporarily disable browser extensions</li>
<li><strong>Try Different Browser:</strong> Test in Chrome, Firefox, Safari, or Edge</li>
<li><strong>Check Login:</strong> Log out and back in</li>
<li><strong>Restart Device:</strong> Restart computer or mobile device</li>
<li><strong>Contact Support:</strong> If issues persist, contact JazzEdge support</li>
</ol>

<h3>What Information to Provide</h3>
<p>If contacting support, include:</p>
<ul>
<li>Browser name and version</li>
<li>Operating system</li>
<li>Device type (desktop, tablet, phone)</li>
<li>Internet connection type and speed</li>
<li>Steps you\'ve already tried</li>
<li>Screenshot of any error messages</li>
<li>Which lesson and chapter you\'re trying to access</li>
</ul>

<h2>Prevention Tips</h2>
<p>To avoid issues:</p>
<ul>
<li><strong>Keep Browser Updated:</strong> Use latest browser versions</li>
<li><strong>Maintain Internet:</strong> Ensure stable, fast connection</li>
<li><strong>Regular Updates:</strong> Keep operating system updated</li>
<li><strong>Clear Cache Regularly:</strong> Periodically clear browser cache</li>
<li><strong>Limit Extensions:</strong> Only use necessary browser extensions</li>
<li><strong>Close Unused Tabs:</strong> Don\'t keep too many tabs open</li>
<li><strong>Check Membership:</strong> Ensure membership is active</li>
</ul>

<h2>Still Having Issues?</h2>
<p>If you\'ve tried all troubleshooting steps and still have problems:</p>
<ul>
<li>Contact JazzEdge Academy support</li>
<li>Provide detailed information about the issue</li>
<li>Include screenshots if possible</li>
<li>Be patient - support will help resolve the issue</li>
</ul>

<p>Remember: Most video player issues are temporary and can be resolved with basic troubleshooting steps. Stay patient and systematic in your approach.</p>',  -- post_content
    'Video Player Troubleshooting Guide',  -- post_title
    'Troubleshoot common video player issues including playback problems, buffering, loading errors, and access issues. Find solutions to get your lessons working smoothly.',  -- post_excerpt
    'publish',
    'closed',
    'closed',
    '',
    'video-player-troubleshooting-guide',
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

