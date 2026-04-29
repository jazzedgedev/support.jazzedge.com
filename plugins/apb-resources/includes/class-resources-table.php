<?php
/**
 * Resources WP_List_Table.
 *
 * @package APB_Resources
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Class APB_Resources_Table
 */
class APB_Resources_Table extends WP_List_Table {

	/**
	 * Selected category filter.
	 *
	 * @var int
	 */
	protected $filter_category_id = 0;

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			[
				'singular' => 'resource',
				'plural'   => 'resources',
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
			'cb'          => '<input type="checkbox" />',
			'title'       => __( 'Title', 'apb-resources' ),
			'category'    => __( 'Category', 'apb-resources' ),
			'url'         => __( 'URL', 'apb-resources' ),
			'button_text' => __( 'Button Text', 'apb-resources' ),
			'sort_order'  => __( 'Order', 'apb-resources' ),
			'actions'     => __( 'Actions', 'apb-resources' ),
		];
	}

	/**
	 * Bulk actions (UI rendered in resources-list.php; core tablenav suppressed).
	 *
	 * @return array<string, string>
	 */
	public function get_bulk_actions(): array {
		return [
			'change_category' => __( 'Change Category', 'apb-resources' ),
			'delete'            => __( 'Delete Selected', 'apb-resources' ),
		];
	}

	/**
	 * Row checkbox.
	 *
	 * @param array<string, mixed> $item Row.
	 * @return string
	 */
	protected function column_cb( $item ): string {
		return '<input type="checkbox" name="apb_resource_ids[]" value="' . esc_attr( (string) $item['id'] ) . '" />';
	}

	/**
	 * Suppress default tablenav — bulk/filter controls live in the view template.
	 *
	 * @param string $which Top or bottom.
	 * @return void
	 */
	protected function display_tablenav( $which ) {
		unset( $which );
	}

	/**
	 * Load items with optional category filter.
	 *
	 * @return void
	 */
	public function prepare_items(): void {
		global $wpdb;

		$this->filter_category_id = isset( $_GET['category_id'] ) ? absint( wp_unslash( $_GET['category_id'] ) ) : 0;

		$res_table = APB_DB::resources_table();
		$cat_table = APB_DB::categories_table();

		if ( $this->filter_category_id > 0 ) {
			$this->items = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT r.*, c.name AS category_name
					FROM {$res_table} r
					INNER JOIN {$cat_table} c ON r.category_id = c.id
					WHERE r.category_id = %d
					ORDER BY r.category_id ASC, r.sort_order ASC, r.id ASC",
					$this->filter_category_id
				),
				ARRAY_A
			);
		} else {
			$this->items = $wpdb->get_results(
				"SELECT r.*, c.name AS category_name
				FROM {$res_table} r
				INNER JOIN {$cat_table} c ON r.category_id = c.id
				ORDER BY r.category_id ASC, r.sort_order ASC, r.id ASC",
				ARRAY_A
			);
		}

		if ( ! is_array( $this->items ) ) {
			$this->items = [];
		}

		$this->_column_headers = [
			$this->get_columns(),
			[],
			[],
		];
	}

	/**
	 * Render category filter dropdown above table.
	 *
	 * @return void
	 */
	public function render_category_filter(): void {
		global $wpdb;

		$cat_table = APB_DB::categories_table();
		$cats      = $wpdb->get_results(
			"SELECT id, name FROM {$cat_table} ORDER BY sort_order ASC, name ASC",
			ARRAY_A
		);

		$current_url = admin_url( 'admin.php' );
		$selected    = $this->filter_category_id;
		?>
		<form method="get" action="<?php echo esc_url( $current_url ); ?>" class="apb-resources-filter" style="margin: 1em 0;">
			<input type="hidden" name="page" value="apb-resources" />
			<label for="apb-filter-category"><?php esc_html_e( 'Category', 'apb-resources' ); ?></label>
			<select name="category_id" id="apb-filter-category" onchange="this.form.submit();">
				<option value="0"><?php esc_html_e( 'All Categories', 'apb-resources' ); ?></option>
				<?php
				if ( is_array( $cats ) ) {
					foreach ( $cats as $c ) {
						if ( ! isset( $c['id'], $c['name'] ) ) {
							continue;
						}
						printf(
							'<option value="%s"%s>%s</option>',
							esc_attr( (string) $c['id'] ),
							selected( $selected, (int) $c['id'], false ),
							esc_html( $c['name'] )
						);
					}
				}
				?>
			</select>
			<?php submit_button( __( 'Filter', 'apb-resources' ), 'secondary', 'submit', false ); ?>
		</form>
		<?php
	}

	/**
	 * Title with edit link.
	 *
	 * @param array<string, mixed> $item Row.
	 * @return string
	 */
	protected function column_title( array $item ): string {
		$id  = absint( $item['id'] );
		$url = admin_url( 'admin.php?page=apb-resources&action=edit&id=' . $id );
		return '<a href="' . esc_url( $url ) . '">' . esc_html( $item['title'] ) . '</a>';
	}

	/**
	 * Category name.
	 *
	 * @param array<string, mixed> $item Row.
	 * @return string
	 */
	protected function column_category( array $item ): string {
		$name = isset( $item['category_name'] ) ? (string) $item['category_name'] : '';
		return esc_html( $name );
	}

	/**
	 * Truncated external link.
	 *
	 * @param array<string, mixed> $item Row.
	 * @return string
	 */
	protected function column_url( array $item ): string {
		$url = isset( $item['url'] ) ? (string) $item['url'] : '';
		if ( '' === $url ) {
			return '';
		}
		$display = $url;
		if ( strlen( $display ) > 50 ) {
			$display = substr( $display, 0, 47 ) . '...';
		}
		return '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $display ) . '</a>';
	}

	/**
	 * Actions column.
	 *
	 * @param array<string, mixed> $item Row.
	 * @return string
	 */
	protected function column_actions( array $item ): string {
		$id = absint( $item['id'] );

		$edit = '<a href="' . esc_url( admin_url( 'admin.php?page=apb-resources&action=edit&id=' . $id ) ) . '" class="apb-icon-edit" title="' . esc_attr__( 'Edit', 'apb-resources' ) . '">' . apb_icon_edit() . '</a>';

		$delete_args = [
			'action' => 'apb_delete_resource',
			'id'     => $id,
		];
		if ( $this->filter_category_id > 0 ) {
			$delete_args['category_id'] = $this->filter_category_id;
		}
		$delete_url = wp_nonce_url(
			add_query_arg( $delete_args, admin_url( 'admin-post.php' ) ),
			'apb_delete_resource_' . $id
		);
		$delete     = '<a href="' . esc_url( $delete_url ) . '" class="apb-icon-delete" title="' . esc_attr__( 'Delete', 'apb-resources' ) . '" onclick="return confirm(\'' . esc_js( __( 'Delete this resource?', 'apb-resources' ) ) . '\')">' . apb_icon_delete() . '</a>';

		return '<span class="apb-list-table">' . $edit . ' ' . $delete . '</span>';
	}

	/**
	 * Default column.
	 *
	 * @param array<string, mixed> $item        Row.
	 * @param string               $column_name Key.
	 * @return string
	 */
	protected function column_default( $item, $column_name ): string {
		if ( ! isset( $item[ $column_name ] ) ) {
			return '';
		}
		return esc_html( (string) $item[ $column_name ] );
	}

	/**
	 * Empty state.
	 *
	 * @return void
	 */
	public function no_items(): void {
		esc_html_e( 'No resources found.', 'apb-resources' );
	}
}
