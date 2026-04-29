<?php
/**
 * Front-end shortcodes.
 *
 * @package APB_Resources
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class APB_Shortcodes
 */
class APB_Shortcodes {

	/**
	 * Bootstrap.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_shortcode( 'apb_resources_dashboard', [ __CLASS__, 'render_dashboard' ] );
		add_shortcode( 'apb_resources', [ __CLASS__, 'render_category' ] );
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue_frontend' ] );
	}

	/**
	 * Enqueue public CSS and view-toggle script.
	 *
	 * @return void
	 */
	public static function enqueue_frontend(): void {
		if ( is_admin() ) {
			return;
		}

		wp_enqueue_style(
			'apb-resources-frontend',
			APB_RES_URL . 'assets/css/frontend.css',
			[],
			APB_RES_VERSION
		);

		wp_enqueue_script(
			'apb-resources-frontend',
			APB_RES_URL . 'assets/js/frontend.js',
			[],
			APB_RES_VERSION,
			true
		);
	}

	/**
	 * Category cards linking to chosen pages.
	 *
	 * @return string
	 */
	public static function render_dashboard(): string {
		global $wpdb;

		$cat_table    = APB_DB::categories_table();
		$categories = $wpdb->get_results(
			"SELECT * FROM {$cat_table} ORDER BY sort_order ASC, id ASC",
			OBJECT
		);

		if ( ! is_array( $categories ) || empty( $categories ) ) {
			return '';
		}

		ob_start();
		?>
		<div class="apb-resources-dashboard">
			<?php foreach ( $categories as $cat ) : ?>
				<?php
				$name     = isset( $cat->name ) ? $cat->name : '';
				$desc     = isset( $cat->description ) ? $cat->description : '';
				$pid      = isset( $cat->page_id ) ? absint( $cat->page_id ) : 0;
				$page_url = $pid ? get_permalink( $pid ) : '';
				$page_url = $page_url ? $page_url : '';
				$img_id   = isset( $cat->card_image_id ) ? absint( $cat->card_image_id ) : 0;
				$img_url  = $img_id ? wp_get_attachment_image_url( $img_id, 'medium_large' ) : '';
				$img_url  = $img_url ? $img_url : '';
				$tag      = $page_url ? 'a' : 'div';
				$href     = $page_url ? ' href="' . esc_url( $page_url ) . '"' : '';
				?>
			<<?php echo $tag . $href; ?> class="apb-category-card">
				<?php if ( $img_url ) : ?>
				<div class="apb-category-card__img-wrap">
					<img src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( $name ); ?>" class="apb-category-card__img">
				</div>
				<?php endif; ?>
				<div class="apb-category-card__content">
					<h3 class="apb-category-card__title"><?php echo esc_html( $name ); ?></h3>
					<?php if ( '' !== $desc ) : ?>
						<p class="apb-category-card__desc"><?php echo esc_html( $desc ); ?></p>
					<?php endif; ?>
					<?php if ( $page_url ) : ?>
						<span class="apb-category-card__link"><?php esc_html_e( 'Explore →', 'apb-resources' ); ?></span>
					<?php endif; ?>
				</div>
			</<?php echo esc_html( $tag ); ?>>
			<?php endforeach; ?>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Category page: hero, card/list toggle, resources.
	 *
	 * @param array<string, string>|string $atts Shortcode attributes.
	 * @return string
	 */
	public static function render_category( $atts ): string {
		$atts = shortcode_atts(
			[
				'category' => '',
			],
			$atts,
			'apb_resources'
		);

		if ( ! $atts['category'] ) {
			return '';
		}

		global $wpdb;

		$cat_table = APB_DB::categories_table();
		$res_table = APB_DB::resources_table();

		$slug = sanitize_title( $atts['category'] );
		if ( '' === $slug ) {
			return '';
		}

		$cat = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$cat_table} WHERE slug = %s",
				$slug
			)
		);

		if ( ! $cat ) {
			return '';
		}

		$resources = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$res_table} WHERE category_id = %d ORDER BY sort_order ASC, id ASC",
				absint( $cat->id )
			),
			OBJECT
		);

		if ( ! is_array( $resources ) ) {
			$resources = [];
		}

		$img_id    = isset( $cat->hero_image_id ) ? absint( $cat->hero_image_id ) : 0;
		$image_url = $img_id ? wp_get_attachment_image_url( $img_id, 'full' ) : '';
		$uid       = 'apb-res-' . sanitize_title( $cat->slug );

		ob_start();
		?>
		<div class="apb-resources-wrap" id="<?php echo esc_attr( $uid ); ?>">
			<?php if ( $image_url ) : ?>
			<div class="apb-category-hero">
				<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $cat->name ); ?>" class="apb-category-hero__img">
			</div>
			<?php endif; ?>

			<div class="apb-resources-inner">
				<div class="apb-resources-toolbar">
					<div class="apb-view-toggle">
						<button type="button" class="apb-view-btn apb-view-btn--active" data-target="<?php echo esc_attr( $uid ); ?>" data-view="cards" title="<?php esc_attr_e( 'Card view', 'apb-resources' ); ?>">
							<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
						</button>
						<button type="button" class="apb-view-btn" data-target="<?php echo esc_attr( $uid ); ?>" data-view="list" title="<?php esc_attr_e( 'List view', 'apb-resources' ); ?>">
							<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
						</button>
					</div>
				</div>

				<div class="apb-resources-grid">
					<?php if ( empty( $resources ) ) : ?>
						<p class="apb-no-resources"><?php esc_html_e( 'No resources found in this category.', 'apb-resources' ); ?></p>
					<?php else : ?>
						<?php foreach ( $resources as $r ) : ?>
							<div class="apb-resource-card">
								<?php if ( ! empty( $r->image_url ) ) : ?>
								<div class="apb-resource-card__img-wrap">
									<img src="<?php echo esc_url( $r->image_url ); ?>" alt="<?php echo esc_attr( $r->title ); ?>" class="apb-resource-card__img">
								</div>
								<?php endif; ?>
								<div class="apb-resource-card__body">
									<h3 class="apb-resource-card__title"><?php echo esc_html( $r->title ); ?></h3>
									<?php if ( ! empty( $r->description ) ) : ?>
										<p class="apb-resource-card__desc"><?php echo esc_html( $r->description ); ?></p>
									<?php endif; ?>
								</div>
								<div class="apb-resource-card__footer">
									<a href="<?php echo esc_url( $r->url ); ?>" class="apb-resource-card__btn" target="_blank" rel="noopener noreferrer">
										<?php echo esc_html( $r->button_text ? $r->button_text : __( 'Learn more...', 'apb-resources' ) ); ?>
									</a>
								</div>
							</div>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
		return (string) ob_get_clean();
	}
}
