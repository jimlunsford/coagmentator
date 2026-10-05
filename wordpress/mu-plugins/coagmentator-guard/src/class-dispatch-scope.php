<?php
/**
 * Balanced external REST lifecycle, independent of WordPress's dispatch boolean.
 *
 * @package Coagmentator
 */

namespace Coagmentator\Guard;

/** One admitted object per external scope, never an internal request. */
final class Dispatch_Scope {
	/** @var bool External cycle. */
	private bool $active = false;
	/** @var bool Admission consumed. */
	private bool $consumed = false;
	/** @var int Current dispatch depth. */
	private int $depth = 0;
	/** @var object|null Original admitted object. */
	private ?object $request = null;

	/** @return bool A new external cycle may start. */
	public function begin(): bool {
		if ( $this->active || $this->depth > 0 ) {
			return false;
		}
		$this->active = true;
		return true;
	}

	/**
	 * Consume only an authenticated external admission.
	 *
	 * @param object $request Actual request object.
	 * @return bool First top-level dispatch.
	 */
	public function enter( object $request ): bool {
		if ( ! $this->active || $this->consumed || 0 !== $this->depth ) {
			return false;
		}
		$this->consumed = true;
		$this->request  = $request;
		++$this->depth;
		return true;
	}

	/**
	 * Defense-in-depth hook can only observe the admitted object.
	 *
	 * @param object $request Actual request.
	 * @return bool Current admission.
	 */
	public function matches( object $request ): bool {
		return $this->active && 1 === $this->depth && $request === $this->request;
	}

	/** Balance the single successful enter in finally. */
	public function leave(): void {
		$this->depth = 0;
	}

	/** Clear all request-local state in the external owner's finally. */
	public function finish(): void {
		$this->active   = false;
		$this->consumed = false;
		$this->depth    = 0;
		$this->request  = null;
	}
}
