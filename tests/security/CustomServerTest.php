<?php
/**
 * A competing server denies service authority without a public/human outage.
 *
 * @package Coagmentator
 */

/**
 * Runs before issuance and again with existing disposable credentials.
 */
final class CustomServerTest extends GuardHttpCase {
	/**
	 * Verify the actual selection, credential state, denied targets and controls.
	 */
	public function test_competing_server_boundary(): void {
		$before = $this->snapshot();
		self::assertSame( 'C02_Custom_REST_Server', $before['server'] );
		$issued = isset( $this->fixture['service_secret'] );
		self::assertSame( $issued ? 2 : 0, $before['credentials'] );
		if ( ! $issued ) {
			self::assertArrayNotHasKey( 'human_secret', $this->fixture );
			self::assertArrayNotHasKey( 'service_uuid', $this->fixture );
		}
		foreach ( array( '', 'service-cookie', 'marker-cookie', 'human-cookie' ) as $auth ) {
			$this->denied( '/wp-json/coagmentator/v1/site_info', 'POST', $auth );
			$this->denied( '/wp-json/coagmentator/v1/get_content', 'POST', $auth );
		}
		foreach ( array( 'service-cookie', 'marker-cookie' ) as $auth ) {
			$this->denied( '/wp-json/wp/v2/users/me', 'GET', $auth );
			$this->denied( '/wp-json/c02/v1/target', 'POST', $auth );
		}
		$this->denied( '/wp-json/c02/v1/target', 'POST', '', '', array( 'X-C02-User: service' ) );
		if ( $issued ) {
			$this->denied( '/wp-json/coagmentator/v1/site_info', 'POST', 'service-basic' );
			$this->denied( '/wp-json/wp/v2/users/me', 'GET', 'service-basic' );
			$this->denied( '/wp-json/c02/v1/target', 'POST', 'service-basic' );
		}
		self::assertSame( $before, $this->snapshot(), 'Conflict must prevent callbacks and protected state changes.' );
		$public = $this->request( '/wp-json/wp/v2/types/post' );
		self::assertSame( 200, $public['status'] );
		self::assertSame( 'post', json_decode( $public['body'], true, 512, JSON_THROW_ON_ERROR )['slug'] );
		$probe = $this->request( '/c02-observe.php', 'GET', 'human-cookie' );
		$data  = json_decode( $probe['body'], true, 512, JSON_THROW_ON_ERROR );
		self::assertFalse( $data['guard'] );
		self::assertSame( 'C02_Custom_REST_Server', $data['server'] );
		self::assertNotSame( '', $data['nonce'] );
		$human = $this->request( '/wp-json/wp/v2/users/me', 'GET', 'human-cookie', '', array( 'X-WP-Nonce: ' . $data['nonce'] ) );
		self::assertSame( 200, $human['status'] );
		self::assertSame( $this->fixture['human'], json_decode( $human['body'], true, 512, JSON_THROW_ON_ERROR )['id'] );
		$after = $this->snapshot();
		self::assertSame( $before['editorial'], $after['editorial'] );
		self::assertSame( $before['users'], $after['users'] );
		self::assertSame( $before['credentials'], $after['credentials'] );
		self::assertSame( $before['outer'], $after['outer'] );
	}
}
