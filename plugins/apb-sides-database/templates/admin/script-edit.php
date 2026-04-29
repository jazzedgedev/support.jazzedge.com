<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$s   = $data['script'];
$sid = (int) $s['id'];
$pdf = (string) ( $s['source_file_url'] ?? '' );
?>
<div class="wrap apb-script-edit" data-entity-type="script" data-entity-id="<?php echo esc_attr( (string) $sid ); ?>">
	<h1><?php esc_html_e( 'Edit script', 'apb-sides-database' ); ?></h1>
	<p><a href="<?php echo esc_url( admin_url( 'admin.php?page=apb-sides-scripts' ) ); ?>">&larr; <?php esc_html_e( 'Back', 'apb-sides-database' ); ?></a></p>
	<table class="form-table">
		<tr><th><label><?php esc_html_e( 'Title', 'apb-sides-database' ); ?></label></th><td><input type="text" class="regular-text apb-field" name="title" value="<?php echo esc_attr( (string) $s['title'] ); ?>" /></td></tr>
		<tr><th><label><?php esc_html_e( 'Medium', 'apb-sides-database' ); ?></label></th><td><input type="text" class="regular-text apb-field" name="medium" value="<?php echo esc_attr( (string) ( $s['medium'] ?? '' ) ); ?>" /></td></tr>
		<tr><th><label><?php esc_html_e( 'Genre', 'apb-sides-database' ); ?></label></th><td><textarea class="large-text apb-field" name="genre"><?php echo esc_textarea( (string) ( $s['genre'] ?? '' ) ); ?></textarea></td></tr>
		<tr><th><label><?php esc_html_e( 'Status', 'apb-sides-database' ); ?></label></th><td><input type="text" class="regular-text apb-field" name="status" value="<?php echo esc_attr( (string) $s['status'] ); ?>" /></td></tr>
		<tr>
			<th><label><?php esc_html_e( 'Screenplay PDF', 'apb-sides-database' ); ?></label></th>
			<td>
				<?php if ( $pdf ) : ?>
					<p><a href="<?php echo esc_url( $pdf ); ?>" target="_blank" class="button"><?php esc_html_e( 'View / Download current PDF', 'apb-sides-database' ); ?></a></p>
				<?php endif; ?>
				<input type="file" id="apb-script-pdf-upload" accept=".pdf" data-script-id="<?php echo esc_attr( (string) $sid ); ?>" />
				<button type="button" class="button apb-upload-script-pdf" data-script-id="<?php echo esc_attr( (string) $sid ); ?>"><?php esc_html_e( 'Upload PDF', 'apb-sides-database' ); ?></button>
				<span class="apb-inline-notice" id="apb-pdf-upload-notice"></span>
			</td>
		</tr>
	</table>
	<p>
		<button type="button" class="button button-primary apb-save-entity"><?php esc_html_e( 'Save', 'apb-sides-database' ); ?></button>
		<span class="apb-inline-notice"></span>
	</p>
</div>
