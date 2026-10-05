<?php
/**
 * Test-only fixed-identity controller. It never reads content or returns success.
 *
 * @package Coagmentator
 */

namespace Coagmentator\Rest;

/**
 * Exercise an admitted outer callback whose sole result is a safe test failure.
 */
final class ReadController {
	/**
	 * Test fixture permission only, never production authorization.
	 *
	 * @return bool Test fixture permission only, never production authorization.
	 */
	public static function authorize_guard_request(): bool {
		return 'disposable' === getenv( 'C01_TEST_ENVIRONMENT' );
	}

	/**
	 * Instrument recursion from an actual core Application Password event.
	 *
	 * @param \WP_REST_Request $request Outer object.
	 * @return \WP_Error Always denies, never a real bridge operation.
	 * @throws \RuntimeException Deliberate exception cleanup probe.
	 */
	public static function site_info( \WP_REST_Request $request ): \WP_Error {
		update_option( 'c02_outer', (int) get_option( 'c02_outer', 0 ) + 1 );
		$checks = array();
		$server = rest_get_server();
		foreach ( array( $request, clone $request, new \WP_REST_Request( 'GET', '/c02/v1/target' ), new \WP_REST_Request( 'POST', '/batch/v1' ) ) as $nested ) {
			$checks[] = 403 === $server->dispatch( $nested )->get_status();
			$checks[] = 403 === rest_do_request( $nested )->get_status();
		}
		$checks[] = false === $server->serve_request( '/coagmentator/v1/site_info' );
		wp_set_current_user( 0 );
		$checks[] = 403 === rest_do_request( new \WP_REST_Request( 'GET', '/c02/v1/target' ) )->get_status();
		wp_set_current_user( (int) get_option( 'c02_human' ) );
		$checks[] = 403 === rest_do_request( new \WP_REST_Request( 'GET', '/c02/v1/target' ) )->get_status();
		update_option( 'c02_internal_checks', $checks );
		if ( 'exception' === $request->get_body() ) {
			throw new \RuntimeException( 'Disposable exception cleanup probe.' );
		}
		return new \WP_Error( 'c02_denied', 'Disposable guard test completed.', array( 'status' => 403 ) );
	}
}
add_action(
	'rest_api_init',
	static function (): void {
		register_rest_route(
			'coagmentator/v1',
			'/site_info',
			array(
				'methods'             => 'POST',
				'callback'            => array( ReadController::class, 'site_info' ),
				'permission_callback' => array( ReadController::class, 'authorize_guard_request' ),
			)
		);
	}
);
