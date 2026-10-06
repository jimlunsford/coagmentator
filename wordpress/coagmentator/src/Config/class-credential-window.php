<?php
/**
 * One credential or a fixed, at-most-24-hour two-credential overlap.
 *
 * @package Coagmentator
 */

namespace Coagmentator\Config;

/** No secret, credential record, issuance or rotation side effect. */
final class Credential_Window {
	/**
	 * Approved UUIDs only.
	 *
	 * @var list<string> Approved UUIDs only.
	 */
	private array $uuids;
	/**
	 * Exclusive overlap expiry.
	 *
	 * @var int|null Exclusive overlap expiry.
	 */
	private ?int $expires;
	/**
	 * Inclusive overlap start.
	 *
	 * @var int|null Inclusive overlap start.
	 */
	private ?int $starts;

	/**
	 * Construct only after closed validation.
	 *
	 * @param list<string> $uuids UUIDs.
	 * @param int|null    $starts Start.
	 * @param int|null    $expires Expiry.
	 */
	private function __construct( array $uuids, ?int $starts, ?int $expires ) {
		$this->uuids   = $uuids;
		$this->starts  = $starts;
		$this->expires = $expires;
	}

	/**
	 * Validate bounded configuration without coercion or a rolling expiry.
	 *
	 * @param mixed $uuids UUID list.
	 * @param mixed $rotation Null or the exact timing pair.
	 * @param int   $now Trusted server clock.
	 * @return self|null Valid window.
	 */
	public static function parse( mixed $uuids, mixed $rotation, int $now ): ?self {
		if ( ! is_array( $uuids ) || ! array_is_list( $uuids ) || count( $uuids ) < 1 || count( $uuids ) > 2 || $now < 1 ) {
			return null;
		}
		$seen = array();
		foreach ( $uuids as $uuid ) {
			if ( ! Identity_Values::uuid( $uuid ) || ! is_string( $uuid ) || in_array( $uuid, $seen, true ) ) {
				return null;
			}
			$seen[] = $uuid;
		}
		if ( 1 === count( $seen ) ) {
			return null === $rotation ? new self( $seen, null, null ) : null;
		}
		if ( ! is_array( $rotation ) || array_keys( $rotation ) !== array( 'started_at', 'expires_at' ) || ! is_int( $rotation['started_at'] ) || ! is_int( $rotation['expires_at'] ) ) {
			return null;
		}
		$start = $rotation['started_at'];
		$end   = $rotation['expires_at'];
		if ( $start < 1 || $start > $now || $end <= $now || $end <= $start || $end - $start > 86400 ) {
			return null;
		}
		return new self( $seen, $start, $end );
	}

	/**
	 * Recheck the clock on every admission, including an already parsed policy.
	 * Expired overlap disables both credentials until the operator pins one.
	 *
	 * @param string $uuid Observed UUID, never app_id.
	 * @param int    $now Trusted server clock.
	 * @return bool Approved now.
	 */
	public function allows( string $uuid, int $now ): bool {
		return $now > 0 && in_array( $uuid, $this->uuids, true ) && ( null === $this->expires || ( null !== $this->starts && $now >= $this->starts && $now < $this->expires ) );
	}
}
