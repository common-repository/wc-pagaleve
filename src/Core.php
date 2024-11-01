<?php
/**
 * WcPagaleve Core.
 *
 * @package Wc Pagaleve
 */
declare(strict_types=1);

namespace WcPagaleve;

use WcPagaleve\API\Routes;
use WcPagaleve\Helper\WcPagaleveUtils as Utils_Helper;

class Core
{
  private const ENVS = [
    'sandbox'    => 'https://stage-provider-public-assets.pagaleve.io/woocommerce/cart.woocommerce.pagaleve.js',
    'production' => 'https://provider-public-assets.pagaleve.com.br/woocommerce/cart.woocommerce.pagaleve.js'
  ];

	private static $_instance = null;

	const SLUG               = PAGALEVE_SLUG;
	const LOCALIZE_SCRIPT_ID = 'PPWAGlobalVars';

	private function __construct() {
		add_action( 'init', [ __CLASS__, 'load_textdomain' ] );
		add_action('rest_api_init', [__CLASS__, 'registerRestAPI']);

		self::initialize();
		self::admin_enqueue_scripts();
		self::front_enqueue_scripts();
	}

	public static function get_selected_environment() {
		return get_option( 'wc_pagaleve_settings_environment' );
	}

	public static function load_textdomain() {
		load_plugin_textdomain( self::SLUG, false, self::plugin_rel_path( 'languages' ) );
	}

	public static function initialize() {
		$controllers = [
			'WooCommerce\Checkout\WcPagaleveCheckout',
			'WooCommerce\Orders\WcPagaleveOrder',
			'WooCommerce\Orders\WcPagaleveOrderCancelled',
			'WooCommerce\Orders\WcPagaleveOrderRefunded',
			'WooCommerce\Settings\WcPagaleveSettings',
			'WooCommerce\Widgets\WcPagaleveWidgets',
			'WooCommerce\Webhooks\WcPagaleveOnboarding',
			'WooCommerce\Webhooks\WcPagaleveOrderChange'
		];

		self::load_controllers( $controllers );
	}

	public static function load_controllers( $controllers ) {
		foreach ( $controllers as $controller ) {
			$class = sprintf( __NAMESPACE__ . '\Controller\%s', $controller );
			new $class();
		}
	}

	public static function get_localize_script_args( $args = [] ) {
		$defaults = [
			'ajaxUrl' => Utils_Helper::get_admin_url( 'admin-ajax.php' ),
		];

		return array_merge( $defaults, $args );
	}

	public static function admin_enqueue_scripts() {
		add_action( 'admin_enqueue_scripts', [ __CLASS__, 'scripts_admin' ] );
	}

	public static function front_enqueue_scripts() {
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'scripts_front' ] );
	}

	public static function scripts_admin() {
		self::enqueue_scripts( 'admin' );
		self::enqueue_styles( 'admin' );
	}

	public static function scripts_front() {

		self::enqueue_scripts( 'front', ['pagaleve-checkout'] );
		if ( is_checkout() || is_order_received_page() ) {
      $environment = self::get_selected_environment();

      wp_enqueue_script(
        'pagaleve-checkout-cart-script',
        self::ENVS[$environment],
        [],
        false,
        true
      );
    }

		if ( !is_checkout() ) {
			self::add_pagaleve_script();

			return;
		}

		self::enqueue_styles( 'front' );
	}

	public static function enqueue_scripts( $type, $deps = [], $localize_args = [] ) {
		$id = "{$type}-script-" . self::SLUG;

		wp_enqueue_script(
			$id,
			self::plugins_url( "assets/javascripts/{$type}/built.js" ),
			array_merge( [ 'jquery' ], $deps ),
			self::filemtime( "assets/javascripts/{$type}/built.js" ),
			true
		);

		wp_localize_script(
			$id,
			self::LOCALIZE_SCRIPT_ID,
			self::get_localize_script_args( $localize_args )
		);
	}

	public static function enqueue_styles( $type ) {
		wp_enqueue_style(
			"{$type}-style-" . self::SLUG,
			self::plugins_url( "assets/stylesheets/{$type}/style.css" ),
			[],
			self::filemtime( "assets/stylesheets/{$type}/style.css" )
		);
	}

	/**
	 * Add pagaleve widget script.
	 *
	 * @since 1.5.0
	 * @return string
	 */
	public static function add_pagaleve_script() {
		if ( is_cart() || is_product() ) {
			wp_enqueue_script(
				'pagaleve-widget',
				'https://widget.pagaleve.com.br/pagaleve-widget-installer.js',
				[],
				false,
				true
			);
		}
	}

	public static function plugin_dir_path( $path = '' ) {
		return plugin_dir_path( PAGALEVE_ROOT_FILE ) . $path;
	}

	public static function plugin_rel_path( $path ) {
		return dirname( self::plugin_basename() ) . '/' . $path;
	}

	/**
	 * Plugin file root path
	 *
	 * @since 1.0
	 * @param String $file
	 * @return String
	 */
	public static function get_file_path( $file, $path = '' ) {
		return self::plugin_dir_path( $path ) . $file;
	}

	public static function plugins_url( $path ) {
		return esc_url( plugins_url( $path, PAGALEVE_ROOT_FILE ) );
	}

	public static function filemtime( $path ) {
		$file = self::plugin_dir_path( $path );

		return file_exists( $file ) ? filemtime( $file ) : PAGALEVE_VERSION;
	}

	public static function get_page_link() {
		return Utils_Helper::get_admin_url( 'plugins.php?plugin_status=all' );
	}

	/**
	 * Plugin base name
	 *
	 * @since 1.0
	 * @param String $filter
	 * @return String
	 */
	public static function plugin_basename( $filter = '' ) {
		return $filter . plugin_basename( PAGALEVE_ROOT_FILE );
	}

	public static function instance() {
		if ( is_null( self::$_instance ) ) :
			self::$_instance = new self;
		endif;
	}

	public static function registerRestAPI(): void
    {
        $routes = new Routes();
        $routes->register();
    }
}
