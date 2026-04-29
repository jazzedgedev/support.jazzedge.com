<?php
/**
 * Settings page template.
 *
 * @var array<string, mixed> $data Data including table_status.
 * @package APB_Sides_Database
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$table_status      = isset( $data['table_status'] ) && is_array( $data['table_status'] ) ? $data['table_status'] : array();
$claude_configured = ! empty( $data['claude_configured'] );
?>
<div class="wrap">
	<h1><?php esc_html_e( 'APB Sides Settings', 'apb-sides-database' ); ?></h1>
	<form method="post" action="options.php">
		<?php
		settings_fields( 'apb_sides_settings_group' );
		do_settings_sections( 'apb-sides-settings' );
		submit_button();
		?>
	</form>
	<?php if ( ! empty( $data['api_key_hint'] ) ) : ?>
		<p class="description">
			Stored key: <code><?php echo esc_html( $data['api_key_hint'] ); ?></code> — leave blank to keep it, or enter a new key to replace it.
		</p>
	<?php else : ?>
		<p class="description">No key stored yet.</p>
	<?php endif; ?>

	<div class="apb-settings-pdf-capabilities">
		<h2><?php esc_html_e( 'PDF extraction capabilities', 'apb-sides-database' ); ?></h2>
		<ul class="apb-cap-list">
			<li>
				<?php esc_html_e( 'Claude API configured:', 'apb-sides-database' ); ?>
				<strong><?php echo $claude_configured ? '✅' : '❌'; ?></strong>
			</li>
			<li>
				<?php esc_html_e( 'Native PDF support (Claude):', 'apb-sides-database' ); ?>
				<strong>✅</strong>
			</li>
		</ul>
	</div>

	<div class="apb-shortcode-reference">
		<h2><?php esc_html_e( 'Available Shortcodes', 'apb-sides-database' ); ?></h2>
		<p class="description"><?php esc_html_e( 'Use these shortcodes on any WordPress page or post.', 'apb-sides-database' ); ?></p>

		<div class="apb-shortcode-card">
			<code>[apb_sides_search]</code>
			<p><?php esc_html_e( 'Displays the full searchable and filterable sides database. Includes keyword search, filter panel (medium, gender, age range, scene type, tone, energy level, dialogue type, difficulty), AJAX-powered results grid, and pagination. Place on any page where you want users to browse and search sides.', 'apb-sides-database' ); ?></p>
		</div>

		<div class="apb-shortcode-card">
			<code>[apb_side_detail id="123"]</code>
			<p><?php esc_html_e( 'Displays the full detail view for a single side. Replace 123 with the side ID. Shows title, script, characters, scene stats, scene context, actor notes, emotions, acting skills, and script excerpt. Only published sides are visible to non-admins.', 'apb-sides-database' ); ?></p>
			<p class="apb-shortcode-note"><?php esc_html_e( 'Replace `123` with the actual record ID from the database.', 'apb-sides-database' ); ?></p>
		</div>

		<div class="apb-shortcode-card">
			<code>[apb_script_detail id="123"]</code>
			<p><?php esc_html_e( 'Displays the full detail view for a single script. Replace 123 with the script ID. Shows script metadata and a list of all published sides from that script.', 'apb-sides-database' ); ?></p>
			<p class="apb-shortcode-note"><?php esc_html_e( 'Replace `123` with the actual record ID from the database.', 'apb-sides-database' ); ?></p>
		</div>

		<div class="apb-shortcode-card">
			<code>[apb_side_detail id="123"]</code>
			<p><?php esc_html_e( 'Displays the full detail view for a single character. Replace 123 with the character ID. Shows character profile and a list of all published sides featuring that character.', 'apb-sides-database' ); ?></p>
			<p class="apb-shortcode-note"><?php esc_html_e( 'Replace `123` with the actual record ID from the database.', 'apb-sides-database' ); ?></p>
		</div>

		<p class="apb-shortcode-footer-note"><?php esc_html_e( 'Side, Script, and Character IDs can be found in the ID column of their respective admin list pages.', 'apb-sides-database' ); ?></p>
	</div>

	<div class="apb-settings-db-status">
		<h2><?php esc_html_e( 'Database Status', 'apb-sides-database' ); ?></h2>
		<table class="widefat striped apb-db-status-table">
			<thead>
				<tr>
					<th scope="col"><?php esc_html_e( 'Table Name', 'apb-sides-database' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Status', 'apb-sides-database' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Row Count', 'apb-sides-database' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $table_status as $row ) : ?>
					<tr>
						<td><?php echo esc_html( (string) ( $row['name'] ?? '' ) ); ?></td>
						<td>
							<?php if ( ! empty( $row['exists'] ) ) : ?>
								<span class="apb-db-ok"><?php esc_html_e( 'OK', 'apb-sides-database' ); ?></span>
							<?php else : ?>
								<span class="apb-db-missing"><?php esc_html_e( 'MISSING', 'apb-sides-database' ); ?></span>
							<?php endif; ?>
						</td>
						<td>
							<?php
							if ( ! empty( $row['exists'] ) && isset( $row['count'] ) ) {
								echo esc_html( (string) (int) $row['count'] );
							} else {
								echo esc_html( '—' );
							}
							?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<p>
			<button type="button" id="apb-reinstall-tables" class="button button-secondary"><?php esc_html_e( 'Reinstall Missing Tables', 'apb-sides-database' ); ?></button>
			<button type="button" id="apb-migrate-schema" class="button button-secondary" style="margin-left:8px;"><?php esc_html_e( 'Migrate Schema (Remove Old Columns)', 'apb-sides-database' ); ?></button>
			<span id="apb-reinstall-result" style="margin-left:10px;"></span>
			<span id="apb-migrate-result" style="margin-left:10px;"></span>
		</p>
	</div>

	<?php
	$log_path         = isset( $data['log_path'] ) ? (string) $data['log_path'] : APB_Sides_Logger::get_log_path();
	$log_file_present = ! empty( $data['log_file_present'] );
	$log_readable     = ! empty( $data['log_readable'] );
	$log_content      = isset( $data['log_content'] ) ? (string) $data['log_content'] : '';
	$log_size         = isset( $data['log_size'] ) ? (string) $data['log_size'] : '';
	$log_mtime        = isset( $data['log_mtime'] ) ? (string) $data['log_mtime'] : '';
	?>
	<div class="apb-log-viewer">
		<h2><?php esc_html_e( 'Debug Log', 'apb-sides-database' ); ?></h2>
		<p class="description">
			<?php esc_html_e( 'Log file:', 'apb-sides-database' ); ?>
			<code id="apb-log-path-display"><?php echo esc_html( $log_path ); ?></code>
		</p>
		<?php if ( ! $log_file_present ) : ?>
			<div class="notice notice-info inline"><p><?php esc_html_e( 'No log file found yet. Use Test Logging to verify writes, or trigger Parse to generate entries.', 'apb-sides-database' ); ?></p></div>
		<?php elseif ( $log_file_present && ! $log_readable ) : ?>
			<div class="notice notice-warning inline"><p><?php esc_html_e( 'Log file exists but is not readable. Check file permissions.', 'apb-sides-database' ); ?></p></div>
		<?php endif; ?>
		<?php if ( $log_file_present && $log_readable ) : ?>
			<p class="apb-log-meta">
				<?php
				printf(
					/* translators: 1: file size, 2: last modified datetime */
					esc_html__( 'Size: %1$s · Last modified: %2$s', 'apb-sides-database' ),
					esc_html( $log_size ),
					esc_html( $log_mtime )
				);
				?>
			</p>
		<?php endif; ?>
		<div class="apb-log-actions">
			<button type="button" class="button" id="apb-refresh-log"><?php esc_html_e( 'Refresh Log', 'apb-sides-database' ); ?></button>
			<button type="button" class="button" id="apb-clear-log"><?php esc_html_e( 'Clear Log', 'apb-sides-database' ); ?></button>
			<button type="button" class="button" id="apb-copy-log"><?php esc_html_e( 'Copy Log', 'apb-sides-database' ); ?></button>
			<button type="button" class="button" id="apb-test-logging"><?php esc_html_e( 'Test Logging', 'apb-sides-database' ); ?></button>
			<span id="apb-log-feedback" class="apb-log-feedback" aria-live="polite"></span>
			<span id="apb-copy-feedback" class="apb-copy-feedback" style="margin-left:8px;font-size:12px;" aria-live="polite"></span>
			<span id="apb-test-logging-result" style="margin-left:10px;font-size:12px;" aria-live="polite"></span>
		</div>
		<label class="screen-reader-text" for="apb-log-content"><?php esc_html_e( 'Debug log contents', 'apb-sides-database' ); ?></label>
		<textarea id="apb-log-content" readonly><?php echo esc_textarea( $log_content ); ?></textarea>
	</div>
</div>
