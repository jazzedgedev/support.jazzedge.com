<?php
/**
 * Sides list template.
 *
 * @var array<string, mixed> $data Data.
 * @package APB_Sides_Database
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$rows   = $data['sides'] ?? array();
$total  = (int) ( $data['total'] ?? 0 );
$per    = (int) ( $data['per_page'] ?? 20 );
$paged  = (int) ( $data['current_page'] ?? 1 );
$pages  = $per > 0 ? (int) ceil( $total / $per ) : 1;
$titles = $data['script_titles'] ?? array();
?>
<div class="wrap">
	<h1><?php esc_html_e( 'Sides', 'apb-sides-database' ); ?></h1>
	<form method="get" action="">
		<input type="hidden" name="page" value="apb-sides-sides" />
		<select name="script_id">
			<option value="0"><?php esc_html_e( 'All scripts', 'apb-sides-database' ); ?></option>
			<?php foreach ( $data['scripts'] as $sc ) : ?>
				<option value="<?php echo esc_attr( (string) $sc['id'] ); ?>" <?php selected( (int) ( $data['script_id'] ?? 0 ), (int) $sc['id'] ); ?>><?php echo esc_html( (string) $sc['title'] ); ?></option>
			<?php endforeach; ?>
		</select>
		<select name="character_id">
			<option value="0"><?php esc_html_e( 'All characters', 'apb-sides-database' ); ?></option>
			<?php foreach ( $data['characters'] as $ch ) : ?>
				<option value="<?php echo esc_attr( (string) $ch['id'] ); ?>" <?php selected( (int) ( $data['character_id'] ?? 0 ), (int) $ch['id'] ); ?>><?php echo esc_html( (string) $ch['name'] ); ?></option>
			<?php endforeach; ?>
		</select>
		<select name="status">
			<option value=""><?php esc_html_e( 'All statuses', 'apb-sides-database' ); ?></option>
			<?php
			$st_opts = array( APB_Sides_Statuses::REVIEW_PENDING, APB_Sides_Statuses::APPROVED, APB_Sides_Statuses::PUBLISHED, APB_Sides_Statuses::UNPUBLISHED, APB_Sides_Statuses::REJECTED );
			foreach ( $st_opts as $st ) :
				?>
				<option value="<?php echo esc_attr( $st ); ?>" <?php selected( (string) ( $data['status'] ?? '' ), $st ); ?>><?php echo esc_html( APB_Sides_Helpers::status_label( $st ) ); ?></option>
			<?php endforeach; ?>
		</select>
		<button type="submit" class="button"><?php esc_html_e( 'Filter', 'apb-sides-database' ); ?></button>
	</form>
	<table class="widefat striped">
		<thead>
			<tr>
				<th><?php esc_html_e( 'ID', 'apb-sides-database' ); ?></th>
				<th><?php esc_html_e( 'Title', 'apb-sides-database' ); ?></th>
				<th><?php esc_html_e( 'Script', 'apb-sides-database' ); ?></th>
				<th><?php esc_html_e( 'Scene type', 'apb-sides-database' ); ?></th>
				<th><?php esc_html_e( 'Status', 'apb-sides-database' ); ?></th>
				<th><?php esc_html_e( 'Featured', 'apb-sides-database' ); ?></th>
				<th><?php esc_html_e( 'Actions', 'apb-sides-database' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $rows as $r ) : ?>
				<tr data-side-id="<?php echo esc_attr( (string) $r['id'] ); ?>">
					<td><?php echo esc_html( (string) $r['id'] ); ?></td>
					<td><?php echo esc_html( (string) $r['title'] ); ?></td>
					<td><?php echo esc_html( $titles[ (int) $r['script_id'] ] ?? '' ); ?></td>
					<td><?php echo esc_html( (string) ( $r['scene_type'] ?? '' ) ); ?></td>
					<td><?php echo wp_kses_post( APB_Sides_Helpers::get_status_badge_html( (string) $r['status'] ) ); ?></td>
					<td><?php echo ! empty( $r['is_featured'] ) ? '&#9733;' : '—'; ?></td>
					<td class="apb-actions">
						<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=apb-sides-sides&id=' . absint( $r['id'] ) ) ); ?>"><?php esc_html_e( 'Edit', 'apb-sides-database' ); ?></a>
						<button type="button" class="button apb-toggle-featured"><?php esc_html_e( 'Toggle featured', 'apb-sides-database' ); ?></button>
						<button type="button" class="button button-link-delete apb-delete-side"><?php esc_html_e( 'Delete', 'apb-sides-database' ); ?></button>
						<span class="apb-inline-notice"></span>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php
	if ( $pages > 1 ) {
		$q = array(
			'page'         => 'apb-sides-sides',
			'script_id'    => (int) ( $data['script_id'] ?? 0 ),
			'character_id' => (int) ( $data['character_id'] ?? 0 ),
			'status'       => (string) ( $data['status'] ?? '' ),
			'paged'        => '%#%',
		);
		echo '<div class="tablenav"><div class="tablenav-pages">';
		echo paginate_links(
			array(
				'base'      => esc_url_raw( add_query_arg( $q, admin_url( 'admin.php' ) ) ),
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
