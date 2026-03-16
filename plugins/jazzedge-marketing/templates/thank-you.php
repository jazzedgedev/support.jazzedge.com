<?php
/**
 * JEM Marketing - Thank You Page Template
 *
 * @package Jazzedge_Marketing
 * @var object $lead Lead object (from get_lead_data via extract)
 * @var object $funnel Funnel object
 * @var bool   $expired Whether coupon has expired
 * @var string $product_url Product URL with coupon
 * @var string $download_url Download URL
 * @var float  $regular_price,$sale_price,$your_price,$total_savings
 * @var int    $savings_pct
 * @var string $product_title
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="jem-thankyou-wrapper">
	<div class="jem-ty-page" style="max-width:960px;margin:20px auto;">
		<?php include JEM_PLUGIN_DIR . 'templates/download.php'; ?>
		<?php include JEM_PLUGIN_DIR . 'templates/offer.php'; ?>
	</div><!-- end jem-ty-page -->
</div><!-- end jem-thankyou-wrapper -->
