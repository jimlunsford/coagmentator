<?php
/**
 * G01 native and plugin callback denial with independent state snapshots.
 *
 * @package Coagmentator
 */

/** Promotion never grants a protected identity native API reach. */
final class NativeRestBypassTest extends GuardHttpCase {
	/** Every HTTP case checks callback and editorial/user/credential sentinels. */
	public function test_native_bypasses(): void {
		$id = $this->fixture['service'];
		foreach ( array(
			array( 'GET', '/wp/v2/posts' ),
			array( 'POST', '/wp/v2/posts' ),
			array( 'POST', '/wp/v2/posts/1' ),
			array( 'DELETE', '/wp/v2/posts/1' ),
			array( 'POST', '/wp/v2/users/me' ),
			array( 'GET', '/wp/v2/users/' . $id . '/application-passwords' ),
			array( 'POST', '/wp/v2/users/' . $id . '/application-passwords' ),
			array( 'DELETE', '/wp/v2/users/' . $id . '/application-passwords/' . $this->fixture['service_uuid'] ),
			array( 'GET', '/wp/v2/users/' . $id . '/application-passwords/introspect' ),
			array( 'POST', '/batch/v1' ),
			array( 'GET', '/c02/v1/target' ),
			array( 'GET', '/wp-abilities/v1/abilities' ),
			array( 'GET', '/' ),
		) as $case ) {
			$this->denied( '/wp-json' . $case[1], $case[0], 'service-basic', '{"title":"must-not-change","name":"must-not-create"}', array( 'Content-Type: application/json' ) );
		}
	}
}
