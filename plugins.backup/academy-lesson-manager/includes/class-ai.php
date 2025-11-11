<?php
// Prevent direct access
if (!defined('ABSPATH')) { exit; }

class ALM_AI {
    /**
     * Extract structured filters from a natural language query.
     * Default implementation returns empty; site owners can hook into the
     * 'alm_ai_extract_filters' filter to implement AI parsing.
     */
    public static function extract_filters($query) {
        $filters = array();
        /**
         * Filters can set keys: membership_level, collection_id, has_resources ('has'|'none'),
         * song_lesson ('y'|'n'), min_duration, max_duration, date_from, date_to
         */
        return apply_filters('alm_ai_extract_filters', $filters, $query);
    }

    /**
     * Re-rank items with AI (top-N) based on the query.
     * Default: no-op. Use 'alm_ai_rerank_items' to inject re-ranking.
     *
     * @param array $items
     * @param string $query
     * @return array
     */
    public static function rerank_items($items, $query) {
        return apply_filters('alm_ai_rerank_items', $items, $query);
    }
}


