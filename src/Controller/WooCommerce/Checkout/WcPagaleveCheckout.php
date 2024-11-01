<?php
/**
 * WcPagaleve Checkout.
 *
 * @package Wc Pagaleve
 */

declare(strict_types=1);

namespace WcPagaleve\Controller\WooCommerce\Checkout;

use WcPagaleve\Model\{
	WcPagaleveApi as Api_Model,
	WcPagaleveCheckout as Checkout_Model,
};

use WcPagaleve\Helper\{
	WcPagaleveLogs as Logs_Helper,
	WcPagaleveUtils as Utils_Helper,
};

use WcPagaleve\View\WcPagaleveCheckout as Checkout_View;

use WC_Order;
use WP_Error;

/**
 * Pagaleve Checkout.
 */
class WcPagaleveCheckout {

	/**
	 * {@inheritDoc}
	 */
	public function __construct() {
		$this->services_api = new Api_Model();

		add_filter( 'woocommerce_available_payment_gateways', [ $this, 'pagaleve_change_payment_button' ] );
		add_filter( 'woocommerce_order_button_html', [ $this, 'pagaleve_order_button_html' ], 10 );
		add_action( 'wp_ajax_ajax_popup_access', [ $this, 'pagaleve_checkout_request_popup_access' ] );
		add_action( 'wp_ajax_nopriv_ajax_popup_access', [ $this, 'pagaleve_checkout_popup_access' ] );
		add_action( 'wp_footer', [ $this, 'pagaleve_modal_html' ] );
	}

	/**
	 * Pagaleve change payment button html.
	 *
	 * @since 1.5.0
	 */
	public function pagaleve_change_payment_button( $available_gateways ) {
        if ( !is_checkout() ) {
            return $available_gateways;
        }

        if ( array_key_exists( 'pagaleve-pix-cash', $available_gateways ) ) {
            $available_gateways['pagaleve-pix-cash']->order_button_text = __( 'Finalizar compra', 'woocommerce' );
        }

        if ( array_key_exists( 'pagaleve-pix', $available_gateways ) ) {
            $available_gateways['pagaleve-pix']->order_button_text = __( 'Finalizar compra', 'woocommerce' );
        }

        return $available_gateways;
    }

	/**
	 * Pagaleve order button html.
	 *
	 * @since  1.5.0
	 * @access public
	 *
	 * @param string $button_html Button html.
	 * @return string Button html.
	 */
	public function pagaleve_order_button_html( $button_html ) {
		$chosen_payment_method = WC()->session->get( 'chosen_payment_method' );
		$checkout_pix          = Utils_Helper::get_checkout_pix();
		$checkout_cash         = Utils_Helper::get_checkout_cash();

		if ( ( $chosen_payment_method === 'pagaleve-pix' && $checkout_pix === 'checkout_transparent' )
		|| ( $chosen_payment_method === 'pagaleve-pix-cash' && $checkout_cash === 'checkout_transparent' ) ) {
			return str_replace( '<button', '<button id="pagaleve_place_order"', $button_html );
		}

		return $button_html;
	}

	/**
	 * Remove poducts from WooCommerce cart
	 * 
	 * @since 1.5.0
	 * @return void
	 */
	public function pagaleve_checkout_request_popup_access() {
		$order_id = json_decode( json_encode( Utils_Helper::post( 'order_id' ) ), false );
		$order    = wc_get_order($order_id);
		
		if ( $order ) {
			$order->update_meta_data('_pagaleve_transparente_checkout_access', true);
			$order->save();

			wp_send_json_success( [ 'status' => 'success' ], 200 );
		}

		wp_send_json_error(
			new WP_Error( 'error', __( 'Não foi possível identificar o pedido!', 'woocommerce' ) ),
			400
		);

		wp_die();
	}

	/**
	 * Get cart items ID.
	 *
	 * @since 1.5.0
	 * @return array
	 */
	protected function cart_items() {
		$cart_items = WC()->cart->get_cart();

		if ( ! $cart_items ) {
			return [];
		}

		$items = [];

		foreach ( $cart_items as $key => $item ) {
			$items[ $key ] = [
				'ID'           => $item['data']->get_id(),
				'product_id'   => $item['product_id'],
				'variation_id' => $item['variation_id'],
				'quantity'     => $item['quantity']
			];
		}

		return $items;
	}

	/**
	 * Format billing address.
	 *
	 * @since 1.5.0
	 * @param json $data Data.
	 * @return array
	 */
	protected function format_billing_address( $data ) {
		return [
			'first_name' => isset( $data->billing_first_name ) ? $data->billing_first_name : '',
			'last_name'  => isset( $data->billing_last_name ) ? $data->billing_last_name : '',
			'company'    => isset( $data->billing_company ) ? $data->billing_company : '',
			'email'      => isset( $data->billing_email ) ? $data->billing_email  : '',
			'phone'      => isset( $data->billing_phone ) ? $data->billing_phone : '',
			'address_1'  => isset( $data->billing_address_1 ) ? $data->billing_address_1 : '',
			'address_2'  => isset( $data->billing_address_2 ) ? $data->billing_address_2 : '',
			'city'       => isset( $data->billing_city ) ? $data->billing_city : '',
			'state'      => isset( $data->billing_state ) ? $data->billing_state : '',
			'postcode'   => isset( $data->billing_postcode ) ? $data->billing_postcode : '',
			'country'    => isset( $data->billing_country ) ? $data->billing_country : 'BR'
		];
	}

	/**
	 * Format shipping address.
	 *
	 * @since 1.5.0
	 * @param object $data
	 * @return array
	 */
	protected function format_shipping_address( $data ) {
		return [
			'first_name' => isset( $data->shipping_first_name ) ? $data->shipping_first_name : '',
			'last_name'  => isset( $data->shipping_last_name ) ? $data->shipping_last_name : '',
			'company'    => isset( $data->shipping_company ) ? $data->shipping_company : '',
			'email'      => isset( $data->billing_email ) ? $data->billing_email  : '',
			'phone'      => isset( $data->billing_phone ) ? $data->billing_phone : '',
			'address_1'  => isset( $data->shipping_address_1 ) ? $data->shipping_address_1 : '',
			'address_2'  => isset( $data->shipping_address_2 ) ? $data->shipping_address_2 : '',
			'city'       => isset( $data->shipping_city ) ? $data->shipping_city : '',
			'state'      => isset( $data->shipping_state ) ? $data->shipping_state : '',
			'postcode'   => isset( $data->shipping_postcode ) ? $data->shipping_postcode : '',
			'country'    => isset( $data->shipping_country ) ? $data->shipping_country : 'BR'
		];
	}

	/**
	 * Add Order meta.
	 *
	 * @since 1.5.0
	 * @param WC_Order $order WC Order.
	 * @return void
	 */
	protected function add_order_meta( $order, $data ) {

		if ( isset( $data->billing_cellphone ) ) {
			$order->update_meta_data('_billing_cellphone', $data->billing_cellphone);
		}

		if ( isset( $data->billing_number ) ) {
			$order->update_meta_data('_billing_number', $data->billing_number);
		}

		if ( isset( $data->shipping_number ) ) {
			$order->update_meta_data('_shipping_number', $data->shipping_number);
		}

		$order->save();
	}

	/**
	 * Add order shipping.
	 *
	 * @since 1.5.0
	 * @param WC_Order $order WC Order.
	 * @return void
	 */
	protected function add_order_shipping( $order ) {
		$cart      = WC()->cart;
		$cart_hash = md5( json_encode( wc_clean( $cart->get_cart_for_session() ) ) . $cart->total );

        $order->set_cart_hash( $cart_hash );
        $order->set_total( $cart->total );
        $order->set_shipping_total( $cart->shipping_total );
        $order->set_discount_total( $cart->get_cart_discount_total() );
        $order->set_discount_tax( $cart->get_cart_discount_tax_total() );
        $order->set_cart_tax( $cart->tax_total );
        $order->set_shipping_tax( $cart->shipping_tax_total );
	}

	/**
	 * Set cookie order
	 *
	 * @since 1.5.0
	 */
	protected function set_cookie_order( $order_id ) {
		$order = wc_get_order( $order_id );

		if ( class_exists( 'WC_Vouchers' ) ) {
			$order_url = wc_get_vouchers_url() . '?order=' . $order_id . '&key=' . $order->get_order_key();

			Utils_Helper::pagaleve_set_cookie( 'pagaleveOrderID', (string) $order_id );
			Utils_Helper::pagaleve_set_cookie( 'pagaleveOrderUrl', $order_url );

			return;
		}

		if ( $order ) {
			$order_url = $order->get_checkout_order_received_url();
		} else {
			$order_url = wc_get_endpoint_url( 'order-received', '', wc_get_checkout_url() );
		}

		Utils_Helper::pagaleve_set_cookie( 'pagaleveOrderID', (string) $order_id );
		Utils_Helper::pagaleve_set_cookie( 'pagaleveOrderUrl', $order_url );
	}

	/**
	 * Add checkout modal html.
	 *
	 * @since 1.5.0
	 * @return void
	 */
	public function pagaleve_modal_html() {
		if ( is_checkout() ) {
			Checkout_View::render_iframe_checkout();
		}
	}
}
