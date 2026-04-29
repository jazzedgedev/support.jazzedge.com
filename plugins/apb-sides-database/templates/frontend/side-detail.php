<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
$s          = $data['side'];
$script     = $data['script'] ?? null;
$characters = $data['characters'] ?? array();
$pdf_url    = $script ? (string) ( $script['source_file_url'] ?? '' ) : '';
$back_url   = home_url( '/sides/' );
$script_url = $script ? home_url( '/scripts/?script_id=' . (int) $script['id'] ) : '';
?>
<div class="apb-side-detail">

	<p><a href="<?php echo esc_url( $back_url ); ?>">&larr; Back to Sides</a></p>

	<?php if ( $script ) : ?>
		<p class="apb-side-show"><a href="<?php echo esc_url( $script_url ); ?>"><?php echo esc_html( (string) $script['title'] ); ?></a></p>
	<?php endif; ?>

	<h1><?php echo esc_html( (string) $s['title'] ); ?></h1>

	<?php if ( ! empty( $characters ) ) : ?>
		<p class="apb-side-characters">
			<?php foreach ( $characters as $i => $c ) :
				echo esc_html( (string) $c['name'] );
				if ( ! empty( $c['gender'] ) || ! empty( $c['age_range_label'] ) ) {
					$meta = array_filter( array( $c['gender'] ?? '', $c['age_range_label'] ?? '' ) );
					echo ' <span class="apb-char-meta">(' . esc_html( implode( ', ', $meta ) ) . ')</span>';
				}
				if ( $i < count( $characters ) - 1 ) {
					echo ' &nbsp;·&nbsp; ';
				}
			endforeach; ?>
		</p>
	<?php endif; ?>

	<?php if ( $script && ! empty( $script['genre'] ) ) : ?>
		<p class="apb-side-genre"><?php echo esc_html( (string) $script['genre'] ); ?></p>
	<?php endif; ?>

	<?php if ( ! empty( $s['scene_context'] ) ) : ?>
		<div class="apb-side-block">
			<h2>Scene Context</h2>
			<p><?php echo wp_kses_post( (string) $s['scene_context'] ); ?></p>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $s['actor_notes'] ) ) : ?>
		<div class="apb-side-block">
			<h2>Actor Notes</h2>
			<p><?php echo wp_kses_post( (string) $s['actor_notes'] ); ?></p>
		</div>
	<?php endif; ?>

	<div class="apb-side-actions">
		<?php if ( $pdf_url ) : ?>
			<a href="<?php echo esc_url( $pdf_url ); ?>" class="button button-primary" download>Download PDF</a>
			<button type="button" class="button apb-toggle-pdf" data-pdf="<?php echo esc_attr( $pdf_url ); ?>">View PDF</button>
		<?php endif; ?>
	</div>

	<?php if ( $pdf_url ) : ?>
	<div id="apb-pdf-viewer" class="apb-pdf-viewer" style="display:none;">
		<iframe id="apb-pdf-frame" src="" title="Screenplay PDF" allowfullscreen></iframe>
	</div>
	<?php endif; ?>

	<details style="margin-top:40px;border:1px solid #ddd;padding:12px;border-radius:4px;">
		<summary style="cursor:pointer;font-weight:bold;font-size:13px;color:#555;">Debug — All Data</summary>

		<h3 style="margin-top:16px;">Side</h3>
		<table style="border-collapse:collapse;width:100%;font-family:monospace;font-size:12px;">
			<?php foreach ( $s as $key => $value ) : ?>
			<tr style="border-bottom:1px solid #f0f0f0;">
				<td style="padding:5px 10px;color:#888;width:180px;vertical-align:top;"><?php echo esc_html( $key ); ?></td>
				<td style="padding:5px 10px;"><?php echo nl2br( esc_html( $value === null ? '—' : (string) $value ) ); ?></td>
			</tr>
			<?php endforeach; ?>
		</table>

		<h3 style="margin-top:16px;">Script</h3>
		<table style="border-collapse:collapse;width:100%;font-family:monospace;font-size:12px;">
			<?php foreach ( ( $script ?? array() ) as $key => $value ) : ?>
			<tr style="border-bottom:1px solid #f0f0f0;">
				<td style="padding:5px 10px;color:#888;width:180px;vertical-align:top;"><?php echo esc_html( $key ); ?></td>
				<td style="padding:5px 10px;"><?php echo nl2br( esc_html( $value === null ? '—' : (string) $value ) ); ?></td>
			</tr>
			<?php endforeach; ?>
		</table>

		<?php if ( ! empty( $characters ) ) : ?>
		<h3 style="margin-top:16px;">Characters</h3>
		<?php foreach ( $characters as $c ) : ?>
		<table style="border-collapse:collapse;width:100%;font-family:monospace;font-size:12px;margin-bottom:12px;border:1px solid #eee;">
			<?php foreach ( $c as $key => $value ) : ?>
			<tr style="border-bottom:1px solid #f0f0f0;">
				<td style="padding:5px 10px;color:#888;width:180px;"><?php echo esc_html( $key ); ?></td>
				<td style="padding:5px 10px;"><?php echo esc_html( $value === null ? '—' : (string) $value ); ?></td>
			</tr>
			<?php endforeach; ?>
		</table>
		<?php endforeach; ?>
		<?php endif; ?>

	</details>

</div>
