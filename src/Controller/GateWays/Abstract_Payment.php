<?php
/**
 * WcPagaleve Abstract Subscription.
 *
 * @package WcPagaleve
 */
declare(strict_types=1);

namespace WcPagaleve\Controller\GateWays;

use WC_Payment_Gateway;

use WcPagaleve\Model\{
	WcPagaleveApi as Api_Model,
	WcPagaleveCheckout as Checkout_Model,
};

use WcPagaleve\Helper\{
	WcPagaleveUtils as Utils_Helper,
	WcPagaleveOrder as Order_Helper,
	WcPagaleveLogs as Logs_Helper,
};

/**
 * Abstract Payment
 */
abstract class Abstract_Payment extends WC_Payment_Gateway {

	/**
	 * Init actions.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function init_actions() {
		$this->service_api = new Api_Model( $this->user, $this->password, $this->is_sandbox() );

		add_action( 'woocommerce_thankyou_' . $this->id, [ $this, 'thank_you_page' ] );
	}

	/**
	 * Plugin admin options,
	 *
	 * @since 1.0.0
	 */
	public function admin_options() {
		$environment = __( 'MODO DE PRODUÇÃO HABILITADO!' );

		if ( $this->sandbox ) {
			$environment = __( 'MODO DE TESTE HABILITADO!' );
		}

		printf(
			'<h3>%1$s</h3>
			<p>%2$s</p>
			%3$s
			<h4>%4$s</h4>',
			$this->method_title,
			wp_sprintf( __( 'Metódo de pagamento %s' ), $this->method_title ),
			$this->services_api->build_admin_message_api_credentials_if_empty(),
			$this->services_api->check_api_credentials() ? $environment : ''
		);
		?>
			<table class="form-table">
				<?php $this->generate_settings_html(); ?>
			</table>
		<?php

		$this->services_api->build_admin_message_api_credentials_if_empty();
	}

	/**
	 * Is sandbox,
	 *
	 * @since 1.0.0
	 */
	public function is_sandbox(): bool {
		return $this->sandbox;
	}

	/**
	 * Update status by order,
	 *
	 * @since 1.0.0
	 */
	protected function update_order_by_status( $wc_order, $status, $details = [] ) {
		$order_controller = new Order_Helper( $wc_order );

		switch ( $status ) {
			case 'APPROVED':
				$order_controller->payment_paid( $wc_order->get_id() );
				break;
			case 'NOT APPROVED':
				$order_controller->payment_on_hold( wc_clean( $details['description'] ) );
				break;
			default:
				$order_controller->payment_on_hold( wc_clean( $details['description'] ) );
				break;
		}
	}

	public function process_payment( $order_id ) {
		$is_pix					= $this->id === 'pagaleve-pix-cash';
		$access_token           = '';
		$order                  = wc_get_order( $order_id );
		$filtered_url			= apply_filters( 'pagaleve_redirect_success_order', $order_id);
		$order_url				= $this->get_return_url( $order );
		$return_url             = filter_var($filtered_url, FILTER_VALIDATE_URL) ? $filtered_url : $order_url;

		$checkout_data          = Checkout_Model::create_checkout( $order, $return_url, $is_pix );
		$checkout_response      = $this->services_api->send_request_post( $checkout_data, $access_token, 'checkouts' );
		$response_body_checkout = wp_remote_retrieve_body( $checkout_response );
		$response_checkout      = json_decode( $response_body_checkout, true );
		$cart_content           = WC()->cart->get_cart_contents();
		$cart_coupon            = WC()->cart->get_applied_coupons();

		$order->update_meta_data( "_pagaleve_cart_content_{$order_id}", $cart_content );
		$order->update_meta_data( "_pagaleve_cart_coupon_{$order_id}", $cart_coupon );

		$status_code_checkout = isset( $response_checkout['statusCode'] ) ? $response_checkout['statusCode'] : 200;

		if (is_wp_error($checkout_response ) || $status_code_checkout !== 200) {
     		wc_add_notice( __( 'Pagaleve: Não foi possível gerar o pedido!', 'wc-pagaleve' ), 'error' );
			if ( $this->logs ) {
				Logs_Helper::order_response_error( 'checkout', 'PAGALEVE RESPONSE ERROR CHECKOUT', $checkout_response );
			}

			return [
				'result'   => 'error',
				'redirect' => wc_get_checkout_url()
			];
		}

		if ( $status_code_checkout === 200 ) {
			if ( $this->logs ) {
				Logs_Helper::order_response( 'checkout', 'PAGALEVE ORDER ID', $order_id );
				Logs_Helper::order_response( 'checkout', 'PAGALEVE RESPONSE CHECKOUT', $response_checkout );
			}
		} else {
			if ( $this->logs ) {
				Logs_Helper::order_response_error( 'checkout', 'PAGALEVE ORDER ID', $order_id );
				Logs_Helper::order_response_error( 'checkout', 'PAGALEVE RESPONSE ERROR CHECKOUT', $response_checkout );
			}
		}

		$checkout_url = isset( $response_checkout['checkout_url'] ) ? $response_checkout['checkout_url'] : $order_url;
		$redirect_url = $this->checkout_type === 'checkout_transparent' ? $order_url : $checkout_url;

		$order->update_meta_data( '_pagaleve_checkout_response', $response_checkout );

		$order->update_status( 'on-hold', __( 'Pagaleve: Aguardando pagamento.' ) );

        wc_reduce_stock_levels( $order_id );

		WC()->cart->empty_cart();

		Utils_Helper::pagaleve_set_cookie( 'pagaleveOrderID', (string) $order_id );

		return [
			'result'   => 'success',
			'redirect' => $redirect_url
		];
	}

	/**
	 * Thank You Page
	 *
	 * @since 1.0.0
	 */
	public function thank_you_page($order_id)
	{
		Utils_Helper::render_payment_popup($order_id);
	}
}
