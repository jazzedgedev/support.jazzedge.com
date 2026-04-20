<?php
/**
 * Plugin Name: JECM Academy → SJE Backfill
 * Description: Sends existing FluentCart orders to the Jazzedge Support (JECM) FluentCart webhook in batches. Install on jazzedge.academy only.
 * Version: 1.0.0
 * Author: JazzEdge
 * Requires at least: 6.0
 * License: GPL-2.0-or-later
 */
if (!defined('ABSPATH')) {
    exit;
}

define('JECM_AB_VERSION', '1.0.0');
define('JECM_AB_FILE', __FILE__);
define('JECM_AB_DIR', __DIR__);

require_once JECM_AB_DIR . '/includes/class-jecm-academy-backfill.php';

JECM_Academy_Backfill::instance();
