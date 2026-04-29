<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$script = $data['script'];
$sides  = $data['sides'] ?? array();
?>
<div class="apb-script-detail">
	<p><a href="<?php echo esc_url( home_url( '/sides/' ) ); ?>">&larr; Back to Sides</a></p>
	<h1><?php echo esc_html( (string) $script['title'] ); ?></h1>
	<?php if ( ! empty( $script['genre'] ) ) : ?>
		<p class="apb-script-genre"><?php echo esc_html( (string) $script['genre'] ); ?></p>
	<?php endif; ?>
	<?php if ( ! empty( $script['source_file_url'] ) ) : ?>
		<p><a href="<?php echo esc_url( (string) $script['source_file_url'] ); ?>" class="button" download>Download PDF</a></p>
	<?php endif; ?>
	<h2 style="margin-top:32px;">Available Sides (<?php echo count( $sides ); ?>)</h2>
	<?php if ( empty( $sides ) ) : ?>
		<p>No sides available yet.</p>
	<?php else : ?>
		<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;margin-top:16px;">
			<?php foreach ( $sides as $side ) :
				$side_url = home_url( '/sides/view/?side_id=' . (int) $side['id'] );
				?>
			<div class="apb-result-card">
				<h3><a href="<?php echo esc_url( $side_url ); ?>"><?php echo esc_html( (string) $side['title'] ); ?></a></h3>
				<?php if ( ! empty( $side['scene_context'] ) ) : ?>
					<p style="font-size:0.9em;color:#555;"><?php echo esc_html( wp_trim_words( (string) $side['scene_context'], 20 ) ); ?></p>
				<?php endif; ?>
				<?php if ( ! empty( $side['casting_type'] ) ) : ?>
					<p style="font-size:0.85em;color:#777;"><?php echo esc_html( (string) $side['casting_type'] ); ?></p>
				<?php endif; ?>
				<p><a href="<?php echo esc_url( $side_url ); ?>" class="button">View Side</a></p>
			</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>
