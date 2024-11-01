<?php
/**
 * WcPagaleve Order Cancelled.
 *
 * @package Wc Pagaleve
 */

declare(strict_types=1);

namespace WcPagaleve\Controller\OnBoarding\Graphql;

/**
 * Pagaleve Automatic Onboarding.
 */
class WcPagaleveOnboardingQuery
{
    private $query;

	/**
	 * {@inheritDoc}
	 */
	public function __construct()
    {
		$this->build_query();
	}

    /**
     * Get query proprety
     * 
     * @since 1.5.6
     * @return string
     */
    public function get_query() {
        return $this->query;
    }

    /**
     * Build create onboarding query
     * 
     * @since 1.5.6
     * @return void
     */
    private function build_query()
	{
        $business_name          = $this->get_business_name();
        $business_email         = $this->get_business_email();
        $provider               = 'woocommerce';
        $provider_creation_date = date("Y-m-d",strtotime("-15 day"));
        $redirect_url           = get_site_url() . '/wp-admin/admin.php?page=wc-settings&tab=pagaleve-settings';
        $webhook_url            = $this->get_webhook_url();

		$this->query = 'mutation createLeadMutation {createLead(input: {business_corporate_name: "'. $business_name .'", email: "' .$business_email .'", provider: "'. $provider .'", provider_creation_date: "'. $provider_creation_date .'", redirect_url: "'. $redirect_url .'", webhook_url:"'. $webhook_url .'"}) {lead_id onboarding_url}}';
	}

    /**
     * Get webhook url
     * 
     * @since 1.5.6
     * @return string
     */
    private function get_webhook_url() {
        if (!get_option( 'permalink_structure' )) {
            return get_site_url() . '/?wc-api=wc_pagaleve_onboarding&token=' . get_option('wc_pagaleve_webhook_token');
        }

        return get_site_url() . '/wc-api/wc_pagaleve_onboarding?token=' . get_option('wc_pagaleve_webhook_token');
    }

    /**
     * Get blog name
     * 
     * @since 1.5.6
     * @return string
     */
    private function get_business_name()
    {
        return get_option('blogname');
    }

    /**
     * Get admin email
     * 
     * @since 1.5.6
     * @return string
     */
    private function get_business_email()
    {
        return get_option('admin_email');
    }
}
