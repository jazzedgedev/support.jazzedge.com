<?php
/**
 * Admin menus and form handlers.
 *
 * @package APB_Resources
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class APB_Admin
 */
class APB_Admin {

	/**
	 * Bootstrap.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'admin_menu', [ __CLASS__, 'register_menus' ] );
		add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_assets' ] );
		add_action( 'admin_post_apb_save_category', [ __CLASS__, 'handle_save_category' ] );
		add_action( 'admin_post_apb_delete_category', [ __CLASS__, 'handle_delete_category' ] );
		add_action( 'admin_post_apb_save_resource', [ __CLASS__, 'handle_save_resource' ] );
		add_action( 'admin_post_apb_delete_resource', [ __CLASS__, 'handle_delete_resource' ] );
		add_action( 'admin_post_apb_bulk_resources', [ __CLASS__, 'handle_bulk_resources' ] );
		add_action( 'admin_post_apb_save_category_image', [ __CLASS__, 'handle_save_category_image' ] );
	}

	/**
	 * Register admin pages.
	 *
	 * @return void
	 */
	public static function register_menus(): void {
		add_menu_page(
			'APB Resources',
			'APB Resources',
			'manage_options',
			'apb-resources',
			[ __CLASS__, 'render_resources_page' ],
			'dashicons-archive',
			30
		);

		add_submenu_page(
			'apb-resources',
			'Resources',
			'Resources',
			'manage_options',
			'apb-resources',
			[ __CLASS__, 'render_resources_page' ]
		);

		add_submenu_page(
			'apb-resources',
			'Categories',
			'Categories',
			'manage_options',
			'apb-resource-categories',
			[ __CLASS__, 'render_categories_page' ]
		);

		add_submenu_page(
			'apb-resources',
			'Import JSON',
			'Import JSON',
			'manage_options',
			'apb-resource-import',
			[ __CLASS__, 'render_import_page' ]
		);

		add_submenu_page(
			'apb-resources',
			'Shortcodes',
			'Shortcodes',
			'manage_options',
			'apb-resource-shortcodes',
			function () {
				require_once APB_RES_PATH . 'admin/views/shortcodes.php';
			}
		);
	}

	/**
	 * Enqueue admin CSS on plugin screens.
	 *
	 * @param string $hook_suffix Current admin page hook.
	 * @return void
	 */
	public static function enqueue_assets( string $hook_suffix ): void {
		if ( strpos( $hook_suffix, 'apb-resource' ) === false && 'toplevel_page_apb-resources' !== $hook_suffix ) {
			return;
		}
		wp_enqueue_style(
			'apb-resources-admin',
			APB_RES_URL . 'assets/css/admin.css',
			[],
			APB_RES_VERSION
		);

		$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
		if ( 'apb-resource-shortcodes' === $page ) {
			wp_enqueue_media();
			wp_add_inline_script(
				'media-editor',
				<<<'JS'
document.addEventListener('DOMContentLoaded', function () {
	document.querySelectorAll('.apb-copy-btn').forEach(function (btn) {
		btn.addEventListener('click', function () {
			var btnEl = this;
			var code = this.closest('.apb-shortcode-row').querySelector('code').innerText;
			navigator.clipboard.writeText(code).then(function () {
				var orig = btnEl.textContent;
				btnEl.textContent = 'Copied!';
				btnEl.classList.add('apb-copied');
				setTimeout(function () {
					btnEl.textContent = orig;
					btnEl.classList.remove('apb-copied');
				}, 1500);
			});
		});
	});
	document.querySelectorAll('.apb-sc-upload-btn').forEach(function (btn) {
		btn.addEventListener('click', function () {
			var catId = this.dataset.id;
			var frame = wp.media({
				title: 'Select Card Image (600×300px)',
				button: { text: 'Use this image' },
				multiple: false
			});
			frame.on('select', function () {
				var att = frame.state().get('selection').first().toJSON();
				document.getElementById('apb-img-id-' + catId).value = att.id;
				var preview = document.getElementById('apb-preview-' + catId);
				preview.innerHTML = '<img src="' + att.url + '" style="width:100%;height:100%;object-fit:cover;display:block;">';
			});
			frame.open();
		});
	});
	document.querySelectorAll('.apb-sc-remove-btn').forEach(function (btn) {
		btn.addEventListener('click', function () {
			var catId = this.dataset.id;
			document.getElementById('apb-img-id-' + catId).value = '';
			var preview = document.getElementById('apb-preview-' + catId);
			preview.innerHTML = '<span style="display:flex;align-items:center;justify-content:center;height:100%;color:#aaa;font-size:12px;">No image</span>';
		});
	});
});
JS
				,
				'after'
			);
		}

		if ( 'apb-resource-categories' === $page ) {
			wp_enqueue_media();
			wp_enqueue_script( 'jquery' );
			wp_add_inline_script(
				'jquery',
				<<<'JS'
jQuery(function($){
	$('.apb-upload-image-btn').on('click', function(e){
		e.preventDefault();
		var field = $(this).data('field');
		var frame = wp.media({ title: 'Select Image', button: { text: 'Use this image' }, multiple: false });
		frame.on('select', function(){
			var att = frame.state().get('selection').first().toJSON();
			$('#apb_' + field + '_id').val(att.id);
			$('#apb-' + field + '-preview').html('<img src="' + att.url + '" style="max-width:400px;height:200px;object-fit:cover;border-radius:4px;margin-top:8px;">');
			$('#apb-' + field + '-remove').show();
		});
		frame.open();
	});
	$('.apb-remove-image-btn').on('click', function(e){
		e.preventDefault();
		var field = $(this).data('field');
		$('#apb_' + field + '_id').val('');
		$('#apb-' + field + '-preview').html('');
		$(this).hide();
	});
});
JS
			);
		}

		if ( $page && str_starts_with( $page, 'apb-resources' ) && isset( $_GET['action'] ) ) {
			wp_enqueue_media();
			wp_enqueue_script( 'jquery' );
			wp_add_inline_script(
				'jquery',
				<<<'JS'
jQuery(function($){
	$('#apb-upload-image-btn').on('click', function(e){
		e.preventDefault();
		var frame = wp.media({ title: 'Select Resource Image', button: { text: 'Use this image' }, multiple: false });
		frame.on('select', function(){
			var att = frame.state().get('selection').first().toJSON();
			$('#apb_image_url').val(att.url);
			$('#apb-image-preview').html('<img src="' + att.url + '" style="max-width:200px;height:280px;object-fit:cover;border-radius:4px;margin-top:8px;">');
			$('#apb-remove-image-btn').show();
		});
		frame.open();
	});
	$('#apb-remove-image-btn').on('click', function(e){
		e.preventDefault();
		$('#apb_image_url').val('');
		$('#apb-image-preview').html('');
		$(this).hide();
	});
});
JS
			);
		}

	}

	/**
	 * Resources list / form.
	 *
	 * @return void
	 */
	public static function render_resources_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';
		if ( 'edit' === $action || 'add' === $action ) {
			global $wpdb;
			$res_table = APB_DB::resources_table();
			$resource  = null;
			if ( 'edit' === $action && isset( $_GET['id'] ) ) {
				$resource = $wpdb->get_row(
					$wpdb->prepare(
						"SELECT * FROM {$res_table} WHERE id = %d",
						absint( wp_unslash( $_GET['id'] ) )
					)
				);
			}
			require APB_RES_PATH . 'admin/views/resource-form.php';
			return;
		}

		require APB_RES_PATH . 'admin/views/resources-list.php';
	}

	/**
	 * Categories list / form.
	 *
	 * @return void
	 */
	public static function render_categories_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';
		if ( 'edit' === $action || 'add' === $action ) {
			global $wpdb;
			$cat_table = APB_DB::categories_table();
			$category  = null;
			if ( 'edit' === $action && isset( $_GET['id'] ) ) {
				$category = $wpdb->get_row(
					$wpdb->prepare(
						"SELECT * FROM {$cat_table} WHERE id = %d",
						absint( wp_unslash( $_GET['id'] ) )
					)
				);
			}
			require APB_RES_PATH . 'admin/views/category-form.php';
			return;
		}

		require APB_RES_PATH . 'admin/views/categories-list.php';
	}

	/**
	 * Import view.
	 *
	 * @return void
	 */
	public static function render_import_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		require APB_RES_PATH . 'admin/views/import.php';
	}

	/**
	 * Save category (admin-post).
	 *
	 * @return void
	 */
	public static function handle_save_category(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'apb-resources' ) );
		}
		check_admin_referer( 'apb_save_category', 'apb_category_nonce' );

		$name        = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$slug        = isset( $_POST['slug'] ) ? sanitize_title( wp_unslash( $_POST['slug'] ) ) : '';
		$description = isset( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '';
		$page_id     = isset( $_POST['page_id'] ) ? absint( wp_unslash( $_POST['page_id'] ) ) : 0;
		$sort_order  = isset( $_POST['sort_order'] ) ? intval( wp_unslash( $_POST['sort_order'] ) ) : 0;
		$id            = isset( $_POST['id'] ) ? absint( wp_unslash( $_POST['id'] ) ) : 0;
		$hero_image_id = isset( $_POST['apb_hero_image_id'] ) ? absint( wp_unslash( $_POST['apb_hero_image_id'] ) ) : 0;
		$card_image_id = isset( $_POST['apb_card_image_id'] ) ? absint( wp_unslash( $_POST['apb_card_image_id'] ) ) : 0;

		if ( '' === $slug ) {
			$slug = sanitize_title( $name );
		}

		global $wpdb;
		$table = APB_DB::categories_table();

		$data = [
			'name'          => $name,
			'slug'          => $slug,
			'description'   => $description,
			'page_id'       => $page_id,
			'sort_order'    => $sort_order,
			'hero_image_id' => $hero_image_id > 0 ? $hero_image_id : null,
			'card_image_id' => $card_image_id > 0 ? $card_image_id : null,
		];

		if ( $id > 0 ) {
			$wpdb->update(
				$table,
				$data,
				[ 'id' => $id ],
				[ '%s', '%s', '%s', '%d', '%d', '%d', '%d' ],
				[ '%d' ]
			);
		} else {
			$wpdb->insert(
				$table,
				$data,
				[ '%s', '%s', '%s', '%d', '%d', '%d', '%d' ]
			);
		}

		wp_safe_redirect(
			esc_url_raw(
				admin_url( 'admin.php?page=apb-resource-categories&apb_saved=1' )
			)
		);
		exit;
	}

	/**
	 * Save category card image from Shortcodes admin (dashboard thumbnails).
	 *
	 * @return void
	 */
	public static function handle_save_category_image(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'apb-resources' ) );
		}
		if ( ! isset( $_POST['apb_cat_image_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['apb_cat_image_nonce'] ), 'apb_save_category_image' ) ) {
			wp_die( esc_html__( 'Bad nonce', 'apb-resources' ) );
		}

		$category_id   = isset( $_POST['apb_category_id'] ) ? absint( wp_unslash( $_POST['apb_category_id'] ) ) : 0;
		$card_image_id = isset( $_POST['apb_image_id'] ) ? absint( wp_unslash( $_POST['apb_image_id'] ) ) : 0;

		if ( ! $category_id ) {
			wp_safe_redirect( esc_url_raw( admin_url( 'admin.php?page=apb-resource-shortcodes' ) ) );
			exit;
		}

		global $wpdb;
		$table = APB_DB::categories_table();

		if ( $card_image_id > 0 ) {
			$wpdb->update(
				$table,
				[ 'card_image_id' => $card_image_id ],
				[ 'id' => $category_id ],
				[ '%d' ],
				[ '%d' ]
			);
		} else {
			$wpdb->query(
				$wpdb->prepare(
					"UPDATE {$table} SET card_image_id = NULL WHERE id = %d",
					$category_id
				)
			);
		}

		wp_safe_redirect(
			esc_url_raw(
				admin_url( 'admin.php?page=apb-resource-shortcodes&apb_image_saved=1' )
			)
		);
		exit;
	}

	/**
	 * Delete category and its resources.
	 *
	 * @return void
	 */
	public static function handle_delete_category(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'apb-resources' ) );
		}

		$id = isset( $_GET['id'] ) ? absint( wp_unslash( $_GET['id'] ) ) : 0;
		check_admin_referer( 'apb_delete_category_' . $id );

		global $wpdb;
		$res_table = APB_DB::resources_table();
		$cat_table = APB_DB::categories_table();

		$wpdb->delete( $res_table, [ 'category_id' => $id ], [ '%d' ] );
		$wpdb->delete( $cat_table, [ 'id' => $id ], [ '%d' ] );

		wp_safe_redirect(
			esc_url_raw(
				admin_url( 'admin.php?page=apb-resource-categories&apb_deleted=1' )
			)
		);
		exit;
	}

	/**
	 * Save resource (admin-post).
	 *
	 * @return void
	 */
	public static function handle_save_resource(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'apb-resources' ) );
		}
		check_admin_referer( 'apb_save_resource', 'apb_resource_nonce' );

		$title       = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
		$description = isset( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '';
		$url         = isset( $_POST['url'] ) ? esc_url_raw( wp_unslash( $_POST['url'] ) ) : '';
		$image_url   = isset( $_POST['apb_image_url'] ) ? esc_url_raw( wp_unslash( $_POST['apb_image_url'] ) ) : '';
		$button_text = isset( $_POST['button_text'] ) ? sanitize_text_field( wp_unslash( $_POST['button_text'] ) ) : '';
		$category_id = isset( $_POST['category_id'] ) ? absint( wp_unslash( $_POST['category_id'] ) ) : 0;
		$sort_order  = isset( $_POST['sort_order'] ) ? intval( wp_unslash( $_POST['sort_order'] ) ) : 0;
		$id          = isset( $_POST['id'] ) ? absint( wp_unslash( $_POST['id'] ) ) : 0;

		if ( '' === $button_text ) {
			$button_text = 'Learn more...';
		}

		global $wpdb;
		$table = APB_DB::resources_table();

		$data = [
			'title'       => $title,
			'description' => $description,
			'url'         => $url,
			'image_url'   => $image_url,
			'button_text' => $button_text,
			'category_id' => $category_id,
			'sort_order'  => $sort_order,
		];

		if ( $id > 0 ) {
			$wpdb->update(
				$table,
				$data,
				[ 'id' => $id ],
				[ '%s', '%s', '%s', '%s', '%s', '%d', '%d' ],
				[ '%d' ]
			);
		} else {
			$wpdb->insert(
				$table,
				$data,
				[ '%s', '%s', '%s', '%s', '%s', '%d', '%d' ]
			);
		}

		wp_safe_redirect(
			esc_url_raw(
				admin_url( 'admin.php?page=apb-resources&apb_saved=1' )
			)
		);
		exit;
	}

	/**
	 * Delete resource.
	 *
	 * @return void
	 */
	public static function handle_delete_resource(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'apb-resources' ) );
		}

		$id = isset( $_GET['id'] ) ? absint( wp_unslash( $_GET['id'] ) ) : 0;
		check_admin_referer( 'apb_delete_resource_' . $id );

		global $wpdb;
		$table = APB_DB::resources_table();
		$wpdb->delete( $table, [ 'id' => $id ], [ '%d' ] );

		$url = admin_url( 'admin.php?page=apb-resources&apb_deleted=1' );
		if ( isset( $_GET['category_id'] ) ) {
			$url = add_query_arg( 'category_id', absint( wp_unslash( $_GET['category_id'] ) ), $url );
		}

		wp_safe_redirect( esc_url_raw( $url ) );
		exit;
	}

	/**
	 * Bulk actions: change category or delete (list table).
	 *
	 * @return void
	 */
	public static function handle_bulk_resources(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'apb-resources' ) );
		}
		if ( ! isset( $_POST['apb_bulk_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['apb_bulk_nonce'] ), 'apb_bulk_change_category' ) ) {
			wp_die( esc_html__( 'Bad nonce', 'apb-resources' ) );
		}

		$bulk_action = isset( $_POST['apb_bulk_action'] ) ? sanitize_text_field( wp_unslash( $_POST['apb_bulk_action'] ) ) : '';
		$resource_ids = isset( $_POST['apb_resource_ids'] ) ? array_map( 'absint', wp_unslash( (array) $_POST['apb_resource_ids'] ) ) : [];
		$resource_ids = array_values( array_filter( $resource_ids ) );

		if ( empty( $resource_ids ) || '' === $bulk_action ) {
			wp_safe_redirect( esc_url_raw( admin_url( 'admin.php?page=apb-resources' ) ) );
			exit;
		}

		global $wpdb;
		$table = APB_DB::resources_table();

		if ( 'delete' === $bulk_action ) {
			$deleted = 0;
			foreach ( $resource_ids as $id ) {
				$result = $wpdb->delete( $table, [ 'id' => $id ], [ '%d' ] );
				if ( false !== $result && $result > 0 ) {
					++$deleted;
				}
			}
			$redirect = admin_url( 'admin.php?page=apb-resources&apb_deleted=' . $deleted );
			if ( isset( $_POST['apb_filter_category_id'] ) ) {
				$fid = absint( wp_unslash( $_POST['apb_filter_category_id'] ) );
				if ( $fid > 0 ) {
					$redirect = add_query_arg( 'category_id', $fid, $redirect );
				}
			}
			wp_safe_redirect( esc_url_raw( $redirect ) );
			exit;
		}

		if ( 'change_category' === $bulk_action ) {
			$new_category_id = isset( $_POST['apb_new_category_id'] ) ? absint( wp_unslash( $_POST['apb_new_category_id'] ) ) : 0;
			if ( ! $new_category_id ) {
				wp_safe_redirect(
					esc_url_raw(
						admin_url( 'admin.php?page=apb-resources&apb_bulk_error=1' )
					)
				);
				exit;
			}
			$updated = 0;
			foreach ( $resource_ids as $id ) {
				$result = $wpdb->update(
					$table,
					[ 'category_id' => $new_category_id ],
					[ 'id' => $id ],
					[ '%d' ],
					[ '%d' ]
				);
				if ( false !== $result && $result > 0 ) {
					++$updated;
				}
			}
			$redirect = admin_url( 'admin.php?page=apb-resources&apb_updated=' . $updated );
			if ( isset( $_POST['apb_filter_category_id'] ) ) {
				$fid = absint( wp_unslash( $_POST['apb_filter_category_id'] ) );
				if ( $fid > 0 ) {
					$redirect = add_query_arg( 'category_id', $fid, $redirect );
				}
			}
			wp_safe_redirect( esc_url_raw( $redirect ) );
			exit;
		}

		wp_safe_redirect( esc_url_raw( admin_url( 'admin.php?page=apb-resources' ) ) );
		exit;
	}
}

/**
 * Edit icon (SVG).
 *
 * @return string
 */
function apb_icon_edit(): string {
	return '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>';
}

/**
 * Delete icon (SVG).
 *
 * @return string
 */
function apb_icon_delete(): string {
	return '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>';
}

/**
 * Import icon (SVG).
 *
 * @return string
 */
function apb_icon_import(): string {
	return '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>';
}
