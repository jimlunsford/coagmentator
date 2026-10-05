<?php
/**
 * Disposable competing REST implementation, never a runtime package file.
 *
 * @package Coagmentator
 */

if ( 'disposable' !== getenv( 'C01_TEST_ENVIRONMENT' ) ) {
	exit( 1 );
}

/**
 * Inherit ordinary WordPress REST behavior without guard-owned dispatch scope.
 */
final class C02_Custom_REST_Server extends WP_REST_Server {
}

add_filter(
	'wp_rest_server_class',
	static function (): string {
		return C02_Custom_REST_Server::class;
	}
);
