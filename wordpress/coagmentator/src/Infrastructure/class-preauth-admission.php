<?php
/**
 * Explicit early host bootstrap, before WordPress credential authentication.
 *
 * @package Coagmentator
 */

namespace Coagmentator\Infrastructure;

use Coagmentator\Config\Feature_Config;

/** Adds denial only. Missing bootstrap cannot authorize a protected operation. */
final class Preauth_Admission {
	/**
	 * Request-local accounting evidence.
	 *
	 * @var bool Request-local accounting evidence.
	 */
	private static bool $passed = false;

	/**
	 * Host installs before wp-settings.php, using only fixed host configuration.
	 * Failed pre-auth admission terminates before authentication/detail disclosure.
	 *
	 * @param Feature_Config|null $config Closed operator policy.
	 * @throws Operational_Failure Internally caught; never escapes to the caller.
	 */
	public static function bootstrap( ?Feature_Config $config ): void {
		if ( 'cli' === PHP_SAPI ) {
			return;
		}
		$prefix = defined( 'COAGMENTATOR_GUARD_REST_PREFIX' ) && is_string( COAGMENTATOR_GUARD_REST_PREFIX ) ? COAGMENTATOR_GUARD_REST_PREFIX : '/wp-json';
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- Broad raw ingress classification adds only denials; C02 still owns canonical dispatch.
		$uri = $_SERVER['REQUEST_URI'] ?? '';
		if ( ! is_string( $uri ) || ! str_starts_with( $uri, $prefix . '/coagmentator' ) ) {
			return;
		}
		try {
			if ( self::$passed || null === $config ) {
				throw new Operational_Failure();
			}
			$store = new Local_Store( $config->storage( 'admission_directory' ), $config->excluded_roots() );
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- Reads only C03B's canonical immediate REMOTE_ADDR, never forwarding headers.
			( new Rate_Admission( $store, new Server_Clock() ) )->preauth( $_SERVER );
			self::$passed = true;
		} catch ( \Throwable $failure ) {
			http_response_code( 429 );
			header( 'Content-Type: application/json; charset=UTF-8' );
			header( 'Cache-Control: no-store' );
			header( 'Retry-After: 60' );
			// Fixed ingress failure only; full normalized envelopes belong to C05.
			exit( '{"ok":false}' );
		}
	}

	/**
	 * Successful accounting supplies no authentication or capability authority.
	 *
	 * @return bool Current-request preauth evidence.
	 */
	public static function passed(): bool {
		return self::$passed;
	}
}
