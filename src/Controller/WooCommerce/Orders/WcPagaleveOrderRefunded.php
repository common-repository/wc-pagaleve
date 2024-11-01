<?php
/**
 * WcPagaleve Order Refunded.
 *
 * @package Wc Pagaleve
 */

declare(strict_types=1);

namespace WcPagaleve\Controller\WooCommerce\Orders;

use WcPagaleve\Helper\{
	WcPagaleveLogs as Logs_Helper,
	WcPagaleveUtils as Utils_Helper,
};

use WcPagaleve\Model\WcPagaleveApi as Api_Model;

/**
 * Pagaleve Order.
 */
class WcPagaleveOrderRefunded {

	/**
	 * {@inheritDoc}
	 */
	public function __construct() {
        $this->services_api = new Api_Model();
	}

    /**
     * WooCommerce Refund Order Manual
     *
     * @since  1.0.0
     * @access public
     */
    public function pagaleve_woo_order_refunded( $order_id, $payment_options, $order ) {

        $get_refund    = Utils_Helper::post( 'refund_amount', FILTER_SANITIZE_STRING );
        $refund_format = wc_format_decimal( $get_refund, 2 );
        $refund_remain = $order->get_remaining_refund_amount();
        $replace       = str_replace( '.', '', $refund_format );
        $refund_value  = str_pad( $replace, 4, '0', STR_PAD_LEFT );

        if ( $refund_format === '0.00' ) {
            return $order->add_order_note(  __( 'Pagaleve: Pagamento não reembolsado.' ) );
        }

        if ( $refund_format === '0.00' && $refund_remain === '0.00' ) {
            return $order->add_order_note(  __( 'Pagaleve: Pagamento totalmente reembolsado.' ) );
        }

        $this->refund_order_api( $payment_options, $order_id, $refund_value, $refund_format );
    }

    /**
	 * Register Refund Order route
	 *
	 * @since  1.0.0
	 * @access public
	 */
	public function refund_order_api( $options, $order_id, $refund_value, $price ) {
        $access_token  = '';
        $payment_id    = '';

        if ( isset( $options['id'] ) ) {
			$payment_id = $options['id'];
		}

        $body = [
            'amount' => (int) $refund_value,
            'reason' => 'REQUESTED_BY_CUSTOMER'
        ];

        $response = $this->services_api->send_request_post( $body, $access_token, 'payments/'.$payment_id.'/refund' );
        $code     = wp_remote_retrieve_response_code( $response );
        $body     = wp_remote_retrieve_body( $response );
        $response = json_decode( $body );
        $is_pix   = Utils_Helper::is_log_pix();
        $is_cash  = Utils_Helper::is_log_cash();
        $order    = wc_get_order($order_id);

		if (in_array($code, [200, 201]) && $order) {
            $order->update_meta_data('_pagaleve_api_order_status', 'refunded');
            $order->save();

			$order->add_order_note(  __( 'Pagaleve: Pagamento reembolsado. '. wc_price( $price ) ) );

			if ( $is_pix || $is_cash ) {
				Logs_Helper::refund_order( 'PAGALEVE REFUNDED ORDER ID', $order_id );
				Logs_Helper::refund_order( 'PAGALEVE REFUNDED RESPONSE', $response );
			}
		}

		if ( in_array( !$code, [200, 201] ) ) {
			$order->add_order_note(  __( 'Pagaleve: Pagamento não reembolsado.' ) );
		}
	}
}
