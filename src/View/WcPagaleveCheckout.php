<?php
/**
 * View.
 *
 * @package WcPagaleve
 */

declare(strict_types=1);

namespace WcPagaleve\View;

class WcPagaleveCheckout {

	/**
	 * Render Iframe Checkout.
	 * @since 1.5.0
	 */
	public static function render_iframe_checkout() {
		ob_start();
		?>
		<?php

		echo ob_get_clean();
    }
}
