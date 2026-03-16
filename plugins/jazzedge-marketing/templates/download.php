<?php
/**
 * JEM Marketing - Download Section Template
 *
 * Used by [jem_download] shortcode. Expects: $lead, $funnel, $download_url (from get_lead_data).
 *
 * @package Jazzedge_Marketing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="jem-ty-col jem-ty-download-col">
	<div class="jem-section jem-download-section">
		<div class="jem-checkmark">✓</div>
		<h1><?php esc_html_e( "You're In! Your Sheet Music is Ready", 'jazzedge-marketing' ); ?></h1>
		<p class="jem-sub"><?php
			/* translators: %s: funnel name */
			echo wp_kses_post( sprintf( __( 'Click the button below to download your free sheet music for <strong>%s</strong>.', 'jazzedge-marketing' ), esc_html( $funnel->name ) ) );
		?></p>
		<a href="<?php echo esc_url( $download_url ); ?>"
		   class="jem-btn jem-btn-download"
		   id="jem-download-btn"
		   data-lead="<?php echo (int) $lead->id; ?>"
		   data-funnel="<?php echo (int) $funnel->id; ?>">
			<?php esc_html_e( '⬇ Download Your Free Sheet Music', 'jazzedge-marketing' ); ?>
		</a>
	</div>
</div>
