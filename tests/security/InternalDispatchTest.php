<?php
/**
 * G03 actual authenticated outer callback with no real read handler or success.
 *
 * @package Coagmentator
 */

/**
 * Executed separately with the synthetic, fixed-identity denial callback.
 */
final class InternalDispatchTest extends GuardHttpCase {
	/**
	 * Nested objects, batch, rest_do_request and switched users never reach targets.
	 */
	public function test_internal_dispatch_and_independent_requests(): void {
		foreach ( array( '', 'exception', '' ) as $body ) {
			$before = $this->snapshot();
			$result = $this->request( '/wp-json/coagmentator/v1/site_info', 'POST', 'service-basic', $body );
			self::assertSame( 403, $result['status'] );
			$after = $this->snapshot();
			self::assertSame( $before['outer'] + 1, $after['outer'], 'The real authentication path must reach the synthetic outer callback.' );
			self::assertCount( 11, $after['checks'] );
			self::assertNotContains( false, $after['checks'], 'An internal dispatch bypassed the guard.' );
			// One dispatch instrumentation hit belongs to the outer callback only.
			self::assertSame( $before['targets'] + 1, $after['targets'] );
			self::assertSame( $before['editorial'], $after['editorial'] );
			self::assertSame( $before['users'], $after['users'] );
		}
	}
}
