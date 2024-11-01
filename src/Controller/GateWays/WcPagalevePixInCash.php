<?php
/**
 * WcPagaleve Pix In Cash Gateway.
 *
 * @package Wc Pagaleve
 */

declare(strict_types=1);

namespace WcPagaleve\Controller\GateWays;

use WcPagaleve\Core;

use WcPagaleve\Helper\{
	WcPagaleveLogs as Logs_Helper,
	WcPagaleveUtils as Utils_Helper,
};

use WcPagaleve\Model\{
	WcPagaleveApi as Api_Model,
	WcPagaleveCheckout as Checkout_Model,
};

class WcPagalevePixInCash extends Abstract_Payment {

	public function __construct() {
		$this->services_api = new Api_Model();

		$this->id                 = 'pagaleve-pix-cash';
		$this->icon               = Core::plugins_url( 'assets/images/icons/logo-pix.png' );
		$this->has_fields         = true;
		$this->method_title       = __( 'Pagaleve' );
		$this->method_description = __( 'Metódo de pagamento PAGALEVE Pix.' );
		$this->supports           = [ 'products' ];

		$this->init_form_fields();
		$this->init_settings();

		$this->title         = $this->get_option( 'title' );
		$this->enabled       = $this->get_option( 'enabled' );
		$this->order_status  = $this->get_option( 'order_status' );
		$this->instructions  = $this->get_option( 'instructions' );
		$this->checkout_type = $this->get_option( 'checkout_type' );
		$this->sandbox       = 'sandbox' === get_option( 'wc_pagaleve_settings_environment' );
		$this->logs          = 'yes' === $this->get_option( 'logs' );

		$this->environment_name = $this->services_api->get_selected_environment();
		$this->user             = $this->services_api->user;
		$this->password         = $this->services_api->password;

		if ( is_admin() ) {
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, [ $this, 'process_admin_options' ] );
		}

		$this->init_actions();
	}

	/**
	 * Plugin init form,
	 * @since 1.0.0
	 */
	public function init_form_fields() {
		if ( ! $this->services_api->check_api_credentials() ) {
			return;
		}

		$this->form_fields = [
			'enabled' => [
				'title'       => __( 'Habilitar' ),
				'label'       => __( 'Habilitar Pagaleve' ),
				'type'        => 'checkbox',
				'description' => '',
				'default'     => 'no'
			],
			'title' => [
				'title'       => __( 'Título' ),
				'type'        => 'text',
				'description' => __( 'Controla o título que o usuário vê durante o checkout.' ),
				'default'     => 'Pix',
				'desc_tip'    => true
			],
			'checkout_type' => [
				'title'       => __( 'Tipo de checkout' ),
				'type'        => 'select',
				'description' => __( 'Selecione um tipo de checkout, checkout padrão ou checkout transparente.' ),
				'desc_tip'    => true,
				'default'     => 'checkout_default',
				'options'     => [
					'checkout_default'     => __( 'Checkout Padrão' ),
					'checkout_transparent' => __( 'Checkout Transparente' )
				]
			],
			'order_status' => [
				'title'       => __( 'Status do pedido' ),
				'type'        => 'select',
				'description' => __( 'Insira um prefixo para os números dos seus pedidos. Se você usar sua conta Pagaleve para várias lojas, certifique-se de que este prefixo seja único.' ),
				'desc_tip'    => true,
				'default'     => 'processing',
				'options'     => [
					'processing' => __( 'Processando' ),
					'completed'  => __( 'Concluído' )
				]
			],
			'logs' => [
				'title'       =>  __( 'Logs' ),
				'label'       => __( 'Habilita os logs.' ),
				'type'        => 'checkbox',
				'description' => __( 'Logs: pagaleve-pix-order ou pagaleve-pix-order-error. Para visualizar: WooCommerce > Status > Logs' ),
				'desc_tip'    => true,
				'default'     => 'yes'
			],
		];
	}

	public function validate_fields() {
		return true;
	}

	public function payment_fields() {
		$environment = '';

		if ( $this->sandbox ) {
			$environment = '<div class="wc-pagaleve-env">'.__( 'MODO DE TESTE HABILITADO!' ).'</div>';
		}

		printf('<div class="wc-%s-container" style="background:transparent;">
				<div class="clear"></div>%s
				<img id="wc-pagaleve-cash-background" src="%s" alt="" />
			</div>',
			esc_attr( $this->id ),
			$environment,
			esc_url( 'https://assets.pagaleve.com.br/checkout/upfront.png' )
		);
	}
}
