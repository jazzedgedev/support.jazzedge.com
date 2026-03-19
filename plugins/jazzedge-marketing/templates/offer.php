<?php
/**
 * JEM Marketing - Offer Section Template
 *
 * Used by [jem_offer] shortcode. Expects: $lead, $funnel, $expired, $product_url, $product_title,
 * $regular_price, $sale_price, $your_price, $total_savings, $savings_pct (from get_lead_data).
 *
 * @package Jazzedge_Marketing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="jem-ty-col jem-ty-offer-col" style="position: relative;">
	<?php if ( ! $expired ) : ?>
	<div class="jem-ty-urgent-badge">⚡ <?php echo (int) $savings_pct; ?>% OFF · Save $<?php echo number_format( $total_savings, 2 ); ?> · <?php echo (int) $funnel->coupon_days; ?> Days Only</div>
	<?php endif; ?>
	<!-- OFFER SECTION (hidden when expired) -->
	<div class="jem-section jem-offer-section jem-ty-col jem-ty-offer-col" style="<?php echo $expired ? 'display:none!important;' : ''; ?>">
		<?php if ( ! $expired ) : ?>
		<!-- COUNTDOWN -->
		<div class="jem-ty-countdown-label"><?php esc_html_e( '⏰ This offer expires in:', 'jazzedge-marketing' ); ?></div>
		<div class="jem-countdown-wrapper">
			<div class="jem-countdown jem-ty-countdown" id="jem-countdown">
				<div class="jem-countdown-block jem-ty-cd-block"><span id="jem-days">--</span><label><?php esc_html_e( 'Days', 'jazzedge-marketing' ); ?></label></div>
				<div class="jem-countdown-sep jem-ty-cd-sep">:</div>
				<div class="jem-countdown-block jem-ty-cd-block"><span id="jem-hours">--</span><label><?php esc_html_e( 'Hrs', 'jazzedge-marketing' ); ?></label></div>
				<div class="jem-countdown-sep jem-ty-cd-sep">:</div>
				<div class="jem-countdown-block jem-ty-cd-block"><span id="jem-minutes">--</span><label><?php esc_html_e( 'Min', 'jazzedge-marketing' ); ?></label></div>
				<div class="jem-countdown-sep jem-ty-cd-sep">:</div>
				<div class="jem-countdown-block jem-ty-cd-block"><span id="jem-seconds">--</span><label><?php esc_html_e( 'Sec', 'jazzedge-marketing' ); ?></label></div>
			</div>
		</div>

		<h3 class="jem-ty-offer-title"><?php esc_html_e( 'Learn to Play', 'jazzedge-marketing' ); ?><br><em><?php echo esc_html( $product_title ); ?></em></h3>

		<!-- PRICE STACK -->
		<div class="jem-ty-price-stack">
			<div class="jem-ty-price-row jem-ty-price-original">
				<span class="jem-ty-price-label"><?php esc_html_e( 'Regular Price', 'jazzedge-marketing' ); ?></span>
				<span class="jem-ty-price-val jem-ty-strikethrough">
					<?php echo $regular_price > 0 ? '$' . number_format( $regular_price, 2 ) : ''; ?>
				</span>
			</div>
			<div class="jem-ty-price-row jem-ty-price-sale">
				<span class="jem-ty-price-label"><?php esc_html_e( 'Current Sale Price', 'jazzedge-marketing' ); ?></span>
				<span class="jem-ty-price-val jem-ty-strikethrough-light">
					<?php echo $sale_price > 0 ? '$' . number_format( $sale_price, 2 ) : ''; ?>
				</span>
			</div>
			<div class="jem-ty-price-row jem-ty-price-yours">
				<span class="jem-ty-price-label"><?php esc_html_e( '🎉 Your Price with Code', 'jazzedge-marketing' ); ?></span>
				<span class="jem-ty-price-val jem-ty-price-big">
					<?php echo $your_price > 0 ? '$' . number_format( $your_price, 2 ) : (int) $funnel->discount_pct . '% OFF'; ?>
				</span>
			</div>
		</div>

		<!-- STEP BY STEP -->
		<div class="jem-ty-steps">
			<p class="jem-ty-steps-title"><?php esc_html_e( 'Follow These Steps to Claim Your Discount:', 'jazzedge-marketing' ); ?></p>

			<div class="jem-ty-step">
				<div class="jem-ty-step-num">1</div>
				<div class="jem-ty-step-content">
					<strong><?php esc_html_e( 'Copy Your Coupon Code', 'jazzedge-marketing' ); ?></strong>
					<p><?php esc_html_e( 'Click the COPY button below — it copies automatically to your clipboard.', 'jazzedge-marketing' ); ?></p>
					<div class="jem-ty-coupon-row">
						<span class="jem-ty-coupon-code" id="jem-coupon-code"><?php echo esc_html( $lead->coupon_code ); ?></span>
						<button class="jem-ty-copy-btn jem-btn jem-btn-copy" type="button" onclick="jemCopyCoupon()"><?php esc_html_e( '📋 COPY', 'jazzedge-marketing' ); ?></button>
					</div>
					<p class="jem-ty-personal-note">
						<?php echo esc_html( sprintf( __( '🔒 This coupon is personal and attached to %s — it cannot be used by anyone else.', 'jazzedge-marketing' ), $lead->email ) ); ?>
					</p>
				</div>
			</div>

			<div class="jem-ty-step">
				<div class="jem-ty-step-num">2</div>
				<div class="jem-ty-step-content">
					<strong><?php esc_html_e( 'Click the Button to Go to the Order Page', 'jazzedge-marketing' ); ?></strong>
					<p><?php esc_html_e( "This opens the lesson page in a new tab. Don't close this page!", 'jazzedge-marketing' ); ?></p>
					<a href="<?php echo esc_url( $product_url ); ?>"
					   class="jem-ty-btn jem-ty-btn-purchase jem-btn jem-btn-purchase jem-track-purchase"
					   id="jem-purchase-btn"
					   data-lead="<?php echo esc_attr( $lead->id ); ?>"
					   data-funnel="<?php echo esc_attr( $funnel->id ); ?>"
					   target="_blank"
					   rel="noopener noreferrer">
						<?php esc_html_e( '👉 Go to Order Page Now →', 'jazzedge-marketing' ); ?>
					</a>
				</div>
			</div>

			<div class="jem-ty-step">
				<div class="jem-ty-step-num">3</div>
				<div class="jem-ty-step-content">
					<strong><?php esc_html_e( 'Paste Your Coupon Code at Checkout', 'jazzedge-marketing' ); ?></strong>
					<p><?php echo wp_kses_post( __( 'On the checkout page find the <strong>"Coupon Code"</strong> box, paste your code and click <strong>"Apply"</strong>.', 'jazzedge-marketing' ) ); ?></p>
					<div class="jem-ty-tip">
						<?php esc_html_e( '💡 Your code is:', 'jazzedge-marketing' ); ?> <span class="jem-ty-inline-code"><?php echo esc_html( $lead->coupon_code ); ?></span>
					</div>
				</div>
			</div>

			<div class="jem-ty-step jem-ty-step-warning">
				<div class="jem-ty-step-num jem-ty-step-num-warning">4</div>
				<div class="jem-ty-step-content">
					<strong><?php esc_html_e( '⚠️ Make Sure the Discount is Applied BEFORE You Order!', 'jazzedge-marketing' ); ?></strong>
					<p><?php echo wp_kses_post( __( 'Check that your total shows the discounted price before clicking the final order button. <strong>We cannot apply discounts after an order is placed.</strong>', 'jazzedge-marketing' ) ); ?></p>
				</div>
			</div>

		</div>

		<p class="jem-disclaimer jem-ty-disclaimer">
			<?php esc_html_e( 'Discount applied at checkout with code', 'jazzedge-marketing' ); ?>
			<strong><?php echo esc_html( $lead->coupon_code ); ?></strong>.
			<?php esc_html_e( 'Offer expires when the timer hits zero.', 'jazzedge-marketing' ); ?>
		</p>
		<?php endif; ?>
	</div>

	<!-- EXPIRED SECTION -->
	<div class="jem-section jem-expired-section" style="<?php echo $expired ? '' : 'display:none!important;'; ?>">
		<div class="jem-ty-expired-box">
			<div class="jem-expired-icon jem-ty-expired-icon">⏰</div>
			<h3><?php esc_html_e( 'Your Discount Has Expired', 'jazzedge-marketing' ); ?></h3>
			<p><?php esc_html_e( 'The special offer has ended. The full lesson is still available at the regular price.', 'jazzedge-marketing' ); ?></p>
			<a href="<?php echo esc_url( $product_url ); ?>" class="jem-btn jem-btn-purchase jem-ty-btn jem-ty-btn-purchase" data-lead="<?php echo (int) $lead->id; ?>" data-funnel="<?php echo (int) $funnel->id; ?>" target="_blank" rel="noopener noreferrer">
				<?php esc_html_e( 'View the Full Lesson →', 'jazzedge-marketing' ); ?>
			</a>
		</div>
	</div>
</div>

<script>
var jemExpiry = '<?php echo esc_js( $lead->coupon_expires ); ?>';

function jemCopyCoupon() {
	var code = document.getElementById('jem-coupon-code').innerText;
	var btn = document.querySelector('.jem-ty-copy-btn');
	if ( navigator.clipboard && navigator.clipboard.writeText ) {
		navigator.clipboard.writeText(code).then(function() {
			if ( btn ) { btn.textContent = '✅ Copied!'; }
			setTimeout(function() {
				if ( btn ) { btn.textContent = '📋 COPY'; }
			}, 3000);
		});
	}
}

function jemUpdateCountdown() {
	var now    = new Date().getTime();
	var expiry = new Date(jemExpiry).getTime();
	var diff   = expiry - now;

	if ( diff <= 0 ) {
		var countEl = document.getElementById('jem-countdown');
		if ( countEl ) countEl.innerHTML = '<p class="jem-expired-inline"><?php echo esc_js( __( 'This offer has expired.', 'jazzedge-marketing' ) ); ?></p>';
		var offer = document.querySelector('.jem-offer-section');
		var expiredSec = document.querySelector('.jem-expired-section');
		if ( offer ) offer.style.display = 'none';
		if ( expiredSec ) expiredSec.style.display = 'block';
		return;
	}

	var days    = Math.floor( diff / (1000 * 60 * 60 * 24) );
	var hours   = Math.floor( (diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60) );
	var minutes = Math.floor( (diff % (1000 * 60 * 60)) / (1000 * 60) );
	var seconds = Math.floor( (diff % (1000 * 60)) / 1000 );

	var d = document.getElementById('jem-days');
	var h = document.getElementById('jem-hours');
	var m = document.getElementById('jem-minutes');
	var s = document.getElementById('jem-seconds');
	if ( d ) d.innerText = String(days).padStart(2,'0');
	if ( h ) h.innerText = String(hours).padStart(2,'0');
	if ( m ) m.innerText = String(minutes).padStart(2,'0');
	if ( s ) s.innerText = String(seconds).padStart(2,'0');
}

if ( document.readyState === 'loading' ) {
	document.addEventListener('DOMContentLoaded', function() {
		setInterval(jemUpdateCountdown, 1000);
		jemUpdateCountdown();
	});
} else {
	setInterval(jemUpdateCountdown, 1000);
	jemUpdateCountdown();
}
</script>
