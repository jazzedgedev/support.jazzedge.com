<?php
/**
 * JSON import screen.
 *
 * @package APB_Resources
 */

defined( 'ABSPATH' ) || exit;

global $wpdb;

$cat_table  = APB_DB::categories_table();
$categories = $wpdb->get_results(
	"SELECT id, name FROM {$cat_table} ORDER BY sort_order ASC"
);
?>
<div class="wrap">
	<h1><?php echo apb_icon_import(); ?> <?php esc_html_e( 'Import Resources from JSON', 'apb-resources' ); ?></h1>

	<?php
	if ( isset( $_GET['apb_import_error'] ) && 'invalid_json' === sanitize_text_field( wp_unslash( $_GET['apb_import_error'] ) ) ) :
		?>
		<div class="notice notice-error"><p><?php esc_html_e( 'Invalid JSON.', 'apb-resources' ); ?></p></div>
	<?php endif; ?>

	<?php
	if ( isset( $_GET['apb_import_error'] ) && 'no_category' === sanitize_text_field( wp_unslash( $_GET['apb_import_error'] ) ) ) :
		?>
		<div class="notice notice-error is-dismissible"><p><?php esc_html_e( 'Please select a category before importing.', 'apb-resources' ); ?></p></div>
	<?php endif; ?>

	<?php
	if ( isset( $_GET['apb_imported'] ) ) :
		?>
		<div class="notice notice-success is-dismissible">
			<p>
				<?php
				printf(
					/* translators: %d: number imported */
					esc_html__( 'Imported %d resources.', 'apb-resources' ),
					absint( wp_unslash( $_GET['apb_imported'] ) )
				);
				if ( isset( $_GET['apb_skipped'] ) ) {
					echo ' ';
					printf(
						/* translators: %d: skipped count */
						esc_html__( 'Skipped %d rows.', 'apb-resources' ),
						absint( wp_unslash( $_GET['apb_skipped'] ) )
					);
				}
				?>
			</p>
		</div>
	<?php endif; ?>

	<p>
		<?php esc_html_e( 'Select a category, then paste a JSON array. Each item needs:', 'apb-resources' ); ?>
		<code>title</code>, <code>url</code>.
		<?php esc_html_e( 'Optional:', 'apb-resources' ); ?>
		<code>description</code>, <code>button_text</code>, <code>sort_order</code>.
		<?php esc_html_e( 'No', 'apb-resources' ); ?> <code>category</code> <?php esc_html_e( 'field needed.', 'apb-resources' ); ?>
	</p>

	<?php if ( empty( $categories ) ) : ?>
		<div class="notice notice-warning">
			<p>
				<?php esc_html_e( 'No categories exist yet.', 'apb-resources' ); ?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=apb-resource-categories&action=add' ) ); ?>"><?php esc_html_e( 'Add a category first →', 'apb-resources' ); ?></a>
			</p>
		</div>
	<?php else : ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="apb_import_json">
		<?php wp_nonce_field( 'apb_import_nonce', 'apb_import_nonce' ); ?>

		<table class="form-table">
			<tr>
				<th scope="row"><label for="apb_category_id"><?php esc_html_e( 'Assign to Category', 'apb-resources' ); ?></label></th>
				<td>
					<select name="apb_category_id" id="apb_category_id" required>
						<option value=""><?php esc_html_e( '— Select a category —', 'apb-resources' ); ?></option>
						<?php foreach ( $categories as $cat ) : ?>
							<?php
							if ( ! isset( $cat->id, $cat->name ) ) {
								continue;
							}
							?>
							<option value="<?php echo esc_attr( (string) $cat->id ); ?>"><?php echo esc_html( $cat->name ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
		</table>

		<textarea name="apb_json" rows="20" style="width:100%;font-family:monospace;"></textarea>
		<p><input type="submit" class="button button-primary" value="<?php esc_attr_e( 'Import', 'apb-resources' ); ?>"></p>
	</form>

	<?php endif; ?>
</div>
