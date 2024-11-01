<?php
/**
 * WcPagaleve Payment.
 *
 * @package Wc Pagaleve
 */

declare(strict_types=1);

namespace WcPagaleve\Model;

use WcPagaleve\Helper\{
	WcPagaleveLogs as Logs_Helper,
    WcPagaleveOrder,
};

use WcPagaleve\Model\WcPagaleveApi as Api_Model;

class WcPagalevePayment {

    /**
    * Create Payment.
    * @since  1.2.0
    */
	public static function create_payment( $checkout, $order_id, $intent ) {
        $amount      = isset( $checkout['order']['amount'] ) ? $checkout['order']['amount'] : '';
        $checkout_id = isset( $checkout['id'] ) ? $checkout['id'] : '';

        return (object) [
            'amount'      => $amount,
            'checkout_id' => $checkout_id,
            'currency'    => 'BRL',
            'intent'      => $intent,
            'reference'   => (string) $order_id
        ];
    }

    /**
    * Create Request Payment.
    * @since  1.2.0
    */
    public static function create_request_payment($payment_data, $order_id, $status = 'processing')
    {
        $order = wc_get_order($order_id);

        if (!$order) {
            return;
        }

        $payment_option = $order->get_meta('_pagaleve_payment_response', true);

        if ($payment_option && isset($payment_option['id'])) {
            return;
        }

        $services_api          = new Api_Model();
        $access_token          = '';
        $payment_response      = $services_api->send_request_post( $payment_data, $access_token, 'payments' );
		$response_body_payment = wp_remote_retrieve_body( $payment_response );
		$response_payment      = json_decode( $response_body_payment, true );

        if ( isset( $payment_response['response']['code'] ) && $payment_response['response']['code'] === 201 ) {
            WcPagaleveOrder::payment_completed($order_id);
        }

        Logs_Helper::order_response( 'payment', 'PAGALEVE ORDER ID', $order_id );
        Logs_Helper::order_response( 'payment', 'PAGALEVE ORDER STATUS', $status );
        Logs_Helper::order_response( 'payment', 'PAGALEVE RESPONSE PAYMENT', $response_payment );

		$order->update_meta_data($order_id, '_pagaleve_payment_response', $response_payment);
        $order->save();
    }
}
