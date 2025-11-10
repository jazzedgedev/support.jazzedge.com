-- Create documentation post for "Understanding Your Dashboard Stats: LEVEL, XP, STREAK, and GEMS"
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
<p>Your Practice Dashboard displays four key statistics that track your progress and engagement: <strong>LEVEL</strong>, <strong>XP</strong>, <strong>STREAK</strong>, and <strong>GEMS</strong>. These gamification elements help motivate consistent practice and reward your dedication to learning jazz piano.</p>

<h2>⭐ LEVEL</h2>
<p><strong>What it is:</strong> Your overall progress level, calculated from your total XP. Higher levels indicate more experience and dedication to practice.</p>

<p><strong>How it works:</strong></p>
<ul>
<li>Your level is automatically calculated from your total XP using an exponential formula</li>
<li>As you earn more XP, your level increases</li>
<li>Levels get progressively harder to reach as they increase</li>
<li>Level 1 starts at 0-99 XP, Level 2 at 100-399 XP, Level 3 at 400-899 XP, and so on</li>
</ul>

<p><strong>How to level up:</strong> Practice regularly! The more XP you earn through practice sessions, the faster you\'ll level up. Each level represents a milestone in your musical journey.</p>

<h2>💫 XP (Experience Points)</h2>
<p><strong>What it is:</strong> Experience points earned from your practice activities. XP is the foundation of your level progression.</p>

<p><strong>How you earn XP:</strong></p>
<ul>
<li><strong>Practice Sessions:</strong> Base XP is 1 point per minute of practice</li>
<li><strong>Sentiment Bonuses:</strong> How you feel about your practice session affects your XP:
    <ul>
        <li>😊 Excellent (5/5): 1.5x multiplier</li>
        <li>🙂 Good (4/5): 1.3x multiplier</li>
        <li>😐 Okay (3/5): 1.0x multiplier (base rate)</li>
        <li>😕 Challenging (2/5): 0.8x multiplier</li>
        <li>😞 Frustrating (1/5): 0.6x multiplier</li>
    </ul>
</li>
<li><strong>Improvement Bonus:</strong> When you report noticing improvement during practice, you get an additional 25% bonus XP</li>
<li><strong>JPC Completions:</strong> Completing steps in the Jazz Piano Curriculum earns 25 XP per key completed</li>
<li><strong>Badges:</strong> Earning various achievement badges also awards XP</li>
</ul>

<p><strong>Example:</strong> If you practice for 30 minutes and rate it as "Excellent" (5/5), you\'ll earn: 30 minutes × 1.5 = 45 XP. If you also noticed improvement, you\'d get an additional 25% bonus!</p>

<p><strong>Minimum XP:</strong> Every practice session earns at least 1 XP, even if it\'s a very short session.</p>

<h2>🔥 STREAK</h2>
<p><strong>What it is:</strong> Your consecutive days of practice. A streak shows your consistency and dedication to daily practice.</p>

<p><strong>How it works:</strong></p>
<ul>
<li>Practice at least once per day to maintain your streak</li>
<li>Your streak increases by 1 each day you practice</li>
<li><strong>Missing a day resets your streak to 0</strong></li>
<li>The system tracks your longest streak ever achieved</li>
<li>Streaks are based on calendar days, not 24-hour periods</li>
</ul>

<p><strong>Protecting your streak:</strong></p>
<ul>
<li><strong>Streak Shields:</strong> You can purchase Streak Shields with gems (50 gems each) to protect your streak from being broken</li>
<li>You can hold up to 3 Streak Shields at a time</li>
<li>Maximum of 5 shield purchases per month</li>
<li>Shields automatically activate if you miss a practice day</li>
</ul>

<p><strong>Streak Badges:</strong> Maintaining long streaks can earn you special badges:
<ul>
<li>Hot Streak: 7 days in a row</li>
<li>Lightning: 30 days in a row</li>
<li>Legend: 100 days in a row</li>
</ul>
</p>

<h2>💎 GEMS</h2>
<p><strong>What it is:</strong> Your practice currency that can be earned and spent on special features and rewards.</p>

<p><strong>How you earn GEMS:</strong></p>
<ul>
<li><strong>Badges:</strong> Many achievement badges award gems when earned</li>
<li><strong>JPC Completion:</strong> Completing all 12 keys in a Jazz Piano Curriculum earns 50 gems</li>
<li><strong>Practice Milestones:</strong> Various practice milestones and achievements award gems</li>
<li><strong>XP Milestones:</strong> Reaching certain XP thresholds can earn bonus gems</li>
</ul>

<p><strong>How you spend GEMS:</strong></p>
<ul>
<li><strong>Streak Shields:</strong> Purchase shields to protect your streak (50 gems each)</li>
<li><strong>Streak Repair:</strong> Use gems to repair a broken streak (cost varies by days missed)</li>
<li>More features may be added in the future for gem spending</li>
</ul>

<p><strong>Managing your gems:</strong></p>
<ul>
<li>Your gem balance is displayed prominently on your dashboard</li>
<li>Gems are earned through consistent practice and achievement</li>
<li>Plan your gem spending wisely - they\'re valuable for maintaining streaks!</li>
</ul>

<h2>How These Stats Work Together</h2>
<p>All four stats are interconnected:</p>
<ul>
<li><strong>Practice Sessions</strong> → Earn <strong>XP</strong> → Increase your <strong>LEVEL</strong></li>
<li><strong>Daily Practice</strong> → Maintain your <strong>STREAK</strong></li>
<li><strong>Achievements & Milestones</strong> → Earn <strong>GEMS</strong></li>
<li><strong>GEMS</strong> → Purchase <strong>Streak Shields</strong> → Protect your <strong>STREAK</strong></li>
<li><strong>Consistent Practice</strong> → Earn badges → Get bonus <strong>XP</strong> and <strong>GEMS</strong></li>
</ul>

<h2>Tips for Maximizing Your Stats</h2>
<ul>
<li><strong>Practice Daily:</strong> Even short sessions count toward your streak and earn XP</li>
<li><strong>Be Honest:</strong> Rate your practice sessions accurately - better sentiment ratings can earn more XP!</li>
<li><strong>Track Improvement:</strong> Report when you notice improvement to get bonus XP</li>
<li><strong>Longer Sessions:</strong> Longer practice sessions earn more XP (1 XP per minute)</li>
<li><strong>Complete Curriculum:</strong> Finishing JPC steps earns XP and completing all 12 keys earns gems</li>
<li><strong>Build Your Streak:</strong> Use gems to purchase Streak Shields if you\'re worried about missing a day</li>
<li><strong>Earn Badges:</strong> Focus on achieving badges for bonus XP and gems</li>
</ul>

<h2>Frequently Asked Questions</h2>

<p><strong>Q: Can I lose XP or levels?</strong><br>
A: No, XP and levels never decrease. They only increase as you practice more.</p>

<p><strong>Q: What happens if I miss a practice day?</strong><br>
A: Your streak resets to 0. However, if you have Streak Shields, one will automatically activate to protect your streak.</p>

<p><strong>Q: How many gems do I need for a Streak Shield?</strong><br>
A: Each Streak Shield costs 50 gems. You can hold up to 3 shields at a time.</p>

<p><strong>Q: Do I earn XP for just logging a session, or do I need to complete something?</strong><br>
A: You earn XP for logging practice sessions. The amount depends on duration, sentiment, and whether you noticed improvement.</p>

<p><strong>Q: Can I see my XP history or how much XP I need for the next level?</strong><br>
A: Your total XP is displayed on the dashboard. The level formula uses exponential growth, so higher levels require significantly more XP.</p>

<p><strong>Q: What\'s the fastest way to earn gems?</strong><br>
A: Completing badges, finishing JPC curriculum steps, and maintaining consistent practice are the best ways to earn gems.</p>',  -- post_content
    'Understanding Your Dashboard Stats: LEVEL, XP, STREAK, and GEMS',  -- post_title
    'Learn what LEVEL, XP, STREAK, and GEMS mean on your Practice Dashboard. Understand how to earn XP, maintain streaks, use gems, and level up through consistent practice.',  -- post_excerpt
    'publish',  -- post_status
    'closed',  -- comment_status
    'closed',  -- ping_status
    '',  -- post_password
    'understanding-your-dashboard-stats-level-xp-streak-and-gems',  -- post_name (slug)
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

