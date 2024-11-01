<?php
/**
 * WcPagaleve Order Refunded.
 *
 * @package Wc Pagaleve
 */
declare(strict_types=1);

namespace WcPagaleve\Controller\WooCommerce\Orders;

use WcPagaleve\Model\WcPagaleveApi as Api_Model;

use WcPagaleve\Helper\{
	WcPagaleveLogs as Logs_Helper,
	WcPagaleveOrder as Orders_Helper,
	WcPagaleveUtils as Utils_Helper,
};

use WcPagaleve\Controller\WooCommerce\Orders\{
	WcPagaleveOrderCancelled,
	WcPagaleveOrderRefunded,
};

/**
 * Pagaleve Order.
 */
class WcPagaleveOrder {

	/**
	 * {@inheritDoc}
	 */
	public function __construct() {
		$this->services_api = new Api_Model();

		add_action( 'woocommerce_order_refunded', [ $this, 'pagaleve_woo_order' ], 10 );
		add_action( 'wp_loaded', [ $this, 'pagaleve_order_cancel_by_url' ] );
		add_filter( 'cron_schedules', [ $this, 'pagaleve_cron_schedules' ] );
		add_action( 'admin_init', [ $this, 'pagaleve_register_schedules' ] );
		add_action( 'pageleve_update_woo_order', [ $this, 'pagaleve_update_order_by_cron' ] );
		add_action( 'woocommerce_admin_order_data_after_billing_address', [ $this, 'display_order_fields' ], 20 );
	}

	/**
	 * Pagaleve woocommerce order refunded.
	 *
	 * @return void
	 */
	public function pagaleve_woo_order( $order_id ) {
		$order           = wc_get_order( $order_id );
		$payment_options = $order->get_meta( '_pagaleve_payment_response' );
		$payment_id      = isset( $payment_options['id'] ) ? $payment_options['id'] : '';

		if ( !$payment_id ) {
			return;
		}

		$order_service = new WcPagaleveOrderRefunded();
		$order_service->pagaleve_woo_order_refunded( $order_id, $payment_options, $order );
	}

	/**
	 * Pagaleve woocommerce order cancelled.
	 *
	 * @return void
	 */
	public function pagaleve_order_cancel_by_url() {
		if ( !class_exists( 'WooCommerce' ) ) {
			return;
		}

		$is_pagaleve = Utils_Helper::get( 'pagaleve' );

		if ( !$is_pagaleve || $is_pagaleve != 'true' ) {
			return;
		}

		$order_id = Utils_Helper::cookie( 'pagaleveOrderID' );

		if ( !$order_id ) {
			return;
		}

		$order_service = new WcPagaleveOrderCancelled();
		$order_service->pagaleve_woo_order_cancelled( (int) $order_id );
	}

	/**
     * Add Cron Schedules
     *
     * @since  1.2.0
     * @access public
     */
    public function pagaleve_cron_schedules() {
		$cron_options = Orders_Helper::set_cron_time();

		return [
			'pagaleve_update_order' => [
				'interval' => $cron_options->interval,
				'display'  => $cron_options->display
			]
		];
	}

	/**
     * Register Cron Schedules
     *
     * @since  1.2.0
     * @access public
     */
	public function pagaleve_register_schedules() {
		if ( ! wp_next_scheduled( 'pageleve_update_woo_order' ) ) {
			wp_schedule_event( time(), 'pagaleve_update_order', 'pageleve_update_woo_order' );
		}
	}

	/**
     * Cron Update WooCommerce Order
     *
     * @since  1.2.0
     * @access public
     */
	public function pagaleve_update_order_by_cron() {
		$order_ids    = Orders_Helper::get_all_id_orders();
		$access_token = '';

		if ( !$order_ids ) {
			return;
		}

		foreach ( $order_ids as $order_id ) {
            $order = wc_get_order($order_id);

			$checkout_meta = $order->get_meta('_pagaleve_checkout_response', true);

			if ( !$checkout_meta ) {
				continue;
			}

			$checkout_id = isset( $checkout_meta['id'] ) ? $checkout_meta['id'] : '';

			if ( !$checkout_id ) {
				continue;
			}

			$checkout_response      = $this->services_api->get_checkout( $access_token, $checkout_id );
			$response_body_checkout = wp_remote_retrieve_body( $checkout_response );
			$response_checkout      = json_decode( $response_body_checkout, true );
			$checkout_status        = isset( $response_checkout['state'] ) ? $response_checkout['state'] : '';
			$is_pix                 = Utils_Helper::is_log_pix();
			$is_cash                = Utils_Helper::is_log_cash();

			if ( !$checkout_status
			|| $checkout_status === 'NEW'
			|| $checkout_status === 'ACCEPTED' ) {
				continue;
			}

			if ( $is_pix || $is_cash ) {
				Logs_Helper::cron_order_update( 'PAGALEVE RESPONSE ORDER ID', $order_id );
				Logs_Helper::cron_order_update( 'PAGALEVE RESPONSE STATUS', $checkout_status );
				Logs_Helper::cron_order_update( 'PAGALEVE RESPONSE', json_encode( $response_checkout ) );
			}

			Orders_Helper::update_order_by_id( $checkout_status, $order_id );
		}
	}

	/**
     * Display fields in order
     * @since  1.3.7
     */
	public function display_order_fields() {
		$order_id      = get_the_id();
		$order         = wc_get_order($order_id);

		if ($order) {
			$checkout_meta = $order->get_meta($order_id, '_pagaleve_checkout_response', true);
			$payment_meta  = $order->get_meta($order_id, '_pagaleve_payment_response', true);
			$checkout_id   = isset( $checkout_meta['id'] ) ? $checkout_meta['id'] : '';
			$payment_id    = isset( $payment_meta['id'] ) ? $payment_meta['id'] : '';
	
			if ( !$checkout_id ) {
				return;
			}
	
			Utils_Helper::template_include( 'templates/orders/pix', compact( 'checkout_id', 'payment_id' ) );
		}
		
	}
}
