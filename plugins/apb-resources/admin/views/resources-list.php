<?php
/**
 * Resources list with bulk category change.
 *
 * @package APB_Resources
 */

defined( 'ABSPATH' ) || exit;

global $wpdb;

$cat_table  = APB_DB::categories_table();
$categories = $wpdb->get_results(
	"SELECT id, name FROM {$cat_table} ORDER BY sort_order ASC"
);

$filter_cat = isset( $_GET['category_id'] ) ? absint( wp_unslash( $_GET['category_id'] ) ) : 0;
?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Resources', 'apb-resources' ); ?></h1>
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=apb-resources&action=add' ) ); ?>" class="page-title-action"><?php esc_html_e( 'Add New', 'apb-resources' ); ?></a>

	<?php if ( isset( $_GET['apb_saved'] ) ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Saved.', 'apb-resources' ); ?></p></div>
	<?php endif; ?>
	<?php if ( isset( $_GET['apb_deleted'] ) ) : ?>
		<div class="notice notice-success is-dismissible">
			<p>
				<?php
				printf(
					/* translators: %d: number of resources deleted */
					esc_html__( '%d resource(s) deleted.', 'apb-resources' ),
					absint( wp_unslash( $_GET['apb_deleted'] ) )
				);
				?>
			</p>
		</div>
	<?php endif; ?>
	<?php if ( isset( $_GET['apb_updated'] ) ) : ?>
		<div class="notice notice-success is-dismissible">
			<p>
				<?php
				printf(
					/* translators: %d: number of resources moved */
					esc_html__( '%d resource(s) moved to new category.', 'apb-resources' ),
					absint( wp_unslash( $_GET['apb_updated'] ) )
				);
				?>
			</p>
		</div>
	<?php endif; ?>
	<?php if ( isset( $_GET['apb_bulk_error'] ) ) : ?>
		<div class="notice notice-error is-dismissible"><p><?php esc_html_e( 'Please select a category before applying.', 'apb-resources' ); ?></p></div>
	<?php endif; ?>

	<div class="tablenav top">
		<form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" class="alignleft actions">
			<input type="hidden" name="page" value="apb-resources" />
			<label for="apb-filter-category" class="screen-reader-text"><?php esc_html_e( 'Filter by category', 'apb-resources' ); ?></label>
			<select name="category_id" id="apb-filter-category">
				<option value=""><?php esc_html_e( 'All Categories', 'apb-resources' ); ?></option>
				<?php if ( is_array( $categories ) ) : ?>
					<?php foreach ( $categories as $cat ) : ?>
						<?php
						if ( ! isset( $cat->id, $cat->name ) ) {
							continue;
						}
						?>
						<option value="<?php echo esc_attr( (string) $cat->id ); ?>" <?php selected( $filter_cat, (int) $cat->id ); ?>>
							<?php echo esc_html( $cat->name ); ?>
						</option>
					<?php endforeach; ?>
				<?php endif; ?>
			</select>
			<?php submit_button( __( 'Filter', 'apb-resources' ), '', '', false ); ?>
		</form>
		<div class="clear"></div>
	</div>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return apbConfirmBulk(event);">
		<input type="hidden" name="action" value="apb_bulk_resources" />
		<?php wp_nonce_field( 'apb_bulk_change_category', 'apb_bulk_nonce' ); ?>
		<?php if ( $filter_cat > 0 ) : ?>
			<input type="hidden" name="apb_filter_category_id" value="<?php echo esc_attr( (string) $filter_cat ); ?>" />
		<?php endif; ?>

		<div class="tablenav top">
			<div class="alignleft actions bulkactions">
				<label for="apb-bulk-action" class="screen-reader-text"><?php esc_html_e( 'Select bulk action', 'apb-resources' ); ?></label>
				<select id="apb-bulk-action" name="apb_bulk_action">
					<option value=""><?php esc_html_e( '— Bulk Actions —', 'apb-resources' ); ?></option>
					<option value="change_category"><?php esc_html_e( 'Change Category', 'apb-resources' ); ?></option>
					<option value="delete"><?php esc_html_e( 'Delete Selected', 'apb-resources' ); ?></option>
				</select>

				<label for="apb-new-category" class="screen-reader-text"><?php esc_html_e( 'Move to category', 'apb-resources' ); ?></label>
				<select id="apb-new-category" name="apb_new_category_id">
					<option value=""><?php esc_html_e( '— Select New Category —', 'apb-resources' ); ?></option>
					<?php if ( is_array( $categories ) ) : ?>
						<?php foreach ( $categories as $cat ) : ?>
							<?php
							if ( ! isset( $cat->id, $cat->name ) ) {
								continue;
							}
							?>
							<option value="<?php echo esc_attr( (string) $cat->id ); ?>"><?php echo esc_html( $cat->name ); ?></option>
						<?php endforeach; ?>
					<?php endif; ?>
				</select>

				<?php submit_button( __( 'Apply', 'apb-resources' ), 'action', '', false ); ?>
			</div>
			<div class="clear"></div>
		</div>

		<?php
		$table = new APB_Resources_Table();
		$table->prepare_items();
		$table->display();
		?>
	</form>

	<script>
	function apbConfirmBulk(e) {
		var action = document.getElementById('apb-bulk-action').value;
		if ( action === 'delete' ) {
			var checked = document.querySelectorAll('input[name="apb_resource_ids[]"]:checked').length;
			if ( checked === 0 ) { e.preventDefault(); return false; }
			return confirm('Delete ' + checked + ' resource(s)? This cannot be undone.');
		}
		return true;
	}
	</script>
</div>
