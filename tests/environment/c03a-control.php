<?php
/**
 * Trusted local C03A scenarios after the unchanged C02 guard preflight.
 *
 * @package Coagmentator
 */

if ( 'cli' !== PHP_SAPI || 'disposable' !== getenv( 'C01_TEST_ENVIRONMENT' ) ) {
	exit( 1 );
}
$root = dirname( __DIR__, 2 );
require $root . '/.runtime/wordpress/src/wp-load.php';
require $root . '/tests/fixtures/c03a-policy.php';
$c03a_fixture = $root . '/.runtime/c02-fixtures.json';
$data         = json_decode( file_get_contents( $c03a_fixture ), true, 512, JSON_THROW_ON_ERROR );
$c03a_case    = $argv[1] ?? '';
if ( 'setup' === $c03a_case ) {
	// C02 must already have established the independent guard and issued its
	// ephemeral credential. This is not an alternate provisioning preflight.
	if ( ! rest_get_server() instanceof Coagmentator\Guard\Guarded_REST_Server || ! Coagmentator\Guard\Guard::instance()->protected_id( $data['service'] ) || null === WP_Application_Passwords::get_user_application_password( $data['service'], $data['service_uuid'] ) ) {
		throw new RuntimeException( 'Accepted guard fixture is required.' );
	}
	$credential = WP_Application_Passwords::create_new_application_password( $data['service'], array( 'name' => 'Disposable C03A overlap', 'app_id' => $data['service_uuid'] ) );
	if ( is_wp_error( $credential ) ) {
		throw new RuntimeException( 'Disposable overlap issuance failed.' );
	}
	$data['rotation_secret'] = $credential[0];
	$data['rotation_uuid']   = $credential[1]['uuid'];
	unset( $credential );
	file_put_contents( $c03a_fixture, json_encode( $data ) );
	echo "C03A disposable overlap credential issued after accepted guard preflight.\n";
	exit;
}
$policy   = c03a_policy( $data['service'], $data['service_uuid'] );
$registry = array( 'version' => 1, 'protected_user_ids' => array( $data['service'] ), 'credential_uuids' => array( $data['service_uuid'], $data['rotation_uuid'] ) );
update_option( 'active_plugins', array( 'coagmentator/coagmentator.php' ) );
update_user_meta( $data['service'], 'coagmentator_service', '1' );
( new WP_User( $data['service'] ) )->set_role( 'subscriber' );
( new WP_User( $data['human'] ) )->set_role( 'administrator' );
if ( 'wrong-service' === $c03a_case ) {
	$policy['service_user_id']        = $data['human'];
	$policy['protected_user_ids']     = array( $data['human'] );
	$registry['protected_user_ids'][] = $data['human'];
} elseif ( 'wrong-uuid' === $c03a_case ) {
	$policy['credential_uuids'] = array( $data['rotation_uuid'] );
} elseif ( 'disabled' === $c03a_case ) {
	$policy['enabled'] = false;
} elseif ( 'overlap' === $c03a_case || 'expired-overlap' === $c03a_case ) {
	$policy['credential_uuids'] = array( $data['service_uuid'], $data['rotation_uuid'] );
	$policy['rotation']         = array( 'started_at' => time() - 60, 'expires_at' => time() + ( 'overlap' === $c03a_case ? 600 : -1 ) );
} elseif ( 'promoted-unmarked' === $c03a_case ) {
	delete_user_meta( $data['service'], 'coagmentator_service' );
	( new WP_User( $data['service'] ) )->set_role( 'administrator' );
} elseif ( 'role-only' === $c03a_case ) {
	add_role( 'coagmentator_service', 'Disposable named role', array( 'read' => true ) );
	( new WP_User( $data['human'] ) )->set_role( 'coagmentator_service' );
} elseif ( 'wrong-user' === $c03a_case ) {
	( new WP_User( $data['human'] ) )->set_role( 'subscriber' );
} elseif ( 'missing-evidence' === $c03a_case ) {
	update_option( 'active_plugins', array() );
} elseif ( 'revoke-after-event' === $c03a_case || 'revoked' === $c03a_case ) {
	$policy['credential_uuids'] = array( $data['rotation_uuid'] );
}
file_put_contents( $root . '/.runtime/guard-config/registry.json', json_encode( $registry ) );
file_put_contents( $root . '/.runtime/guard-config/policy.json', '{"version":1,"guard_api":1}' );
file_put_contents( $root . '/.runtime/guard-config/feature.json', json_encode( $policy, JSON_UNESCAPED_SLASHES ) );
$data['c03a_scenario'] = $c03a_case;
file_put_contents( $c03a_fixture, json_encode( $data ) );
update_option( 'c03a_case', $c03a_case );
update_option( 'c03a_observed', array() );
echo "C03A nonsecret identity scenario configured.\n";
