<?php
/**
 * Shortcodes reference, category card images, copy helpers.
 *
 * @package APB_Resources
 */

defined( 'ABSPATH' ) || exit;

if ( ! current_user_can( 'manage_options' ) ) {
	return;
}

global $wpdb;

$cat_table  = APB_DB::categories_table();
$categories = $wpdb->get_results(
	"SELECT * FROM {$cat_table} ORDER BY sort_order ASC, id ASC",
	OBJECT
);
if ( ! is_array( $categories ) ) {
	$categories = [];
}
?>
<div class="wrap">
	<h1><?php esc_html_e( 'Resource Shortcodes', 'apb-resources' ); ?></h1>
	<p>
		<?php esc_html_e( 'Click', 'apb-resources' ); ?>
		<strong><?php esc_html_e( 'Copy', 'apb-resources' ); ?></strong>
		<?php esc_html_e( 'to copy a shortcode. Set a', 'apb-resources' ); ?>
		<strong>600 × 300px</strong>
		<?php esc_html_e( 'card image for each category to enhance the dashboard display.', 'apb-resources' ); ?>
	</p>

	<?php if ( isset( $_GET['apb_image_saved'] ) ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Category image saved.', 'apb-resources' ); ?></p></div>
	<?php endif; ?>

	<h2 style="margin-top:1.5rem;"><?php esc_html_e( 'Dashboard', 'apb-resources' ); ?></h2>
	<table class="widefat striped apb-shortcodes-table">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Description', 'apb-resources' ); ?></th>
				<th><?php esc_html_e( 'Shortcode', 'apb-resources' ); ?></th>
				<th style="width:90px;"></th>
			</tr>
		</thead>
		<tbody>
			<tr class="apb-shortcode-row">
				<td><?php esc_html_e( 'Displays a card grid linking to all resource categories', 'apb-resources' ); ?></td>
				<td><code>[apb_resources_dashboard]</code></td>
				<td><button type="button" class="button apb-copy-btn"><?php esc_html_e( 'Copy', 'apb-resources' ); ?></button></td>
			</tr>
		</tbody>
	</table>

	<h2 style="margin-top:2rem;"><?php esc_html_e( 'Category Pages', 'apb-resources' ); ?></h2>
	<?php if ( empty( $categories ) ) : ?>
		<p>
			<?php esc_html_e( 'No categories yet.', 'apb-resources' ); ?>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=apb-resource-categories&action=add' ) ); ?>"><?php esc_html_e( 'Add your first category →', 'apb-resources' ); ?></a>
		</p>
	<?php else : ?>
	<table class="widefat apb-shortcodes-table" style="border-collapse:separate;border-spacing:0 8px;">
		<thead>
			<tr>
				<th style="width:180px;">
					<?php esc_html_e( 'Card Image', 'apb-resources' ); ?>
					<br><small style="color:#888;font-size:11px;font-weight:400;"><?php esc_html_e( 'Card image — 600×300px', 'apb-resources' ); ?></small>
				</th>
				<th><?php esc_html_e( 'Category', 'apb-resources' ); ?></th>
				<th><?php esc_html_e( 'Shortcode', 'apb-resources' ); ?></th>
				<th style="width:90px;"></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $categories as $cat ) : ?>
				<?php
				if ( ! isset( $cat->id, $cat->name, $cat->slug ) ) {
					continue;
				}
				$card_img_id = isset( $cat->card_image_id ) ? absint( $cat->card_image_id ) : 0;
				$image_url   = $card_img_id ? wp_get_attachment_image_url( $card_img_id, 'medium' ) : '';
				?>
			<tr class="apb-shortcode-row" style="background:#fff;">
				<td style="vertical-align:middle;">
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:flex;flex-direction:column;gap:6px;align-items:flex-start;">
						<input type="hidden" name="action" value="apb_save_category_image">
						<input type="hidden" name="apb_category_id" value="<?php echo esc_attr( (string) $cat->id ); ?>">
						<input type="hidden" name="apb_image_id" id="apb-img-id-<?php echo esc_attr( (string) $cat->id ); ?>" value="<?php echo esc_attr( $card_img_id ? (string) $card_img_id : '' ); ?>">
						<?php wp_nonce_field( 'apb_save_category_image', 'apb_cat_image_nonce' ); ?>

						<div id="apb-preview-<?php echo esc_attr( (string) $cat->id ); ?>" style="width:160px;height:80px;background:#f0f0f0;border-radius:4px;overflow:hidden;border:1px solid #ddd;">
							<?php if ( $image_url ) : ?>
								<img src="<?php echo esc_url( $image_url ); ?>" alt="" style="width:100%;height:100%;object-fit:cover;display:block;">
							<?php else : ?>
								<span style="display:flex;align-items:center;justify-content:center;height:100%;color:#aaa;font-size:12px;"><?php esc_html_e( 'No image', 'apb-resources' ); ?></span>
							<?php endif; ?>
						</div>

						<div style="display:flex;gap:4px;flex-wrap:wrap;">
							<button type="button" class="button button-small apb-sc-upload-btn" data-id="<?php echo esc_attr( (string) $cat->id ); ?>"><?php esc_html_e( 'Select', 'apb-resources' ); ?></button>
							<?php if ( $card_img_id ) : ?>
							<button type="button" class="button button-small apb-sc-remove-btn" data-id="<?php echo esc_attr( (string) $cat->id ); ?>"><?php esc_html_e( 'Remove', 'apb-resources' ); ?></button>
							<?php endif; ?>
							<button type="submit" class="button button-small button-primary"><?php esc_html_e( 'Save', 'apb-resources' ); ?></button>
						</div>
					</form>
				</td>
				<td style="vertical-align:middle;"><?php echo esc_html( $cat->name ); ?></td>
				<td style="vertical-align:middle;"><code>[apb_resources category="<?php echo esc_attr( $cat->slug ); ?>"]</code></td>
				<td style="vertical-align:middle;"><button type="button" class="button apb-copy-btn"><?php esc_html_e( 'Copy', 'apb-resources' ); ?></button></td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php endif; ?>
</div>

<style>
.apb-shortcodes-table code {
	background: #f0f0f0;
	padding: 4px 8px;
	border-radius: 3px;
	font-size: 13px;
	user-select: all;
}
.apb-copy-btn { min-width: 70px; }
.apb-copy-btn.apb-copied {
	background: #66c5b4 !important;
	border-color: #66c5b4 !important;
	color: #fff !important;
}
</style>
