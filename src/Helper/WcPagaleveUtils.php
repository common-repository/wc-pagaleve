<?php
/**
 * WcPagaleve Core.
 *
 * @package Wc Pagaleve
 */
declare(strict_types=1);

namespace WcPagaleve\Helper;

use WcPagaleve\Core;
use WcPagaleve\Model\{
	WcPagalevePayment as Payment_Model
};

class WcPagaleveUtils
{
	/**
	 * Sanitize value from custom method
	 *
	 * @since 1.0.0
	 * @param String $name
	 * @param Mixed $default
	 * @param String|Array $sanitize
	 * @return Mixed
	*/
	public static function request( $type, $name, $default, $sanitize = 'rm_tags' ) {
		$request = filter_input_array( $type, FILTER_SANITIZE_SPECIAL_CHARS );

		if ( ! isset( $request[ $name ] ) || empty( $request[ $name ] ) ) {
			return $default;
		}

		return self::sanitize( $request[ $name ], $sanitize );
	}

	/**
	 * Sanitize value from methods post
	 *
	 * @since 1.0.0
	 * @param String $name
	 * @param Mixed $default
	 * @param String|Array $sanitize
	 * @return Mixed
	*/
	public static function post( $name, $default = '', $sanitize = 'rm_tags' ) {
		return self::request( INPUT_POST, $name, $default, $sanitize );
	}

	/**
	 * Sanitize value from methods get
	 *
	 * @since 1.0.0
	 * @param String $name
	 * @param Mixed $default
	 * @param String|Array $sanitize
	 * @return Mixed
	*/
	public static function get( $name, $default = '', $sanitize = 'rm_tags' ) {
		return self::request( INPUT_GET, $name, $default, $sanitize );
	}

	/**
	 * Sanitize value from cookie
	 *
	 * @since 1.0.0
	 * @param String $name
	 * @param Mixed $default
	 * @param String|Array $sanitize
	 * @return Mixed
	*/
	public static function cookie( $name, $default = '', $sanitize = 'rm_tags' ) {
		return self::request( INPUT_COOKIE, $name, $default, $sanitize );
	}

	/**
	 * Sanitize requests
	 *
	 * @since 1.0.0
	 * @param String $value
	 * @param String|Array $sanitize
	 * @return String
	*/
	public static function sanitize( $value, $sanitize ) {
		if ( ! is_callable( $sanitize ) ) {
	    	return ( false === $sanitize ) ? $value : self::rm_tags( $value );
		}

		if ( is_array( $value ) ) {
			return array_map( $sanitize, $value );
		}

		return call_user_func( $sanitize, $value );
	}

	/**
	 * Properly strip all HTML tags including script and style
	 *
	 * @since 1.0.0
	 * @param Mixed String|Array $value
	 * @param Boolean $remove_breaks
	 * @return Mixed String|Array
	 */
	public static function rm_tags( $value, $remove_breaks = false ) {
		if ( empty( $value ) || is_object( $value ) ) {
			return $value;
		}

		if ( is_array( $value ) ) {
			return array_map( __METHOD__, $value );
		}

	    return wp_strip_all_tags( $value, $remove_breaks );
	}

	/**
	 * get Digits
	 *
	 * @since  1.0.0
	 * @access public
	 */
	public static function getDigits( string $value ) {
		return preg_replace( '/\D/', '', $value );
	}

	/**
	 * get pix settings
	 *
	 * @since  1.0.0
	 * @access public
	 */
	public static function getPixSettings() {
		return get_option( 'woocommerce_pagaleve-pix_settings' );
	}

	/**
	 * Payment status notification
	 *
	 * @since  1.0.0
	 * @access public
	 */
	public static function payment_statuses_to_notification( $status ) {
		switch ( $status ) {
			case 'pagaleve-pix':
				return 'pix';
			default:
				return 'undefined';
		}
	}

	/**
	 * Payment status
	 *
	 * @since  1.0.0
	 * @access public
	 */
	public static function payment_capture_status( $status ) {
		switch ( $status ) {
			case 'capture':
				return 'CAPTURE';
			case 'authorize':
				return 'AUTH';
			default:
				return 'undefined';
		}
	}

	/**
	 * Calc percentage
	 *
	 * @since  1.0.0
	 * @param string|float $percentage Percentage.
	 * @param string|float|int $total Total.
	 * @return mixed
	 */
	public static function calc_percentage( $percentage, $total ) {
		if ( !$percentage ) {
			return 0;
		}

		$percentage = self::str_to_float( $percentage );

		return ( $percentage / 100 ) * $total;
	}

	/**
	 * Convert str to float.
	 *
	 * @since  1.0.0
	 * @param string $string String.
	 * @return float
	 */
	public static function str_to_float( $string ) {
		return floatval( str_replace( ',', '.', $string ) );
	}

	/**
	 * Convert float to str
	 *
	 * @since  1.0.0
	 * @param string|float $string String
	 * @return array|string
	 */
	public static function float_to_str( $string ) {
		return str_replace( '.', ',', $string );
	}

	/**
	 * Convert str to integer.
	 *
	 * @since  1.0.0
	 * @param string $string String.
	 * @return float
	 */
	public static function str_to_integer( $string ) {
		return intval( str_replace( '.', '', $string ) );
	}

	/**
	 * Convert format number.
	 *
	 * @since  1.3.0
	 * @param string $string String.
	 * @return float
	 */
	public static function format_price( $string ) {
		return number_format( (float)$string, 2, '', '' );
	}

	/**
	 * Admin sanitize url
	 *
	 * @since 1.0
	 * @param String $path
	 * @return String
	 */
	public static function get_admin_url( $path = '' ) {
		return esc_url( get_admin_url( null, $path ) );
	}

	/**
	 * Template include
	 *
	 */
	public static function template_include( $file, $args = [] ) {
		if ( $args && is_array( $args ) ) {
			extract( $args );
		}

		$locale = Core::plugin_dir_path() . $file . '.php';

		if ( !file_exists( $locale ) ) {
			return;
		}

		include $locale;
    }

	/**
	 * Delete cookie
	 *
	 * @since 1.0.0
	 */
	public static function pagaleve_delete_cookie( $name ) {
		setcookie( $name, '', -1, '/' );
	}

	/**
	 * Get cookie
	 *
	 * @since 1.0.0
	 */
	public static function pagaleve_get_cookie( $name, $pre_reload = false, $sanitize = FILTER_DEFAULT ) {
		if ( $pre_reload && isset( $_COOKIE[ $name ] ) ) {
			return filter_var( $_COOKIE[ $name ], $sanitize );
		}

		return filter_input( INPUT_COOKIE, $name, $sanitize );

	}

	/**
	 * set cookie
	 *
	 * @since 1.0.0
	 */
	public static function pagaleve_set_cookie( $name, $value, $force = false ) {
		self::pagaleve_delete_cookie( $name );

		setcookie( $name, $value, time() + 3600 , '/' );

		if ( $force ) {
			$_COOKIE[ $name ] = $value;
		}

	}

	/**
	 * Is product widget
	 *
	 * @since 1.3.0
	 */
	public static function is_product_widget() {
		return ( 'yes' === get_option( 'wc_pagaleve_settings_product' ) );
	}

	/**
	 * Is cart widget
	 *
	 * @since 1.3.0
	 */
	public static function is_cart_widget() {
		return ( 'yes' === get_option( 'wc_pagaleve_settings_cart' ) );
	}

	/**
	 * Is logs pix
	 *
	 * @since 1.5.0
	 */
	public static function is_log_pix() {
		$options = get_option( 'woocommerce_pagaleve-pix_settings' );

		return ( 'yes' === $options['logs'] );
	}

	/**
	 * Is logs pix cash
	 *
	 * @since 1.5.0
	 */
	public static function is_log_cash() {
		$options = get_option( 'woocommerce_pagaleve-pix-cash_settings' );

		return ( 'yes' === $options['logs'] );
	}

	/**
	 * Get response checkout
	 *
	 * @since 1.3.6
	 */
	public static function response_checkout( $payment_id, $order_id ) {
		$order = wc_get_order($order_id);

		if (!$order) {
			return [];
		}

		$response_default = $order->get_meta('_pagaleve_checkout_response', true);
		$response_pix     = $order->get_meta("_pagaleve_checkout_{$payment_id}_response", true);

		return $response_default ? $response_default : $response_pix;
	}

	/**
	 * Is pix checkout type default
	 *
	 * @since 1.5.0
	 */
	public static function get_checkout_pix() {
		$options = get_option( 'woocommerce_pagaleve-pix_settings' );
		$type    = isset( $options['checkout_type'] ) ? $options['checkout_type'] : 'checkout_default';

		return $type;
	}

	/**
	 * Is pix cash checkout type default
	 *
	 * @since 1.5.0
	 */
	public static function get_checkout_cash() {
		$options = get_option( 'woocommerce_pagaleve-pix-cash_settings' );
		$type    = isset( $options['checkout_type'] ) ? $options['checkout_type'] : 'checkout_default';

		return $type;
	}

	/**
     * Get payment title
     *
     * @since  1.5.0
     * @access public
     */
	public static function get_payment_title( $payment_method ) {
		$pix      = get_option( 'woocommerce_pagaleve-pix_settings' );
		$pix_cash = get_option( 'woocommerce_pagaleve-pix-cash_settings' );

		switch ( $payment_method ) {
			case 'pagaleve-pix':
				return isset( $pix['title'] ) ? $pix['title'] : __( 'Pix 4x sem juros' );
				break;
			case 'pagaleve-pix-cash':
				return isset( $pix_cash['title'] ) ? $pix_cash['title'] : __( 'Pix' );
				break;
		}
	}

	/**
	 * Render the popup payment
	 *
	 * @since 1.6.0
	 */
	public static function render_payment_popup( $order_id, $args = [] )
	{
        wp_enqueue_script(
            'pagaleve-checkout',
            'https://transparent-checkout.pagaleve.com.br/pagaleve-transparent-checkout-installer.js',
            true
        );

		$order = wc_get_order($order_id);

		if (!$order) {
			return;
		}

		$response_checkout    = $order->get_meta('_pagaleve_checkout_response', true);
		$args['order_status'] = $order->get_status();

		WcPagaleveOrder::payment_paid( $order_id, $response_checkout );

		if (is_array($response_checkout) && !$order->get_meta('_pagaleve_transparente_checkout_access', true)) {
			if (isset($response_checkout['checkout_url'])) {
				$args['checkout_url'] = $response_checkout['checkout_url'] . '&t=pagaleve';
			}
		}

		if ( !$args ) {
			return;
		}

		self::template_include( 'templates/checkout/thank-you', compact( 'order_id', 'args' ) );
	}

	/**
	 * Request payment after checkout,
	 *
	 * @since 1.0.0
	 */
	public static function request_payment( $response_checkout, $order_id ) {

		$get_intent   = 'CAPTURE';
		$payment_data = Payment_Model::create_payment( $response_checkout, $order_id, $get_intent );

		$order_status = WcPagaleveOrder::get_order_success_status($order_id);
		$order_status = $order_status ? $order_status : 'processing';

		Payment_Model::create_request_payment( $payment_data, $order_id, $order_status );
	}

	public static function dir($dir = __DIR__, $level = 2)
    {
        return dirname( $dir, $level );
    }

	public static function folder( )
    {
        $dir = explode( "/", self::dir() );
        return $dir[ count( $dir ) - 1 ];
    }

	public static function assets($relative = "")
    {
        return plugins_url() . "/". self::folder() ."/assets/$relative";
    }

}
