<?php
/**
 * G04 real interactive, cookie, XML-RPC and alternate-auth boundary controls.
 *
 * @package Coagmentator
 */

/** Human recovery must work in every emergency scenario. */
final class NonRestBypassTest extends GuardHttpCase {
	/** Service cookies, remote credentials and alternate identity injection deny. */
	public function test_non_rest(): void {
		foreach ( array( '/wp-admin/', '/wp-admin/admin-ajax.php?action=c02_target', '/wp-admin/admin-post.php?action=c02_target', '/wp-login.php', '/c02-target.php' ) as $path ) {
			$this->denied( $path, 'GET', 'service-cookie' );
			$this->denied( $path, 'GET', 'service-basic' );
			$this->denied( $path, 'GET', '', '', array( 'X-C02-User: service' ) );
		}
		foreach ( array( 'wp.getUsersBlogs', 'system.multicall' ) as $method ) {
			$before = $this->snapshot();
			$result = $this->request( '/xmlrpc.php', 'POST', 'service-basic', '<?xml version="1.0"?><methodCall><methodName>' . $method . '</methodName><params><param><value><string>c02_service</string></value></param><param><value><string>' . $this->fixture['service_secret'] . '</string></value></param></params></methodCall>', array( 'Content-Type: text/xml' ) );
			self::assertTrue( $result['status'] >= 400 || str_contains( $result['body'], '<fault>' ) );
			self::assertSame( $before, $this->snapshot() );
		}
	}

	/** Real password login plus authorized human admin and cookie/nonce recovery. */
	public function test_human_recovery_and_public(): void {
		$result = $this->request( '/wp-login.php' );
		self::assertSame( 200, $result['status'] );
		self::assertStringContainsString( 'loginform', $result['body'] );
		$result = $this->request( '/wp-login.php', 'POST', '', http_build_query( array( 'log' => 'c02_human', 'pwd' => $this->fixture['human_password'], 'redirect_to' => 'https://wordpress.test/wp-admin/', 'testcookie' => '1' ) ), array( 'Content-Type: application/x-www-form-urlencoded', 'Cookie: wordpress_test_cookie=WP%20Cookie%20check' ) );
		self::assertSame( 302, $result['status'], 'Human password authentication must create a session.' );
		$result = $this->request( '/wp-admin/', 'GET', 'human-cookie' );
		self::assertSame( 200, $result['status'] );
		self::assertStringContainsString( 'id="wpbody"', $result['body'] );
		self::assertStringNotContainsString( 'id="loginform"', $result['body'] );
		$probe = $this->request( '/c02-observe.php', 'GET', 'human-cookie' );
		$nonce = json_decode( $probe['body'], true, 512, JSON_THROW_ON_ERROR )['nonce'];
		self::assertNotSame( '', $nonce );
		$result = $this->request( '/wp-json/wp/v2/users/me', 'GET', 'human-cookie', '', array( 'X-WP-Nonce: ' . $nonce ) );
		self::assertSame( 200, $result['status'] );
		$this->denied( '/wp-json/coagmentator/v1/site_info', 'POST', 'human-cookie', '', array( 'X-WP-Nonce: ' . $nonce ) );
		$result = $this->request( '/' );
		self::assertSame( 200, $result['status'] );
	}
}
