<?php
/**
 * B02 fixed handler identity while the genuine synthetic class is loaded.
 *
 * @package Coagmentator
 */

/**
 * Replacement must not reach validation, permission or target callbacks.
 */
final class ReplacementTest extends GuardHttpCase {
	/**
	 * A matching namespace and a loaded expected class are not authority.
	 */
	public function test_replacement_denied(): void {
		$this->denied( '/wp-json/coagmentator/v1/site_info', 'POST', 'service-basic', '{"probe":"value"}', array( 'Content-Type: application/json' ) );
	}
}
