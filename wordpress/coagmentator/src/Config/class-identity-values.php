<?php
/**
 * Closed identity primitives. No normalization can manufacture a valid identity.
 *
 * @package Coagmentator
 */

namespace Coagmentator\Config;

/** Shared pure validation for trusted policy and observed core facts. */
final class Identity_Values {
	/**
	 * Canonical UUIDv4, including the version and variant bits.
	 *
	 * @param mixed $value Candidate value.
	 * @return bool Valid UUID.
	 */
	public static function uuid( mixed $value ): bool {
		return is_string( $value ) && 1 === preg_match( '/^[a-f0-9]{8}-[a-f0-9]{4}-4[a-f0-9]{3}-[89ab][a-f0-9]{3}-[a-f0-9]{12}$/D', $value );
	}

	/**
	 * WordPress identity and policy integers use the contract's safe range.
	 *
	 * @param mixed $value Candidate value.
	 * @return bool Valid positive ID.
	 */
	public static function id( mixed $value ): bool {
		return is_int( $value ) && $value > 0 && $value <= 9007199254740991;
	}

	/**
	 * Actor names are opaque, bounded ASCII values, never role names.
	 *
	 * @param mixed $value Candidate value.
	 * @return bool Valid actor.
	 */
	public static function actor( mixed $value ): bool {
		return is_string( $value ) && 1 === preg_match( '/^[A-Za-z0-9_-]{1,64}$/D', $value );
	}
}
