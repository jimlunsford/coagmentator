<?php
/**
 * Four fixed nonblocking local flock slots, with no leases.
 *
 * @package Coagmentator
 */

namespace Coagmentator\Infrastructure;

/** Ownership persists until explicit completion or operating-system release. */
final class Read_Slot {
	/**
	 * Owned descriptor.
	 *
	 * @var resource|null Owned descriptor.
	 */
	private $file;

	/**
	 * Acquire once; no queue, sleep, retry, timestamp or unlink.
	 *
	 * @param Local_Store $store Fixed admission store.
	 * @throws Operational_Failure Fifth request or storage failure.
	 */
	public function __construct( Local_Store $store ) {
		for ( $index = 0; $index < 4; ++$index ) {
			$this->file = $store->lock( 'slot-' . $index );
			if ( null !== $this->file ) {
				return;
			}
		}
		throw new Operational_Failure( 'concurrency_limit' );
	}

	/** Release on every normal/error exit; process death is handled by the OS. */
	public function release(): void {
		if ( is_resource( $this->file ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Release the mandatory local flock descriptor.
			fclose( $this->file );
		}
		$this->file = null;
	}

	/** Last-resort ordinary object cleanup is not an OOM recovery promise. */
	public function __destruct() {
		$this->release();
	}
}
