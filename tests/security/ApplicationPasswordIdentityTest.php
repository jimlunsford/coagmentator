<?php
/**
 * A01/A03 real core Application Password events, normal-plugin identity checks.
 *
 * @package Coagmentator
 */

/** Each trusted CLI scenario uses a fresh HTTP request and unchanged MU guard. */
final class ApplicationPasswordIdentityTest extends GuardHttpCase {
	/** Correct credentials alone cannot override a mismatched or disabled binding. */
	public function test_current_identity_scenario(): void {
		$case     = $this->fixture['c03a_scenario'];
		$accepted = in_array( $case, array( 'approved', 'overlap', 'promoted-unmarked' ), true );
		$auth     = in_array( $case, array( 'wrong-admin', 'wrong-user', 'role-only' ), true ) ? 'human-basic' : 'service-basic';
		if ( in_array( $case, array( 'app-id', 'overlap', 'expired-overlap', 'revoke-after-event', 'revoked' ), true ) ) {
			$this->fixture['service_secret'] = $this->fixture['rotation_secret'];
		}
		$before = $this->snapshot();
		$result = $this->request( '/wp-json/coagmentator/v1/site_info', 'POST', $auth );
		self::assertSame( 403, $result['status'], 'Synthetic handler must never return bridge success.' );
		$this->closed_failure( $result['body'] );
		$after = $this->snapshot();
		self::assertSame( $before['editorial'], $after['editorial'] );
		self::assertSame( $before['targets'], $after['targets'] );
		if ( 'revoke-after-event' !== $case ) {
			self::assertSame( $before['users'], $after['users'] );
		}
		$response = $this->request( '/c03a-observe.php' );
		self::assertSame( 200, $response['status'] );
		$observed = json_decode( $response['body'], true, 512, JSON_THROW_ON_ERROR );
		self::assertNotEmpty( $observed['checks'], 'Normal identity foundation must actually be invoked.' );
		self::assertSame( $accepted, in_array( true, $observed['checks'], true ), 'Unexpected foundation result for ' . $case );
		self::assertSame( $accepted ? 1 : 0, $observed['outer'] );
		self::assertSame( 'revoked' === $case ? 0 : 1, $observed['events'], 'Core success event must be independently observed.' );
		if ( $accepted ) {
			self::assertTrue( $observed['serialization_denied'] );
			self::assertTrue( $observed['stale_denied'] );
		}
		if ( 'revoke-after-event' === $case ) {
			self::assertTrue( $observed['removed'], 'Revocation after capture must be independently verified.' );
		}
		if ( 'app-id' === $case || 'overlap' === $case ) {
			self::assertTrue( $observed['uuid_differs_from_app_id'] );
		}
	}
}
