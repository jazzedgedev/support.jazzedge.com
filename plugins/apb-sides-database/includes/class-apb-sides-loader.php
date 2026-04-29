<?php
/**
 * Registers WordPress actions and filters.
 *
 * @package APB_Sides_Database
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hook loader: queue then run().
 */
class APB_Sides_Loader {

	/**
	 * @var array<int, array{0: string, 1: callable, 2: int, 3: int}>
	 */
	protected $actions = array();

	/**
	 * @var array<int, array{0: string, 1: callable, 2: int, 3: int}>
	 */
	protected $filters = array();

	/**
	 * Add action to queue.
	 *
	 * @param string   $hook Hook name.
	 * @param callable $callback Callback.
	 * @param int      $priority Priority.
	 * @param int      $accepted_args Args count.
	 */
	public function add_action( string $hook, callable $callback, int $priority = 10, int $accepted_args = 1 ): void {
		$this->actions[] = array( $hook, $callback, $priority, $accepted_args );
	}

	/**
	 * Add filter to queue.
	 *
	 * @param string   $hook Hook name.
	 * @param callable $callback Callback.
	 * @param int      $priority Priority.
	 * @param int      $accepted_args Args count.
	 */
	public function add_filter( string $hook, callable $callback, int $priority = 10, int $accepted_args = 1 ): void {
		$this->filters[] = array( $hook, $callback, $priority, $accepted_args );
	}

	/**
	 * Register all queued hooks with WordPress.
	 */
	public function run(): void {
		foreach ( $this->filters as $f ) {
			add_filter( $f[0], $f[1], $f[2], $f[3] );
		}
		foreach ( $this->actions as $a ) {
			add_action( $a[0], $a[1], $a[2], $a[3] );
		}
	}
}
