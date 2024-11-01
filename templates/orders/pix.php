<?php
/**
 * Pix partial order
 *
 * @package WcPagaleve
 */
?>
<div class="clear"></div>
<div class="order_data_column_container">
	<div class="order_data_column_wide">
		<h3><?php _e( 'Pagaleve' ); ?></h3>
		<div id="wc-pagaleve-order-container">
			<p>
				<?php if ( $checkout_id ) : ?>
					<strong><?php _e( 'Checkout ID: ' ); ?></strong>
					<span style="color:red;"><?php echo esc_attr( $checkout_id ); ?></span><br>
				<?php endif; ?>
				
				<?php if ( $payment_id ) : ?>
					<strong><?php _e( 'Payment ID: ' ); ?></strong>
					<span style="color:red;"><?php echo esc_attr( $payment_id ); ?></span><br>
				<?php endif; ?>
			</p>
		</div>
	</div>
</div>
