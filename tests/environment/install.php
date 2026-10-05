<?php
/**
 * Disposable CLI-only WordPress setup, without a service user.
 *
 * @package Coagmentator
 */

if ( 'cli' !== PHP_SAPI || 'disposable' !== getenv( 'C01_TEST_ENVIRONMENT' ) ) {
	exit( 1 );
}
define( 'WP_INSTALLING', true );
require dirname( __DIR__, 2 ) . '/.runtime/wordpress/src/wp-load.php';
add_filter( 'pre_wp_mail', '__return_true' );
require_once ABSPATH . 'wp-admin/includes/upgrade.php';
wp_install( 'C01 fixture', 'c01_control_admin', 'fixture@example.test', false, '', bin2hex( random_bytes( 32 ) ) );
update_option( 'active_plugins', array( 'coagmentator/coagmentator.php' ) );
echo "Disposable WordPress installed; no service identity or Application Password.\n";
