<?php
/**
 * Isolated legacy runner and real core bootstrap.
 *
 * @package Coagmentator
 */
if ( 'disposable' !== getenv( 'C01_TEST_ENVIRONMENT' ) ) {
	throw new RuntimeException( 'Disposable environment required.' );
}
$coagmentator_root = dirname( __DIR__, 2 );
define( 'WP_TESTS_CONFIG_FILE_PATH', $coagmentator_root . '/tests/environment/wp-tests-config.php' );
define( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH', $coagmentator_root . '/tools/wp-tests/vendor/yoast/phpunit-polyfills' );
require_once $coagmentator_root . '/.runtime/wordpress/tests/phpunit/includes/functions.php';
tests_add_filter(
	'muplugins_loaded',
	static function () use ( $coagmentator_root ): void {
		$GLOBALS['coagmentator_c01_loaded'] = require $coagmentator_root . '/wordpress/coagmentator/coagmentator.php';
	}
);
require $coagmentator_root . '/.runtime/wordpress/tests/phpunit/includes/bootstrap.php';
