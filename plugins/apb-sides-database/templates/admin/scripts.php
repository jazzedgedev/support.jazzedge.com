<?php
/**
 * Scripts list template.
 *
 * @var array<string, mixed> $data Data.
 * @package APB_Sides_Database
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$scripts = $data['scripts'] ?? array();
$total   = (int) ( $data['total'] ?? 0 );
$per     = (int) ( $data['per_page'] ?? 20 );
$paged   = (int) ( $data['current_page'] ?? 1 );
$search  = $data['search'] ?? '';
$pages   = $per > 0 ? (int) ceil( $total / $per ) : 1;
?>
<div class="wrap">
	<h1><?php esc_html_e( 'Scripts', 'apb-sides-database' ); ?></h1>
	<form method="get" action="">
		<input type="hidden" name="page" value="apb-sides-scripts" />
		<p>
			<input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Search title or writer', 'apb-sides-database' ); ?>" />
			<button type="submit" class="button"><?php esc_html_e( 'Search', 'apb-sides-database' ); ?></button>
		</p>
	</form>
	<table class="widefat striped">
		<thead>
			<tr>
				<th><?php esc_html_e( 'ID', 'apb-sides-database' ); ?></th>
				<th><?php esc_html_e( 'Title', 'apb-sides-database' ); ?></th>
				<th><?php esc_html_e( 'Writer', 'apb-sides-database' ); ?></th>
				<th><?php esc_html_e( 'Medium', 'apb-sides-database' ); ?></th>
				<th><?php esc_html_e( 'Status', 'apb-sides-database' ); ?></th>
				<th><?php esc_html_e( 'Created', 'apb-sides-database' ); ?></th>
				<th><?php esc_html_e( 'Actions', 'apb-sides-database' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $scripts as $s ) : ?>
				<tr data-script-id="<?php echo esc_attr( (string) $s['id'] ); ?>">
					<td><?php echo esc_html( (string) $s['id'] ); ?></td>
					<td><?php echo esc_html( (string) $s['title'] ); ?></td>
					<td><?php echo esc_html( (string) ( $s['writer'] ?? '' ) ); ?></td>
					<td><?php echo esc_html( (string) ( $s['medium'] ?? '' ) ); ?></td>
					<td><?php echo wp_kses_post( APB_Sides_Helpers::get_status_badge_html( (string) $s['status'] ) ); ?></td>
					<td><?php echo esc_html( mysql2date( get_option( 'date_format' ), $s['created_at'] ) ); ?></td>
					<td class="apb-actions">
						<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=apb-sides-scripts&id=' . absint( $s['id'] ) ) ); ?>"><?php esc_html_e( 'Edit', 'apb-sides-database' ); ?></a>
						<button type="button" class="button apb-publish-script"><?php esc_html_e( 'Publish', 'apb-sides-database' ); ?></button>
						<button type="button" class="button apb-unpublish-script"><?php esc_html_e( 'Unpublish', 'apb-sides-database' ); ?></button>
						<button type="button" class="button button-link-delete apb-delete-script"><?php esc_html_e( 'Delete', 'apb-sides-database' ); ?></button>
						<span class="apb-inline-notice"></span>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php
	if ( $pages > 1 && $search === '' ) {
		echo '<div class="tablenav"><div class="tablenav-pages">';
		echo paginate_links(
			array(
				'base'      => esc_url( add_query_arg( array( 'page' => 'apb-sides-scripts', 'paged' => '%#%' ), admin_url( 'admin.php' ) ) ),
				'format'    => '',
				'current'   => $paged,
				'total'     => $pages,
				'prev_text' => '&laquo;',
				'next_text' => '&raquo;',
			)
		);
		echo '</div></div>';
	}
	?>
</div>
