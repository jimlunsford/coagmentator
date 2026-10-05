<?php
/**
 * Read-only disposable assertion probe. Never distributed with the MU package.
 *
 * @package Coagmentator
 */

// Load only the disposable WordPress runtime.
require __DIR__ . '/wp-load.php';
if ( 'disposable' !== getenv( 'C01_TEST_ENVIRONMENT' ) ) {
	exit( 1 );
}
global $wpdb;
// Direct fixed-table reads are only a disposable no-side-effect assertion.
// phpcs:disable WordPress.DB.DirectDatabaseQuery
$editorial = array();
foreach ( array( $wpdb->posts, $wpdb->postmeta, $wpdb->terms, $wpdb->term_taxonomy, $wpdb->term_relationships ) as $table ) {
	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Core-owned fixed table identifiers in isolated test snapshot.
	$editorial[] = $wpdb->get_results( "SELECT * FROM $table", ARRAY_A );
}
$users = array();
foreach ( get_users() as $user ) {
	$credentials = array();
	foreach ( WP_Application_Passwords::get_user_application_passwords( $user->ID ) as $item ) {
		$credentials[] = array( $item['uuid'], $item['password'] );
	}
	$users[] = array( $user->data, $user->roles, $credentials );
}
header( 'Content-Type: application/json' );
echo wp_json_encode(
	array(
		'editorial' => hash( 'sha256', wp_json_encode( $editorial ) ),
		'users'     => hash( 'sha256', wp_json_encode( $users ) ),
		'targets'   => (int) get_option( 'c02_targets', 0 ),
		'outer'     => (int) get_option( 'c02_outer', 0 ),
		'checks'    => get_option( 'c02_internal_checks', array() ),
		'nonce'     => current_user_can( 'manage_options' ) ? wp_create_nonce( 'wp_rest' ) : '',
		'guard'     => rest_get_server() instanceof Coagmentator\Guard\Guarded_REST_Server,
	)
);
