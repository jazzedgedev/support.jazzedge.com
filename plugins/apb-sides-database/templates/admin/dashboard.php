<?php
/**
 * Admin dashboard template.
 *
 * @var array<string, mixed> $data Metrics + db_tables.
 * @package APB_Sides_Database
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$db_tables = isset( $data['db_tables'] ) && is_array( $data['db_tables'] ) ? $data['db_tables'] : array();
?>
<div class="wrap apb-sides-dashboard">
	<h1><?php esc_html_e( 'APB Sides Database', 'apb-sides-database' ); ?></h1>
	<div class="apb-dash-grid">
		<div class="apb-dash-card">
			<h2><?php esc_html_e( 'Total uploads', 'apb-sides-database' ); ?></h2>
			<p class="apb-dash-number"><?php echo esc_html( (string) ( $data['total_uploads'] ?? 0 ) ); ?></p>
			<p><a href="<?php echo esc_url( admin_url( 'admin.php?page=apb-sides-uploads' ) ); ?>"><?php esc_html_e( 'View uploads', 'apb-sides-database' ); ?></a></p>
		</div>
		<div class="apb-dash-card">
			<h2><?php esc_html_e( 'Published scripts', 'apb-sides-database' ); ?></h2>
			<p class="apb-dash-number"><?php echo esc_html( (string) ( $data['published_scripts'] ?? 0 ) ); ?></p>
			<p><a href="<?php echo esc_url( admin_url( 'admin.php?page=apb-sides-scripts' ) ); ?>"><?php esc_html_e( 'Scripts', 'apb-sides-database' ); ?></a></p>
		</div>
		<div class="apb-dash-card">
			<h2><?php esc_html_e( 'Published sides', 'apb-sides-database' ); ?></h2>
			<p class="apb-dash-number"><?php echo esc_html( (string) ( $data['published_sides'] ?? 0 ) ); ?></p>
			<p><a href="<?php echo esc_url( admin_url( 'admin.php?page=apb-sides-sides' ) ); ?>"><?php esc_html_e( 'Sides', 'apb-sides-database' ); ?></a></p>
		</div>
	</div>

	<details class="apb-db-status-wrap">
		<summary class="apb-db-status-summary"><?php esc_html_e( 'Database Status', 'apb-sides-database' ); ?></summary>
		<div class="apb-db-status-inner">
			<p class="description"><?php esc_html_e( 'Shows whether plugin tables exist and how many rows each contains.', 'apb-sides-database' ); ?></p>
			<table class="widefat striped apb-db-status-table">
				<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'Table', 'apb-sides-database' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Exists', 'apb-sides-database' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Rows', 'apb-sides-database' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $db_tables as $row ) : ?>
						<tr>
							<td><code><?php echo esc_html( (string) ( $row['name'] ?? '' ) ); ?></code></td>
							<td><?php echo ! empty( $row['exists'] ) ? esc_html__( 'Yes', 'apb-sides-database' ) : esc_html__( 'No', 'apb-sides-database' ); ?></td>
							<td><?php echo esc_html( (string) ( $row['count'] ?? 'N/A' ) ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<p>
				<button type="button" class="button button-secondary" id="apb-reinstall-tables">
					<?php esc_html_e( 'Reinstall Tables', 'apb-sides-database' ); ?>
				</button>
				<span class="apb-inline-notice apb-reinstall-msg" id="apb-reinstall-result" aria-live="polite"></span>
			</p>
		</div>
	</details>
</div>
