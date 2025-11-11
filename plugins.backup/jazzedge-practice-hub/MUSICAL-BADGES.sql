-- JazzEdge Practice Hub - Musical Badges SQL
-- Categories: Technique, Theory, Performance, Practice, Community
-- Economy: XP ranges 25-2000, Gems 3-200 (maintaining current scale)

-- TECHNIQUE BADGES (Focus on musical skills)
INSERT INTO wp_jph_badges (badge_key, name, description, image_url, category, rarity, xp_reward, gem_reward, criteria_type, criteria_value, is_active, display_order, created_at, updated_at) VALUES
('scales_master', 'Scales Master', 'Practice scales 50 times', '', 'technique', 'uncommon', 175, 15, 'scales_practice', 50, 1, 10, NOW(), NOW()),
('chord_wizard', 'Chord Wizard', 'Practice chord progressions 30 times', '', 'technique', 'uncommon', 150, 12, 'chords_practice', 30, 1, 11, NOW(), NOW()),
('finger_speed', 'Finger Speed', 'Practice fast passages 20 times', '', 'technique', 'rare', 225, 25, 'fast_passages', 20, 1, 12, NOW(), NOW()),
('rhythm_king', 'Rhythm King', 'Practice rhythm exercises 40 times', '', 'technique', 'uncommon', 125, 10, 'rhythm_practice', 40, 1, 13, NOW(), NOW()),
('arpeggio_ace', 'Arpeggio Ace', 'Master arpeggios 25 times', '', 'technique', 'uncommon', 140, 12, 'arpeggios_practice', 25, 1, 14, NOW(), NOW()),
('dynamics_maestro', 'Dynamics Maestro', 'Practice dynamics contrast 35 times', '', 'technique', 'rare', 180, 20, 'dynamics_practice', 35, 1, 15, NOW(), NOW()),

-- THEORY BADGES (Music theory knowledge)
INSERT INTO wp_jph_badges (badge_key, name, description, image_url, category, rarity, xp_reward, gem_reward, criteria_type, criteria_value, is_active, display_order, created_at, updated_at) VALUES
('theory_student', 'Theory Student', 'Study music theory 15 times', '', 'theory', 'common', 75, 8, 'theory_study', 15, 1, 20, NOW(), NOW()),
('harmony_hero', 'Harmony Hero', 'Practice harmonic progressions 25 times', '', 'theory', 'uncommon', 160, 18, 'harmony_practice', 25, 1, 21, NOW(), NOW()),
('ear_training', 'Ear Training', 'Complete ear training exercises 40 times', '', 'theory', 'uncommon', 190, 22, 'ear_training', 40, 1, 22, NOW(), NOW()),
('modal_explorer', 'Modal Explorer', 'Practice modal scales 20 times', '', 'theory', 'rare', 200, 25, 'modal_practice', 20, 1, 23, NOW(), NOW()),
('circle_progressions', 'Circle of Progressions', 'Master circle of fifths practice 15 times', '', 'theory', 'rare', 220, 28, 'circle_progressions', 15, 1, 24, NOW(), NOW()),
('transposition_pro', 'Transposition Pro', 'Practice transpositions 30 times', '', 'theory', 'uncommon', 135, 15, 'transposition', 30, 1, 25, NOW(), NOW()),

-- PERFORMANCE BADGES (Playing and expression)
INSERT INTO wp_jph_badges (badge_key, name, description, image_url, category, rarity, xp_reward, gem_reward, criteria_type, criteria_value, is_active, display_order, created_at, updated_at) VALUES
('solo_performer', 'Solo Performer', 'Practice solo pieces 20 times', '', 'performance', 'uncommon', 165, 18, 'solo_practice', 20, 1, 30, NOW(), NOW()),
('ensemble_player', 'Ensemble Player', 'Practice ensemble pieces 25 times', '', 'performance', 'uncommon', 155, 15, 'ensemble_practice', 25, 1, 31, NOW(), NOW()),
('improvisation', 'Improvisation', 'Practice improvisation 30 times', '', 'performance', 'rare', 210, 30, 'improvisation', 30, 1, 32, NOW(), NOW()),
('stage_presence', 'Stage Presence', 'Practice performance posture 18 times', '', 'performance', 'common', 95, 8, 'stage_practice', 18, 1, 33, NOW(), NOW()),
('memory_master', 'Memory Master', 'Play pieces from memory 12 times', '', 'performance', 'rare', 245, 35, 'memory_performance', 12, 1, 34, NOW(), NOW()),
('expression_art', 'Expression Art', 'Focus on musical expression 35 times', '', 'performance', 'uncommon', 185, 20, 'expression_practice', 35, 1, 35, NOW(), NOW()),

-- PRACTICE MILESTONE BADGES (Based on our strategy document)
INSERT INTO wp_jph_badges (badge_key, name, description, image_url, category, rarity, xp_reward, gem_reward, criteria_type, criteria_value, is_active, display_order, created_at, updated_at) VALUES
('dedicated_musician', 'Dedicated Musician', 'Complete 25 practice sessions', '', 'milestone', 'uncommon', 100, 10, 'practice_sessions_25', 25, 1, 40, NOW(), NOW()),
('committed_artist', 'Committed Artist', 'Complete 50 practice sessions', '', 'milestone', 'uncommon', 250, 25, 'practice_sessions_50', 50, 1, 41, NOW(), NOW()),
('devoted_practitioner', 'Devoted Practitioner', 'Complete 100 practice sessions', '', 'milestone', 'rare', 500, 50, 'practice_sessions_100', 100, 1, 42, NOW(), NOW()),
('practice_master', 'Practice Master', 'Complete 250 practice sessions', '', 'milestone', 'rare', 1000, 100, 'practice_sessions_250', 250, 1, 43, NOW(), NOW()),
('virtuoso_practitioner', 'Virtuoso Practitioner', 'Complete 500 practice sessions', '', 'milestone', 'legendary', 2000, 200, 'practice_sessions_500', 500, 1, 44, NOW(), NOW()),

-- TIME-BASED PRACTICE BADGES
INSERT INTO wp_jph_badges (badge_key, name, description, image_url, category, rarity, xp_reward, gem_reward, criteria_type, criteria_value, is_active, display_order, created_at, updated_at) VALUES
('micro_practice', 'Micro Practice', 'Complete 10 sessions of 15+ minutes', '', 'time', 'common', 75, 5, 'session_duration_15', 10, 1, 50, NOW(), NOW()),
('deep_focus', 'Deep Focus', 'Complete 5 sessions of 45+ minutes', '', 'time', 'uncommon', 125, 12, 'session_duration_45', 5, 1, 51, NOW(), NOW()),
('marathon_player', 'Marathon Player', 'Complete 3 sessions of 90+ minutes', '', 'time', 'rare', 275, 35, 'session_duration_90', 3, 1, 52, NOW(), NOW()),
('morning_melody', 'Morning Melody', 'Practice before 7 AM (5 times)', '', 'time', 'common', 80, 8, 'morning_practice', 5, 1, 53, NOW(), NOW()),
('night_notes', 'Night Notes', 'Practice after 10 PM (5 times)', '', 'time', 'common', 80, 8, 'evening_practice', 5, 1, 54, NOW(), NOW()),

-- SKILL DEVELOPMENT BADGES
INSERT INTO wp_jph_badges (badge_key, name, description, image_url, category, rarity, xp_reward, gem_reward, criteria_type, criteria_value, is_active, display_order, created_at, updated_at) VALUES
('breakthrough_moment', 'Breakthrough Moment', 'Report musical improvement 25 times', '', 'skill', 'uncommon', 175, 20, 'musical_improvement', 25, 1, 60, NOW(), NOW()),
('consistent_growth', 'Consistent Growth', 'Report improvement 5 days in a row', '', 'skill', 'rare', 220, 25, 'improvement_streak', 5, 1, 61, NOW(), NOW()),
('genre_explorer', 'Genre Explorer', 'Practice 5 different musical styles in one week', '', 'skill', 'uncommon', 145, 15, 'genre_variety', 5, 1, 62, NOW(), NOW()),
('specialist_focus', 'Specialist Focus', 'Practice same piece 20 times', '', 'skill', 'uncommon', 160, 18, 'piece_mastery', 20, 1, 63, NOW(), NOW()),
('perfection_seeker', 'Perfection Seeker', 'Report "excellent" playing 15 times', '', 'skill', 'rare', 195, 25, 'excellent_sessions', 15, 1, 64, NOW(), NOW()),

-- ENHANCED STREAK BADGES (New musical naming)
INSERT INTO wp_jph_badges (badge_key, name, description, image_url, category, rarity, xp_reward, gem_reward, criteria_type, criteria_value, is_active, display_order, created_at, updated_at) VALUES
('getting_started', 'Getting Started', 'Practice 3 days in a row', '', 'streak', 'common', 60, 8, 'streak_3', 3, 1, 70, NOW(), NOW()),
('building_momentum', 'Building Momentum', 'Practice 5 days in a row', '', 'streak', 'common', 110, 15, 'streak_5', 5, 1, 71, NOW(), NOW()),
('hot_streak', 'Hot Streak', 'Practice 7 days in a row', '', 'streak', 'uncommon', 150, 18, 'streak_7', 7, 1, 72, NOW(), NOW()),
('unstoppable', 'Unstoppable', 'Practice 14 days in a row', '', 'streak', 'rare', 300, 30, 'streak_14', 14, 1, 73, NOW(), NOW()),
('lightning_fast', 'Lightning Fast', 'Practice 30 days in a row', '', 'streak', 'rare', 500, 50, 'streak_30', 30, 1, 74, NOW(), NOW()),
('musical_legend', 'Musical Legend', 'Practice 100 days in a row', '', 'streak', 'legendary', 1000, 100, 'streak_100', 100, 1, 75, NOW(), NOW()),
('immortal_practitioner', 'Immortal Practitioner', 'Practice 365 days in a row', '', 'streak', 'legendary', 2500, 250, 'streak_365', 365, 1, 76, NOW(), NOW()),

-- COMMUNITY BADGES (Phase 3 - Musical community focus)
INSERT INTO wp_jph_badges (badge_key, name, description, image_url, category, rarity, xp_reward, gem_reward, criteria_type, criteria_value, is_active, display_order, created_at, updated_at) VALUES
('practice_pioneer', 'Practice Pioneer', 'Complete 50 practice sessions', '', 'community', 'uncommon', 200, 25, 'practice_sessions_50', 50, 1, 80, NOW(), NOW()),
('practice_champion', 'Practice Champion', 'Complete 100 practice sessions', '', 'community', 'rare', 300, 40, 'practice_sessions_100', 100, 1, 81, NOW(), NOW()),
('practice_legend', 'Practice Legend', 'Complete 250 practice sessions', '', 'community', 'rare', 500, 70, 'practice_sessions_250', 250, 1, 82, NOW(), NOW()),
('practice_warrior', 'Practice Warrior', 'Complete 500 practice sessions', '', 'community', 'legendary', 1000, 120, 'practice_sessions_500', 500, 1, 83, NOW(), NOW()),
('streak_master', 'Streak Master', 'Achieve 30-day streak', '', 'community', 'rare', 400, 50, 'streak_30', 30, 1, 84, NOW(), NOW()),
('streak_legend', 'Streak Legend', 'Achieve 100-day streak', '', 'community', 'legendary', 800, 80, 'streak_100', 100, 1, 85, NOW(), NOW()),
('marathon_champion', 'Marathon Champion', 'Complete 10+ marathon sessions in one month', '',:**

**Music Maestro', 'Music Maestro', 'Complete 25+ marathon sessions in one month', '', 'community', 'legendary', 600, 60, 'marathon_sessions_25', 25, 1, 87, NOW(), NOW()),
('consistency_royalty', 'Consistency Royalty', 'Practice every day for 30 days', '', 'community', 'rare', 450, 55, 'perfect_month', 30, 1, 88, NOW(), NOW()),
('musical_scholar', 'Musical Scholar', 'Complete 100 sessions with 80%+ improvement', '', 'community', 'rare', 375, 45, 'quality_milestone', 100, 1, 89, NOW(), NOW()),
('time_master', 'Time Master', 'Practice 100+ hours total', '', 'community', 'legendary', 700, 85, 'hours_milestone', 6000, 1, 90, NOW(), NOW());

-- SEASONAL BADGES (Calendar-based musical events)
INSERT INTO wp_jph_badges (badge_key, name, description, image_url, category, rarity, xp_reward, gem_reward, criteria_type, criteria_value, is_active, display_order, created_at, updated_at) VALUES
('resolution_rhythm', 'Resolution Rhythm', 'Practice on January 1st', '', 'special', 'common', 100, 10, 'new_year_practice', 1, 1, 100, NOW(), NOW()),
('summer_sessions', 'Summer Sessions', 'Practice 20 days during summer months', '', 'special', 'uncommon', 200, 20, 'summer_practice', 20, 1, 101, NOW(), NOW()),
('holiday_hero', 'Holiday Hero', 'Practice on 3 major holidays', '', 'special', 'rare', 250, 25, 'holiday_practice', 3, 1, 102, NOW(), NOW()),
('birthday_bach', 'Birthday Bach', 'Practice on your birthday', '', 'special', 'common', 120, 12, 'birthday_practice', 1, 1, 103, NOW(), NOW()),
('anniversary_artist', 'Anniversary Artist', 'Practice on JazzEdge Academy anniversary', '', 'special', 'rare', 400, 40, 'platform_anniversary', 1, 1, 104, NOW(), NOW());

-- GENRE-SPECIFIC BADGES (Various musical styles)
INSERT INTO wp_jph_badges (badge_key, name, description, image_url, category, rarity, xp_reward, gem_reward, criteria_type, criteria_value, is_active, display_order, created_at, updated_at) VALUES
('blues_blaster', 'Blues Blaster', 'Practice blues style 15 times', '', 'genre', 'uncommon', 150, 15, 'blues_practice', 15, 1, 110, NOW(), NOW()),
('classical_coltrane', 'Classical Coltrane', 'Practice classical pieces 20 times', '', 'genre', 'uncommon', 170, 18, 'classical_practice', 20, 1, 111, NOW(), NOW()),
('funk_fusion', 'Funk Fusion', 'Practice funk grooves 25 times', '', 'genre', 'uncommon', 160, 16, 'funk_practice', 25, 1, 112, NOW(), NOW()),
('latin_lover', 'Latin Lover', 'Practice Latin rhythms 18 times', '', 'genre', 'uncommon', 145, 14, 'latin_practice', 18, 1, 113, NOW(), NOW()),
('rock_rebel', 'Rock Rebel', 'Practice rock techniques 22 times', '', 'genre', 'uncommon', 155, 15, 'rock_practice', 22, 1, 114, NOW(), NOW());

-- LEGACY BADGES (Keep existing badges for compatibility)
-- These are already in the system, just documenting them here:
-- first_steps, marathon, rising_star, hot_streak (replacement), legend, lightning (replacement)

-- BADGE TEMPLATES FOR ADMIN USE
-- Technique Template:
-- INSERT INTO wp_jph_badges (badge_key, name, description, image_url, category, rarity, xp_reward, gem_reward, criteria_type, criteria_value, is_active, display_order, created_at, updated_at) 
-- VALUES ('[badge_key]', '[badge_name]', '[badge_description]', '', 'technique', '[rarity]', [xp_reward], [gem_reward], '[criteria_type]', [criteria_value], 1, [display_order], NOW(), NOW());

-- Theory Template:
-- INSERT INTO wp_jph_badges (badge_key, name, description, image_url, category, rarity, xp_reward, gem_reward, criteria_type, criteria_value, is_active, display_order, created_at, updated_at) 
-- VALUES ('[badge_key]', '[badge_name]', '[badge_description]', '', 'theory', '[rarity]', [xp_reward], [gem_reward], '[criteria_type]', [criteria_value], 1, [display_order], NOW(), NOW());

-- Performance Template:
-- INSERT INTO wp_jph_badges (badge_key, name, description, image_url, category, rarity, xp_reward, gem_reward, criteria_type, criteria_value, is_active, display_order, created_at, updated_at) 
-- VALUES ('[badge_key]', '[badge_name]', '[badge_description]', '', 'performance', '[rarity]', [xp_reward], [gem_reward], '[criteria_type]', [criteria_value], 1, [display_order], NOW(), NOW());

-- Milestone Template:
-- INSERT INTO wp_jph_badges (badge_key, name, description, image_url, category, rarity, xp_reward, gem_reward, criteria_type, criteria_value, is_active, display_order, created_at, updated_at) 
-- VALUES ('[badge_key]', '[badge_name]', '[badge_description]', '', 'milestone', '[rarity]', [xp_reward], [gem_reward], '[criteria_type]', [criteria_value], 1, [display_order], NOW(), NOW());

-- Streak Template:
-- INSERT INTO wp_jph_badges (badge_key, name, description, image_url, category, rarity, xp_reward, gem_reward, criteria_type, criteria_value, is_active, display_order, created_at, updated_at) 
-- VALUES ('[badge_key]', '[badge_name]', '[badge_description]', '', 'streak', '[rarity]', [xp_reward], [gem_reward], '[criteria_type]', [criteria_value], 1, [display_order], NOW(), NOW());
