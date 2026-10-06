<?php
/**
 * Host clock with a trusted-code-only deterministic test seam.
 *
 * @package Coagmentator
 */

namespace Coagmentator\Infrastructure;

/** Neither clock source is taken from request values. */
final class Server_Clock {

	/**
	 * Optional sources exist for trusted local tests, never HTTP configuration.
	 *
	 * @param \Closure|null $wall Unix seconds.
	 * @param \Closure|null $ticks Monotonic nanoseconds.
	 * @phpstan-param (\Closure(): mixed)|null $wall
	 * @phpstan-param (\Closure(): mixed)|null $ticks
	 */
	public function __construct( private ?\Closure $wall = null, private ?\Closure $ticks = null ) {}

	/**
	 * Persistent accounting clock.
	 *
	 * @return int Valid Unix seconds.
	 * @throws Operational_Failure Invalid clock.
	 */
	public function now(): int {
		$value = null === $this->wall ? time() : ( $this->wall )();
		if ( ! is_int( $value ) || $value < 1 || $value > 253402300799 ) {
			throw new Operational_Failure();
		}
		return $value;
	}

	/**
	 * Request budget clock, independent of wall-clock adjustments.
	 *
	 * @return int Valid monotonic nanoseconds.
	 * @throws Operational_Failure Invalid clock.
	 */
	public function ticks(): int {
		$value = null === $this->ticks ? hrtime( true ) : ( $this->ticks )();
		if ( ! is_int( $value ) || $value < 0 || $value > PHP_INT_MAX - 15000000000 ) {
			throw new Operational_Failure();
		}
		return $value;
	}
}
