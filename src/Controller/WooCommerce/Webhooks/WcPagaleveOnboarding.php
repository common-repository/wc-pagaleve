<?php

namespace WcPagaleve\Controller\WooCommerce\Webhooks;

use WcPagaleve\Controller\GateWays\WcPagalevePix;
use WcPagaleve\Controller\GateWays\WcPagalevePixInCash;
use WcPagaleve\Helper\WcPagaleveLogs;

/**
 * Handle Webhooks
 * 
 * @package Services
 * @since 1.0.0
 */
class WcPagaleveOnboarding
{
    private $logger;
    
    public function __construct()
    {
        $this->logger = new WcPagaleveLogs;
        add_action('woocommerce_api_wc_pagaleve_onboarding', [$this, 'callback']);
    }

    /**
     * Callback handler
     * 
     * @since 1.5.6
     * @return void
     */
    public function callback()
    {
        $token = filter_input(INPUT_GET, 'token', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
        $body  = json_decode( file_get_contents('php://input'));
    
        $this->check_webhook_token( $token );
        $this->handle_onboarding_return( $body );
    }

    /**
     * Check if webhook token is valid
     * 
     * @since 1.5.6
     * @param string $token
     * @return bool|void
     */
    private function check_webhook_token($token)
    {
        if ( ! $token || get_option('wc_pagaleve_webhook_token', true) !== $token) {
            $this->return_unauthorized_access_error();
        }
    }

    /**
     * Handle body received
     * 
     * @since 1.5.6
     * @param object $body
     * @return void
     */
    private function handle_onboarding_return($body)
    {
        $data = ( array ) $body;

        $this->logger::automatic_onboarding("==== PAGALEVE WEBHOOK ONBOARDING RECEIVED ====\n", $body);
        
        $this->validate_fields($data);
        $this->check_onboarding_lead($data);

        $this->activate_payment_methods(
            $data['is_pix_upfront_enabled'],
            $data['is_pix_installment_enabled']
        );

        $this->save_pagaleve_account_data(
            $data['username'],
            $data['password'],
            $data['env']
        );
        
        $this->logger::automatic_onboarding("==== PAGALEVE WEBHOOK ONBOARDING SUCCESSFUL ====\n", $data);
    }

    /**
     * Validade required fields
     * 
     * @since 1.5.6
     * @param array $data
     * @return void
     */
    private function validate_fields($data = [])
    {
        $needed = [
            'lead_id',
            'env',
            'username',
            'password',
            'is_pix_upfront_enabled',
            'is_pix_installment_enabled'
        ];

        foreach ($needed as $field) {
            if (!isset($data[$field])) {
                return $this->return_missing_parameter_error();
            }
        }
    }

    /**
     * Check if onboarding lead is valid
     * 
     * @since 1.5.6
     * @param string $lead_id
     * @return void
     */
    private function check_onboarding_lead($data)
    {
        $option = get_option('wc_pagaleve_onboarding_lead_id', false);
        delete_option('wc_pagaleve_onboarding_lead_id');
        
        if ($data['lead_id'] !== $option) {
            $this->logger::automatic_onboarding("==== PAGALEVE WEBHOOK ONBOARDING INVALID LEAD ====\n", [
                'saved_lead' => $option
            ]);
            $this->return_invalid_lead_error();
        }
    }

    /**
     * Save pagaleve option on database
     * 
     * @since 1.5.6
     * @param string $username
     * @param string $password
     * @param string $enviroment
     * @return void
     */
    private function save_pagaleve_account_data($username, $password, $enviroment)
    {
        $options = [];
        $options['wc_pagaleve_settings_environment'] = $enviroment === 'prod' ? 'production' : 'sandbox';

        if($enviroment === 'prod') {
            $options['wc_pagaleve_settings_user_production'] = $username;
            $options['wc_pagaleve_settings_password_production'] = $password;

        } else {
            $options['wc_pagaleve_settings_user_sandbox'] = $username;
            $options['wc_pagaleve_settings_password_sandbox'] = $password;
        }

        foreach($options as $option => $value) {
            update_option($option, $value);
        }

    }

    /**
     * Activate payement methods
     * 
     * @since 1.5.6
     * @param bool $pix_upfront
     * @param bool $pix_installments
     * @return void
     */
    private function activate_payment_methods($pix_upfront, $pix_installments)
    {
        if ( $pix_upfront ) {
            $gateway = new WcPagalevePixInCash;
            $gateway->update_option('enabled', 'yes');
        }

        if ( $pix_installments ) {
            $gateway = new WcPagalevePix;
            $gateway->update_option('enabled', 'yes');
        }
    }

    /**
     * Retorna erro de falta de parametro
     * 
     * @since 1.5.6
     * @return void
     */
    private function return_missing_parameter_error()
    {
        $this->return_error(422, "Webhook type is invalid. Missing parameters!");
    }

    /**
     * Retorna erro de flead inválido
     * 
     * @since 1.5.6
     * @return void
     */
    private function return_invalid_lead_error()
    {
        $this->return_error(422, "Lead ID is invalid!");
    }

    /**
     * Retorna erro de tipo de webhook
     * 
     * @since 1.5.6
     * @return void
     */
    private function return_invalid_type_error()
    {
        $this->return_error(403, "Webhook type is invalid!");
    }

    /**
     * Retorna erro de autorização
     * 
     * @since 1.5.6
     * @return void
     */
    private function return_unauthorized_access_error()
    {
        $this->return_error(401, "invalid access token!");
    }

    /**
     * Retorna e loga os erros
     * 
     * @since 1.5.6
     * @param int $code
     * @param string $message
     */
    private function return_error($code, $message)
    {
        $response = [
            "code"    => $code, 
            "message" => $message 
        ];

        $this->logger::automatic_onboarding("==== PAGALEVE WEBHOOK ONBOARDING ERROR ====\n", $response);

        http_response_code($code);
        die(
            json_encode($response)
        );
    }
}