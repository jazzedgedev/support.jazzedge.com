<?php
/**
 * Jazzedge Marketing - Coupon Insert (FluentCart)
 *
 * @package Jazzedge_Marketing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class JEM_Coupon
 */
class JEM_Coupon {

	/**
	 * Insert coupon into FluentCart fct_coupons table.
	 *
	 * @param object $funnel     Funnel object.
	 * @param string $coupon_code Coupon code.
	 * @param string $coupon_expires Expiry datetime (Y-m-d H:i:s).
	 * @param string $email      Lead email (for notes).
	 * @return int|false Insert ID or false on failure.
	 */
	public function insert( $funnel, $coupon_code, $coupon_expires, $email ) {
		global $wpdb;

		$table = $wpdb->prefix . 'fct_coupons';

		// The funnel stores the FluentCart post_id — we need the variation id from fct_product_variations
		$variation_id = null;
		if ( ! empty( $funnel->product_id ) ) {
			$variation = $wpdb->get_row( $wpdb->prepare(
				"SELECT id FROM {$wpdb->prefix}fct_product_variations 
				 WHERE post_id = %d 
				 LIMIT 1",
				absint( $funnel->product_id )
			) );
			if ( $variation ) {
				$variation_id = (string) $variation->id;
			}
		}

		$conditions = wp_json_encode( array(
			'max_uses'             => 1,
			'buy_quantity'         => null,
			'get_quantity'         => null,
			'is_recurring'         => 'no',
			'max_per_customer'     => 1,
			'apply_to_quantity'    => 'no',
			'excluded_products'    => array(),
			'included_products'    => $variation_id ? array( $variation_id ) : array(),
			'email_restrictions'   => '',
			'apply_to_whole_cart'  => 'yes',
			'excluded_categories'  => array(),
			'included_categories'  => array(),
			'max_discount_amount'  => 0,
			'max_purchase_amount'  => 0,
			'min_purchase_amount'  => 0,
		) );

		$data = array(
			'title'            => $funnel->name . ' - ' . $coupon_code,
			'code'             => $coupon_code,
			'priority'         => 0,
			'type'             => 'percentage',
			'conditions'       => $conditions,
			'amount'           => (float) $funnel->discount_pct,
			'use_count'        => 0,
			'status'           => 'active',
			'notes'            => $email,
			'stackable'        => 'no',
			'show_on_checkout'  => 'no',
			'start_date'       => null,
			'end_date'         => $coupon_expires,
			'created_at'        => current_time( 'mysql' ),
			'updated_at'       => current_time( 'mysql' ),
		);

		$result = $wpdb->insert( $table, $data );

		return $result ? $wpdb->insert_id : false;
	}

	/**
	 * Delete coupon by code.
	 *
	 * @param string $code Coupon code.
	 * @return bool
	 */
	public function delete_by_code( $code ) {
		global $wpdb;
		$table = $wpdb->prefix . 'fct_coupons';
		return (bool) $wpdb->delete( $table, array( 'code' => $code ) );
	}
}
