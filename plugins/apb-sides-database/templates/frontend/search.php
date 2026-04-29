<?php
/**
 * Frontend search shortcode template.
 *
 * @var array<string, mixed> $data Data.
 * @package APB_Sides_Database
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$filters = $data['filters'] ?? array();
$shows   = $filters['shows'] ?? array();
$genres  = $filters['genres'] ?? array();
$mediums = $filters['mediums'] ?? array();
$genders = $filters['genders'] ?? array();
?>
<div class="apb-sides-search" id="apb-sides-search-root">
	<div class="apb-sides-search-layout">
		<aside class="apb-sides-filters">

			<p>
				<label for="apb-filter-keyword"><?php esc_html_e( 'Keyword', 'apb-sides-database' ); ?></label>
				<input type="search" id="apb-filter-keyword" class="apb-sides-input apb-filter" placeholder="<?php esc_attr_e( 'Search scenes, notes…', 'apb-sides-database' ); ?>" />
			</p>

			<?php if ( ! empty( $shows ) ) : ?>
			<p>
				<label for="apb-filter-show"><?php esc_html_e( 'Show', 'apb-sides-database' ); ?></label>
				<select id="apb-filter-show" class="apb-sides-select apb-filter">
					<option value=""><?php esc_html_e( 'All Shows', 'apb-sides-database' ); ?></option>
					<?php foreach ( $shows as $show ) : ?>
						<option value="<?php echo esc_attr( (string) $show['id'] ); ?>"><?php echo esc_html( (string) $show['title'] ); ?></option>
					<?php endforeach; ?>
				</select>
			</p>
			<?php endif; ?>

			<?php if ( ! empty( $genres ) ) : ?>
			<p>
				<label for="apb-filter-genre"><?php esc_html_e( 'Genre', 'apb-sides-database' ); ?></label>
				<select id="apb-filter-genre" class="apb-sides-select apb-filter">
					<option value=""><?php esc_html_e( 'All Genres', 'apb-sides-database' ); ?></option>
					<?php foreach ( $genres as $g ) : ?>
						<option value="<?php echo esc_attr( (string) $g ); ?>"><?php echo esc_html( (string) $g ); ?></option>
					<?php endforeach; ?>
				</select>
			</p>
			<?php endif; ?>

			<?php if ( ! empty( $mediums ) ) : ?>
			<p>
				<label for="apb-filter-medium"><?php esc_html_e( 'Type', 'apb-sides-database' ); ?></label>
				<select id="apb-filter-medium" class="apb-sides-select apb-filter">
					<option value=""><?php esc_html_e( 'All Types', 'apb-sides-database' ); ?></option>
					<?php foreach ( $mediums as $m ) : ?>
						<option value="<?php echo esc_attr( (string) $m ); ?>"><?php echo esc_html( ucwords( str_replace( '_', ' ', (string) $m ) ) ); ?></option>
					<?php endforeach; ?>
				</select>
			</p>
			<?php endif; ?>

			<?php if ( ! empty( $genders ) ) : ?>
			<p>
				<label for="apb-filter-gender"><?php esc_html_e( 'Character Gender', 'apb-sides-database' ); ?></label>
				<select id="apb-filter-gender" class="apb-sides-select apb-filter">
					<option value=""><?php esc_html_e( 'Any', 'apb-sides-database' ); ?></option>
					<?php foreach ( $genders as $g ) : ?>
						<option value="<?php echo esc_attr( (string) $g ); ?>"><?php echo esc_html( (string) $g ); ?></option>
					<?php endforeach; ?>
				</select>
			</p>
			<?php endif; ?>

			<p>
				<label for="apb-filter-casting_type"><?php esc_html_e( 'Character Name', 'apb-sides-database' ); ?></label>
				<input type="search" id="apb-filter-casting_type" class="apb-sides-input apb-filter" placeholder="<?php esc_attr_e( 'e.g. Bob Harris', 'apb-sides-database' ); ?>" />
			</p>

			<p>
				<button type="button" id="apb-clear-filters" class="apb-btn-clear"><?php esc_html_e( 'Clear Filters', 'apb-sides-database' ); ?></button>
			</p>

		</aside>
		<div class="apb-sides-results-wrap">
			<div id="apb-sides-results" class="apb-sides-results-grid"></div>
			<div class="apb-sides-pagination">
				<button type="button" class="button" id="apb-sides-prev"><?php esc_html_e( 'Previous', 'apb-sides-database' ); ?></button>
				<span id="apb-sides-page-label"></span>
				<button type="button" class="button" id="apb-sides-next"><?php esc_html_e( 'Next', 'apb-sides-database' ); ?></button>
			</div>
		</div>
	</div>
</div>
