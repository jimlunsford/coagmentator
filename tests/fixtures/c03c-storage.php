<?php
/**
 * Trusted local test setup only, never loaded from a runtime route.
 *
 * @package Coagmentator
 */

use Coagmentator\Config\Feature_Config;
use Coagmentator\Guard\Guard_Config;

require_once __DIR__ . '/c03a-policy.php';

/**
 * Provision disposable directories and fixed files before HTTP exists.
 *
 * @param string $root Isolated test root chosen by local setup.
 * @throws RuntimeException Existing state or failed setup.
 */
function c03c_storage( string $root ): void {
	foreach ( array( $root, $root . '/admission', $root . '/audit' ) as $directory ) {
		if ( ! is_dir( $directory ) && ! mkdir( $directory, 0700 ) ) {
			throw new RuntimeException( 'Disposable setup failed.' );
		}
	}
	foreach ( array(
		'admission/control.lock' => '',
		'admission/clock'        => "0\n",
		'admission/slot-0'       => '',
		'admission/slot-1'       => '',
		'admission/slot-2'       => '',
		'admission/slot-3'       => '',
		'audit/control.lock'     => '',
		'audit/clock'            => "0\n",
		'audit/events'           => '',
		'audit/state'            => Coagmentator\Infrastructure\Read_Audit::fingerprint( '' ),
	) as $name => $bytes ) {
		if ( file_exists( $root . '/' . $name ) ) {
			throw new RuntimeException( 'Setup refuses existing operational state.' );
		}
		file_put_contents( $root . '/' . $name, $bytes );
		chmod( $root . '/' . $name, 0600 );
	}
	foreach ( array( 'admission', 'audit' ) as $component ) {
		$store = new Coagmentator\Infrastructure\Local_Store( $root . '/' . $component, array( dirname( __DIR__, 2 ) ) );
		$names = 'admission' === $component ? array( 'control.lock', 'slot-0', 'slot-1', 'slot-2', 'slot-3' ) : array( 'control.lock' );
		foreach ( $names as $name ) {
			$first = $store->lock( $name );
			if ( null === $first ) {
				throw new RuntimeException( 'Disposable lock probe failed.' );
			}
			try {
				$second = $store->lock( $name );
				if ( null !== $second ) {
					fclose( $second );
					throw new RuntimeException( 'Incompatible local lock semantics.' );
				}
			} finally {
				fclose( $first );
			}
		}
	}
}

/**
 * Unit/process-only policy, no credential or WordPress authentication claim.
 *
 * @param string $root Disposable root.
 * @return Feature_Config Validated synthetic configuration.
 * @throws RuntimeException Invalid synthetic policy.
 */
function c03c_config( string $root ): Feature_Config {
	$uuid     = '123e4567-e89b-42d3-a456-426614174000';
	$registry = $root . '/registry';
	if ( ! is_file( $registry ) ) {
		file_put_contents(
			$registry,
			json_encode(
				array(
					'version'            => 1,
					'protected_user_ids' => array( 7 ),
					'credential_uuids'   => array( $uuid ),
				)
			)
		);
		chmod( $registry, 0600 );
	}
	$policy                                   = c03a_policy( 7, $uuid );
	$policy['storage']['admission_directory'] = $root . '/admission';
	$policy['storage']['audit_directory']     = $root . '/audit';
	$config                                   = Feature_Config::parse( json_encode( $policy, JSON_UNESCAPED_SLASHES ), new Guard_Config( $registry ), 1800000000, array( dirname( __DIR__, 2 ) ) );
	if ( null === $config ) {
		throw new RuntimeException( 'Synthetic policy failed.' );
	}
	return $config;
}
