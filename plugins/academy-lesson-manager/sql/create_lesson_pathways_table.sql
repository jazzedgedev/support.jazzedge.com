-- SQL to create or update wp_alm_lesson_pathways table
-- This junction table allows lessons to belong to multiple pathways
-- for AI-powered lesson recommendations

-- Check if table exists, if not create it
CREATE TABLE IF NOT EXISTS `wp_alm_lesson_pathways` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `lesson_id` int(11) NOT NULL,
  `pathway` VARCHAR(100) NOT NULL,
  `pathway_rank` tinyint(1) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `unique_lesson_pathway` (`lesson_id`, `pathway`),
  KEY `lesson_id` (`lesson_id`),
  KEY `pathway` (`pathway`),
  KEY `pathway_rank` (`pathway_rank`),
  KEY `pathway_rank_composite` (`pathway`, `pathway_rank`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- If table already exists with ENUM column, convert it to VARCHAR:
-- ALTER TABLE `wp_alm_lesson_pathways` MODIFY COLUMN `pathway` VARCHAR(100) NOT NULL;

-- Example usage:
-- Insert a lesson into a pathway with rank:
-- INSERT INTO wp_alm_lesson_pathways (lesson_id, pathway, pathway_rank) VALUES (123, 'improvisation', 1);
-- 
-- Insert same lesson into multiple pathways:
-- INSERT INTO wp_alm_lesson_pathways (lesson_id, pathway, pathway_rank) VALUES (123, 'beginner_improv', 2);
-- INSERT INTO wp_alm_lesson_pathways (lesson_id, pathway, pathway_rank) VALUES (123, 'chord_voicings', 3);
--
-- Query lessons by pathway, ordered by rank:
-- SELECT l.* FROM wp_alm_lessons l
-- INNER JOIN wp_alm_lesson_pathways lp ON l.ID = lp.lesson_id
-- WHERE lp.pathway = 'improvisation'
-- ORDER BY lp.pathway_rank ASC;

