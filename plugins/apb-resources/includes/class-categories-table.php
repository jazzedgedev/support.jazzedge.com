<?php
/**
 * Categories WP_List_Table.
 *
 * @package APB_Resources
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Class APB_Categories_Table
 */
class APB_Categories_Table extends WP_List_Table {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			[
				'singular' => 'category',
				'plural'   => 'categories',
				'ajax'     => false,
			]
		);
	}

	/**
	 * Columns definition.
	 *
	 * @return array<string, string>
	 */
	public function get_columns(): array {
		return [
			'name'       => __( 'Name', 'apb-resources' ),
			'slug'       => __( 'Slug', 'apb-resources' ),
			'page'       => __( 'Linked Page', 'apb-resources' ),
			'count'      => __( 'Resources', 'apb-resources' ),
			'sort_order' => __( 'Order', 'apb-resources' ),
			'actions'    => __( 'Actions', 'apb-resources' ),
		];
	}

	/**
	 * Load items.
	 *
	 * @return void
	 */
	public function prepare_items(): void {
		global $wpdb;

		$cat_table = APB_DB::categories_table();
		$res_table = APB_DB::resources_table();

		$this->items = $wpdb->get_results(
			"SELECT * FROM {$cat_table} ORDER BY sort_order ASC, id ASC",
			ARRAY_A
		);

		if ( ! is_array( $this->items ) ) {
			$this->items = [];
		}

		foreach ( $this->items as $k => $row ) {
			$cid                    = (int) $row['id'];
			$count                  = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$res_table} WHERE category_id = %d",
					$cid
				)
			);
			$this->items[ $k ]['resource_count'] = $count;
		}

		$this->_column_headers = [
			$this->get_columns(),
			[],
			[],
		];
	}

	/**
	 * Name column with edit link.
	 *
	 * @param array<string, mixed> $item Row.
	 * @return string
	 */
	protected function column_name( array $item ): string {
		$url = admin_url( 'admin.php?page=apb-resource-categories&action=edit&id=' . absint( $item['id'] ) );
		return '<a href="' . esc_url( $url ) . '">' . esc_html( $item['name'] ) . '</a>';
	}

	/**
	 * Linked page title.
	 *
	 * @param array<string, mixed> $item Row.
	 * @return string
	 */
	protected function column_page( array $item ): string {
		$pid = isset( $item['page_id'] ) ? absint( $item['page_id'] ) : 0;
		if ( $pid <= 0 ) {
			return '&mdash;';
		}
		$title = get_the_title( $pid );
		return '' !== $title ? esc_html( $title ) : '&mdash;';
	}

	/**
	 * Resource count.
	 *
	 * @param array<string, mixed> $item Row.
	 * @return string
	 */
	protected function column_count( array $item ): string {
		$n = isset( $item['resource_count'] ) ? (int) $item['resource_count'] : 0;
		return esc_html( (string) $n );
	}

	/**
	 * Edit / delete actions.
	 *
	 * @param array<string, mixed> $item Row.
	 * @return string
	 */
	protected function column_actions( array $item ): string {
		$id   = absint( $item['id'] );
		$edit = '<a href="' . esc_url( admin_url( 'admin.php?page=apb-resource-categories&action=edit&id=' . $id ) ) . '" class="apb-icon-edit" title="' . esc_attr__( 'Edit', 'apb-resources' ) . '">' . apb_icon_edit() . '</a>';

		$delete_url = wp_nonce_url(
			admin_url( 'admin-post.php?action=apb_delete_category&id=' . $id ),
			'apb_delete_category_' . $id
		);
		$delete     = '<a href="' . esc_url( $delete_url ) . '" class="apb-icon-delete" title="' . esc_attr__( 'Delete', 'apb-resources' ) . '" onclick="return confirm(\'' . esc_js( __( 'Delete this category and all its resources?', 'apb-resources' ) ) . '\')">' . apb_icon_delete() . '</a>';

		return '<span class="apb-list-table">' . $edit . ' ' . $delete . '</span>';
	}

	/**
	 * Default column output.
	 *
	 * @param array<string, mixed> $item        Row.
	 * @param string               $column_name Column key.
	 * @return string
	 */
	protected function column_default( $item, $column_name ): string {
		if ( ! isset( $item[ $column_name ] ) ) {
			return '';
		}
		return esc_html( (string) $item[ $column_name ] );
	}

	/**
	 * Message when empty.
	 *
	 * @return void
	 */
	public function no_items(): void {
		esc_html_e( 'No categories found.', 'apb-resources' );
	}
}
