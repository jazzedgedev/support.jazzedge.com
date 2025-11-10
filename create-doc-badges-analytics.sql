-- ============================================================================
-- Badges and Analytics Documentation - Complete SQL
-- WordPress table prefix: wp_
-- Category term_id: 233
-- 
-- This file creates 2 documentation posts:
-- 1. Understanding Badges: Earning Achievements and Rewards
-- 2. Understanding Analytics: Tracking Progress and AI Analysis
-- ============================================================================

-- ============================================================================
-- DOCUMENT 1: Understanding Badges: Earning Achievements and Rewards
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
<p>Badges are achievement rewards you earn by completing various practice milestones and goals. They provide motivation, recognition, and valuable rewards as you progress through your musical journey.</p>

<h2>Where to Find Badges</h2>
<p>View your badges on the Practice Dashboard:</p>
<ol>
<li>Navigate to your Practice Dashboard</li>
<li>Click on the <strong>"Badges"</strong> tab in the navigation menu</li>
<li>You\'ll see all available badges organized by category</li>
</ol>

<h2>Understanding Badge Status</h2>
<p>Each badge has one of two statuses:</p>

<h3>Earned Badges</h3>
<p>Badges you\'ve already earned show:</p>
<ul>
<li>Green checkmark icon (✓)</li>
<li>"Earned" status indicator</li>
<li>Completed badge icon (colored and visible)</li>
<li>Date when you earned the badge</li>
<li>Full description and rewards</li>
</ul>

<h3>Locked Badges</h3>
<p>Badges you haven\'t earned yet show:</p>
<ul>
<li>Grey padlock icon</li>
<li>"Locked" status indicator</li>
<li>Greyed-out badge icon</li>
<li>Requirements clearly displayed</li>
<li>Rewards you\'ll receive when earned</li>
</ul>

<h2>Badge Categories</h2>
<p>Badges are organized into several categories, each representing a different aspect of your practice journey:</p>

<h3>🌟 First Steps</h3>
<p><strong>Description:</strong> Your journey begins here</p>
<p>These badges recognize your early achievements:</p>
<ul>
<li><strong>First Note:</strong> Complete your first practice session</li>
<li><strong>Getting Started:</strong> Complete multiple practice sessions</li>
<li><strong>Week Warrior:</strong> Maintain a 7-day practice streak</li>
</ul>
<p>These badges help you get started and build momentum in your practice routine.</p>

<h3>🔥 Streak Specialist</h3>
<p><strong>Description:</strong> Consistency is key</p>
<p>These badges reward consistent daily practice:</p>
<ul>
<li><strong>Week Warrior:</strong> 7-day streak</li>
<li><strong>Monthly Master:</strong> 30-day streak</li>
<li><strong>Legend:</strong> 100-day streak</li>
</ul>
<p>Maintaining your practice streak is essential for earning these badges. Use Streak Shields to protect your streak if needed.</p>

<h3>⭐ XP Collector</h3>
<p><strong>Description:</strong> Points and progress</p>
<p>These badges recognize your accumulated experience points:</p>
<ul>
<li><strong>XP Collector:</strong> Earn 1,000 XP</li>
<li><strong>XP Master:</strong> Earn 5,000 XP</li>
<li><strong>XP Legend:</strong> Earn 10,000+ XP</li>
</ul>
<p>Earn XP through practice sessions, improvements, and other achievements.</p>

<h3>⚔️ Session Warrior</h3>
<p><strong>Description:</strong> Practice makes perfect</p>
<p>These badges reward the number of practice sessions you\'ve completed:</p>
<ul>
<li><strong>Practice Regular:</strong> Complete 10 practice sessions</li>
<li><strong>Practice Pro:</strong> Complete 50 practice sessions</li>
<li><strong>Practice Master:</strong> Complete 100+ practice sessions</li>
</ul>
<p>Every logged practice session counts toward these milestones.</p>

<h3>🎯 Quality Over Quantity</h3>
<p><strong>Description:</strong> Deep focus sessions</p>
<p>These badges recognize focused, meaningful practice:</p>
<ul>
<li><strong>Century Club:</strong> Practice for 100 minutes total</li>
<li><strong>Marathon Player:</strong> Practice for 500 minutes total</li>
<li><strong>Improvement Champion:</strong> Report improvement multiple times</li>
</ul>
<p>These badges value consistent, quality practice over quick sessions.</p>

<h3>🏆 Level Achievements</h3>
<p>These badges recognize reaching specific levels:</p>
<ul>
<li><strong>Level 5 Achiever:</strong> Reach level 5</li>
<li><strong>Level 10 Master:</strong> Reach level 10</li>
<li><strong>Level 20 Legend:</strong> Reach level 20</li>
</ul>
<p>Levels are calculated from your total XP, so earning XP helps you reach these milestones.</p>

<h2>Understanding Badge Requirements</h2>
<p>Each badge has specific requirements you must meet to earn it:</p>

<h3>Session-Based Requirements</h3>
<p>Some badges require completing a certain number of practice sessions:</p>
<ul>
<li>Example: "Complete 10 practice sessions"</li>
<li>Every logged session counts</li>
<li>Check your progress in the badges table</li>
</ul>

<h3>Streak-Based Requirements</h3>
<p>Some badges require maintaining practice streaks:</p>
<ul>
<li>Example: "Maintain a 7-day streak"</li>
<li>Must practice consecutive days</li>
<li>Use Streak Shields to protect your streak</li>
</ul>

<h3>XP-Based Requirements</h3>
<p>Some badges require earning a certain amount of XP:</p>
<ul>
<li>Example: "Earn 1,000 XP"</li>
<li>XP is earned from practice sessions, improvements, and other achievements</li>
<li>Check your current XP in your dashboard stats</li>
</ul>

<h3>Level-Based Requirements</h3>
<p>Some badges require reaching specific levels:</p>
<ul>
<li>Example: "Reach level 10"</li>
<li>Levels are calculated from your total XP</li>
<li>Continue earning XP to level up</li>
</ul>

<h3>Time-Based Requirements</h3>
<p>Some badges require practicing for a certain total time:</p>
<ul>
<li>Example: "Practice for 100 minutes total"</li>
<li>Total minutes from all sessions count</li>
<li>Longer sessions accumulate faster</li>
</ul>

<h2>Badge Rewards</h2>
<p>Earning badges provides valuable rewards:</p>

<h3>Experience Points (XP)</h3>
<p>Most badges award XP:</p>
<ul>
<li>XP helps you level up</li>
<li>Higher levels unlock new features</li>
<li>XP amounts vary by badge difficulty</li>
<li>Rarer badges typically award more XP</li>
</ul>

<h3>Gems</h3>
<p>Many badges also award gems:</p>
<ul>
<li>Gems are virtual currency</li>
<li>Use gems to purchase Streak Shields</li>
<li>Use gems to repair broken streaks</li>
<li>Gems can be earned through badges and practice</li>
</ul>

<h3>Reward Examples</h3>
<p>Typical badge rewards:</p>
<ul>
<li><strong>Common Badges:</strong> 25-50 XP, 5-25 Gems</li>
<li><strong>Uncommon Badges:</strong> 50-100 XP, 25-50 Gems</li>
<li><strong>Rare Badges:</strong> 200-500 XP, 100-150 Gems</li>
<li><strong>Legendary Badges:</strong> 1000+ XP, 200+ Gems</li>
</ul>

<h2>Tracking Your Progress</h2>
<p>The badges section shows your progress toward earning badges:</p>

<h3>Category Progress</h3>
<p>Each category shows:</p>
<ul>
<li>Number of badges earned (e.g., "3 / 5 earned")</li>
<li>Progress bar showing completion percentage</li>
<li>Visual indicator of your advancement</li>
</ul>

<h3>Badge Table</h3>
<p>The badge table displays:</p>
<ul>
<li><strong>Status:</strong> Earned or Locked</li>
<li><strong>Badge:</strong> Icon, name, and description</li>
<li><strong>Requirement:</strong> What you need to do</li>
<li><strong>Rewards:</strong> XP and Gems you\'ll receive</li>
</ul>

<h3>Visual Indicators</h3>
<p>Earned badges:</p>
<ul>
<li>Show with full color</li>
<li>Display checkmark</li>
<li>Include earned date</li>
<li>Highlight your achievements</li>
</ul>

<p>Locked badges:</p>
<ul>
<li>Show greyed out</li>
<li>Display padlock icon</li>
<li>Show requirements clearly</li>
<li>Motivate you to earn them</li>
</ul>

<h2>How Badges Are Earned</h2>
<p>Badges are earned automatically when you meet their requirements:</p>

<h3>Automatic Awarding</h3>
<p>When you complete a requirement:</p>
<ul>
<li>The badge is automatically awarded</li>
<li>You\'ll see a notification</li>
<li>The badge appears in your collection</li>
<li>XP and Gems are added to your account</li>
</ul>

<h3>Check Your Progress</h3>
<p>To see how close you are to earning a badge:</p>
<ul>
<li>Visit the Badges tab</li>
<li>Review locked badges</li>
<li>Read the requirements</li>
<li>Track your progress</li>
</ul>

<h3>Focus Areas</h3>
<p>Different badges reward different practice habits:</p>
<ul>
<li><strong>Consistency:</strong> Focus on streaks</li>
<li><strong>Volume:</strong> Focus on sessions</li>
<li><strong>Quality:</strong> Focus on improvement</li>
<li><strong>Longevity:</strong> Focus on XP and levels</li>
</ul>

<h2>Tips for Earning Badges</h2>

<h3>Practice Consistently</h3>
<p>Regular practice helps you earn multiple badge types:</p>
<ul>
<li>Maintain your practice streak</li>
<li>Log sessions regularly</li>
<li>Focus on improvement</li>
<li>Build long-term habits</li>
</ul>

<h3>Focus on Requirements</h3>
<p>Target specific badges:</p>
<ul>
<li>Read badge requirements</li>
<li>Set goals for specific badges</li>
<li>Track your progress</li>
<li>Celebrate achievements</li>
</ul>

<h3>Use Multiple Strategies</h3>
<p>Different badges reward different approaches:</p>
<ul>
<li>Mix short and long sessions</li>
<li>Focus on quality practice</li>
<li>Report improvements honestly</li>
<li>Maintain consistency</li>
</ul>

<h2>Badge Rarity</h2>
<p>Badges have different rarity levels:</p>

<h3>Common Badges</h3>
<p>Easier to earn, lower rewards:</p>
<ul>
<li>First practice session</li>
<li>Early milestones</li>
<li>Smaller achievements</li>
</ul>

<h3>Uncommon Badges</h3>
<p>Moderate difficulty, good rewards:</p>
<ul>
<li>Week streaks</li>
<li>Multiple sessions</li>
<li>Moderate XP milestones</li>
</ul>

<h3>Rare Badges</h3>
<p>Challenging to earn, significant rewards:</p>
<ul>
<li>Month-long streaks</li>
<li>High session counts</li>
<li>Major XP milestones</li>
</ul>

<h3>Legendary Badges</h3>
<p>Most difficult, highest rewards:</p>
<ul>
<li>100+ day streaks</li>
<li>Hundreds of sessions</li>
<li>Thousands of XP</li>
</ul>

<h2>Frequently Asked Questions</h2>

<p><strong>Q: Do I need to do anything special to earn badges?</strong><br>
A: No, badges are awarded automatically when you meet their requirements. Just practice regularly!</p>

<p><strong>Q: Can I see how close I am to earning a badge?</strong><br>
A: Yes, check the Badges tab to see locked badges and their requirements. Compare with your current stats.</p>

<p><strong>Q: What happens if I already meet a requirement?</strong><br>
A: The badge should be automatically awarded. If not, continue practicing and it should sync.</p>

<p><strong>Q: Do badges expire?</strong><br>
A: No, once you earn a badge, it\'s yours permanently. They don\'t expire or get removed.</p>

<p><strong>Q: Can I earn multiple badges at once?</strong><br>
A: Yes! Completing a practice session can earn multiple badges if you meet several requirements simultaneously.</p>

<p><strong>Q: Are there badges for specific types of practice?</strong><br>
A: Yes, some badges recognize specific practice types, improvements, or achievements. Check the badge categories for details.</p>

<p><strong>Q: Do badges help me level up?</strong><br>
A: Yes! Badges award XP, which directly contributes to your level. Earning badges is a great way to level up faster.</p>

<p><strong>Q: What should I do if a badge doesn\'t appear after meeting requirements?</strong><br>
A: Try refreshing the page or logging another practice session. If it still doesn\'t appear, contact support.</p>',
    'Understanding Badges: Earning Achievements and Rewards',
    'Learn how badges work, what categories exist, how to earn them, and what rewards they provide to enhance your practice journey.',
    'publish',
    'closed',
    'closed',
    '',
    'understanding-badges-earning-achievements-rewards',
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
SET @term_taxonomy_id = (SELECT `term_taxonomy_id` FROM `wp_term_taxonomy` WHERE `term_id` = 233 AND `taxonomy` = 'jazzedge_doc_category' LIMIT 1);
INSERT INTO `wp_term_relationships` (`object_id`, `term_taxonomy_id`, `term_order`) VALUES (@doc_post_id, @term_taxonomy_id, 0) ON DUPLICATE KEY UPDATE `term_order` = 0;
UPDATE `wp_term_taxonomy` SET `count` = `count` + 1 WHERE `term_taxonomy_id` = @term_taxonomy_id;
INSERT INTO `wp_postmeta` (`post_id`, `meta_key`, `meta_value`) VALUES (@doc_post_id, '_jazzedge_doc_featured', 'no') ON DUPLICATE KEY UPDATE `meta_value` = 'no';

-- ============================================================================
-- DOCUMENT 2: Understanding Analytics: Tracking Progress and AI Analysis
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
<p>The Analytics section provides detailed insights into your practice habits, progress, and performance. Most importantly, it includes AI-powered analysis that gives personalized feedback and recommendations based on your practice data.</p>

<h2>Where to Find Analytics</h2>
<p>Access analytics on your Practice Dashboard:</p>
<ol>
<li>Navigate to your Practice Dashboard</li>
<li>Click on the <strong>"Analytics"</strong> tab in the navigation menu</li>
<li>You\'ll see your practice statistics and AI analysis</li>
</ol>

<h2>What Analytics Shows</h2>
<p>The Analytics section displays:</p>

<h3>Practice Statistics</h3>
<p>Key metrics from your practice sessions:</p>
<ul>
<li><strong>Total Sessions:</strong> Number of practice sessions logged</li>
<li><strong>Total Practice Time:</strong> Total minutes practiced</li>
<li><strong>Average Session Length:</strong> Average minutes per session</li>
<li><strong>Average Sentiment:</strong> Your average mood rating (1-5 scale)</li>
<li><strong>Improvement Rate:</strong> Percentage of sessions where you reported improvement</li>
<li><strong>Most Frequent Practice Day:</strong> Day of week you practice most often</li>
<li><strong>Most Practiced Item:</strong> Your most frequently practiced item</li>
<li><strong>Current Level:</strong> Your current practice level</li>
<li><strong>Current Streak:</strong> Your current practice streak in days</li>
</ul>

<h3>Data Period</h3>
<p>Analytics typically show data from:</p>
<ul>
<li><strong>Last 30 Days:</strong> Most recent month of practice</li>
<li>Provides recent, relevant insights</li>
<li>Updates as you continue practicing</li>
<li>Focuses on current habits and trends</li>
</ul>

<h2>AI Analysis: Your Personal Practice Coach</h2>
<p>The most powerful feature of Analytics is the AI Analysis, which provides personalized feedback and recommendations.</p>

<h3>What Is AI Analysis?</h3>
<p>AI Analysis uses artificial intelligence to review your practice data and generate:</p>
<ul>
<li>Personalized insights about your practice habits</li>
<li>Recognition of your strengths</li>
<li>Identification of areas for improvement</li>
<li>Specific recommendations for next steps</li>
<li>Lesson recommendations tailored to your progress</li>
</ul>

<h3>How AI Analysis Works</h3>
<p>The AI analyzes your practice data from the last 30 days:</p>

<h4>Data Collection</h4>
<p>The system gathers:</p>
<ul>
<li>Session duration and frequency</li>
<li>Sentiment scores (how you felt about each session)</li>
<li>Improvement indicators</li>
<li>Practice item preferences</li>
<li>Day-of-week patterns</li>
<li>Your current level and streak</li>
</ul>

<h4>Data Analysis</h4>
<p>The AI processes this data to identify:</p>
<ul>
<li><strong>Trends:</strong> Patterns in your practice habits</li>
<li><strong>Strengths:</strong> What you\'re doing well</li>
<li><strong>Opportunities:</strong> Areas where you can improve</li>
<li><strong>Consistency:</strong> Regularity of your practice</li>
<li><strong>Progress:</strong> How you\'re advancing over time</li>
</ul>

<h4>Personalized Feedback</h4>
<p>Based on the analysis, the AI generates three paragraphs:</p>

<h5>1. STRENGTHS</h5>
<p>The first paragraph highlights:</p>
<ul>
<li>What you\'re doing well</li>
<li>Positive aspects of your practice</li>
<li>Achievements worth celebrating</li>
<li>Habits that are working</li>
</ul>

<h5>2. IMPROVEMENT AREAS</h5>
<p>The second paragraph discusses:</p>
<ul>
<li>Trends that could be improved</li>
<li>Areas where you might focus more</li>
<li>Patterns that could enhance your practice</li>
<li>Opportunities for growth</li>
</ul>

<h5>3. NEXT STEPS</h5>
<p>The third paragraph provides:</p>
<ul>
<li>Practical recommendations</li>
<li>Specific next actions to take</li>
<li>Lesson recommendations tailored to your progress</li>
<li>Actionable advice for improvement</li>
</ul>

<h3>Understanding AI Recommendations</h3>
<p>The AI may recommend specific lessons based on:</p>

<h4>Your Practice Patterns</h4>
<p>If you practice certain types of material:</p>
<ul>
<li>The AI recognizes your interests</li>
<li>Recommends related lessons</li>
<li>Suggests complementary content</li>
</ul>

<h4>Your Current Level</h4>
<p>Based on your level:</p>
<ul>
<li>Recommendations match your skill level</li>
<li>Suggest appropriate challenges</li>
<li>Provide progressive learning paths</li>
</ul>

<h4>Your Improvement Areas</h4>
<p>If you report less improvement:</p>
<ul>
<li>The AI may suggest technique-focused lessons</li>
<li>Recommend foundational material</li>
<li>Guide you toward areas needing work</li>
</ul>

<h4>Lesson Recommendations</h4>
<p>The AI may recommend lessons from:</p>
<ul>
<li><strong>Technique:</strong> Jazzedge Practice Curriculum™</li>
<li><strong>Improvisation:</strong> The Confident Improviser™</li>
<li><strong>Accompaniment:</strong> Piano Accompaniment Essentials™</li>
<li><strong>Jazz Standards:</strong> Standards By The Dozen™</li>
<li><strong>Easy Standards:</strong> Super Simple Standards™</li>
</ul>

<h2>When AI Analysis Updates</h2>
<p>AI Analysis updates:</p>

<h3>Automatic Updates</h3>
<p>The analysis refreshes:</p>
<ul>
<li>As you log new practice sessions</li>
<li>Based on your most recent 30 days</li>
<li>When significant changes occur</li>
<li>To reflect your latest habits</li>
</ul>

<h3>Data Requirements</h3>
<p>For AI Analysis to work:</p>
<ul>
<li>You need practice sessions in the last 30 days</li>
<li>More sessions provide better insights</li>
<li>Regular practice improves recommendations</li>
<li>Varied practice data enhances analysis</li>
</ul>

<h3>If You Haven\'t Practiced</h3>
<p>If you haven\'t practiced in 30 days:</p>
<ul>
<li>The AI will encourage you to start practicing</li>
<li>Remind you of the benefits of regular practice</li>
<li>Suggest getting back into your routine</li>
<li>Provide motivation to begin again</li>
</ul>

<h2>Using Analytics to Improve</h2>
<p>Here\'s how to use Analytics effectively:</p>

<h3>Review Regularly</h3>
<p>Check your analytics:</p>
<ul>
<li>Weekly to track trends</li>
<li>After logging multiple sessions</li>
<li>Before planning practice goals</li>
<li>To understand your patterns</li>
</ul>

<h3>Read AI Recommendations</h3>
<p>Pay attention to:</p>
<ul>
<li>What the AI identifies as strengths</li>
<li>Areas for improvement</li>
<li>Specific next steps</li>
<li>Lesson recommendations</li>
</ul>

<h3>Act on Feedback</h3>
<p>Use insights to:</p>
<ul>
<li>Adjust your practice schedule</li>
<li>Focus on recommended areas</li>
<li>Try suggested lessons</li>
<li>Improve your habits</li>
</ul>

<h3>Track Progress</h3>
<p>Monitor changes:</p>
<ul>
<li>Compare analytics over time</li>
<li>See how recommendations change</li>
<li>Track improvement in metrics</li>
<li>Celebrate positive trends</li>
</ul>

<h2>Understanding Practice Statistics</h2>

<h3>Total Sessions</h3>
<p>This shows:</p>
<ul>
<li>How many practice sessions you\'ve logged</li>
<li>Your overall practice activity</li>
<li>Progress toward session-based badges</li>
<li>Consistency in practice</li>
</ul>

<h3>Total Practice Time</h3>
<p>This indicates:</p>
<ul>
<li>Cumulative minutes practiced</li>
<li>Your total time investment</li>
<li>Progress toward time-based badges</li>
<li>Overall commitment level</li>
</ul>

<h3>Average Session Length</h3>
<p>This reveals:</p>
<ul>
<li>Typical duration of your sessions</li>
<li>Whether sessions are consistent</li>
<li>If you prefer longer or shorter practice</li>
<li>Opportunities to adjust session length</li>
</ul>

<h3>Average Sentiment</h3>
<p>This measures:</p>
<ul>
<li>How you feel about your practice (1-5 scale)</li>
<li>Overall enjoyment and satisfaction</li>
<li>Whether practice is rewarding</li>
<li>If adjustments might improve satisfaction</li>
</ul>

<h3>Improvement Rate</h3>
<p>This shows:</p>
<ul>
<li>Percentage of sessions where you felt improvement</li>
<li>How often practice leads to progress</li>
<li>Whether practice is effective</li>
<li>If you\'re noticing growth</li>
</ul>

<h3>Most Frequent Practice Day</h3>
<p>This identifies:</p>
<ul>
<li>Which day you practice most often</li>
<li>Your preferred practice schedule</li>
<li>Potential scheduling patterns</li>
<li>Opportunities to add more practice days</li>
</ul>

<h3>Most Practiced Item</h3>
<p>This shows:</p>
<ul>
<li>What you practice most frequently</li>
<li>Your preferred practice material</li>
<li>Focus areas in your routine</li>
<li>Potential areas to diversify</li>
</ul>

<h2>Making the Most of AI Analysis</h2>

<h3>Use It as a Guide</h3>
<p>The AI analysis is:</p>
<ul>
<li>A helpful guide, not a requirement</li>
<li>Based on your actual data</li>
<li>Personalized to your situation</li>
<li>Designed to help you improve</li>
</ul>

<h3>Be Open to Suggestions</h3>
<p>Consider:</p>
<ul>
<li>Recommendations you might not have thought of</li>
<li>New lessons or approaches</li>
<li>Different practice strategies</li>
<li>Areas outside your comfort zone</li>
</ul>

<h3>Track Changes</h3>
<p>Notice how:</p>
<ul>
<li>Recommendations evolve as you practice</li>
<li>Different insights appear over time</li>
<li>Your patterns change</li>
<li>AI feedback adapts to your progress</li>
</ul>

<h2>Privacy and Data</h2>
<p>Analytics uses:</p>

<h3>Your Practice Data</h3>
<ul>
<li>Session logs you create</li>
<li>Sentiment scores you provide</li>
<li>Improvement indicators you report</li>
<li>Progress data from your account</li>
</ul>

<h3>Secure Processing</h3>
<ul>
<li>Data is processed securely</li>
<li>AI analysis is generated automatically</li>
<li>Your privacy is protected</li>
<li>Data is used only for your benefit</li>
</ul>

<h2>Frequently Asked Questions</h2>

<p><strong>Q: How often does AI Analysis update?</strong><br>
A: AI Analysis updates based on your most recent practice sessions. It analyzes your last 30 days of practice data.</p>

<p><strong>Q: What if I don\'t see AI Analysis?</strong><br>
A: You need at least some practice sessions logged in the last 30 days. Start logging sessions to see AI insights.</p>

<p><strong>Q: Are AI recommendations required?</strong><br>
A: No, they\'re suggestions to help guide your practice. Use them as helpful guidance, not requirements.</p>

<p><strong>Q: Can I see analytics for longer periods?</strong><br>
A: Currently, analytics focus on the last 30 days to provide recent, actionable insights.</p>

<p><strong>Q: Does the AI know what I\'m practicing?</strong><br>
A: Yes, if you log practice sessions with specific items, the AI can see which items you practice most.</p>

<p><strong>Q: How accurate is the AI analysis?</strong><br>
A: The AI analyzes your actual practice data, so insights are based on your real habits and patterns.</p>

<p><strong>Q: Can I improve my AI recommendations?</strong><br>
A: Yes! More practice sessions, honest sentiment reporting, and varied practice improve the quality of recommendations.</p>

<p><strong>Q: What if I disagree with the AI analysis?</strong><br>
A: That\'s okay! The AI provides suggestions based on data patterns. Use what\'s helpful and trust your own judgment.</p>

<p><strong>Q: Does AI Analysis cost anything?</strong><br>
A: No, AI Analysis is included as part of your Practice Dashboard features.</p>

<p><strong>Q: How detailed is the AI analysis?</strong><br>
A: The AI provides three paragraphs covering your strengths, improvement areas, and next steps with specific recommendations.</p>',
    'Understanding Analytics: Tracking Progress and AI Analysis',
    'Learn how Analytics works, what statistics it tracks, and how AI Analysis provides personalized feedback and recommendations for your practice.',
    'publish',
    'closed',
    'closed',
    '',
    'understanding-analytics-tracking-progress-ai-analysis',
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
SET @term_taxonomy_id = (SELECT `term_taxonomy_id` FROM `wp_term_taxonomy` WHERE `term_id` = 233 AND `taxonomy` = 'jazzedge_doc_category' LIMIT 1);
INSERT INTO `wp_term_relationships` (`object_id`, `term_taxonomy_id`, `term_order`) VALUES (@doc_post_id, @term_taxonomy_id, 0) ON DUPLICATE KEY UPDATE `term_order` = 0;
UPDATE `wp_term_taxonomy` SET `count` = `count` + 1 WHERE `term_taxonomy_id` = @term_taxonomy_id;
INSERT INTO `wp_postmeta` (`post_id`, `meta_key`, `meta_value`) VALUES (@doc_post_id, '_jazzedge_doc_featured', 'no') ON DUPLICATE KEY UPDATE `meta_value` = 'no';

