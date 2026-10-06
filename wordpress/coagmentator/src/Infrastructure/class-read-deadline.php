<?php
/**
 * Fixed fifteen-second request-local read budget.
 *
 * @package Coagmentator
 */

namespace Coagmentator\Infrastructure;

/** Downstream readers must check and consume this remaining budget. */
final class Read_Deadline {
	/**
	 * Last observed monotonic time.
	 *
	 * @var int Last observed monotonic time.
	 */
	private int $last;
	/**
	 * Exclusive expiration.
	 *
	 * @var int Exclusive expiration.
	 */
	private int $end;
	/**
	 * Sticky clock/deadline failure.
	 *
	 * @var bool Sticky clock/deadline failure.
	 */
	private bool $failed = false;

	/**
	 * Start from trusted code, never a client timestamp.
	 *
	 * @param Server_Clock $clock Host clock.
	 */
	public function __construct( private Server_Clock $clock ) {
		$this->last = $clock->ticks();
		$this->end  = $this->last + 15000000000;
	}

	/**
	 * Check before and after every bounded downstream operation.
	 *
	 * @return int Remaining nanoseconds.
	 * @throws Operational_Failure Expired or invalid clock, permanently.
	 */
	public function remaining(): int {
		try {
			$now = $this->clock->ticks();
			if ( $this->failed || $now < $this->last || $now >= $this->end ) {
				throw new Operational_Failure( 'deadline_exceeded' );
			}
			$this->last = $now;
			return $this->end - $now;
		} catch ( \Throwable $failure ) {
			$this->failed = true;
			throw new Operational_Failure( 'deadline_exceeded' );
		}
	}
}
