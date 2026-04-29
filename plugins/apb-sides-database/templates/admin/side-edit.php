<?php
/**
 * Side edit template.
 *
 * @var array<string, mixed> $data Data.
 * @package APB_Sides_Database
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$s          = $data['side'];
$chars      = $data['characters'] ?? array();
$selected   = $data['selected'] ?? array();
$sid        = (int) $s['id'];
$script     = $data['script'] ?? null;
?>
<div class="wrap apb-side-edit" data-entity-type="side" data-entity-id="<?php echo esc_attr( (string) $sid ); ?>">
	<h1><?php esc_html_e( 'Edit side', 'apb-sides-database' ); ?></h1>
	<p><a href="<?php echo esc_url( admin_url( 'admin.php?page=apb-sides-sides' ) ); ?>">&larr; <?php esc_html_e( 'Back', 'apb-sides-database' ); ?></a></p>
	<?php if ( $script ) : ?>
		<p><?php esc_html_e( 'Script:', 'apb-sides-database' ); ?> <strong><?php echo esc_html( (string) $script['title'] ); ?></strong></p>
	<?php endif; ?>
	<table class="form-table">
		<tr><th><?php esc_html_e( 'Title', 'apb-sides-database' ); ?></th><td><input type="text" class="large-text apb-field" name="title" value="<?php echo esc_attr( (string) $s['title'] ); ?>" /></td></tr>
		<tr><th><?php esc_html_e( 'Scene pages', 'apb-sides-database' ); ?></th><td>
			<input type="number" class="small-text apb-field" name="scene_start_page" value="<?php echo esc_attr( (string) ( $s['scene_start_page'] ?? '' ) ); ?>" />
			&ndash;
			<input type="number" class="small-text apb-field" name="scene_end_page" value="<?php echo esc_attr( (string) ( $s['scene_end_page'] ?? '' ) ); ?>" />
		</td></tr>
		<tr><th><?php esc_html_e( 'Location', 'apb-sides-database' ); ?></th><td><input type="text" class="regular-text apb-field" name="location" value="<?php echo esc_attr( (string) ( $s['location'] ?? '' ) ); ?>" /></td></tr>
		<tr><th><?php esc_html_e( 'Scene type', 'apb-sides-database' ); ?></th><td><input type="text" class="regular-text apb-field" name="scene_type" value="<?php echo esc_attr( (string) ( $s['scene_type'] ?? '' ) ); ?>" /></td></tr>
		<tr><th><?php esc_html_e( 'Tone', 'apb-sides-database' ); ?></th><td><input type="text" class="regular-text apb-field" name="tone" value="<?php echo esc_attr( (string) ( $s['tone'] ?? '' ) ); ?>" /></td></tr>
		<tr><th><?php esc_html_e( 'Energy', 'apb-sides-database' ); ?></th><td><input type="text" class="regular-text apb-field" name="energy_level" value="<?php echo esc_attr( (string) ( $s['energy_level'] ?? '' ) ); ?>" /></td></tr>
		<tr><th><?php esc_html_e( 'Difficulty', 'apb-sides-database' ); ?></th><td><input type="text" class="regular-text apb-field" name="difficulty_level" value="<?php echo esc_attr( (string) ( $s['difficulty_level'] ?? '' ) ); ?>" /></td></tr>
		<tr><th><?php esc_html_e( 'Scene context', 'apb-sides-database' ); ?></th><td><textarea class="large-text apb-field" name="scene_context"><?php echo esc_textarea( (string) ( $s['scene_context'] ?? '' ) ); ?></textarea></td></tr>
		<tr><th><?php esc_html_e( 'Actor notes', 'apb-sides-database' ); ?></th><td><textarea class="large-text apb-field" name="actor_notes"><?php echo esc_textarea( (string) ( $s['actor_notes'] ?? '' ) ); ?></textarea></td></tr>
		<tr><th><?php esc_html_e( 'Script excerpt', 'apb-sides-database' ); ?></th><td><textarea class="large-text apb-field" name="script_excerpt"><?php echo esc_textarea( (string) ( $s['script_excerpt'] ?? '' ) ); ?></textarea></td></tr>
		<tr><th><?php esc_html_e( 'Characters', 'apb-sides-database' ); ?></th><td>
			<?php foreach ( $chars as $c ) : ?>
				<label style="display:inline-block;margin-right:10px;">
					<input type="checkbox" class="apb-side-char-cb" value="<?php echo esc_attr( (string) $c['id'] ); ?>" <?php checked( in_array( (int) $c['id'], $selected, true ) ); ?> />
					<?php echo esc_html( (string) $c['name'] ); ?>
				</label>
			<?php endforeach; ?>
		</td></tr>
		<tr><th><?php esc_html_e( 'Status', 'apb-sides-database' ); ?></th><td><input type="text" class="regular-text apb-field" name="status" value="<?php echo esc_attr( (string) $s['status'] ); ?>" /></td></tr>
	</table>
	<p>
		<button type="button" class="button button-primary apb-save-entity"><?php esc_html_e( 'Save', 'apb-sides-database' ); ?></button>
		<span class="apb-inline-notice"></span>
	</p>
</div>
