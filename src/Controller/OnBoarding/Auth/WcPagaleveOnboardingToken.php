<?php
/**
 * WcPagaleve Order Cancelled.
 *
 * @package Wc Pagaleve
 */

declare(strict_types=1);

namespace WcPagaleve\Controller\OnBoarding\Auth;


/**
 * Pagaleve Onboarding.
 */
class WcPagaleveOnboardingToken
{
	private $token;
	private $token_type;
	private $token_expires_in;

	private static $clientId = 'ebs0hgi991m14fqqa7i14dpff';
	private static $clientSecret = '1sj7836vke30dv6sbf2o1f10p31gia8ctgpoth54vlinmtoqjd6u';
	private static $endpoint = 'https://onboarding-auth.pagaleve.com.br/oauth2/token';
	/**
	 * {@inheritDoc}
	 */
	public function __construct()
    {
        $this->token = '';
        $this->token_type = '';
        $this->token_expires_in = 0;

		$this->generate_auth_token();
	}

	/**
	 * Get created token
	 * 
	 * @since 1.5.6
	 * @return string
	 */
	public function get_token()
	{
        return $this->token;
	}

	/**
	 * Get token type
	 * 
	 * @since 1.5.6
	 * @return string
	 */
    public function get_token_type()
	{
        return $this->token_type;
	}

	/**
	 * Get token expiration
	 * 
	 * @since 1.5.6
	 * @return string
	 */
    public function get_token_expires_in()
	{
        return $this->token_expires_in;
	}

	/**
	 * Generate authentication token
	 * 
	 * @since 1.5.6
	 * @return void
	 */
	private function generate_auth_token()
	{
		$arguments = self::$clientId . ":" . self::$clientSecret;
		$basic = base64_encode($arguments);

		$ch = curl_init();
        curl_setopt_array(
			$ch,
			[
				CURLOPT_URL            => self::$endpoint,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST  => 'POST',
				CURLOPT_POSTFIELDS     => http_build_query([
                    'grant_type' => 'client_credentials'
                ]),
                CURLOPT_HTTPHEADER     => [
                    "Authorization: Basic $basic",
                    'Content-Type: application/x-www-form-urlencoded'
                ],
			]
		);

		$response = curl_exec($ch);
		$status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
		curl_close($ch);

        $response = json_decode($response);
        
		if ($status == 200 && is_object($response)) {
            $this->token = $response->access_token;
            $this->token_type = $response->token_type;
            $this->token_expires_in = $response->expires_in;
		}
	}
}
