<?php
/**
 * Characters list template.
 *
 * @var array<string, mixed> $data Data.
 * @package APB_Sides_Database
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$rows    = $data['characters'] ?? array();
$scripts = $data['scripts'] ?? array();
$total   = (int) ( $data['total'] ?? 0 );
$per     = (int) ( $data['per_page'] ?? 20 );
$paged   = (int) ( $data['current_page'] ?? 1 );
$sid     = (int) ( $data['script_id'] ?? 0 );
$search  = $data['search'] ?? '';
$pages   = $per > 0 ? (int) ceil( $total / $per ) : 1;
$script_titles = array();
foreach ( $scripts as $sc ) {
	$script_titles[ (int) $sc['id'] ] = (string) $sc['title'];
}
?>
<div class="wrap">
	<h1><?php esc_html_e( 'Characters', 'apb-sides-database' ); ?></h1>
	<form method="get" action="">
		<input type="hidden" name="page" value="apb-sides-characters" />
		<select name="script_id">
			<option value="0"><?php esc_html_e( 'All scripts', 'apb-sides-database' ); ?></option>
			<?php foreach ( $scripts as $sc ) : ?>
				<option value="<?php echo esc_attr( (string) $sc['id'] ); ?>" <?php selected( $sid, (int) $sc['id'] ); ?>><?php echo esc_html( (string) $sc['title'] ); ?></option>
			<?php endforeach; ?>
		</select>
		<input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Name', 'apb-sides-database' ); ?>" />
		<button type="submit" class="button"><?php esc_html_e( 'Filter', 'apb-sides-database' ); ?></button>
	</form>
	<table class="widefat striped">
		<thead>
			<tr>
				<th><?php esc_html_e( 'ID', 'apb-sides-database' ); ?></th>
				<th><?php esc_html_e( 'Name', 'apb-sides-database' ); ?></th>
				<th><?php esc_html_e( 'Script', 'apb-sides-database' ); ?></th>
				<th><?php esc_html_e( 'Gender', 'apb-sides-database' ); ?></th>
				<th><?php esc_html_e( 'Age range', 'apb-sides-database' ); ?></th>
				<th><?php esc_html_e( 'Status', 'apb-sides-database' ); ?></th>
				<th><?php esc_html_e( 'Actions', 'apb-sides-database' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $rows as $r ) : ?>
				<tr data-character-id="<?php echo esc_attr( (string) $r['id'] ); ?>">
					<td><?php echo esc_html( (string) $r['id'] ); ?></td>
					<td><?php echo esc_html( (string) $r['name'] ); ?></td>
					<td><?php echo esc_html( $script_titles[ (int) $r['script_id'] ] ?? '' ); ?></td>
					<td><?php echo esc_html( (string) ( $r['gender'] ?? '' ) ); ?></td>
					<td><?php echo esc_html( (string) ( $r['age_range_label'] ?? '' ) ); ?></td>
					<td><?php echo wp_kses_post( APB_Sides_Helpers::get_status_badge_html( (string) $r['status'] ) ); ?></td>
					<td>
						<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=apb-sides-characters&id=' . absint( $r['id'] ) ) ); ?>"><?php esc_html_e( 'Edit', 'apb-sides-database' ); ?></a>
						<button type="button" class="button button-link-delete apb-delete-character"><?php esc_html_e( 'Delete', 'apb-sides-database' ); ?></button>
						<span class="apb-inline-notice"></span>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php
	if ( $pages > 1 ) {
		echo '<div class="tablenav"><div class="tablenav-pages">';
		echo paginate_links(
			array(
				'base'      => esc_url( add_query_arg( array( 'page' => 'apb-sides-characters', 'paged' => '%#%', 'script_id' => $sid, 's' => $search ), admin_url( 'admin.php' ) ) ),
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
