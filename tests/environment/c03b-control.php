<?php
/**
 * Trusted CLI transport scenarios, reusing C02's preflight-issued credential.
 *
 * @package Coagmentator
 */

if ( 'cli' !== PHP_SAPI || 'disposable' !== getenv( 'C01_TEST_ENVIRONMENT' ) ) {
	exit( 1 );
}
$root = dirname( __DIR__, 2 );
require $root . '/.runtime/wordpress/src/wp-load.php';
require $root . '/tests/fixtures/c03a-policy.php';
$c03b_fixture = $root . '/.runtime/c02-fixtures.json';
$data         = json_decode( file_get_contents( $c03b_fixture ), true, 512, JSON_THROW_ON_ERROR );
$c03b_case    = $argv[1] ?? '';
$policy       = c03a_policy( $data['service'], $data['service_uuid'] );
$direct       = str_starts_with( $c03b_case, 'direct' ) || in_array( $c03b_case, array( 'invalid-certificate', 'wrong-certificate-host' ), true );
if ( ! $direct ) {
	$peer = gethostbyname( 'edge' );
	if ( false === filter_var( $peer, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {
		throw new RuntimeException( 'Disposable edge address unresolved.' );
	}
	$policy['transport'] = array(
		'mode'            => 'trusted_proxy',
		'trusted_proxies' => array( $peer ),
	);
}
$subdirectory = in_array( $c03b_case, array( 'subdirectory', 'subdirectory-alias' ), true );
if ( $subdirectory ) {
	$policy['home_path']   = '/journal/';
	$policy['bridge_path'] = '/journal/wp-json/coagmentator/v1';
	file_put_contents( $root . '/.runtime/guard-config/c03b-subdirectory', '' );
} elseif ( is_file( $root . '/.runtime/guard-config/c03b-subdirectory' ) ) {
	unlink( $root . '/.runtime/guard-config/c03b-subdirectory' );
}
if ( 'wrong-configured-host' === $c03b_case ) {
	$policy['home_origin']   = 'https://other.test';
	$policy['bridge_origin'] = 'https://other.test';
} elseif ( 'wrong-configured-path' === $c03b_case ) {
	$policy['home_path']   = '/other/';
	$policy['bridge_path'] = '/other/wp-json/coagmentator/v1';
}
file_put_contents( $root . '/.runtime/guard-config/feature.json', json_encode( $policy, JSON_UNESCAPED_SLASHES ) );
file_put_contents(
	$root . '/.runtime/guard-config/registry.json',
	json_encode(
		array(
			'version'            => 1,
			'protected_user_ids' => array( $data['service'] ),
			'credential_uuids'   => array( $data['service_uuid'] ),
		)
	)
);
update_option( 'active_plugins', array( 'coagmentator/coagmentator.php' ) );
update_option( 'c03a_case', 'approved' );
update_option( 'c03b_case', $c03b_case );
update_option( 'c03a_observed', array() );
update_option( 'c03b_sink', 0 );
$data['c03b_scenario'] = $c03b_case;
file_put_contents( $c03b_fixture, json_encode( $data ) );
echo "C03B fixed host transport scenario configured; no credential issued.\n";
