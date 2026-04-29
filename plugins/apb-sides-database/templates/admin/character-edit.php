<?php
/**
 * Character edit template.
 *
 * @var array<string, mixed> $data Data.
 * @package APB_Sides_Database
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$c = $data['character'];
?>
<div class="wrap apb-character-edit" data-entity-type="character" data-entity-id="<?php echo esc_attr( (string) $c['id'] ); ?>">
	<h1><?php esc_html_e( 'Edit character', 'apb-sides-database' ); ?></h1>
	<p><a href="<?php echo esc_url( admin_url( 'admin.php?page=apb-sides-characters' ) ); ?>">&larr; <?php esc_html_e( 'Back', 'apb-sides-database' ); ?></a></p>
	<table class="form-table">
		<tr><th><?php esc_html_e( 'Name', 'apb-sides-database' ); ?></th><td><input type="text" class="regular-text apb-field" name="name" value="<?php echo esc_attr( (string) $c['name'] ); ?>" /></td></tr>
		<tr><th><?php esc_html_e( 'Gender', 'apb-sides-database' ); ?></th><td><input type="text" class="regular-text apb-field" name="gender" value="<?php echo esc_attr( (string) ( $c['gender'] ?? '' ) ); ?>" /></td></tr>
		<tr><th><?php esc_html_e( 'Age min', 'apb-sides-database' ); ?></th><td><input type="number" class="small-text apb-field" name="age_min" value="<?php echo esc_attr( (string) ( $c['age_min'] ?? '' ) ); ?>" /></td></tr>
		<tr><th><?php esc_html_e( 'Age max', 'apb-sides-database' ); ?></th><td><input type="number" class="small-text apb-field" name="age_max" value="<?php echo esc_attr( (string) ( $c['age_max'] ?? '' ) ); ?>" /></td></tr>
		<tr><th><?php esc_html_e( 'Age label', 'apb-sides-database' ); ?></th><td><input type="text" class="regular-text apb-field" name="age_range_label" value="<?php echo esc_attr( (string) ( $c['age_range_label'] ?? '' ) ); ?>" /></td></tr>
		<tr><th><?php esc_html_e( 'Role size', 'apb-sides-database' ); ?></th><td><input type="text" class="regular-text apb-field" name="role_size" value="<?php echo esc_attr( (string) ( $c['role_size'] ?? '' ) ); ?>" /></td></tr>
		<tr><th><?php esc_html_e( 'Archetype', 'apb-sides-database' ); ?></th><td><input type="text" class="regular-text apb-field" name="archetype" value="<?php echo esc_attr( (string) ( $c['archetype'] ?? '' ) ); ?>" /></td></tr>
		<tr><th><?php esc_html_e( 'Occupation', 'apb-sides-database' ); ?></th><td><input type="text" class="regular-text apb-field" name="occupation" value="<?php echo esc_attr( (string) ( $c['occupation'] ?? '' ) ); ?>" /></td></tr>
		<tr><th><?php esc_html_e( 'Description', 'apb-sides-database' ); ?></th><td><textarea class="large-text apb-field" name="description"><?php echo esc_textarea( (string) ( $c['description'] ?? '' ) ); ?></textarea></td></tr>
		<tr><th><?php esc_html_e( 'Energy', 'apb-sides-database' ); ?></th><td><input type="text" class="regular-text apb-field" name="energy_level" value="<?php echo esc_attr( (string) ( $c['energy_level'] ?? '' ) ); ?>" /></td></tr>
		<tr><th><?php esc_html_e( 'Traits', 'apb-sides-database' ); ?></th><td><textarea class="large-text apb-field" name="traits"><?php echo esc_textarea( (string) ( $c['traits'] ?? '' ) ); ?></textarea></td></tr>
		<tr><th><?php esc_html_e( 'Accent', 'apb-sides-database' ); ?></th><td><input type="text" class="regular-text apb-field" name="accent" value="<?php echo esc_attr( (string) ( $c['accent'] ?? '' ) ); ?>" /></td></tr>
		<tr><th><?php esc_html_e( 'Notes', 'apb-sides-database' ); ?></th><td><textarea class="large-text apb-field" name="notes"><?php echo esc_textarea( (string) ( $c['notes'] ?? '' ) ); ?></textarea></td></tr>
		<tr><th><?php esc_html_e( 'Status', 'apb-sides-database' ); ?></th><td><input type="text" class="regular-text apb-field" name="status" value="<?php echo esc_attr( (string) $c['status'] ); ?>" /></td></tr>
	</table>
	<p>
		<button type="button" class="button button-primary apb-save-entity"><?php esc_html_e( 'Save', 'apb-sides-database' ); ?></button>
		<span class="apb-inline-notice"></span>
	</p>
</div>
