-- Update badge descriptions to be style-agnostic (remove jazz references)
-- Run this SQL in phpMyAdmin to update existing badges

UPDATE wp_jph_badges SET 
    description = 'You''ve started your musical journey!'
WHERE badge_key = 'first_note';

UPDATE wp_jph_badges SET 
    description = 'Three sessions down, a lifetime to go'
WHERE badge_key = 'getting_started';

UPDATE wp_jph_badges SET 
    description = 'A full week of dedication'
WHERE badge_key = 'week_warrior';

UPDATE wp_jph_badges SET 
    description = 'Your star is rising'
WHERE badge_key = 'rising_star';

UPDATE wp_jph_badges SET 
    description = 'Ten sessions of pure dedication'
WHERE badge_key = 'session_master';

UPDATE wp_jph_badges SET 
    description = 'Two weeks of unwavering commitment'
WHERE badge_key = 'consistency_counts';

UPDATE wp_jph_badges SET 
    description = 'A full month of practice perfection'
WHERE badge_key = 'monthly_maestro';

UPDATE wp_jph_badges SET 
    description = 'Fifty days of unbroken rhythm'
WHERE badge_key = 'dedication_defined';

UPDATE wp_jph_badges SET 
    description = 'One hundred days of excellence'
WHERE badge_key = 'streak_legend';

UPDATE wp_jph_badges SET 
    description = 'Six months without missing a beat'
WHERE badge_key = 'unstoppable';

UPDATE wp_jph_badges SET 
    description = 'A full year of dedication - legendary status'
WHERE badge_key = 'year_of_jazz';

UPDATE wp_jph_badges SET 
    description = 'Your first thousand points'
WHERE badge_key = 'xp_novice';

UPDATE wp_jph_badges SET 
    description = 'Growing stronger every day'
WHERE badge_key = 'xp_apprentice';

UPDATE wp_jph_badges SET 
    description = 'Five thousand points of progress'
WHERE badge_key = 'xp_journeyman';

UPDATE wp_jph_badges SET 
    description = 'Ten thousand points of mastery'
WHERE badge_key = 'xp_expert';

UPDATE wp_jph_badges SET 
    description = 'Twenty thousand points - you''re a master'
WHERE badge_key = 'xp_master';

UPDATE wp_jph_badges SET 
    description = 'Fifty thousand points of pure dedication'
WHERE badge_key = 'xp_virtuoso';

UPDATE wp_jph_badges SET 
    description = 'One hundred thousand points - legendary'
WHERE badge_key = 'xp_legend';

UPDATE wp_jph_badges SET 
    description = 'Twenty-five sessions of growth'
WHERE badge_key = 'practice_rookie';

UPDATE wp_jph_badges SET 
    description = 'Fifty sessions strong'
WHERE badge_key = 'practice_veteran';

UPDATE wp_jph_badges SET 
    description = 'One hundred sessions of dedication'
WHERE badge_key = 'practice_champion';

UPDATE wp_jph_badges SET 
    description = 'Two hundred sessions - you''re a hero'
WHERE badge_key = 'practice_hero';

UPDATE wp_jph_badges SET 
    description = 'Five hundred sessions of mastery'
WHERE badge_key = 'practice_legend';

UPDATE wp_jph_badges SET 
    description = 'One thousand sessions - immortal status'
WHERE badge_key = 'practice_immortal';

UPDATE wp_jph_badges SET 
    description = 'Your first long practice session'
WHERE badge_key = 'marathon_beginner';

UPDATE wp_jph_badges SET 
    description = 'Ten long sessions of focus'
WHERE badge_key = 'marathon_runner';

UPDATE wp_jph_badges SET 
    description = 'Twenty-five marathons of practice'
WHERE badge_key = 'marathon_master';

UPDATE wp_jph_badges SET 
    description = 'You came back stronger'
WHERE badge_key = 'comeback_kid';

UPDATE wp_jph_badges SET 
    description = 'Music sounds better after midnight'
WHERE badge_key = 'night_owl';

UPDATE wp_jph_badges SET 
    description = 'The early bird gets the groove'
WHERE badge_key = 'early_bird';

-- Update the badge name for "Year of Jazz" to be more generic
UPDATE wp_jph_badges SET 
    name = 'Year of Music',
    description = 'A full year of dedication - legendary status'
WHERE badge_key = 'year_of_jazz';
