<?php
/**
 * Conjunctive operational boundary for later capability/read validation.
 *
 * @package Coagmentator
 */

namespace Coagmentator\Infrastructure;

use Coagmentator\Auth\Bridge_Identity;
use Coagmentator\Config\Feature_Config;

/** Infrastructure only; no route registration, reader or capability grant. */
final class Read_Operation {

	/**
	 * The caller must supply later authorization/validation inside the operation.
	 * C03C exercises only a test-only callback which always returns a failure.
	 *
	 * @template T
	 * @param Feature_Config $config Fixed host policy.
	 * @param string         $route Fixed operation route from trusted code.
	 * @param string         $site Claimed site, checked by existing identity foundation.
	 * @param string         $actor Claimed actor, checked by existing identity foundation.
	 * @param \Closure       $operation Later capability/read work, test-only at C03C.
	 * @phpstan-param \Closure(Read_Deadline): T $operation
	 * @return T Callback result, never synthesized success.
	 * @throws Operational_Failure Safe failure, with deterministic slot cleanup.
	 */
	public static function run( Feature_Config $config, string $route, string $site, string $actor, \Closure $operation ): mixed {
		if ( ! Bridge_Identity::allows( $config, $route, $site, $actor ) || ! Preauth_Admission::passed() ) {
			throw new Operational_Failure( 'identity_denied' );
		}
		$clock    = new Server_Clock();
		$deadline = new Read_Deadline( $clock );
		$store    = new Local_Store( $config->storage( 'admission_directory' ), $config->excluded_roots() );
		( new Rate_Admission( $store, $clock ) )->authenticated( $config );
		$slot = new Read_Slot( $store );
		try {
			$audit = new Read_Audit( new Local_Store( $config->storage( 'audit_directory' ), $config->excluded_roots() ), $clock );
			$name  = substr( $route, strlen( '/coagmentator/v1/' ) );
			$audit->append( $config->site(), $name, 'started' );
			try {
				$deadline->remaining();
				$result = $operation( $deadline );
				$deadline->remaining();
			} catch ( \Throwable $failure ) {
				$audit->append( $config->site(), $name, 'error' );
				throw new Operational_Failure( $failure instanceof Operational_Failure ? $failure->getMessage() : 'operational_storage_failed' );
			}
			$audit->append( $config->site(), $name, 'returned' );
			return $result;
		} finally {
			$slot->release();
		}
	}
}
