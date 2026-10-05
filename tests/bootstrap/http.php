<?php
/**
 * Fixed disposable HTTP destination and trust scope.
 *
 * @package Coagmentator
 */

if ( 'disposable' !== getenv( 'C01_TEST_ENVIRONMENT' ) ) {
	throw new RuntimeException( 'Disposable environment required.' );
}
define( 'C01_CA', dirname( __DIR__, 2 ) . '/.runtime/tls/ca.crt' );
