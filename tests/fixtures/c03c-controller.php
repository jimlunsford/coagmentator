<?php
/**
 * Disposable authenticated operational probe, always a failure without site data.
 *
 * @package Coagmentator
 */

namespace Coagmentator\Rest;

use Coagmentator\Config\Feature_Config;
use Coagmentator\Infrastructure\Operational_Failure;
use Coagmentator\Infrastructure\Read_Deadline;
use Coagmentator\Infrastructure\Read_Operation;

/** Copied only into the isolated test installation's fixed callback location. */
final class ReadController {
	/**
	 * Retain the real C03A/C03B conjuncts at the accepted guard boundary.
	 *
	 * @return bool Test-only identity result.
	 */
	public static function authorize_guard_request(): bool {
		return 'disposable' === getenv( 'C01_TEST_ENVIRONMENT' ) && c03a_check();
	}

	/**
	 * No content read, capability policy or successful bridge response.
	 *
	 * @return \WP_Error Always a failure.
	 */
	public static function site_info(): \WP_Error {
		$config = Feature_Config::load();
		if ( null === $config ) {
			return new \WP_Error( 'c03c_denied', 'Unavailable.' );
		}
		try {
			Read_Operation::run(
				$config,
				'/coagmentator/v1/site_info',
				'f3858c09-8c56-48fd-99d1-ab75862bb955',
				'operator-1',
				static function ( Read_Deadline $deadline ): \WP_Error {
					$directory = '/var/coagmentator-c03c/control';
					$case      = get_option( 'c03c_case' );
					file_put_contents( $directory . '/target-' . bin2hex( random_bytes( 8 ) ), '' );
					if ( 'parallel' === $case ) {
						$held = $directory . '/held-' . getmypid();
						file_put_contents( $held, '' );
						try {
							$end = hrtime( true ) + 12000000000;
							while ( ! is_file( $directory . '/release' ) && hrtime( true ) < $end ) {
								$deadline->remaining();
								usleep( 10000 );
							}
						} finally {
							unlink( $held );
						}
					} elseif ( 'throw' === $case ) {
						throw new \RuntimeException( 'FAKE_CREDENTIAL_SECRET SELECT fake /private/fake/path STACK_FAKE fake@example.test BODY_FAKE' );
					}
					return new \WP_Error( 'c03c_synthetic_failure', 'Synthetic failure.' );
				}
			);
		} catch ( Operational_Failure $failure ) {
			file_put_contents( '/var/coagmentator-c03c/control/denied-' . bin2hex( random_bytes( 8 ) ), $failure->getMessage() );
		}
		return new \WP_Error( 'c03c_denied', 'Disposable operation completed.', array( 'status' => 403 ) );
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
