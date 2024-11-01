<?php

namespace WcPagaleve\Controller\GateWays\Blocks;

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;
use WcPagaleve\Helper\WcPagaleveUtils;

final class WcPagalevePix extends AbstractPaymentMethodType
{

    /**
     * The gateway instance.
     * @var WKO\Controllers\Gateways\Billet
     */
    private $gateway;

    /**
     * Payment method name/id/slug.
     * @var string
     */
    protected $name = 'pagaleve-pix';

    /**
     * Initializes the payment method type.
     */
    public function initialize() {
        $this->settings = get_option( "woocommerce_{$this->name}_settings", [] );
        
        $gateways       = WC()->payment_gateways->payment_gateways();
        $this->gateway  = isset($gateways[$this->name]) ? $gateways[$this->name] : null;
    }

    /**
     * Returns if this payment method should be active. If false, the scripts will not be enqueued.
     * @return boolean
     */
    public function is_active() {
        return is_null($this->gateway) ? false : $this->gateway->is_available();
    }

    /**
     * Returns an array of scripts/handles to be registered for this payment method.
     * @return array
     */
    public function get_payment_method_script_handles() {
        $script_asset_path = WcPagaleveUtils::assets("dist/blocks/{$this->name}.assets.php");
        $script_asset      = file_exists($script_asset_path)
        ? require_once $script_asset_path
        : array(
            'dependencies' => [],
            'version'      => '1.6.4'
        );

        wp_register_script(
            $this->name,
            WcPagaleveUtils::assets("dist/blocks/{$this->name}.js"),
            $script_asset[ 'dependencies' ],
			$script_asset[ 'version' ],
            true
        );
        
        wp_enqueue_style($this->name, WcPagaleveUtils::assets("dist/blocks/{$this->name}.css"));
        
        return [ $this->name ];
    }

    /**
     * Returns an array of key=>value pairs of data made available to the payment methods script.
     * @return array
     */
    public function get_payment_method_data() {
        return [
            'title'       => $this->get_setting( 'title' ),
            'description' => $this->get_setting( 'description' ),
            'enviroment'  => get_option( 'wc_pagaleve_settings_environment' )
        ];
    }
}
