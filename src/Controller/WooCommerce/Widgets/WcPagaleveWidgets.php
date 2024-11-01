<?php
/**
 * WcPagaleve Settings.
 *
 * @package Wc Pagaleve
 */
declare(strict_types=1);

namespace WcPagaleve\Controller\WooCommerce\Widgets;

use WcPagaleve\Helper\WcPagaleveUtils as Utils_Helper;

class WcPagaleveWidgets
{
    public function __construct() {
        add_action( 'woocommerce_before_add_to_cart_form', [$this, 'pagaleve_add_product_banner'] );
        add_action( 'woocommerce_cart_totals_after_order_total', [$this, 'pagaleve_add_cart_banner'] );
    }
    
    /**
     * Add widget product page
     *
     * @since 1.3.0
     * @param Array $links
     * @return Array
     */
    public function pagaleve_add_product_banner() {
        $is_banner = Utils_Helper::is_product_widget();

        if ( !$is_banner ) {
            return;
        }

        $product_id = get_the_ID();
        $product    = wc_get_product( $product_id );
        $price      = Utils_Helper::format_price( $product->get_price() );

        Utils_Helper::template_include( 'templates/widgets/product', compact( 'price' ) );
    }

    /**
     * Add widget cart page
     *
     * @since 1.3.0
     * @param Array $links
     * @return Array
     */
    public function pagaleve_add_cart_banner() {
        $is_banner = Utils_Helper::is_cart_widget();

        if ( !$is_banner ) {
            return;
        }

        $price = Utils_Helper::str_to_integer( WC()->cart->total );

        Utils_Helper::template_include( 'templates/widgets/cart', compact( 'price' ) );
    }
}