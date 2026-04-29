<?php
/**
 * Resource add/edit form.
 *
 * @package APB_Resources
 */

defined( 'ABSPATH' ) || exit;

global $wpdb;

if ( ! isset( $resource ) || ! is_object( $resource ) ) {
	$resource = (object) [
		'id'          => 0,
		'title'       => '',
		'description' => '',
		'url'         => '',
		'image_url'   => '',
		'button_text' => '',
		'category_id' => 0,
		'sort_order'  => 0,
	];
}

$is_new    = empty( $resource->id );
$image_url = $resource->image_url ?? '';

$cat_table = APB_DB::categories_table();
$cats      = $wpdb->get_results(
	"SELECT id, name FROM {$cat_table} ORDER BY sort_order ASC, name ASC",
	ARRAY_A
);
?>
<div class="wrap">
	<h1><?php echo $is_new ? esc_html__( 'Add Resource', 'apb-resources' ) : esc_html__( 'Edit Resource', 'apb-resources' ); ?></h1>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="apb_save_resource" />
		<?php wp_nonce_field( 'apb_save_resource', 'apb_resource_nonce' ); ?>
		<?php if ( ! $is_new ) : ?>
			<input type="hidden" name="id" value="<?php echo esc_attr( (string) absint( $resource->id ) ); ?>" />
		<?php endif; ?>

		<table class="form-table">
			<tr>
				<th scope="row"><label for="apb_res_title"><?php esc_html_e( 'Title', 'apb-resources' ); ?></label></th>
				<td><input name="title" type="text" id="apb_res_title" class="regular-text" value="<?php echo esc_attr( $resource->title ); ?>" required /></td>
			</tr>
			<tr>
				<th scope="row"><label for="apb_res_desc"><?php esc_html_e( 'Description', 'apb-resources' ); ?></label></th>
				<td><textarea name="description" id="apb_res_desc" class="large-text" rows="4"><?php echo esc_textarea( $resource->description ); ?></textarea></td>
			</tr>
			<tr>
				<th scope="row"><label for="apb_res_url"><?php esc_html_e( 'URL', 'apb-resources' ); ?></label></th>
				<td><input name="url" type="url" id="apb_res_url" class="regular-text" value="<?php echo esc_attr( $resource->url ); ?>" required /></td>
			</tr>
			<tr>
				<th scope="row">
					<label for="apb_image_url"><?php esc_html_e( 'Image', 'apb-resources' ); ?></label><br>
					<small><?php esc_html_e( 'Book cover — portrait orientation', 'apb-resources' ); ?></small>
				</th>
				<td>
					<input type="hidden" name="apb_image_url" id="apb_image_url" value="<?php echo esc_attr( $image_url ); ?>">
					<button type="button" id="apb-upload-image-btn" class="button"><?php esc_html_e( 'Select Image', 'apb-resources' ); ?></button>
					<button type="button" id="apb-remove-image-btn" class="button" <?php echo $image_url ? '' : 'style="display:none"'; ?>><?php esc_html_e( 'Remove', 'apb-resources' ); ?></button>
					<div id="apb-image-preview">
						<?php if ( $image_url ) : ?>
							<img src="<?php echo esc_url( $image_url ); ?>" alt="" style="max-width:200px;height:280px;object-fit:cover;border-radius:4px;margin-top:8px;">
						<?php endif; ?>
					</div>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="apb_res_btn"><?php esc_html_e( 'Button Text', 'apb-resources' ); ?></label></th>
				<td><input name="button_text" type="text" id="apb_res_btn" class="regular-text" value="<?php echo esc_attr( $resource->button_text ); ?>" placeholder="<?php esc_attr_e( 'Learn more...', 'apb-resources' ); ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="apb_res_cat"><?php esc_html_e( 'Category', 'apb-resources' ); ?></label></th>
				<td>
					<select name="category_id" id="apb_res_cat" required>
						<option value=""><?php esc_html_e( '— Select —', 'apb-resources' ); ?></option>
						<?php
						$sel = isset( $resource->category_id ) ? absint( $resource->category_id ) : 0;
						if ( is_array( $cats ) ) {
							foreach ( $cats as $c ) {
								if ( ! isset( $c['id'], $c['name'] ) ) {
									continue;
								}
								printf(
									'<option value="%s"%s>%s</option>',
									esc_attr( (string) $c['id'] ),
									selected( $sel, (int) $c['id'], false ),
									esc_html( $c['name'] )
								);
							}
						}
						?>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="apb_res_order"><?php esc_html_e( 'Sort Order', 'apb-resources' ); ?></label></th>
				<td><input name="sort_order" type="number" id="apb_res_order" class="small-text" value="<?php echo esc_attr( (string) (int) $resource->sort_order ); ?>" /></td>
			</tr>
		</table>
		<?php submit_button( __( 'Save Resource', 'apb-resources' ) ); ?>
	</form>
</div>
