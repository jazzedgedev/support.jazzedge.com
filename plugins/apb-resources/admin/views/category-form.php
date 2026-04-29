<?php
/**
 * Category add/edit form.
 *
 * @package APB_Resources
 */

defined( 'ABSPATH' ) || exit;

if ( ! isset( $category ) || ! is_object( $category ) ) {
	$category = (object) [
		'id'            => 0,
		'name'          => '',
		'slug'          => '',
		'description'   => '',
		'page_id'       => 0,
		'sort_order'    => 0,
		'hero_image_id' => 0,
		'card_image_id' => 0,
	];
}

$is_new   = empty( $category->id );
$page_sel = isset( $category->page_id ) ? absint( $category->page_id ) : 0;

$hero_id  = isset( $category->hero_image_id ) ? absint( $category->hero_image_id ) : 0;
$card_id  = isset( $category->card_image_id ) ? absint( $category->card_image_id ) : 0;
$hero_url = $hero_id ? wp_get_attachment_image_url( $hero_id, 'large' ) : '';
$card_url = $card_id ? wp_get_attachment_image_url( $card_id, 'large' ) : '';
?>
<div class="wrap">
	<h1><?php echo $is_new ? esc_html__( 'Add Category', 'apb-resources' ) : esc_html__( 'Edit Category', 'apb-resources' ); ?></h1>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="apb_save_category" />
		<?php wp_nonce_field( 'apb_save_category', 'apb_category_nonce' ); ?>
		<?php if ( ! $is_new ) : ?>
			<input type="hidden" name="id" value="<?php echo esc_attr( (string) absint( $category->id ) ); ?>" />
		<?php endif; ?>

		<table class="form-table">
			<tr>
				<th scope="row"><label for="apb_cat_name"><?php esc_html_e( 'Name', 'apb-resources' ); ?></label></th>
				<td><input name="name" type="text" id="apb_cat_name" class="regular-text" value="<?php echo esc_attr( $category->name ); ?>" required /></td>
			</tr>
			<tr>
				<th scope="row"><label for="apb_cat_slug"><?php esc_html_e( 'Slug', 'apb-resources' ); ?></label></th>
				<td><input name="slug" type="text" id="apb_cat_slug" class="regular-text" value="<?php echo esc_attr( $category->slug ); ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="apb_cat_desc"><?php esc_html_e( 'Description', 'apb-resources' ); ?></label></th>
				<td><textarea name="description" id="apb_cat_desc" class="large-text" rows="4"><?php echo esc_textarea( $category->description ); ?></textarea></td>
			</tr>
			<tr>
				<th scope="row">
					<label for="apb_hero_image_id"><?php esc_html_e( 'Hero Image', 'apb-resources' ); ?></label><br>
					<small><?php esc_html_e( '1200 × 400px — shown at top of category page. Include title/branding baked into the image.', 'apb-resources' ); ?></small>
				</th>
				<td>
					<input type="hidden" name="apb_hero_image_id" id="apb_hero_image_id" value="<?php echo esc_attr( $hero_id ? (string) $hero_id : '' ); ?>">
					<button type="button" class="button apb-upload-image-btn" data-field="hero_image"><?php esc_html_e( 'Select Hero Image', 'apb-resources' ); ?></button>
					<button type="button" class="button apb-remove-image-btn" id="apb-hero_image-remove" data-field="hero_image" <?php echo $hero_id ? '' : 'style="display:none"'; ?>><?php esc_html_e( 'Remove', 'apb-resources' ); ?></button>
					<div id="apb-hero_image-preview">
						<?php if ( $hero_url ) : ?>
							<img src="<?php echo esc_url( $hero_url ); ?>" alt="" style="max-width:400px;height:200px;object-fit:cover;border-radius:4px;margin-top:8px;">
						<?php endif; ?>
					</div>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="apb_card_image_id"><?php esc_html_e( 'Card Image', 'apb-resources' ); ?></label><br>
					<small><?php esc_html_e( '600 × 300px — shown on the resources dashboard grid. Clean photo, no text overlay.', 'apb-resources' ); ?></small>
				</th>
				<td>
					<input type="hidden" name="apb_card_image_id" id="apb_card_image_id" value="<?php echo esc_attr( $card_id ? (string) $card_id : '' ); ?>">
					<button type="button" class="button apb-upload-image-btn" data-field="card_image"><?php esc_html_e( 'Select Card Image', 'apb-resources' ); ?></button>
					<button type="button" class="button apb-remove-image-btn" id="apb-card_image-remove" data-field="card_image" <?php echo $card_id ? '' : 'style="display:none"'; ?>><?php esc_html_e( 'Remove', 'apb-resources' ); ?></button>
					<div id="apb-card_image-preview">
						<?php if ( $card_url ) : ?>
							<img src="<?php echo esc_url( $card_url ); ?>" alt="" style="max-width:300px;height:150px;object-fit:cover;border-radius:4px;margin-top:8px;">
						<?php endif; ?>
					</div>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="apb_cat_page"><?php esc_html_e( 'Linked Page', 'apb-resources' ); ?></label></th>
				<td>
					<?php
					wp_dropdown_pages(
						[
							'name'              => 'page_id',
							'selected'          => $page_sel,
							'show_option_none'  => __( '— None —', 'apb-resources' ),
							'option_none_value' => '0',
						]
					);
					?>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="apb_cat_order"><?php esc_html_e( 'Sort Order', 'apb-resources' ); ?></label></th>
				<td><input name="sort_order" type="number" id="apb_cat_order" class="small-text" value="<?php echo esc_attr( (string) (int) $category->sort_order ); ?>" /></td>
			</tr>
		</table>
		<?php submit_button( __( 'Save Category', 'apb-resources' ) ); ?>
	</form>
	<?php if ( $is_new ) : ?>
		<script>
		(function() {
			var name = document.getElementById('apb_cat_name');
			var slug = document.getElementById('apb_cat_slug');
			if (!name || !slug || slug.value) return;
			name.addEventListener('blur', function() {
				if (!slug.value) {
					slug.value = name.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
				}
			});
		})();
		</script>
	<?php endif; ?>
</div>
