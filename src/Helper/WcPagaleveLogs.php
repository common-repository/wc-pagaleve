<?php
/**
 * Logs.
 *
 * @package WcPagaleve
 */

declare(strict_types=1);

namespace WcPagaleve\Helper;

use WC_Logger;

/**
 * Logs class.
 */
class WcPagaleveLogs {

	/**
	 * Order Response Log
	 *
	 * @since  1.0.0
	 * @access public
	 */
	public static function order_response( $name, $title, $var ) {
		$log = new WC_Logger();
		$log->add('pagaleve-response-'.$name, "{$title} : ".print_r( $var, true ) );
	}

	/**
	 * Order Response Error Log
	 *
	 * @since  1.0.0
	 * @access public
	 */
	public static function order_response_error( $name, $title, $var ) {
		$log = new WC_Logger();
		$log->add('pagaleve-response-'.$name.'-error-', "{$title} : ".print_r( $var, true ) );
	}

	/**
	 * Order Response Transparent Log
	 *
	 * @since  1.5.0
	 * @access public
	 */
	public static function order_response_transparent( $name, $title, $var ) {
		$log = new WC_Logger();
		$log->add('pagaleve-response-transparent-'.$name, "{$title} : ".print_r( $var, true ) );
	}

	/**
	 * Order Response Transparent Error Log
	 *
	 * @since  1.5.0
	 * @access public
	 */
	public static function order_response_transparent_error( $name, $title, $var ) {
		$log = new WC_Logger();
		$log->add('pagaleve-response-transparent-'.$name.'-error-', "{$title} : ".print_r( $var, true ) );
	}

	/**
	 * Pix Order Log
	 *
	 * @since  1.0.0
	 * @access public
	 */
	public static function get_pix_order( $title, $var ) {
		$log = new WC_Logger();
		$log->add('pagaleve-pix-order-', "{$title} : ".print_r( $var, true ) );
	}

	/**
	 * Pix Order Error Log
	 *
	 * @since  1.0.0
	 * @access public
	 */
	public static function get_pix_error( $title, $var ) {
		$log = new WC_Logger();
		$log->add('pagaleve-pix-order-error-', "{$title} : ".print_r( $var, true ) );
	}

	/**
	 * Token Log
	 *
	 * @since  1.0.0
	 * @access public
	 */
	public static function token_generate_error( $title, $var ) {
		$log = new WC_Logger();
		$log->add('pagaleve-token-generate-error-', "{$title} : ".print_r( $var, true ) );
	}

	/**
	 * Webhook Log
	 *
	 * @since  1.0.0
	 * @access public
	 */
	public static function webhook_log( $status, $message ) {
		$log = new WC_Logger();
		$log->add( 'pagaleve-webhook-log-', "{$status} : ".print_r( $message, true ) );
	}

	/**
	 * Refund Order Log
	 *
	 * @since  1.0.0
	 * @access public
	 */
	public static function refund_order( $title, $var ) {
		$log = new WC_Logger();
		$log->add('pagaleve-refund-order-', "{$title} : ".print_r( $var, true ) );
	}

	/**
	 * Refund Order Error Log
	 *
	 * @since  1.0.0
	 * @access public
	 */
	public static function refund_order_error( $title, $var ) {
		$log = new WC_Logger();
		$log->add('pagaleve-refund-order-error-', "{$title} : ".print_r( $var, true ) );
	}

	/**
	 * Cron Update Log
	 *
	 * @since  1.3.6
	 * @access public
	 */
	public static function cron_order_update( $title, $var ) {
		$log = new WC_Logger();
		$log->add('pagaleve-cron-order-', "{$title} : ".print_r( $var, true ) );
	}

	/**
	 * Automatic Onboarding Log
	 *
	 * @since  1.5.6
	 * @access public
	 */
	public static function automatic_onboarding( $title, $var ) {
		$log = new WC_Logger();
		$log->add('pagaleve-automatic-onboarding-', "{$title} : ".print_r( $var, true ) );
	}

    /**
     * Automatic Order Change Log
     *
     * @since  1.5.6
     * @access public
     */
    public static function automatic_order_change( $title, $var )
    {
        $log = new WC_Logger();
        $log->add('pagaleve-automatic-order-change-', "{$title} : ".print_r( $var, true ) );
    }
}
