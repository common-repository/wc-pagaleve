<?php
/**
 * WcPagaleve Checkout.
 *
 * @package Wc Pagaleve
 */

declare(strict_types=1);

namespace WcPagaleve\Model;

use WC_Order;
use WcPagaleve\Helper\WcPagaleveUtils as Utils_Helper;
use WcPagaleve\Controller\GateWays\WcPagalevePix;
use WcPagaleve\Controller\GateWays\WcPagalevePixInCash;

class WcPagaleveCheckout {

    /**
     * Create Checkout default
     * @since 1.0.0
     */
    public static function create_checkout( $order, $order_url, $is_pix = false ) {
        return (object) [
            'approve_url'    => $order_url,
            'cancel_url'     => wc_get_checkout_url() . '?pagaleve=true',
            'webhook_url'    => site_url() . '/wc-api/wc_pagaleve_order_change/?token=' . get_option('wc_pagaleve_webhook_token', true),
            'order'          => (object) self::set_order( $order ),
            'provider'       => 'WOO_COMMERCE',
            'shopper'        => (object) self::set_shopper( $order ),
            'is_pix_upfront' => $is_pix,
            'metadata'       => [
                'version'      => PAGALEVE_VERSION,
                'merchantName' => get_bloginfo( 'name' ),
            ]
        ];
    }

     /**
     * Create Checkout transparent
     * @since 1.5.0
     */
    public static function create_checkout_transparent( $order, $order_url, $is_pix = false ) {

        return (object) [
            'approve_url'    => $order_url . '&pagaleve-approved=true&order=' . $order->get_id(),
            'cancel_url'     => site_url() . '?pagaleve-cancel=true',
            'webhook_url'    => site_url() . '/wc-api/wc_pagaleve_order_change/?token=' . get_option('wc_pagaleve_webhook_token', true),
            'order'          => (object) self::set_order( $order ),
            'provider'       => 'WOO_COMMERCE',
            'shopper'        => (object) self::set_shopper( $order ),
            'is_pix_upfront' => $is_pix,
            'metadata'       => [
                'version'      => PAGALEVE_VERSION,
                'merchantName' => get_bloginfo( 'name' ),
            ]
        ];
    }

    /**
	 * Get thankyou page url
	 *
	 * @since 1.5.0
	 * @param string $method
     * @param bool $gateway
	 * @return object|bool
	 */
	public static function get_approve_url( $order, $gateway = false ) {

        $payment_method = WC()->session->get( 'chosen_payment_method' );

        if ( $payment_method === 'pagaleve-pix-cash' ) {
            $gateway = new WcPagalevePixInCash;
        }

        if ( $payment_method === 'pagaleve-pix' ) {
            $gateway = new WcPagalevePix;
        }

        return $gateway ? $gateway->get_return_url( $order ) : '';
	}

    /**
     * Get Cancel URL.
     * @since 1.0.0
     */
    public static function get_cancel_url( $order ) {
        $items      = $order->get_items();
        $url_params = [];
        $product_id = [];

        foreach ( $items as $item_id => $item ) {
            $product_id[] = $item->get_product_id();
            $variation_id = $item->get_variation_id();

            if ( $variation_id > 0 ) {
                $product_id[] = $variation_id;
            }

            $url_params = [
                'add-to-cart' => implode( ',', $product_id ),
                'quantity'    => $item->get_quantity(),
                'id'          => $order->get_id()
			];
        }

        return wc_get_checkout_url() .'?'. build_query( $url_params );
    }

    /**
     * Set Order.
     * @since 1.0.0
     */
    public static function set_order( $order ) {
        $order_items = [];
        $items       = $order->get_items();
        $order_prefix = get_option('wc_pagaleve_settings_order_prefix') ?? '';

        foreach ( $items as $item ) {
            $name           = $item->get_name();
            $quantity       = $item->get_quantity();
            $subtotal_order = number_format( (float)$item->get_subtotal(), 2, '', '.' );
            $subtotal       = str_pad( $subtotal_order, 4, '0', STR_PAD_LEFT );

            $order_items[] = [
                'name'      => $name,
                'price'     => Utils_Helper::str_to_integer( $subtotal ),
                'quantity'  => $quantity,
                'reference' => (string) $item->get_product_id()
			];
        }

        return (object) [
            'reference' => $order_prefix . (string) $order->get_id(),
            'tax'       => Utils_Helper::str_to_integer( $order->get_total_tax() ),
            'amount'    => Utils_Helper::str_to_integer( $order->get_total() ),
            'items'     => $order_items
        ];
    }

    public static function get_person_type( $order ) {
        $billing_cpf = $order->get_meta('_billing_cpf');
        $billing_cnpj = $order->get_meta('_billing_cnpj');

        if ($billing_cpf) {
            return Utils_Helper::getDigits($order->get_meta('_billing_cpf'));
        }

        if ($billing_cnpj) {
            return Utils_Helper::getDigits($order->get_meta('_billing_cnpj'));
        }

        if (isset($_COOKIE['document-data'])) {
            $data = unserialize(stripslashes($_COOKIE['document-data']));

            if (isset($data['document'])) {
                return Utils_Helper::getDigits($data['document']);
            }
        }

        return '';
    }

    /**
     * Set Shopper ( costumer ).
     * @since 1.0.0
     */
	public static function set_shopper( $order ) {
		$doc = self::get_person_type( $order );

        return (object) [
            'first_name'      => (string) $order->get_billing_first_name(),
            'last_name'       => (string) $order->get_billing_last_name(),
            'phone'           => (string) Utils_Helper::getDigits( $order->get_billing_phone() ),
            'email'           => (string) $order->get_billing_email(),
            'cpf'             => (string) $doc,
            'billing_address' => (object) self::get_address( $order )
        ];
    }

    /**
     * Set Shipping.
     * @since 1.0.0
     */
	public static function set_shipping( WC_Order $order ): array {
		$first_name = $order->get_shipping_first_name();
		$name       = $order->get_formatted_shipping_full_name();

		return [
			(object) [
				'first_name'      => (string) $first_name ? $first_name : $order->get_billing_first_name(),
				'name'            => (string) $name ? $name : $order->get_formatted_billing_full_name(),
				'email'           => (string) $order->get_billing_email(),
				'phone_number'    => (string) Utils_Helper::getDigits( $order->get_billing_phone() ),
				'shipping_amount' => (int) $order->get_shipping_total() * 100,
				'address'         => (object) self::get_address( $order, 'shipping' )
			]
		];
	}

    /**
     * Get Address.
     * @since 1.0.0
     */
	protected static function get_address( $order, $address_type = 'billing' ) {
		$another_type = 'billing' === $address_type ? 'shipping' : 'billing';
        $name         = $order->get_formatted_shipping_full_name();

		$street      = $order->{"get_{$address_type}_address_1"}();
		$number      = $order->get_meta( "_${address_type}_number" );
		$complement  = $order->{"get_{$address_type}_address_2"}();
		$district    = $order->get_meta( "_${address_type}_neighborhood" );
		$city        = $order->{"get_{$address_type}_city"}();
		$state       = $order->{"get_${address_type}_state"}();
		$country     = $order->{"get_${address_type}_country"}();
		$postal_code = Utils_Helper::getDigits( $order->{"get_${address_type}_postcode"}() );

		$address = [
            'name'         => (string) $name ? $name : $order->get_formatted_billing_full_name(),
            'city'         => (string) $city ? $city : $order->{"get_{$another_type}_city"}(),
            'state'        => (string) $state ? $state : $order->{"get_${another_type}_state"}(),
            'zip_code'     => (string) $postal_code ? $postal_code : Utils_Helper::getDigits( $order->{"get_${another_type}_postcode"}() ),
            'neighborhood' => (string) $district ? $district : $order->get_meta( "_${another_type}_neighborhood" ),
            'number'       => (string) $number ? $number : $order->get_meta( "_${another_type}_number" ),
            'complement'   => (string) $complement ? $complement : $order->{"get_${another_type}_address_2"}(),
            'country'      => (string) $country ? $country : $order->{"get_${another_type}_country"}(),
            'phone_number' => (string) Utils_Helper::getDigits( $order->get_billing_phone() ),
            'street'       => (string) $street ? $street : $order->{"get_{$another_type}_address_1"}()
		];

		return $address;
	}
}
