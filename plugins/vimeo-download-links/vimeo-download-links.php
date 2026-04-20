<?php
/**
 * Plugin Name: Vimeo Download Links
 * Description: Appends a download button to all FV Player Vimeo videos using the Vimeo API. Also suppresses deprecated shortcodes.
 * Version: 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// API token can be overridden in wp-config.php: define( 'VDL_VIMEO_TOKEN', 'your_token' );
if ( ! defined( 'VDL_VIMEO_TOKEN' ) ) {
	define( 'VDL_VIMEO_TOKEN', '4621cf0cf193f9e2162bbc4ec9cadeb4' );
}

// Suppress deprecated shortcodes — returns empty string so they vanish silently
add_shortcode( 'uo_breadcrumbs', '__return_empty_string' );

add_filter( 'do_shortcode_tag', 'vdl_append_download_button', 10, 4 );

function vdl_append_download_button( $output, $tag, $attr, $m ) {
	if ( $tag !== 'fvplayer' ) {
		return $output;
	}

	$src = isset( $attr['src'] ) ? $attr['src'] : '';
	if ( empty( $src ) || strpos( $src, 'vimeo.com' ) === false ) {
		return $output;
	}

	preg_match( '/vimeo\.com\/(\d+)/', $src, $matches );
	if ( empty( $matches[1] ) ) {
		return $output;
	}

	$video_id     = $matches[1];
	$download_url = vdl_get_best_download_url( $video_id );

	if ( ! $download_url ) {
		return $output;
	}

	// Hide download button after December 31, 2026
	$expired = time() > mktime( 23, 59, 59, 12, 31, 2026 );
	if ( $expired ) {
		return $output;
	}

	$banner = sprintf(
		'<div class="vdl-download-wrap">
			<div class="vdl-download-message">
				<span class="vdl-icon">&#8681;</span>
				<div class="vdl-message-text">
					<strong>Download this video for offline access</strong>
					<span>Download availability ends <strong>December 31, 2026</strong> &mdash; save your lessons before then.</span>
				</div>
			</div>
			<a class="vdl-download-btn" href="%s" target="_blank" rel="noopener">&#8595;&nbsp; Download Video</a>
		</div>',
		esc_url( $download_url )
	);

	return $output . $banner;
}

function vdl_get_best_download_url( $video_id ) {
	$cache_key = 'vdl_' . $video_id;
	$cached    = get_transient( $cache_key );

	if ( $cached !== false ) {
		return $cached;
	}

	$response = wp_remote_get(
		'https://api.vimeo.com/videos/' . intval( $video_id ),
		array(
			'headers' => array(
				'Authorization' => 'bearer ' . VDL_VIMEO_TOKEN,
				'Accept'        => 'application/vnd.vimeo.*+json;version=3.4',
			),
			'timeout' => 10,
		)
	);

	if ( is_wp_error( $response ) ) {
		return false;
	}

	$status = wp_remote_retrieve_response_code( $response );
	if ( $status !== 200 ) {
		return false;
	}

	$body = json_decode( wp_remote_retrieve_body( $response ), true );

	if ( empty( $body['download'] ) || ! is_array( $body['download'] ) ) {
		return false;
	}

	// Sort by width descending — pick the highest quality available
	$downloads = $body['download'];
	usort( $downloads, function ( $a, $b ) {
		return ( $b['width'] ?? 0 ) - ( $a['width'] ?? 0 );
	} );

	$url = isset( $downloads[0]['link'] ) ? $downloads[0]['link'] : false;

	if ( $url ) {
		// Cache for 6 hours (Vimeo download links are time-limited but stable enough for this window)
		set_transient( $cache_key, $url, 6 * HOUR_IN_SECONDS );
	}

	return $url;
}

// Styles
add_action( 'wp_head', 'vdl_styles' );

function vdl_styles() {
	echo '<style>
.vdl-download-wrap {
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: 16px;
	width: 100%;
	box-sizing: border-box;
	margin-top: 10px;
	padding: 14px 18px;
	background: #1a1a2e;
	border-left: 4px solid #f0a500;
	border-radius: 6px;
}
.vdl-download-message {
	display: flex;
	align-items: center;
	gap: 12px;
	flex: 1;
	min-width: 0;
}
.vdl-icon {
	font-size: 28px;
	color: #f0a500;
	flex-shrink: 0;
	line-height: 1;
}
.vdl-message-text {
	display: flex;
	flex-direction: column;
	gap: 3px;
}
.vdl-message-text strong {
	color: #ffffff;
	font-size: 14px;
	font-weight: 700;
	display: block;
}
.vdl-message-text span {
	color: #b0b8cc;
	font-size: 13px;
	line-height: 1.4;
}
.vdl-message-text span strong {
	color: #f0a500;
	display: inline;
	font-size: 13px;
}
.vdl-download-btn {
	display: inline-flex;
	align-items: center;
	flex-shrink: 0;
	padding: 10px 20px;
	background: #f0a500;
	color: #1a1a2e !important;
	font-size: 13px;
	font-weight: 700;
	text-decoration: none !important;
	border-radius: 5px;
	white-space: nowrap;
	transition: background 0.15s, transform 0.1s;
}
.vdl-download-btn:hover {
	background: #ffc233;
	transform: translateY(-1px);
}
@media (max-width: 540px) {
	.vdl-download-wrap {
		flex-direction: column;
		align-items: flex-start;
	}
	.vdl-download-btn {
		width: 100%;
		justify-content: center;
	}
}
</style>' . "\n";
}
