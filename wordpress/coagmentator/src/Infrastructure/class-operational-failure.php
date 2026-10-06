<?php
/**
 * Closed infrastructure failures, without incoming exception details.
 *
 * @package Coagmentator
 */

namespace Coagmentator\Infrastructure;

/** No path, body, identity or underlying error is retained. */
final class Operational_Failure extends \RuntimeException {

	/**
	 * Construct a fixed safe reason, not a response envelope.
	 *
	 * @param string $reason Internal fixed reason.
	 */
	public function __construct( string $reason = 'operational_storage_failed' ) {
		parent::__construct( in_array( $reason, array( 'rate_limit', 'concurrency_limit', 'deadline_exceeded', 'identity_denied' ), true ) ? $reason : 'operational_storage_failed' );
	}
}
