<?php
/**
 * Modals template for JE Event Manager
 * Included in admin footer on posts list page
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<!-- Bulk Copy Event Modal -->
<div id="jeem-bulk-copy-event-modal" class="jeem-modal" style="display: none;">
    <div class="jeem-modal-content jeem-modal-large">
        <span class="jeem-modal-close">&times;</span>
        <div class="jeem-modal-header">
            <h2><?php _e('Bulk Copy Event', 'je-event-manager'); ?></h2>
            <p class="jeem-modal-description"><?php _e('Create multiple copies of this event with recurring dates.', 'je-event-manager'); ?></p>
        </div>
        <form id="jeem-bulk-copy-event-form">
            <input type="hidden" id="jeem-bulk-copy-source-id" name="source_id">
            <input type="hidden" id="jeem-bulk-copy-source-ids" name="source_ids" value="">
            <input type="hidden" id="jeem-bulk-copy-multiple" name="copy_multiple" value="false">
            
            <div class="jeem-form-section">
                <h3><?php _e('Select Event to Copy', 'je-event-manager'); ?> <span class="required">*</span></h3>
                <label for="jeem-bulk-copy-event-select"><?php _e('Event', 'je-event-manager'); ?></label>
                <select id="jeem-bulk-copy-event-select" name="event_select" class="regular-text" required>
                    <option value=""><?php _e('Loading events...', 'je-event-manager'); ?></option>
                </select>
                <span class="description"><?php _e('Select an event to use as a template for bulk copying.', 'je-event-manager'); ?></span>
            </div>
            
            <div class="jeem-form-section">
                <h3><?php _e('Start Date & Time', 'je-event-manager'); ?> <span class="required">*</span></h3>
                <div class="jeem-datetime-picker">
                    <div class="jeem-date-input-wrapper">
                        <label for="jeem-bulk-start-date"><?php _e('Date', 'je-event-manager'); ?></label>
                        <input type="text" id="jeem-bulk-start-date" name="start_date" class="jeem-datepicker regular-text" placeholder="YYYY-MM-DD" required readonly>
                        <span class="dashicons dashicons-calendar-alt jeem-date-icon"></span>
                    </div>
                    <div class="jeem-time-input-wrapper">
                        <label for="jeem-bulk-start-time"><?php _e('Time', 'je-event-manager'); ?></label>
                        <input type="time" id="jeem-bulk-start-time" name="start_time" class="regular-text" value="10:00" required>
                    </div>
                </div>
            </div>
            
            <div class="jeem-form-section">
                <h3><?php _e('Recurrence Settings', 'je-event-manager'); ?></h3>
                <div class="jeem-interval-wrapper">
                    <div class="jeem-interval-input">
                        <label for="jeem-bulk-interval"><?php _e('Repeat every', 'je-event-manager'); ?></label>
                        <div class="jeem-interval-controls">
                            <input type="number" id="jeem-bulk-interval" name="interval" class="small-text" min="1" value="2" required>
                            <select id="jeem-bulk-interval-unit" name="interval_unit" class="jeem-interval-select">
                                <option value="days"><?php _e('Day(s)', 'je-event-manager'); ?></option>
                                <option value="weeks" selected><?php _e('Week(s)', 'je-event-manager'); ?></option>
                                <option value="months"><?php _e('Month(s)', 'je-event-manager'); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="jeem-count-input">
                        <label for="jeem-bulk-count"><?php _e('Number of events', 'je-event-manager'); ?></label>
                        <input type="number" id="jeem-bulk-count" name="count" class="small-text" min="1" max="100" value="10" required>
                        <span class="jeem-count-hint"><?php _e('(max 100)', 'je-event-manager'); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="jeem-form-preview" id="jeem-bulk-preview" style="display: none;">
                <h3><?php _e('Preview', 'je-event-manager'); ?></h3>
                <div class="jeem-preview-content" id="jeem-preview-dates"></div>
            </div>
            
            <div class="jeem-modal-footer">
                <button type="button" class="button jeem-modal-cancel"><?php _e('Cancel', 'je-event-manager'); ?></button>
                <button type="submit" class="button button-primary button-large">
                    <span class="dashicons dashicons-calendar-alt" style="vertical-align: middle; margin-right: 5px;"></span>
                    <?php _e('Create Events', 'je-event-manager'); ?>
                </button>
            </div>
        </form>
    </div>
</div>

