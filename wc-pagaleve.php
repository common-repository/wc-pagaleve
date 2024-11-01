<?php
/**
 * Plugin Name: Pix 4x sem juros - Pagaleve
 * Description: A Pagaleve é uma fintech brasileira fundada em 2021 e que oferece aos varejistas a solução de Pix 4x sem juros. É uma forma de pagamento para dividir as compras em 4x sem juros pelo PIX. E melhor: não precisa de cartão de crédito. O cliente paga a primeira parcela no ato da compra e as três outras parcelas são pagas a cada 15 dias. É simples. É fácil. É leve.
 * Author: Apiki
 * Author URI: https://apiki.com/
 * Version: 1.6.6
 * Requires at least: 4.7
 * Requires PHP: 7.1
 * Tested up to: 6.6
 * License: GPL-2.0-only
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wc-pagaleve
 * Domain Path: /languages
 * Requires Plugins:  woocommerce, woocommerce-extra-checkout-fields-for-brazil
**/

use WcPagaleve\Controller\GateWays\Blocks\WcPagalevePix;
use WcPagaleve\Controller\GateWays\Blocks\WcPagalevePixInCash;
use WcPagaleve\Controller\OnBoarding\WcPagaleveOnboarding;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

require_once dirname( __FILE__ ) . '/constants.php';

function pagaleve_render_admin_notice_html( $message, $type = 'error' ) {
?>
	<div class="<?php echo esc_attr( $type ); ?> notice is-dismissible">
		<p>
			<strong><?php _e( 'Pagaleve for WooCommerce', 'wc-pagaleve' ); ?>: </strong>
			<?php echo esc_html( $message ); ?>
		</p>
	</div>
<?php
}


if ( version_compare( PHP_VERSION, '7.1', '<' ) ) {
	function pagaleve_admin_notice_php_version()
	{
		pagaleve_render_admin_notice_html(
			__( 'Sua versão no PHP não é suportada. Requerido >= 7.1', 'wc-pagaleve' )
		);
	}

	pagaleve_load_notice( 'admin_notice_php_version' );
	return;
}

function pagaleve_woo_notice_error() {
	pagaleve_render_admin_notice_html(
		__( 'WooCoomerce é obrigatório.', 'wc-pagaleve' )
	);
}

function pagaleve_ecfb_notice_error() {
	pagaleve_render_admin_notice_html(
		__( 'Brazilian Market on WooCommerce é obrigatório.', 'wc-pagaleve' )
	);
}

function pagaleve_load_notice( $name ) {
	add_action( 'admin_notices', "pagaleve_{$name}" );
}

function pagaleve_load_instances() {
	require_once __DIR__ . '/vendor/autoload.php';

    WcPagaleve\Core::instance();

	do_action( 'pagaleve_init' );
}

function pagaleve_plugins_loaded_check() {
	class_exists( 'WooCommerce' ) ? pagaleve_load_instances() : pagaleve_load_notice( 'woo_notice_error' );
	class_exists( 'Extra_Checkout_Fields_For_Brazil' ) ? pagaleve_load_instances() : pagaleve_load_notice( 'ecfb_notice_error' );
}

add_action( 'plugins_loaded', 'pagaleve_plugins_loaded_check', 0 );

function pagaleve_on_activation()  {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	add_option( PAGALEVE_OPTION_ACTIVATE, true );

	register_uninstall_hook( __FILE__, 'pagaleve_on_uninstall' );

    add_option('wc_pagaleve_do_activation_redirect', true);
}

add_action( 'admin_init', 'pagaleve_activation_redirect', 0 );
add_action( 'admin_init', 'generate_webhook_token', 0 );

add_action( 'wp_ajax_create_onboarding_url', 'ajax_create_onboarding_url', 999 );

function ajax_create_onboarding_url()
{
	$onboarding = new WcPagaleveOnboarding;
	$onboarding->generate_endpoint();

	return wp_send_json(
		[ 'content' => $onboarding->get_response() ],
		$onboarding->get_status()
	);
}

function generate_webhook_token()
{
	if (!get_option('wc_pagaleve_webhook_token', false)) {
		add_option('wc_pagaleve_webhook_token', hash( "sha256", get_site_url() . time()));
	}
}


function pagaleve_activation_redirect()
{
	if (get_option('wc_pagaleve_do_activation_redirect', false)) {
        delete_option('wc_pagaleve_do_activation_redirect');
        wp_redirect( get_site_url() . '/wp-admin/admin.php?page=wc-settings&tab=pagaleve-settings&wc-pagaleve-onboarding=true');
    }
}

function pagaleve_on_deactivation() {}
function pagaleve_on_uninstall() {}

register_activation_hook( __FILE__, 'pagaleve_on_activation' );
register_deactivation_hook( __FILE__, 'pagaleve_on_deactivation' );
