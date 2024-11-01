<?php
/**
 * WcPagaleve Order Cancelled.
 *
 * @package Wc Pagaleve
 */

declare(strict_types=1);

namespace WcPagaleve\Controller\OnBoarding;

use WcPagaleve\Controller\OnBoarding\Graphql\WcPagaleveOnboardingQuery;
use WcPagaleve\Controller\OnBoarding\Auth\WcPagaleveOnboardingToken;
use WcPagaleve\Helper\WcPagaleveLogs;

/**
 * Pagaleve Automatic Onboarding.
 */
class WcPagaleveOnboarding
{
	private $response;
	private $status;
	private $logger;

	private static $endpoint = 'https://onboarding-api.pagaleve.com.br/graphql';

	/**
	 * {@inheritDoc}
	 */
	public function __construct()
    {
		$this->logger = new WcPagaleveLogs;
	}

	/**
	 * Get response data
	 * 
	 * @since 1.5.6
	 * @return array
	 */
	public function get_response()
	{
		return $this->response;
	}

	/**
	 * Get response status
	 * 
	 * @since 1.5.6
	 * @return int
	 */
	public function get_status()
	{
		return $this->status;
	}

	/**
	 * Generate onboarding endpoint
	 * 
	 * @since 1.5.6
	 * @return void
	 */
	public function generate_endpoint()
	{
		$onboarding_token = new WcPagaleveOnboardingToken();
		$auth = $onboarding_token->get_token();

		$graphql = new WcPagaleveOnboardingQuery();

		$data = [
			'query'			=> $graphql->get_query(),
			'variables' 	=> null,
			'operationName' => "createLeadMutation"
		];

		$this->send_onboarding_request($data, $auth);
	}

	/**
	 * Send request to PagaLeve API
	 * 
	 * @since 1.5.6
	 * @param array $data
	 * @param string $token
	 * @return void
	 */
	private function send_onboarding_request($data, $token)
	{
		$this->logger::automatic_onboarding("==== PAGALEVE CREATING ONBOARDING REQUEST ====\n", $data);

		$ch = curl_init();
        curl_setopt_array(
			$ch,
			[
				CURLOPT_URL            => self::$endpoint,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST  => 'POST',
				CURLOPT_POSTFIELDS     => json_encode($data),
                CURLOPT_HTTPHEADER     => [
                    "Authorization: $token",
                    'Content-Type: application/json'
                ],
			]
		);

		$response = curl_exec($ch);
		$this->status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
		curl_close($ch);

        $response = json_decode($response);

		$this->logger::automatic_onboarding("==== PAGALEVE CREATING ONBOARDING RESPONSE ====\n", $response);

		if ($this->status == 200 && is_object($response)) {
			if (isset($response->data->createLead)) {
				$this->response = $response->data->createLead;

				add_option('wc_pagaleve_onboarding_lead_id', $this->response->lead_id);
			}
		} else {
			$this->response = [
				'message' => 'Desculpe! Algo deu errado.'
			];
		}
	}
}
