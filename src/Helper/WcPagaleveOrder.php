<?php
/**
 * WC Order Controller.
 *
 * @package Wc Pagaleve
 */

declare(strict_types=1);

namespace WcPagaleve\Helper;

use WcPagaleve\Controller\GateWays\WcPagalevePix;
use WcPagaleve\Controller\GateWays\WcPagalevePixInCash;
use WcPagaleve\Model\WcPagalevePayment as Payment_Model;

class WcPagaleveOrder {

	/**
	 * Update order status by notifications 1.0.
	 *
	 * @param string $status
	 * @return string
	 */
	public static function update_order_by_id( $status, $order_id ) {
		switch ( $status ) {
			case 'CANCELED':
			case 'EXPIRED':
				return self::payment_canceled( $order_id );
			case 'AUTHORIZED':
				return self::payment_paid( $order_id );
			case 'COMPLETED':
				return self::payment_completed( $order_id );
		}
	}

	/**
	 * Payment Paid.
	 *
	 * @return string
	 */
	public static function payment_paid( $order_id, $response_checkout = false ) {
		$order = wc_get_order($order_id);

		if (!$response_checkout) {
			$response_checkout = $order->get_meta('_pagaleve_checkout_response', true );
		}

		if ($order && $response_checkout) {
			if (in_array($order->get_status(), ['pending', 'on-hold'])) {
				$payment_data = Payment_Model::create_payment( $response_checkout, $order_id, 'CAPTURE' );
				$order_status = WcPagaleveOrder::get_order_success_status($order_id) ?? 'processing';

				Payment_Model::create_request_payment( $payment_data, $order_id, $order_status );
			}
		}
	}

	/**
	 * Payment Completed.
	 *
	 * @return string
	 */
	public static function payment_completed( $order_id ) {
		$order          = wc_get_order( $order_id );
		$current_status = $order->get_status();

		if (in_array( $current_status, [ 'pending', 'on-hold' ] ) ) {
			$order->update_status( self::get_order_success_status($order_id), __( 'Pagaleve: Pagamento confirmado.' ) );
		}
	}

	/**
	 * Payment canceled
	 *
	 * @param string Event message.
	 * @return string
	 */
	public static function payment_canceled( $order_id ) {
		$order          = wc_get_order( $order_id );
		$current_status = $order->get_status();

		if (in_array( $current_status, [ 'pending', 'on-hold' ] ) ) {
			$order->update_status( 'cancelled', __( 'Pagaleve: Pagamento cancelado.' ) );
		}
	}

	/**
	 * Set cron time
	 *
	 * @return object
	 */
	public static function set_cron_time() {
		$time = get_option( 'wc_pagaleve_settings_cron' );

		switch ( $time ) {
			case 'five_minutes':
				return (object) [
					'interval' => 300,
					'display'  => __( 'Atualização a cada 5 minutos' )
				];
			case 'ten_minutes':
				return (object) [
					'interval' => 600,
					'display'  => __( 'Atualização a cada 10 minutos' )
				];
			case 'fifteen_minutes':
				return (object) [
					'interval' => 900,
					'display'  => __( 'Atualização a cada 15 minutos' )
				];
			default:
				return (object) [
					'interval' => 300,
					'display'  => __( 'Atualização a cada 5 minutos' )
				];
		}
	}

	/**
     * Get all orders id
     *
     * @since  1.2.0
     * @access public
     */
	public static function get_all_id_orders() {
		$args = [
			'limit'      => -1,
			'status'     => [ 'on-hold' ],
			'return'     => 'ids',
			'meta_key'   => '_payment_method',
			'meta_value' => [ 'pagaleve-pix', 'pagaleve-pix-cash' ]
		];

		return wc_get_orders( $args );
	}

	public static function get_order_success_status($order_id)
	{
		$order = wc_get_order($order_id);

		if (!$order) {
			return false;
		}

		$method = $order->get_payment_method();

		if ($method === 'pagaleve-pix') {
			$class = new WcPagalevePix;
		}

		if ($method === 'pagaleve-pix-cash') {
			$class = new WcPagalevePixInCash;
		}

		if (!isset($class)) {
			return;
		}

		return $class->get_option('order_status');
	}
}
