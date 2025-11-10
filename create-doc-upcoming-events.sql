-- Create documentation post for "Understanding Upcoming Events"
-- WordPress table prefix: wp_
-- Category term_id: 32

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
<p>The Upcoming Events section on your Practice Dashboard shows live sessions, classes, workshops, and other events happening in the JazzEdge Academy community. These events provide opportunities to learn from instructors, connect with other students, and participate in live interactive sessions.</p>

<h2>Where to Find Upcoming Events</h2>
<p>Upcoming Events are displayed in the <strong>Events</strong> tab on your Practice Dashboard:</p>
<ol>
<li>Navigate to your Practice Dashboard</li>
<li>Click on the <strong>"Events"</strong> tab at the top of the dashboard</li>
<li>You\'ll see a list of upcoming events relevant to your membership level</li>
</ol>

<p>You can also view the full calendar by clicking the <strong>"View Full Calendar"</strong> button in the Events section header.</p>

<h2>What Events Are Shown</h2>
<p>The Upcoming Events section displays:</p>
<ul>
<li><strong>Up to 5 upcoming events</strong> that match your membership level</li>
<li>Events scheduled for the next 365 days</li>
<li>Events that are either upcoming or recently ended (visible for 6 hours after end time)</li>
</ul>

<p><strong>Membership Level Filtering:</strong> Events are automatically filtered based on your current membership level (Free, Studio, or Premier). You\'ll only see events you have access to attend.</p>

<h2>Types of Events</h2>
<p>JazzEdge Academy offers several types of events:</p>

<h3>Live Classes</h3>
<p>Interactive group classes covering various jazz piano topics. These are typically scheduled sessions with a specific teacher and curriculum focus.</p>

<h3>Coaching Sessions</h3>
<p>Personalized coaching opportunities where you can get direct feedback and guidance from instructors.</p>

<h3>Office Hours</h3>
<p>Drop-in sessions where instructors are available to answer questions and provide support.</p>

<h3>Workshops</h3>
<p>Intensive learning sessions focused on specific topics or techniques.</p>

<h3>Webinars</h3>
<p>Educational presentations and demonstrations on various jazz piano subjects.</p>

<h3>Community Calls</h3>
<p>Social sessions for connecting with other Academy members and sharing experiences.</p>

<h3>Special Events</h3>
<p>Unique events, masterclasses, or special occasions that may be limited-time opportunities.</p>

<h2>Event Information Displayed</h2>
<p>Each event card shows:</p>
<ul>
<li><strong>Event Title:</strong> The name of the event</li>
<li><strong>Date & Time:</strong> When the event takes place (displayed in your local timezone)</li>
<li><strong>Event Type:</strong> The category of event (class, workshop, etc.)</li>
<li><strong>Membership Level:</strong> Which membership level the event is for</li>
<li><strong>Teacher:</strong> The instructor leading the event</li>
<li><strong>Action Button:</strong> Either "View" to see details, "Register" if registration is required, or "Join" if you can attend directly</li>
</ul>

<h2>Accessing Events</h2>

<h3>Viewing Event Details</h3>
<p>Click the <strong>"View"</strong> button on any event card to see full details including:</p>
<ul>
<li>Complete event description</li>
<li>Full date and time information</li>
<li>Access requirements</li>
<li>Join or registration links</li>
<li>Resources (if available)</li>
<li>Calendar download options</li>
</ul>

<h3>Registration vs. Direct Join</h3>
<p>Events may require different access methods:</p>
<ul>
<li><strong>Registration Required:</strong> Some events require you to register first. After registering, you\'ll receive a join link via email before the event starts.</li>
<li><strong>Direct Join:</strong> Other events allow you to join directly using a Zoom link or other platform link.</li>
</ul>

<h3>Membership Level Requirements</h3>
<p>Events may be restricted to specific membership levels:</p>
<ul>
<li><strong>Free:</strong> Available to all logged-in members</li>
<li><strong>Studio:</strong> Requires Studio or Premier membership</li>
<li><strong>Premier:</strong> Requires Premier membership</li>
</ul>

<p>If you don\'t have the required membership level, you\'ll see a message indicating which level is needed to access the event.</p>

<h2>Adding Events to Your Calendar</h2>
<p>When viewing event details, you can add events to your personal calendar:</p>

<h3>Google Calendar</h3>
<ol>
<li>Click the <strong>"Add to Google"</strong> button on the event details page</li>
<li>Your Google Calendar will open with the event pre-filled</li>
<li>Click <strong>"Save"</strong> to add it to your calendar</li>
</ol>

<h3>iCal/iCalendar</h3>
<ol>
<li>Click the <strong>"Add to iCal"</strong> button on the event details page</li>
<li>The event file (.ics) will download</li>
<li>Open the file to add it to your calendar app (Apple Calendar, Outlook, etc.)</li>
</ol>

<p><strong>Note:</strong> Calendar links are available for events up to 12 hours after they start.</p>

<h2>Viewing the Full Calendar</h2>
<p>To see all upcoming events in a calendar view:</p>
<ol>
<li>Click the <strong>"View Full Calendar"</strong> button in the Events section header</li>
<li>You\'ll be taken to the full calendar page</li>
<li>Browse events by month, filter by type, or search for specific events</li>
</ol>

<h2>Event Timing and Visibility</h2>
<p>Understanding when events appear and disappear:</p>
<ul>
<li><strong>Events appear:</strong> Up to 365 days in advance</li>
<li><strong>Events remain visible:</strong> For 6 hours after the event ends (to account for timezone differences)</li>
<li><strong>Past events:</strong> Events older than 6 hours past their end time are removed from the upcoming list</li>
<li><strong>Timezone:</strong> All times are displayed in your local timezone</li>
</ul>

<h2>Event Resources</h2>
<p>Some events may include downloadable resources such as:</p>
<ul>
<li>Sheet music</li>
<li>Backing tracks</li>
<li>iReal Pro files</li>
<li>YouTube links</li>
<li>Other supporting materials</li>
</ul>

<p>These resources are typically available after the event or in the event details page if you have access to the event.</p>

<h2>Best Practices</h2>
<ul>
<li><strong>Check Regularly:</strong> Visit the Events tab regularly to see new events being added</li>
<li><strong>Add to Calendar:</strong> Use the calendar download feature to never miss an event you want to attend</li>
<li><strong>Register Early:</strong> For events requiring registration, sign up early to secure your spot</li>
<li><strong>Check Membership:</strong> Understand which events your membership level gives you access to</li>
<li><strong>View Full Calendar:</strong> Use the full calendar view to see more events and plan ahead</li>
<li><strong>Attend Live:</strong> Live events offer the best experience with real-time interaction</li>
</ul>

<h2>Troubleshooting</h2>

<p><strong>Q: I don\'t see any events. Why?</strong><br>
A: This could mean:
<ul>
<li>There are no upcoming events scheduled for your membership level</li>
<li>You\'re not logged in (some events require login)</li>
<li>Try checking the full calendar page for a broader view</li>
</ul>
</p>

<p><strong>Q: I can\'t access an event. What should I do?</strong><br>
A: Check:
<ul>
<li>Your membership level meets the event requirements</li>
<li>You\'re logged in with the correct account</li>
<li>If registration is required, make sure you\'ve registered</li>
<li>Check the event details page for specific access instructions</li>
</ul>
</p>

<p><strong>Q: Can I attend events if I\'m not a member?</strong><br>
A: Free events are available to all logged-in users. For Studio or Premier events, you\'ll need the appropriate membership level.</p>

<p><strong>Q: Will I get reminders about events?</strong><br>
A: If you register for an event or add it to your calendar, you\'ll receive email notifications. Adding events to your calendar also gives you calendar reminders based on your calendar app settings.</p>

<p><strong>Q: Can I watch event recordings?</strong><br>
A: Some events may have recordings available after they occur. Check the event details page after the event to see if recordings are available.</p>

<p><strong>Q: How do I join a live event?</strong><br>
A: When it\'s time for the event:
<ul>
<li>Click the "Join" button on the event (if available)</li>
<li>Or use the Zoom link provided in the event details</li>
<li>Some events may require you to register first and receive a join link via email</li>
</ul>
</p>

<p><strong>Q: What timezone are events shown in?</strong><br>
A: All event times are automatically converted to your local timezone based on your browser settings.</p>

<p><strong>Q: Can I see past events?</strong><br>
A: Events remain visible for 6 hours after they end. After that, they\'re removed from the upcoming events list. Check the event details page or recordings section for access to past event content.</p>',  -- post_content
    'Understanding Upcoming Events',  -- post_title
    'Learn how to view, access, and participate in upcoming events on your Practice Dashboard. Discover live classes, workshops, coaching sessions, and more.',  -- post_excerpt
    'publish',  -- post_status
    'closed',  -- comment_status
    'closed',  -- ping_status
    '',  -- post_password
    'understanding-upcoming-events',  -- post_name (slug)
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

-- Link the post to category term_id 32
-- First, get the term_taxonomy_id for category 32
SET @term_taxonomy_id = (
    SELECT `term_taxonomy_id` 
    FROM `wp_term_taxonomy` 
    WHERE `term_id` = 32 
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

