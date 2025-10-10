-- Add FluentCRM event tracking fields to wp_jph_badges table
-- Run this SQL in phpMyAdmin to add the missing fields

ALTER TABLE wp_jph_badges 
ADD COLUMN fluentcrm_enabled TINYINT(1) DEFAULT 0 COMMENT 'Whether FluentCRM event tracking is enabled (0/1)',
ADD COLUMN fluentcrm_event_key VARCHAR(100) NULL COMMENT 'FluentCRM event key for this badge',
ADD COLUMN fluentcrm_event_title VARCHAR(255) NULL COMMENT 'FluentCRM event title for this badge';
