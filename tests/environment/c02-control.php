<?php
/**
 * Trusted local disposable provisioning, scenario selection and revocation.
 *
 * @package Coagmentator
 */

if ( 'cli' !== PHP_SAPI || 'disposable' !== getenv( 'C01_TEST_ENVIRONMENT' ) ) {
	exit( 1 );
}
$root = dirname( __DIR__, 2 );
require $root . '/.runtime/wordpress/src/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/user.php';
$mode = $argv[1] ?? '';
$path = $root . '/.runtime/c02-fixtures.json';
$reg  = $root . '/.runtime/guard-config/registry.json';
if ( 'setup' === $mode ) {
	$service_password = bin2hex( random_bytes( 48 ) );
	$service          = wp_insert_user( array( 'user_login' => 'c02_service', 'user_pass' => $service_password, 'role' => 'subscriber' ) );
	unset( $service_password );
	if ( ! is_int( $service ) ) {
		throw new RuntimeException( 'Disposable service fixture creation failed.' );
	}
	update_user_meta( $service, 'coagmentator_service', '1' );
	$human_password = bin2hex( random_bytes( 32 ) );
	$human          = wp_insert_user( array( 'user_login' => 'c02_human', 'user_pass' => $human_password, 'role' => 'administrator' ) );
	update_option( 'c02_service', $service );
	update_option( 'c02_human', $human );
	update_option( 'permalink_structure', '/%postname%/' );
	flush_rewrite_rules( false );
	$data = array( 'service' => $service, 'human' => $human, 'human_password' => $human_password );
	foreach ( array( 'service', 'human' ) as $kind ) {
		$id = $data[ $kind ];
		$data[ $kind . '_cookie' ] = SECURE_AUTH_COOKIE . '=' . wp_generate_auth_cookie( $id, time() + 3600, 'secure_auth' ) . '; ' . LOGGED_IN_COOKIE . '=' . wp_generate_auth_cookie( $id, time() + 3600, 'logged_in' );
	}
	file_put_contents( $reg, json_encode( array( 'version' => 1, 'protected_user_ids' => array( $service ), 'credential_uuids' => array() ) ) );
	file_put_contents( $path, json_encode( $data ) );
	chmod( $path, 0600 );
	chown( $path, fileowner( $root ) );
	echo "Disposable identities created with no Application Passwords; ordinary service password discarded.\n";
} elseif ( 'preflight' === $mode ) {
	$data = json_decode( file_get_contents( $path ), true, 512, JSON_THROW_ON_ERROR );
	if ( ! rest_get_server() instanceof Coagmentator\Guard\Guarded_REST_Server || ! Coagmentator\Guard\Guard::instance()->protected_id( $data['service'] ) ) {
		throw new RuntimeException( 'Guard preflight failed; credential issuance prohibited.' );
	}
	foreach ( get_users() as $user ) {
		if ( array() !== WP_Application_Passwords::get_user_application_passwords( $user->ID ) ) {
			throw new RuntimeException( 'Credential exists before preflight.' );
		}
	}
	wp_set_current_user( $data['service'] );
	if ( 403 !== rest_do_request( new WP_REST_Request( 'GET', '/c02/v1/target' ) )->get_status() || get_option( 'c02_targets', 0 ) ) {
		throw new RuntimeException( 'Guard no-callback preflight failed.' );
	}
	// Only this successful preflight can issue ephemeral credentials.
	foreach ( array( 'service', 'human' ) as $kind ) {
		$credential = WP_Application_Passwords::create_new_application_password( $data[ $kind ], array( 'name' => 'Disposable C02 test' ) );
		if ( is_wp_error( $credential ) ) {
			throw new RuntimeException( 'Disposable credential issuance failed.' );
		}
		$data[ $kind . '_secret' ] = $credential[0];
		$data[ $kind . '_uuid' ]   = $credential[1]['uuid'];
		unset( $credential );
	}
	file_put_contents( $path, json_encode( $data ) );
	echo "Guard preflight passed with zero credentials and zero target callbacks; ephemeral test credentials issued.\n";
} elseif ( 'scenario' === $mode ) {
	$data     = json_decode( file_get_contents( $path ), true, 512, JSON_THROW_ON_ERROR );
	$scenario = $argv[2];
	$registry = json_encode( array( 'version' => 1, 'protected_user_ids' => array( $data['service'] ), 'credential_uuids' => array( $data['service_uuid'] ) ) );
	if ( is_file( $reg ) ) {
		chmod( $reg, 0644 );
	}
	file_put_contents( $reg, $registry );
	file_put_contents( $root . '/.runtime/guard-config/policy.json', '{"version":1,"guard_api":1}' );
	update_option( 'active_plugins', array() );
	update_user_meta( $data['service'], 'coagmentator_service', '1' );
	( new WP_User( $data['service'] ) )->set_role( 'subscriber' );
	if ( str_starts_with( $scenario, 'missing-registry' ) ) {
		unlink( $reg );
	} elseif ( str_starts_with( $scenario, 'malformed-registry' ) ) {
		file_put_contents( $reg, '{"version":"1","protected_user_ids":[],"credential_uuids":[]}' );
	} elseif ( 'unreadable-registry' === $scenario ) {
		chmod( $reg, 0000 );
	} elseif ( 'bad-policy' === $scenario ) {
		file_put_contents( $root . '/.runtime/guard-config/policy.json', 'corrupt' );
	} elseif ( 'missing-policy' === $scenario ) {
		unlink( $root . '/.runtime/guard-config/policy.json' );
	} elseif ( 'promoted-unmarked' === $scenario ) {
		delete_user_meta( $data['service'], 'coagmentator_service' );
		( new WP_User( $data['service'] ) )->set_role( 'administrator' );
	} elseif ( 'active' === $scenario ) {
		update_option( 'active_plugins', array( 'coagmentator/coagmentator.php' ) );
	}
	if ( str_ends_with( $scenario, '-active' ) ) {
		update_option( 'active_plugins', array( 'coagmentator/coagmentator.php' ) );
	}
	update_option( 'c02_targets', 0 );
	update_option( 'c02_outer', 0 );
	update_option( 'c02_internal_checks', array() );
	$data['scenario'] = $scenario;
	file_put_contents( $path, json_encode( $data ) );
	echo "Disposable scenario configured.\n";
} elseif ( 'cleanup' === $mode && is_file( $path ) ) {
	$data = json_decode( file_get_contents( $path ), true, 512, JSON_THROW_ON_ERROR );
	foreach ( array( 'service', 'human' ) as $kind ) {
		WP_Application_Passwords::delete_all_application_passwords( $data[ $kind ] );
		if ( array() !== WP_Application_Passwords::get_user_application_passwords( $data[ $kind ] ) ) {
			throw new RuntimeException( 'Disposable credential revocation failed.' );
		}
		wp_delete_user( $data[ $kind ] );
	}
	unlink( $path );
	echo "C02 credentials revoked, revocation verified, disposable identities and secret file removed.\n";
}
