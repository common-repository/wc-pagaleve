<?php
/**
 * Thank you page.
 *
 * @package WcPagaleve
 */
?>
<div class="woocommerce">
	<div id="pagaleve-thankyou">
		<div class="pagaleve-container">
			<fieldset class="pagaleve-pix-form wc-payment-form">
				<?php if ($args['order_status'] == "processing" || $args['order_status'] == "completed") : ?>
					<p><strong><?php _e( 'Seu pagamento foi realizado com sucesso na PAGALEVE.' ); ?></strong></p>
				<?php elseif ($args['order_status'] == "on-hold" || $args['order_status'] == "pending"): ?>
					<p><strong><?php _e( 'Seu pagamento está sendo processado pela PAGALEVE.' ); ?></strong></p>
				<?php else: ?>
					<p><strong><?php _e( 'Não foi possível realizar o seu pagamento na PAGALEVE.' ); ?></strong></p>
				<?php endif; ?>
			</fieldset>
		</div>
		<form id="receipt_form">
			<input type="hidden" id="admin-ajax" value="<?php echo admin_url( 'admin-ajax.php' ); ?>">
			<input type="hidden" name="order_id" id="pagaleve_order_id" value="<?php echo esc_attr( $order_id ); ?>" />
		</form>
	</div>
	<input type="hidden" id="pagaleve-checkout" value="<?php echo esc_url( isset($args['checkout_url']) ? $args['checkout_url'] : '')?>">
	<div id="pagaleve-transparent-checkout-root"></div>
</div>
