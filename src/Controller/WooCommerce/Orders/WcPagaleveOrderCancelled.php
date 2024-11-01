<?php
/**
 * WcPagaleve Order Cancelled.
 *
 * @package Wc Pagaleve
 */

declare(strict_types=1);

namespace WcPagaleve\Controller\WooCommerce\Orders;

use WcPagaleve\Model\WcPagaleveApi as Api_Model;

/**
 * Pagaleve Order.
 */
class WcPagaleveOrderCancelled {

	/**
	 * {@inheritDoc}
	 */
	public function __construct() {
        $this->services_api = new Api_Model();
	}

    /**
     * WooCommerce Cancelled Order by url
     *
     * @since  1.1.0
     * @access public
     */
    public function pagaleve_woo_order_cancelled( $order_id ) {
        $order = wc_get_order($order_id);

        if ($order) {
            $cart_content = $order->get_meta("_pagaleve_cart_content_{$order_id}", true);

            if ( $cart_content ) {
                $cart_coupons = $order->get_meta("_pagaleve_cart_coupon_{$order_id}", true);
                WC()->cart->set_cart_contents( $cart_content );

                if( sizeof( $cart_coupons ) > 0 ) {
                    foreach( $cart_coupons as $coupon_code ) {
                        WC()->cart->add_discount( $coupon_code );
                    }

                    $order->delete_meta_data("_pagaleve_cart_coupon_{$order_id}");
                }

                $order->delete_meta_data("_pagaleve_cart_content_{$order_id}");
            }

            $order->save();
            $order->add_order_note(  __( 'Pagaleve: Pedido cancelado pelo cliente.' ) );
        }
    }
}
