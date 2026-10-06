<?php
/**
 * Test-only fixed callback; observes identity and always returns a safe failure.
 *
 * @package Coagmentator
 */

namespace Coagmentator\Rest;

/** Never distributed as runtime ReadController and never returns content. */
final class ReadController {
	/**
	 * Exercise actual normal-plugin identity instead of an unconditional grant.
	 *
	 * @return bool Identity foundation outcome in the disposable fixture only.
	 */
	public static function authorize_guard_request(): bool {
		return 'disposable' === getenv( 'C01_TEST_ENVIRONMENT' ) && c03a_check();
	}

	/**
	 * No bridge read. A sentinel proves where identity permitted execution.
	 *
	 * @return \WP_Error Always a closed guard failure.
	 */
	public static function site_info(): \WP_Error {
		++$GLOBALS['c03a_observed']['outer'];
		$evidence = \Coagmentator\Auth\Authentication_Evidence::current( '/coagmentator/v1/site_info' );
		if ( null !== $evidence ) {
			try {
				serialize( $evidence );
			} catch ( \LogicException $failure ) {
				$GLOBALS['c03a_observed']['serialization_denied'] = true;
			}
		}
		\Coagmentator\Guard\Guard::instance()->finish();
		$GLOBALS['c03a_observed']['stale_denied'] = ! c03a_check();
		return new \WP_Error( 'c03a_denied', 'Disposable identity test completed.', array( 'status' => 403 ) );
	}
}
add_action(
	'rest_api_init',
	static function (): void {
		register_rest_route( 'coagmentator/v1', '/site_info', array( 'methods' => 'POST', 'callback' => array( ReadController::class, 'site_info' ), 'permission_callback' => array( ReadController::class, 'authorize_guard_request' ) ) );
	}
);
