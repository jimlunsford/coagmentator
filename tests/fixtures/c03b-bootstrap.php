<?php
/**
 * Disposable host bootstrap, explicitly installed before wp-settings.php.
 * Never loaded by request input or distributed as an automatic proxy shim.
 *
 * @package Coagmentator
 */

if ( 'disposable' !== getenv( 'C01_TEST_ENVIRONMENT' ) ) {
	exit( 1 );
}
define( 'COAGMENTATOR_FEATURE_CONFIG', '/run/coagmentator/feature.json' );
$c03b_subdirectory = is_file( '/run/coagmentator/c03b-subdirectory' );
define( 'WP_HOME', 'https://wordpress.test' . ( $c03b_subdirectory ? '/journal' : '' ) );
define( 'WP_SITEURL', WP_HOME );
define( 'COAGMENTATOR_GUARD_REST_PREFIX', $c03b_subdirectory ? '/journal/wp-json' : '/wp-json' );
// Pure definitions only. The independent MU loader still boots its own guard.
require_once ABSPATH . 'wp-content/mu-plugins/coagmentator-guard/src/class-guard-config.php';
require_once ABSPATH . 'wp-content/mu-plugins/coagmentator-guard/src/class-route-boundary.php';
require_once ABSPATH . 'wp-content/plugins/coagmentator/src/foundation.php';
Coagmentator\Auth\Transport_Evidence::bootstrap( Coagmentator\Config\Feature_Config::load() );
unset( $c03b_subdirectory );
