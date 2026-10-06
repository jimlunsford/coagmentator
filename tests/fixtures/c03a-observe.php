<?php
/**
 * Read-only disposable booleans and counts, never credentials or identity data.
 *
 * @package Coagmentator
 */

require __DIR__ . '/wp-load.php';
if ( 'disposable' !== getenv( 'C01_TEST_ENVIRONMENT' ) ) {
	exit( 1 );
}
header( 'Content-Type: application/json' );
echo wp_json_encode( get_option( 'c03a_observed', array() ) );
