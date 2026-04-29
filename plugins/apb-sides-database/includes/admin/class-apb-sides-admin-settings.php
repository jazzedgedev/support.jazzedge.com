<?php
/**
 * Plugin settings (Options API).
 *
 * @package APB_Sides_Database
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers and renders settings page.
 */
class APB_Sides_Admin_Settings {
	/**
	 * Masked API key hint for display on settings page.
	 */
	private string $api_key_hint = '';

	public function apb_sides_register_settings(): void {
		register_setting(
			'apb_sides_settings_group',
			'apb_sides_settings',
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'apb_sides_sanitize_settings' ),
				'default'           => APB_Sides_Helpers::get_settings(),
			)
		);

		add_settings_section(
			'apb_sides_main',
			__( 'AI & Processing', 'apb-sides-database' ),
			static function () {
				echo '<p>' . esc_html__( 'Configure Anthropic Claude and extraction defaults.', 'apb-sides-database' ) . '</p>';
			},
			'apb-sides-settings'
		);

		add_settings_field(
			'apb_sides_anthropic_api_key',
			__( 'Anthropic API Key', 'apb-sides-database' ),
			array( $this, 'field_api_key' ),
			'apb-sides-settings',
			'apb_sides_main'
		);

		add_settings_field(
			'apb_sides_anthropic_model',
			__( 'Claude Model', 'apb-sides-database' ),
			array( $this, 'field_model' ),
			'apb-sides-settings',
			'apb_sides_main'
		);

		add_settings_field(
			'apb_sides_global_instructions',
			__( 'Global AI Instructions', 'apb-sides-database' ),
			array( $this, 'field_instructions' ),
			'apb-sides-settings',
			'apb_sides_main'
		);

		add_settings_field(
			'apb_sides_confidence',
			__( 'Confidence Threshold', 'apb-sides-database' ),
			array( $this, 'field_confidence' ),
			'apb-sides-settings',
			'apb_sides_main'
		);

		add_settings_field(
			'apb_sides_debug',
			__( 'Debug Mode', 'apb-sides-database' ),
			array( $this, 'field_debug' ),
			'apb-sides-settings',
			'apb_sides_main'
		);

		add_settings_field(
			'apb_sides_max_file_mb',
			__( 'Max File Size (MB)', 'apb-sides-database' ),
			array( $this, 'field_max_file_size' ),
			'apb-sides-settings',
			'apb_sides_main'
		);

		add_settings_field(
			'apb_sides_max_input_chars',
			__( 'Max Input Characters (Claude)', 'apb-sides-database' ),
			array( $this, 'field_max_input_chars' ),
			'apb-sides-settings',
			'apb_sides_main'
		);

		add_settings_field(
			'apb_sides_schema_version',
			__( 'Schema Version', 'apb-sides-database' ),
			array( $this, 'field_schema' ),
			'apb-sides-settings',
			'apb_sides_main'
		);

		add_settings_field(
			'apb_sides_publish_warn',
			__( 'Warn on publish if items pending review', 'apb-sides-database' ),
			array( $this, 'field_publish_warn' ),
			'apb-sides-settings',
			'apb_sides_main'
		);
	}

	/**
	 * @param array<string, mixed> $input Raw input.
	 * @return array<string, mixed>
	 */
	public function apb_sides_sanitize_settings( $input ): array {
		$prev  = APB_Sides_Helpers::get_settings();
		$input = is_array( $input ) ? $input : array();

		$api_raw = isset( $input['anthropic_api_key'] ) ? (string) wp_unslash( $input['anthropic_api_key'] ) : '';

		if ( $api_raw !== '' ) {
			if ( preg_match( '/^(enc2:|plain:|enc:|b64:)/', $api_raw ) ) {
				$encrypted = (string) ( $prev['anthropic_api_key'] ?? '' );
			} else {
				$encrypted = APB_Sides_Helpers::encrypt_api_key( $api_raw );
			}
		} else {
			$encrypted = (string) ( $prev['anthropic_api_key'] ?? '' );
		}

		$allowed_models = array(
			'claude-haiku-4-5',
			'claude-sonnet-4-6',
			'claude-opus-4-7',
		);
		$model = isset( $input['anthropic_model'] ) ? sanitize_text_field( wp_unslash( $input['anthropic_model'] ) ) : (string) ( $prev['anthropic_model'] ?? 'claude-haiku-4-5' );
		if ( ! in_array( $model, $allowed_models, true ) ) {
			$model = 'claude-haiku-4-5';
		}

		return array(
			'anthropic_api_key'        => $encrypted,
			'anthropic_model'          => $model,
			'ai_global_instructions'   => isset( $input['ai_global_instructions'] ) ? sanitize_textarea_field( wp_unslash( $input['ai_global_instructions'] ) ) : '',
			'max_file_size_mb'         => isset( $input['max_file_size_mb'] ) ? min( 200, max( 1, absint( $input['max_file_size_mb'] ) ) ) : 20,
			'max_input_chars'          => isset( $input['max_input_chars'] ) ? min( 500000, max( 1000, absint( $input['max_input_chars'] ) ) ) : 30000,
			'schema_version'           => isset( $input['schema_version'] ) ? sanitize_text_field( wp_unslash( $input['schema_version'] ) ) : '1.0',
			'confidence_threshold'     => isset( $input['confidence_threshold'] ) ? min( 1.0, max( 0.0, (float) $input['confidence_threshold'] ) ) : 0.7,
			'debug_mode'               => ! empty( $input['debug_mode'] ),
			'publish_warn_pending'     => ! empty( $input['publish_warn_pending'] ),
		);
	}

	public function field_api_key(): void {
		$has_ssl  = function_exists( 'openssl_encrypt' ) && defined( 'AUTH_KEY' ) && AUTH_KEY !== '';
		if ( ! $has_ssl ) {
			echo '<p class="description">' . esc_html__( 'OpenSSL/AUTH_KEY not available; key is stored with basic encoding only.', 'apb-sides-database' ) . '</p>';
		}
		echo '<input type="password" class="regular-text" name="apb_sides_settings[anthropic_api_key]" value="" autocomplete="new-password" placeholder="' . esc_attr__( 'Enter new key to replace', 'apb-sides-database' ) . '" />';
		if ( ! empty( $this->api_key_hint ) ) {
			echo '<p class="description">Stored key: <code>' . esc_html( $this->api_key_hint ) . '</code> — ' . esc_html__( 'leave blank to keep it, or enter a new key to replace it.', 'apb-sides-database' ) . '</p>';
		} else {
			echo '<p class="description">' . esc_html__( 'No key stored yet.', 'apb-sides-database' ) . '</p>';
		}
		echo '<p><button type="button" id="apb-test-api-key" class="button">' . esc_html__( 'Test Claude API Key', 'apb-sides-database' ) . '</button> ';
		echo '<span id="apb-test-api-key-result" style="margin-left:10px;"></span></p>';
	}

	public function field_model(): void {
		$settings      = APB_Sides_Helpers::get_settings();
		$current_model = isset( $settings['anthropic_model'] ) ? (string) $settings['anthropic_model'] : 'claude-haiku-4-5';
		$allowed       = array( 'claude-haiku-4-5', 'claude-sonnet-4-6', 'claude-opus-4-7' );
		if ( ! in_array( $current_model, $allowed, true ) ) {
			$current_model = 'claude-haiku-4-5';
		}
		?>
		<select name="apb_sides_settings[anthropic_model]">
			<option value="claude-haiku-4-5" <?php selected( $current_model, 'claude-haiku-4-5' ); ?>>
				<?php esc_html_e( 'claude-haiku-4-5 — ~$0.03/extraction (Recommended)', 'apb-sides-database' ); ?>
			</option>
			<option value="claude-sonnet-4-6" <?php selected( $current_model, 'claude-sonnet-4-6' ); ?>>
				<?php esc_html_e( 'claude-sonnet-4-6 — ~$0.09/extraction (Best speed/quality)', 'apb-sides-database' ); ?>
			</option>
			<option value="claude-opus-4-7" <?php selected( $current_model, 'claude-opus-4-7' ); ?>>
				<?php esc_html_e( 'claude-opus-4-7 — ~$0.15/extraction (Most capable)', 'apb-sides-database' ); ?>
			</option>
		</select>
		<p class="description">
			<?php esc_html_e( 'Cost estimates based on ~10,000 input tokens and ~4,000 output tokens per screenplay. Haiku is the recommended default for speed and cost.', 'apb-sides-database' ); ?>
		</p>
		<?php
	}

	public function field_instructions(): void {
		$v = APB_Sides_Helpers::get_setting( 'ai_global_instructions', '' );
		echo '<textarea class="large-text" rows="6" name="apb_sides_settings[ai_global_instructions]">' . esc_textarea( (string) $v ) . '</textarea>';
	}

	public function field_max_file_size(): void {
		$v = (int) APB_Sides_Helpers::get_setting( 'max_file_size_mb', 20 );
		echo '<input type="number" min="1" max="200" class="small-text" name="apb_sides_settings[max_file_size_mb]" value="' . esc_attr( (string) $v ) . '" />';
	}

	public function field_max_input_chars(): void {
		$v = (int) APB_Sides_Helpers::get_setting( 'max_input_chars', 30000 );
		echo '<input type="number" min="1000" max="500000" class="small-text" name="apb_sides_settings[max_input_chars]" value="' . esc_attr( (string) $v ) . '" />';
		echo '<p class="description">' . esc_html__( 'PHP extracts text from the PDF first; only this much text is sent to Claude (middle is omitted if truncated).', 'apb-sides-database' ) . '</p>';
	}

	public function field_schema(): void {
		$v = esc_attr( APB_Sides_Helpers::get_setting( 'schema_version', '1.0' ) );
		echo '<input type="text" class="regular-text" name="apb_sides_settings[schema_version]" value="' . $v . '" />';
	}

	public function field_confidence(): void {
		$v = (float) APB_Sides_Helpers::get_setting( 'confidence_threshold', 0.7 );
		echo '<input type="number" step="0.01" min="0" max="1" class="small-text" name="apb_sides_settings[confidence_threshold]" value="' . esc_attr( (string) $v ) . '" />';
	}

	public function field_debug(): void {
		$on = (bool) APB_Sides_Helpers::get_setting( 'debug_mode', false );
		echo '<label><input type="checkbox" name="apb_sides_settings[debug_mode]" value="1" ' . checked( $on, true, false ) . ' /> ' . esc_html__( 'Enable verbose file logging', 'apb-sides-database' ) . '</label>';
	}

	public function field_publish_warn(): void {
		$on = (bool) APB_Sides_Helpers::get_setting( 'publish_warn_pending', true );
		echo '<label><input type="checkbox" name="apb_sides_settings[publish_warn_pending]" value="1" ' . checked( $on, true, false ) . ' /> ' . esc_html__( 'Include warnings when publishing with pending reviews', 'apb-sides-database' ) . '</label>';
	}

	public function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'apb-sides-database' ) );
		}

		global $wpdb;
		$tables = array(
			'apb_sides_uploads',
			'apb_sides_parsed_documents',
			'apb_sides_ai_extractions',
			'apb_sides_scripts',
			'apb_sides_characters',
			'apb_sides_sides',
			'apb_sides_side_characters',
			'apb_sides_review_queue',
		);

		$table_status = array();
		foreach ( $tables as $table ) {
			$full   = $wpdb->prefix . $table;
			$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $full ) ) === $full;
			$count  = $exists ? (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$full}`" ) : null;
			$table_status[] = array(
				'name'   => $full,
				'exists' => $exists,
				'count'  => $count,
			);
		}

		$log_read         = APB_Sides_Logger::read_log_contents();
		$log_path         = $log_read['path'];
		$log_content      = $log_read['content'];
		$log_file_present = file_exists( $log_path );
		$log_readable     = $log_file_present && is_readable( $log_path );

		$log_size  = '';
		$log_mtime = '';
		if ( $log_readable ) {
			$sz = filesize( $log_path );
			$log_size  = false !== $sz ? size_format( $sz ) : '';
			$log_mtime = wp_date( 'Y-m-d H:i:s', filemtime( $log_path ) );
		}

		$stored_key          = APB_Sides_Helpers::get_anthropic_api_key();
		$key_hint            = ! empty( $stored_key ) ? '••••••••' . substr( $stored_key, -4 ) : '';
		$this->api_key_hint  = $key_hint;

		$data = array(
			'table_status'        => $table_status,
			'log_path'            => $log_path,
			'log_file_present'    => $log_file_present,
			'log_readable'        => $log_readable,
			'log_content'         => $log_content,
			'log_size'            => $log_size,
			'log_mtime'           => $log_mtime,
			'claude_configured'   => APB_Sides_Helpers::get_anthropic_api_key() !== '',
			'api_key_hint'        => $key_hint,
		);
		include APB_SIDES_PLUGIN_DIR . 'templates/admin/settings.php';
	}
}
