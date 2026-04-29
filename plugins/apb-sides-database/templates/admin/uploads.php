<?php
/**
 * Admin uploads template.
 *
 * @var array<string, mixed> $data Data.
 * @package APB_Sides_Database
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$uploads = $data['uploads'] ?? array();
?>
<div class="wrap apb-sides-uploads">
	<h1><?php esc_html_e( 'Uploads', 'apb-sides-database' ); ?></h1>

	<div class="apb-upload-dropzone">
		<h2 style="margin-top:0;"><?php esc_html_e( 'Import JSON directly', 'apb-sides-database' ); ?></h2>
		<p class="description"><?php esc_html_e( 'Paste the JSON output from ChatGPT or another tool. The JSON must include a show title (e.g. the "show" field in the simple format, or "script": { "title" } in the legacy format).', 'apb-sides-database' ); ?></p>
		<p>
			<strong><?php esc_html_e( 'Step 1:', 'apb-sides-database' ); ?></strong>
			<?php esc_html_e( 'Upload your screenplay PDF to ChatGPT-4o, then copy the appropriate prompt:', 'apb-sides-database' ); ?>
		</p>
		<p>
			<button type="button" id="apb-copy-scene-prompt" class="button"><?php esc_html_e( 'Copy: Single Scene Prompt', 'apb-sides-database' ); ?></button>
			<button type="button" id="apb-copy-script-prompt" class="button" style="margin-left:8px;"><?php esc_html_e( 'Copy: Full Script Prompt', 'apb-sides-database' ); ?></button>
			<span id="apb-copy-gpt-msg" style="margin-left:8px;color:green;display:none;"><?php esc_html_e( 'Copied!', 'apb-sides-database' ); ?></span>
		</p>
		<textarea id="apb-gpt-scene-prompt" style="display:none;" readonly><?php echo esc_textarea( 'Analyze this screenplay scene and return ONLY a valid JSON object — no markdown, no code fences, no explanation.

{
  "show": "Show or film title",
  "type": "scene",
  "genre": [],
  "characters": [
    {"name": "", "gender": "", "age_range": ""}
  ],
  "number_of_people_in_scene": 0,
  "scene_context": "What happens in this scene and its dramatic purpose.",
  "actor_notes": "Acting guidance — subtext, energy, power dynamics, what to focus on."
}

Rules:
- genre must be an array of strings
- Return raw JSON only — no wrapper' ); ?></textarea>
		<textarea id="apb-gpt-script-prompt" style="display:none;" readonly><?php echo esc_textarea( 'Analyze this full screenplay and return ONLY a valid JSON object — no markdown, no code fences, no explanation.

{
  "show": "Show or film title",
  "type": "full_script",
  "genre": [],
  "characters": [
    {"name": "", "gender": "", "age_range": ""}
  ],
  "scenes": [
    {
      "scene_context": "What happens in this scene and its dramatic purpose.",
      "actor_notes": "Acting guidance — subtext, energy, power dynamics, what to focus on.",
      "number_of_people_in_scene": 0
    }
  ]
}

Rules:
- Include ALL significant scenes in the scenes array
- genre must be an array of strings
- Characters at the top level should list every named character in the script
- Return raw JSON only — no wrapper' ); ?></textarea>
		<form id="apb-json-import-form">
			<p>
				<label for="apb-json-paste"><strong><?php esc_html_e( 'Paste JSON', 'apb-sides-database' ); ?></strong></label><br>
				<textarea id="apb-json-paste" class="large-text" rows="12" placeholder='{"script":{...},"characters":[...],"sides":[...]}'></textarea>
			</p>
			<p>
				<label for="apb-json-pdf-file"><strong><?php esc_html_e( 'Attach screenplay PDF', 'apb-sides-database' ); ?></strong> <span class="description"><?php esc_html_e( '(optional — for member download)', 'apb-sides-database' ); ?></span></label><br>
				<input type="file" id="apb-json-pdf-file" name="script_pdf" accept=".pdf" />
			</p>
			<p>
				<button type="submit" class="button button-primary" id="apb-json-import-btn"><?php esc_html_e( 'Import JSON', 'apb-sides-database' ); ?></button>
			</p>
		</form>
		<p class="apb-upload-msg" id="apb-json-import-msg" aria-live="polite"></p>
	</div>

	<table class="widefat striped apb-uploads-table">
		<thead>
			<tr>
				<th><?php esc_html_e( 'ID', 'apb-sides-database' ); ?></th>
				<th><?php esc_html_e( 'Filename', 'apb-sides-database' ); ?></th>
				<th><?php esc_html_e( 'Type', 'apb-sides-database' ); ?></th>
				<th><?php esc_html_e( 'Status', 'apb-sides-database' ); ?></th>
				<th><?php esc_html_e( 'Uploaded by', 'apb-sides-database' ); ?></th>
				<th><?php esc_html_e( 'Date', 'apb-sides-database' ); ?></th>
				<th><?php esc_html_e( 'Actions', 'apb-sides-database' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $uploads as $row ) : ?>
				<?php
				$uid           = (int) $row['id'];
				$upload_status = isset( $row['upload_status'] ) ? (string) $row['upload_status'] : '';
				$raw_len       = isset( $row['raw_text_length'] ) ? (int) $row['raw_text_length'] : 0;
				$user          = get_userdata( (int) $row['uploaded_by'] );
				$uname         = $user ? $user->display_name : '—';
				?>
				<tr data-upload-id="<?php echo esc_attr( (string) $uid ); ?>" data-upload-status="<?php echo esc_attr( $upload_status ); ?>">
					<td><?php echo esc_html( (string) $uid ); ?></td>
					<td class="apb-filename-cell">
						<span class="apb-filename-display"><?php echo esc_html( (string) $row['original_filename'] ); ?></span>
						<input type="text" class="apb-filename-input" value="<?php echo esc_attr( (string) $row['original_filename'] ); ?>" style="display:none;width:100%;max-width:220px;" maxlength="255" />
						<button type="button" class="apb-filename-edit button-link" title="<?php esc_attr_e( 'Edit', 'apb-sides-database' ); ?>" style="margin-left:4px;">✏️</button>
						<button type="button" class="apb-filename-save button button-small" style="display:none;margin-left:4px;"><?php esc_html_e( 'Save', 'apb-sides-database' ); ?></button>
						<button type="button" class="apb-filename-cancel button-link" style="display:none;margin-left:2px;">✕</button>
					</td>
					<td><?php echo esc_html( (string) $row['file_type'] ); ?></td>
					<td class="apb-upload-status" id="apb-upload-status-<?php echo esc_attr( (string) $uid ); ?>">
						<?php echo wp_kses_post( APB_Sides_Helpers::get_status_badge_html( (string) $row['upload_status'] ) ); ?>
						<?php if ( APB_Sides_Statuses::PARSED === $upload_status ) : ?>
							<?php
							if ( $raw_len > 1000 ) {
								$q_class = 'apb-text-quality--good';
								$q_label = __( 'Good text', 'apb-sides-database' );
							} elseif ( $raw_len >= 100 ) {
								$q_class = 'apb-text-quality--short';
								$q_label = __( 'Short text', 'apb-sides-database' );
							} else {
								$q_class = 'apb-text-quality--poor';
								$q_label = __( 'Poor text — review before extraction', 'apb-sides-database' );
							}
							?>
							<span class="apb-text-quality <?php echo esc_attr( $q_class ); ?>" title="<?php echo esc_attr( (string) $raw_len . ' chars' ); ?>">
								<span class="apb-text-quality-dot" aria-hidden="true"></span>
								<span class="apb-text-quality-label"><?php echo esc_html( $q_label ); ?></span>
							</span>
						<?php endif; ?>
					</td>
					<td><?php echo esc_html( $uname ); ?></td>
					<td><?php echo esc_html( mysql2date( get_option( 'date_format' ), $row['created_at'] ) ); ?></td>
					<td class="apb-actions">
						<button type="button" class="button apb-publish-upload"><?php esc_html_e( 'Publish', 'apb-sides-database' ); ?></button>
						<button type="button" class="button button-link-delete apb-delete-upload"><?php esc_html_e( 'Delete', 'apb-sides-database' ); ?></button>
						<?php
						$file_type_row = isset( $row['file_type'] ) ? (string) $row['file_type'] : '';
						$allow_reset    = APB_Sides_Statuses::UPLOADED !== $upload_status && 'text' !== $file_type_row;
						?>
						<?php if ( $allow_reset ) : ?>
							<button type="button" class="button apb-reset-upload"><?php esc_html_e( 'Reset', 'apb-sides-database' ); ?></button>
						<?php endif; ?>
						<span class="apb-inline-notice" id="apb-extract-status-<?php echo esc_attr( (string) $uid ); ?>" aria-live="polite"></span>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
