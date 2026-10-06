<?php
/**
 * Persistent single-host minute windows under one nonblocking local lock.
 *
 * @package Coagmentator
 */

namespace Coagmentator\Infrastructure;

use Coagmentator\Config\Feature_Config;
use Coagmentator\Config\Transport_Policy;

/** Counter bytes are canonical, bounded and never interpreted as zero on error. */
final class Rate_Admission {
	public const PEER_BUCKETS = 1024;

	/**
	 * Trusted construction only.
	 *
	 * @param Local_Store  $store Fixed admission directory.
	 * @param Server_Clock $clock Host clock.
	 */
	public function __construct( private Local_Store $store, private Server_Clock $clock ) {}

	/**
	 * One site identity selects accounting, never the envelope or credential UUID.
	 *
	 * @param Feature_Config $config Validated fixed configuration.
	 */
	public function authenticated( Feature_Config $config ): void {
		$this->consume( 'read-' . hash( 'sha256', $config->site() ), 60 );
	}

	/**
	 * Actual immediate peer, using the C03B canonical-address parser.
	 *
	 * @param array<string, mixed> $server Trusted server state, not forwarded input.
	 * @throws Operational_Failure Missing or invalid peer.
	 */
	public function preauth( array $server ): void {
		$peer = Transport_Policy::peer( $server );
		if ( null === $peer ) {
			throw new Operational_Failure();
		}
		$this->consume( 'peer-' . hash( 'sha256', $peer ), 120 );
	}

	/**
	 * Validate the closed record without coercion or normalization.
	 *
	 * @param string $bytes Bounded counter contents.
	 * @param int    $limit Fixed rate ceiling.
	 * @param int    $now Trusted wall time.
	 * @return array{window:int,last:int,count:int} Valid record.
	 * @throws Operational_Failure Corrupt or backwards state.
	 */
	private static function decode( string $bytes, int $limit, int $now ): array {
		$data = json_decode( $bytes, true, 4 );
		// phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode -- Closed local counter representation, not a REST serializer.
		if ( ! is_array( $data ) || array_keys( $data ) !== array( 'window', 'last', 'count' ) || ! is_int( $data['window'] ) || ! is_int( $data['last'] ) || ! is_int( $data['count'] ) || $data['window'] < 0 || intdiv( $data['last'], 60 ) !== $data['window'] || $data['last'] < 1 || $data['last'] > $now || $data['count'] < 1 || $data['count'] > $limit || json_encode( $data ) . "\n" !== $bytes ) {
			throw new Operational_Failure();
		}
		return $data;
	}

	/**
	 * Shared persistent high-water clock prevents restart/backwards-window resets.
	 *
	 * @param Local_Store $store Locked component store.
	 * @param int         $now Host time.
	 * @throws Operational_Failure Missing, corrupt or backwards clock.
	 */
	public static function advance_clock( Local_Store $store, int $now ): void {
		$file = $store->open( 'clock' );
		try {
			$bytes = Local_Store::read( $file, 13 );
			if ( 1 !== preg_match( '/^(0|[1-9][0-9]{0,11})\n$/D', $bytes ) || (int) $bytes > $now ) {
				throw new Operational_Failure();
			}
			Local_Store::write( $file, $now . "\n" );
		} finally {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Release the mandatory local flock descriptor.
			fclose( $file );
		}
	}

	/**
	 * Capacity checks, optional expired-peer retirement and update share one lock.
	 *
	 * @param string $name Hashed internal key.
	 * @param int    $limit Internal accepted constant.
	 * @throws Operational_Failure Ceiling, capacity or storage denial.
	 */
	private function consume( string $name, int $limit ): void {
		$this->store->transaction(
			function () use ( $name, $limit ): void {
				$now   = $this->clock->now();
				$names = $this->store->names( self::PEER_BUCKETS + 7 );
				$peers = array();
				$reads = 0;
				foreach ( $names as $existing ) {
					if ( str_starts_with( $existing, 'read-' ) && ++$reads > 1 ) {
						throw new Operational_Failure();
					}
					if ( 1 === preg_match( '/^peer-[a-f0-9]{64}$/D', $existing ) ) {
						$peers[] = $existing;
					} elseif ( ! in_array( $existing, array( 'control.lock', 'clock', 'slot-0', 'slot-1', 'slot-2', 'slot-3' ), true ) && ( 1 !== preg_match( '/^read-[a-f0-9]{64}$/D', $existing ) || ( 60 === $limit && $existing !== $name ) ) ) {
						throw new Operational_Failure();
					}
				}
				self::advance_clock( $this->store, $now );
				$exists = in_array( $name, $names, true );
				if ( ! $exists && 120 === $limit && count( $peers ) >= self::PEER_BUCKETS ) {
					foreach ( $peers as $peer ) {
						$file = $this->store->open( $peer );
						try {
							$state = self::decode( Local_Store::read( $file, 128 ), 120, $now );
						} finally {
								// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Release the mandatory local flock descriptor.
								fclose( $file );
						}
						if ( $state['window'] < intdiv( $now, 60 ) ) {
								$this->store->retire( $peer );
								// Only one expired bucket is needed, never evict live accounting.
								$peers = array_slice( $peers, 1 );
								break;
						}
					}
					if ( count( $peers ) >= self::PEER_BUCKETS ) {
						throw new Operational_Failure();
					}
				}
				$file = $this->store->open( $name, ! $exists );
				try {
					$state = $exists ? self::decode( Local_Store::read( $file, 128 ), $limit, $now ) : array(
						'window' => intdiv( $now, 60 ),
						'last'   => $now,
						'count'  => 0,
					);
					$count = intdiv( $now, 60 ) === $state['window'] ? $state['count'] : 0;
					if ( $count >= $limit ) {
						throw new Operational_Failure( 'rate_limit' );
					}
                 // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode -- Canonical bounded host counter, not user data.
					$bytes = json_encode(
						array(
							'window' => intdiv( $now, 60 ),
							'last'   => $now,
							'count'  => $count + 1,
						),
						JSON_THROW_ON_ERROR
					) . "\n";
					Local_Store::write( $file, $bytes );
				} finally {
					// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Release the mandatory local flock descriptor.
					fclose( $file );
				}
			}
		);
	}
}
