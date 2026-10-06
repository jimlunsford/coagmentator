<?php
/**
 * Bounded append-oriented read audit, separate from responses and mutations.
 *
 * @package Coagmentator
 */

namespace Coagmentator\Infrastructure;

use Coagmentator\Config\Identity_Values;
use Coagmentator\Guard\Route_Boundary;

/** Exact metadata schema; no caller dictionary or raw error can be logged. */
final class Read_Audit {
	public const CAPACITY          = 8388608;
	public const RECORD_BYTES      = 256;
	public const RETENTION_SECONDS = 7776000;

	/**
	 * Fixed trusted dependencies.
	 *
	 * @param Local_Store  $store Private audit directory.
	 * @param Server_Clock $clock Host clock.
	 */
	public function __construct( private Local_Store $store, private Server_Clock $clock ) {}

	/**
	 * Integrity state makes truncation or interrupted writes a sticky denial.
	 *
	 * @param string $bytes Bounded audit bytes.
	 * @return string Closed size and SHA-256 representation.
	 */
	public static function fingerprint( string $bytes ): string {
		return strlen( $bytes ) . ':' . hash( 'sha256', $bytes ) . "\n";
	}

	/**
	 * Append after validating all retained evidence, with completion headroom.
	 *
	 * @param string $site Configured site UUID, never a request claim.
	 * @param string $operation Fixed enabled read operation.
	 * @param string $outcome One of the internal lifecycle outcomes.
	 * @throws Operational_Failure Corruption, capacity or write failure.
	 */
	public function append( string $site, string $operation, string $outcome ): void {
		if ( ! Identity_Values::uuid( $site ) || ! in_array( $operation, Route_Boundary::OPERATIONS, true ) || ! in_array( $outcome, array( 'started', 'returned', 'error' ), true ) ) {
			throw new Operational_Failure();
		}
		$this->update( $site, $operation, $outcome );
	}

	/** Trusted local retention job; installations must schedule this during idle periods. */
	public function maintain(): void {
		$this->update( null, null, null );
	}

	/**
	 * Locked append or retention-only pass, sharing the same corruption checks.
	 *
	 * @param string|null $site Fixed site, null for retention only.
	 * @param string|null $operation Fixed read name.
	 * @param string|null $outcome Fixed lifecycle outcome.
	 * @throws Operational_Failure Storage or capacity denial.
	 */
	private function update( ?string $site, ?string $operation, ?string $outcome ): void {
		$this->store->transaction(
			function () use ( $site, $operation, $outcome ): void {
				$names = $this->store->names( 4 );
				sort( $names );
				if ( array( 'clock', 'control.lock', 'events', 'state' ) !== $names ) {
						throw new Operational_Failure();
				}
				$now = $this->clock->now();
				Rate_Admission::advance_clock( $this->store, $now );
				$file = $this->store->open( 'events' );
				try {
					$bytes = Local_Store::read( $file, self::CAPACITY );
					$state = $this->store->open( 'state' );
					try {
						if ( Local_Store::read( $state, 100 ) !== self::fingerprint( $bytes ) ) {
							throw new Operational_Failure();
						}
					} finally {
						// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Mandatory local descriptor cleanup.
						fclose( $state );
					}
					$kept = '';
					$last = 0;
					if ( '' !== $bytes ) {
						if ( ! str_ends_with( $bytes, "\n" ) ) {
							throw new Operational_Failure();
						}
						foreach ( explode( "\n", substr( $bytes, 0, -1 ) ) as $line ) {
								$data = json_decode( $line, true, 4 );
                            // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode -- Exact closed host audit format, rejecting corruption rather than coercing it.
							if ( strlen( $line ) + 1 > self::RECORD_BYTES || ! is_array( $data ) || array_keys( $data ) !== array( 'timestamp', 'operation', 'site_id', 'outcome' ) || ! is_int( $data['timestamp'] ) || $data['timestamp'] < 1 || $data['timestamp'] < $last || $data['timestamp'] > $now || ! Identity_Values::uuid( $data['site_id'] ) || ! in_array( $data['operation'], Route_Boundary::OPERATIONS, true ) || ! in_array( $data['outcome'], array( 'started', 'returned', 'error' ), true ) || json_encode( $data ) !== $line ) {
								throw new Operational_Failure();
							}
							$last = $data['timestamp'];
							if ( $last > $now - self::RETENTION_SECONDS ) {
								$kept .= $line . "\n";
							}
						}
					}
					// At most four slot holders can owe completions. Reserve for all four
					// plus this start before execution, without serializing their callbacks.
					$required = null === $site ? 0 : ( 'started' === $outcome ? 5 : 1 ) * self::RECORD_BYTES;
					if ( strlen( $kept ) + $required > self::CAPACITY ) {
						throw new Operational_Failure();
					}
					$line = '';
					if ( null !== $site ) {
						// phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode -- No payload/error/credential fields exist in this schema.
						$line = json_encode(
							array(
								'timestamp' => $now,
								'operation' => $operation,
								'site_id'   => $site,
								'outcome'   => $outcome,
							),
							JSON_THROW_ON_ERROR
						) . "\n";
					}
					if ( strlen( $line ) > self::RECORD_BYTES ) {
							throw new Operational_Failure();
					}
					// Compaction removes only expired records; an interrupted write
					// leaves the old fingerprint mismatched and therefore fails closed.
					if ( $kept !== $bytes ) {
						Local_Store::write( $file, $kept );
					}
					if ( '' !== $line ) {
						Local_Store::write( $file, $line, true );
					}
					$state = $this->store->open( 'state' );
					try {
						Local_Store::write( $state, self::fingerprint( $kept . $line ) );
					} finally {
						// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Mandatory local descriptor cleanup.
						fclose( $state );
					}
				} finally {
					// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Release the mandatory local flock descriptor.
					fclose( $file );
				}
			}
		);
	}
}
