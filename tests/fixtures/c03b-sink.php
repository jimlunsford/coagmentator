<?php
/**
 * Disposable redirect destination sentinel, with no returned request data.
 *
 * @package Coagmentator
 */

// Load only the disposable WordPress installation.
require __DIR__ . '/wp-load.php';
if ( 'disposable' !== getenv( 'C01_TEST_ENVIRONMENT' ) ) {
	exit( 1 );
}
update_option( 'c03b_sink', (int) get_option( 'c03b_sink', 0 ) + 1 );
http_response_code( 403 );
echo 'Disposable destination denied.';
