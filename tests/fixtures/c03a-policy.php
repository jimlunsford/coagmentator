<?php
/**
 * Synthetic nonsecret rich policy builder. Never an installed configuration.
 *
 * @package Coagmentator
 */

/**
 * Closed, canonical field order is part of the host file format.
 *
 * @param int    $user Disposable identity.
 * @param string $uuid Disposable UUID, never the password.
 * @return array<string, mixed> Test configuration values.
 */
function c03a_policy( int $user, string $uuid ): array {
	return array(
		'version'            => 1,
		'guard_api'          => 1,
		'enabled'            => true,
		'site_id'            => 'f3858c09-8c56-48fd-99d1-ab75862bb955',
		'actor_id'           => 'operator-1',
		'service_user_id'    => $user,
		'protected_user_ids' => array( $user ),
		'credential_uuids'   => array( $uuid ),
		'rotation'           => null,
		'home_origin'        => 'https://wordpress.test',
		'home_path'          => '/',
		'bridge_origin'      => 'https://wordpress.test',
		'bridge_path'        => '/wp-json/coagmentator/v1',
		'private_reads'      => false,
		'read_operations'    => array( 'site_info' ),
		'policy_version'     => 1,
		'approval_profile'   => 'strict',
		'writes_enabled'     => false,
		'storage'            => array(
			'cursor_key_file'     => '/run/coagmentator-private/cursor.key',
			'audit_directory'     => '/run/coagmentator-private/audit',
			'admission_directory' => '/run/coagmentator-private/admission',
		),
	);
}
