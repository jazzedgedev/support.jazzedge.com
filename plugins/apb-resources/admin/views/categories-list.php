<?php
/**
 * Categories list.
 *
 * @package APB_Resources
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Resource Categories', 'apb-resources' ); ?></h1>
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=apb-resource-categories&action=add' ) ); ?>" class="page-title-action"><?php esc_html_e( 'Add New', 'apb-resources' ); ?></a>
	<?php if ( isset( $_GET['apb_saved'] ) ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Saved.', 'apb-resources' ); ?></p></div>
	<?php endif; ?>
	<?php if ( isset( $_GET['apb_deleted'] ) ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Category deleted.', 'apb-resources' ); ?></p></div>
	<?php endif; ?>
	<?php
	$table = new APB_Categories_Table();
	$table->prepare_items();
	$table->display();
	?>
</div>
