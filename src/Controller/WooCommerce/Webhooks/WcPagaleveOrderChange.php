<?php

namespace WcPagaleve\Controller\WooCommerce\Webhooks;

use WcPagaleve\Helper\WcPagaleveLogs;
use WcPagaleve\Helper\WcPagaleveOrder;
use WC_Order;



/**
 * Handle Webhooks
 *
 * @package Services
 * @since 1.5.7
 */
class WcPagaleveOrderChange
{
    private $logger;

    public function __construct()
    {
        $this->logger = new WcPagaleveLogs;
        add_action('woocommerce_api_wc_pagaleve_order_change', [$this, 'callback']);
    }

    /**
     * Callback handler
     *
     * @since 1.5.7
     * @return void
     */
    public function callback()
    {
        $token = filter_input(INPUT_GET, 'token', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
        $body  = json_decode( file_get_contents('php://input'));

        $this->check_webhook_token( $token );
        $this->handle_order_change_return( $body );
    }

    /**
     * Check if webhook token is valid
     *
     * @since 1.5.7
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
     * @since 1.5.7
     * @param object $body
     * @return void
     */
    private function handle_order_change_return($body)
    {
        $data = ( array ) $body;
        $this->logger::automatic_order_change("==== PAGALEVE WEBHOOK ORDER CHANGE RECEIVED ====\n", $body);
        $this->validate_fields($data);
		$order = new WC_Order( $data['orderReference'] );

		if (!$order) {
			$this->return_invalid_order_error();
			return;
		}

        if ( !$data['state']
        || $data['state'] === 'NEW'
        || $data['state'] === 'ACCEPTED' ) {
            return;
        }

        $order_id = $this->remove_order_prefix($data['orderReference']);

        WcPagaleveOrder::update_order_by_id($data['state'], $order_id);

        if ($data['state'] == 'AUTHORIZED' || $data['state'] == 'COMPLETED') {
			$this->logger::automatic_order_change("==== PAGALEVE WEBHOOK ORDER CHANGE SUCCESSFUL ====\n", $data);
            return;
        }

		$this->logger::automatic_order_change("==== PAGALEVE WEBHOOK ORDER CHANGE FAILED ====\n", $data);
		$this->return_internal_error();
    }


    private function remove_order_prefix($reference)
    {
        $order_prefix = get_option('wc_pagaleve_settings_order_prefix') ?? '';

        return str_replace($order_prefix, '', $reference);
    }

    /**
     * Validade required fields
     *
     * @since 1.5.7
     * @param array $data
     * @return void
     */
    private function validate_fields($data = [])
    {
		if (empty($data['orderReference']) || empty($data['state'])) {
			$this->return_missing_parameter_error();
		}
    }
    /**
     * Retorna erro de falta de parametro
     *
     * @since 1.5.7
     * @return void
     */
    private function return_missing_parameter_error()
    {
        $this->return_error(422, "Webhook type is invalid. Missing parameters!");
    }

    /**
     * Retorna erro de flead inválido
     *
     * @since 1.5.7
     * @return void
     */
    private function return_invalid_order_error()
    {
        $this->return_error(422, "No orders linked to orderReference were found!");
    }

    /**
     * Retorna erro de autorização
     *
     * @since 1.5.7
     * @return void
     */
    private function return_unauthorized_access_error()
    {
        $this->return_error(401, "invalid access token!");
    }

	/**
	 * Retorna erros internos
	 *
	 * @since 1.5.7
	 * @return void
	 */
	private function return_internal_error()
	{
		$this->return_error(500, "Your request could not be completed, please try again or contact your administrator!");
	}

    /**
     * Retorna e loga os erros
     *
     * @since 1.5.7
     * @param int $code
     * @param string $message
     */
    private function return_error($code, $message)
    {
        $response = [
            "code"    => $code,
            "message" => $message
        ];

        $this->logger::automatic_order_change("==== PAGALEVE WEBHOOK ORDER CHANGE ERROR ====\n", $response);

        http_response_code($code);
        die(
            json_encode($response)
        );
    }
}
