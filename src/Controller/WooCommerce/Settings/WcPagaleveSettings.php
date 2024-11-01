<?php
/**
 * WcPagaleve Settings.
 *
 * @package Wc Pagaleve
 */
declare(strict_types=1);

namespace WcPagaleve\Controller\WooCommerce\Settings;

use WcPagaleve\Controller\GateWays\{
    WcPagalevePix,
    WcPagalevePixInCash
};
use WcPagaleve\Controller\GateWays\Blocks\WcPagalevePix as BlocksWcPagalevePix;
use WcPagaleve\Controller\GateWays\Blocks\WcPagalevePixInCash as BlocksWcPagalevePixInCash;

class WcPagaleveSettings
{
	public function __construct() {
        add_filter( 'plugin_action_links_wc-pagaleve/wc-pagaleve.php', [ $this, 'pagaleve_plugin_links' ] );
        add_filter( 'plugin_row_meta', [ $this, 'pagaleve_support_links' ], 10, 2 );
        add_filter( 'woocommerce_settings_tabs_array', [ $this, 'pagaleve_add_settings_tab' ], 50 );
        add_action( 'woocommerce_settings_tabs_pagaleve-settings', [ $this, 'pagaleve_tab_content' ] );
        add_action( 'woocommerce_update_options_pagaleve-settings', [ $this, 'pagaleve_update_settings' ] );
        add_filter( 'woocommerce_payment_gateways', [ $this, 'pagaleve_gateway' ], 10, 1 );
        add_action('woocommerce_blocks_loaded', [$this, 'load_block_gateways'], 10);
    }

    /**
     * Add link settings page
     *
     * @since 1.0
     * @param Array $links
     * @return Array
     */
    public function pagaleve_plugin_links( $links ) {

        $default = [
            sprintf(
            '<a href="%s">%s</a>',
            'admin.php?page=wc-settings&tab=pagaleve-settings',
            __( 'Settings' )
            )
        ];

        $pix_cash = [
            sprintf(
            '<a href="%s">%s</a>',
            'admin.php?page=wc-settings&tab=checkout&section=pagaleve-pix-cash',
            __( 'Pix' )
            )
        ];

        $pix = [
            sprintf(
            '<a href="%s">%s</a>',
            'admin.php?page=wc-settings&tab=checkout&section=pagaleve-pix',
            __( 'Pix 4x sem juros' )
            )
        ];

        return array_merge( $default, $pix_cash, $pix, $links );
    }

    /**
     * Add support link page
     *
     * @since 1.0
     * @param Array $links
     * @return Array
     */
    public function pagaleve_support_links( $links, $name ) {

        if ( $name === 'wc-pagaleve/wc-pagaleve.php' ) {
            $links[] = '<a href="https://apiki.com/">'.__( 'Suporte' ).'</a>';
        }

        return $links;
    }

    /**
     * Add a settings tab to the settings WooCommerce
     *
     * @param array $settings_tabs
     *
     * @since  1.0.0
     * @access public
     *
     * @return array
     */
    public function pagaleve_add_settings_tab( $tabs ) {
        $tabs['pagaleve-settings'] = __( 'Pagaleve' );

        return $tabs;
    }

    /**
     * Output the tab content
     *
     * @since  1.0.0
     * @access public
     *
     */
    public function pagaleve_tab_content() {
        woocommerce_admin_fields( $this->pagaleve_get_fields() );
    }

    /**
     * Get the setting fields
     *
     * @since  1.0.0
     * @access private
     *
     * @return array $fields
     */
    public function pagaleve_get_fields() {

        $fields = [
            'section_title' => [
                'name' => __( 'Configuração Pagaleve' ),
                'type' => 'title',
                'desc' => '',
                'id'   => 'wc_pagaleve_settings_title'
            ],
            'cron' => [
                'name'    => __( 'Tempo da Cron' ),
                'type'    => 'select',
                'desc'    => __( 'Selecione o tempo da cron para atualizar os pedidos que estão com status aguardando.' ),
                'id'      => 'wc_pagaleve_settings_cron',
                'default' => 'five_minutes',
                'options'          => [
                    'five_minutes'    => __( '5 minutos' ),
                    'ten_minutes'     => __( '10 minutos' ),
                    'fifteen_minutes' => __( '15 minutos' ),
                ],
            ],
            'widget_product' => [
                'name'    => __( 'Produto Widget' ),
                'type'    => 'checkbox',
                'desc'    => __( 'Habilitar marketing widget na página de produto.' ),
                'id'      => 'wc_pagaleve_settings_product',
            ],
            'widget_cart' => [
                'name'    => __( 'Carrinho Widget' ),
                'type'    => 'checkbox',
                'desc'    => __( 'Habilitar marketing widget na página de carrinho.' ),
                'id'      => 'wc_pagaleve_settings_cart',
            ],
            'environment' => [
                'name'    => __( 'Ambiente' ),
                'type'    => 'select',
                'desc'    => __( 'Selecione o ambiente, homologação ou produção.' ),
                'id'      => 'wc_pagaleve_settings_environment',
                'options'          => [
                    'sandbox'    => __( 'Homologação' ),
                    'production' => __( 'Produção' ),
                ],
            ],
            'order_prefix' => [
                'name'              => __( 'Prefixo de pedido' ),
                'type'              => 'text',
                'desc'              => __( 'Define um prefixo para a referenciar os pedidos do WooCommerce na PagaLeve.' ),
                'id'                => 'wc_pagaleve_settings_order_prefix',
            ],
            'user' => [
                'name'              => __( 'Usuário' ),
                'type'              => 'text',
                'desc'              => __( 'Usuário de Produção gerado na minha conta PAGALEVE.' ),
                'id'                => 'wc_pagaleve_settings_user_production',
            ],
            'password' => [
                'name'              => __( 'Senha' ),
                'type'              => 'text',
                'desc'              => __( 'Senha de Produção gerado na minha conta PAGALEVE.' ),
                'id'                => 'wc_pagaleve_settings_password_production',
            ],
            'user_sandbox' => [
                'name'              => __( 'Usuário' ),
                'type'              => 'text',
                'desc'              => __( 'Usuário de Homologação gerado na minha conta PAGALEVE.' ),
                'id'                => 'wc_pagaleve_settings_user_sandbox',
            ],
            'password_sandbox' => [
                'name'              => __( 'Senha' ),
                'type'              => 'text',
                'desc'              => __( 'Senha de Homologação gerado na minha conta PAGALEVE.' ),
                'id'                => 'wc_pagaleve_settings_password_sandbox'
            ],
            'section_end' => [
                'type' => 'sectionend',
                'id'   => 'wc_pagaleve_settings_section_end'
            ],
        ];

        return apply_filters( 'wc_pagaleve_tab_settings', $fields );
    }

    /**
     * Update the settings
     *
     * @since  1.0.0
     * @access public
     */
    public function pagaleve_update_settings() {
        woocommerce_update_options( $this->pagaleve_get_fields() );
    }

    /**
	 * Register Gateway Pagaleve.
	 *
	 * @return void
	 */
	public function pagaleve_gateway( $gateways ) {
		if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
			return;
		}

		$gateways[] = WcPagalevePix::class;
        $gateways[] = WcPagalevePixInCash::class;

		return $gateways;
	}
    /**
     * Register block gateways classes
     * @since 1.2.4
     * @param Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry
     * @return void
     */
    public function register_block_gateway($payment_method_registry)
    {
        $payment_method_registry->register(new BlocksWcPagalevePix());
        $payment_method_registry->register(new BlocksWcPagalevePixInCash());
    }

    /**
     * Load block gateways classes
     * @since 1.2.4
     * @return void
     */
    public function load_block_gateways() {
        if (class_exists('Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType')) {
            add_action('woocommerce_blocks_payment_method_type_registration', [$this, 'register_block_gateway'], 10 , 1);
        }
    }
}
