<?php
/**
 * Disposable observer exposes only counters, booleans and fixed home binding.
 *
 * @package Coagmentator
 */

// Load only the disposable WordPress installation.
require __DIR__ . '/wp-load.php';
if ( 'disposable' !== getenv( 'C01_TEST_ENVIRONMENT' ) ) {
	exit( 1 );
}
header( 'Content-Type: application/json' );
echo wp_json_encode(
	array(
		'identity' => get_option( 'c03a_observed', array() ),
		'sink'     => (int) get_option( 'c03b_sink', 0 ),
		'home'     => home_url(),
		'siteurl'  => site_url(),
		'prefix'   => Coagmentator\Guard\Guard::instance()->prefix(),
	)
);
