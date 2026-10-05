<?php
/**
 * G04 real interactive, cookie, XML-RPC and alternate-auth boundary controls.
 *
 * @package Coagmentator
 */

/**
 * Human recovery must work in every emergency scenario.
 */
final class NonRestBypassTest extends GuardHttpCase {
	/**
	 * Service cookies, remote credentials and alternate identity injection deny.
	 */
	public function test_non_rest(): void {
		foreach ( array( '/wp-admin/', '/wp-admin/admin-ajax.php?action=c02_target', '/wp-admin/admin-post.php?action=c02_target', '/wp-login.php', '/c02-target.php' ) as $path ) {
			$this->denied( $path, 'GET', 'service-cookie' );
			$this->denied( $path, 'GET', 'marker-cookie' );
			$this->denied( $path, 'GET', 'service-basic' );
			$this->denied( $path, 'GET', '', '', array( 'X-C02-User: service' ) );
		}
		$this->xmlrpc_denied( 'c02_service', $this->fixture['service_secret'] );
	}

	/**
	 * Valid XML-RPC single and nested multicall authentication requests.
	 *
	 * @param string $login Fixture login.
	 * @param string $password Memory-only credential.
	 */
	private function xmlrpc_denied( string $login, string $password ): void {
		$params = '<value><string>' . $login . '</string></value><value><string>' . $password . '</string></value>';
		$single = '<methodName>wp.getUsersBlogs</methodName><params><param>' . str_replace( '</value><value>', '</value></param><param><value>', $params ) . '</param></params>';
		$multi  = '<methodName>system.multicall</methodName><params><param><value><array><data><value><struct><member><name>methodName</name><value><string>wp.getUsersBlogs</string></value></member><member><name>params</name><value><array><data>' . $params . '</data></array></value></member></struct></value></data></array></value></param></params>';
		foreach ( array( $single, $multi ) as $call ) {
			$before = $this->snapshot();
			$result = $this->request( '/xmlrpc.php', 'POST', '', '<?xml version="1.0"?><methodCall>' . $call . '</methodCall>', array( 'Content-Type: text/xml' ) );
			self::assertTrue( $result['status'] >= 400 || str_contains( $result['body'], 'faultCode' ), 'XML-RPC authentication must deny.' );
			self::assertSame( $before, $this->snapshot() );
		}
	}

	/**
	 * Emergency XML-RPC must reject ordinary human passwords as well.
	 */
	public function test_xmlrpc_human_control(): void {
		if ( in_array( $this->fixture['scenario'], array( 'missing-registry', 'malformed-registry', 'missing-registry-active', 'malformed-registry-active', 'unreadable-registry', 'missing-support' ), true ) ) {
			$this->xmlrpc_denied( 'c02_human', $this->fixture['human_password'] );
		} else {
			$result = $this->request( '/xmlrpc.php', 'POST', '', '<?xml version="1.0"?><methodCall><methodName>wp.getUsersBlogs</methodName><params><param><value><string>c02_human</string></value></param><param><value><string>' . $this->fixture['human_password'] . '</string></value></param></params></methodCall>', array( 'Content-Type: text/xml' ) );
			self::assertSame( 200, $result['status'] );
			self::assertStringContainsString( '<name>blogid</name>', $result['body'] );
			self::assertStringNotContainsString( 'faultCode', $result['body'] );
		}
	}

	/**
	 * Real password login plus authorized human admin and cookie/nonce recovery.
	 */
	public function test_human_recovery_and_public(): void {
		$this->human_recovery();
	}
}
