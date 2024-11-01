<?php
/**
 * WcPagaleve Constants.
 *
 * @package Wc Pagaleve
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 0 );
}

function pagaleve_define( $name, $value ) {
	if ( ! defined( $name ) ) {
		define( $name, $value );
	}
}

pagaleve_define( 'PAGALEVE_SLUG', 'wc-pagaleve' );
pagaleve_define( 'PAGALEVE_VERSION', '1.6.6' );
pagaleve_define( 'PAGALEVE_ROOT_PATH', dirname( __FILE__ ) . '/' );
pagaleve_define( 'PAGALEVE_ROOT_SRC', PAGALEVE_ROOT_PATH . 'src/' );
pagaleve_define( 'PAGALEVE_ROOT_FILE', PAGALEVE_ROOT_PATH . PAGALEVE_SLUG . '.php' );

pagaleve_define( 'PAGALEVE_OPTION_ACTIVATE', 'pagaleve_activate' );
