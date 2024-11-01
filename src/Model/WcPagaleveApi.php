<?php
/**
 * WcPagaleve Api.
 *
 * @package Wc Pagaleve
 */

declare(strict_types=1);

namespace WcPagaleve\Model;

use WcPagaleve\Helper\WcPagaleveLogs as Logs_Helper;

use WP_Error;

class WcPagaleveApi {

	private const ENVS = [
		'sandbox'    => 'https://sandbox-api.pagaleve.io',
		'production' => 'https://api.pagaleve.com.br'
	];

	public function __construct() {
		$this->environment_name = self::get_selected_environment();
		$this->user             = get_option( "wc_pagaleve_settings_user_{$this->environment_name}" );
		$this->password         = get_option( "wc_pagaleve_settings_password_{$this->environment_name}" );
	}

	/**
	 * Get selected environment.
	 *
	 * @return string
	 */
	public static function get_environment_url() {
		$environment = self::get_selected_environment();

		return self::ENVS[$environment];
	}

	/**
	 * Get selected environment.
	 *
	 * @return string
	 */
	public static function get_selected_environment() {
		return get_option( 'wc_pagaleve_settings_environment' );
	}

	public function check_api_credentials() {
		$hasAllCredentials = true;

		if ( !$this->user ) {
			$hasAllCredentials = false;
		}

		if ( !$this->password ) {
			$hasAllCredentials = false;
		}

		return $hasAllCredentials;
	}

	public function build_admin_message_api_credentials_if_empty() {
		if ( $this->check_api_credentials() ) {
			return;
		}

		return sprintf(
			'<h3 style="color:red">%s</h3>
			<a href="/wp-admin/admin.php?page=wc-settings&tab=pagaleve-settings">%s</a>',
			__( 'Não há credenciais Pagaleve definidas para este ambiente!' ),
			__( 'Clique aqui para configurar suas chaves!' )
		);
	}

	public function send_request_post( $data, $access_token, $type ) {
		$header          = $this->get_header( $access_token );
		$environment_url = self::get_environment_url();

		if ( !$header ) {
			return new WP_Error(
				'400',
				__( 'Erro ao processar o cabeçalho da requisição', 'wc_pagaleve' )
			);
		}

		return wp_remote_post(
			$environment_url.'/v1/'.$type,
			[
				'body'        => ( $data ) ? wp_json_encode( $data ) : '',
				'headers'     => $header,
				'method'      => 'POST',
				'data_format' => 'body'
			]
		);
	}

	public function get_checkout( $access_token, $id ) {
		$header          = $this->get_header( $access_token );
		$environment_url = self::get_environment_url();

		if ( !$header ) {
			return new WP_Error(
				'400',
				__( 'Erro ao processar o cabeçalho da requisição', 'wc_pagaleve' )
			);
		}

		return wp_remote_get(
			$environment_url.'/v1/checkouts/'.$id,
			[
				'headers' => $header,
				'method'  => 'GET'
			]
		);
	}

	/**
	 * Get auth token.
	 *
	 * @param string $user $password.
	 * @since 1.0.0
	 * @return string
	 */
	public static function get_auth_token( $user, $password ): string {
        $url  = self::get_environment_url().'/v1/authentication';
		$body = [
			'username' => $user,
			'password' => $password
		];

		$response = wp_remote_post(
            $url,
            [
				'headers' => [ "content-type" => "application/json" ],
                'body'    => json_encode( $body )
        	]
		);

		$response_body     = wp_remote_retrieve_body( $response );
		$response          = json_decode( $response_body, true );
		$status_code_token = isset( $response['statusCode'] ) ? $response['statusCode'] : 200;
		$token             = isset( $response['token'] ) ? $response['token'] : '';

		if ( $status_code_token !== 200 && !$token ) {
			Logs_Helper::token_generate_error( 'PAGALEVE: não foi possível gerar o token de autorização', $response['message'] );
		}

        return $token;
    }

	/**
	 * Get Header.
	 *
	 * @param string $access_token Access token.
	 * @return array
	 */
	protected function get_header( $access_token = '' ) {
		if ( !$access_token ) {
			$access_token = self::get_auth_token( $this->user, $this->password );
		}

		if ( !$access_token ) {
			return [];
		}

		return [
			'Content-Type'    => 'application/json; charset=utf-8',
			'Authorization'   => 'Bearer ' . $access_token,
			'Idempotency-Key' => md5( uniqid( "" ) )
		];
	}
}
