<?php
/**
 * Test-only edge probe, never packaged or deployed.
 *
 * @package Coagmentator
 */

require __DIR__ . '/wp-load.php';
header( 'Content-Type: application/json' );
echo wp_json_encode(
	array(
		'wordpress'               => '7.1.2' === $GLOBALS['wp_version'],
		'database'                => false !== get_option( 'siteurl' ),
		'authorization_preserved' => isset( $_SERVER['HTTP_AUTHORIZATION'] ) && 'C01 noncredential-sentinel' === $_SERVER['HTTP_AUTHORIZATION'],
		// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Bounded empty disposable fixture inventory.
		'service_users_absent'    => array() === get_users( array( 'meta_key' => 'coagmentator_service' ) ),
		// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Bounded empty disposable fixture inventory.
		'credentials_absent'      => array() === get_users( array( 'meta_key' => '_application_passwords' ) ),
	)
);
