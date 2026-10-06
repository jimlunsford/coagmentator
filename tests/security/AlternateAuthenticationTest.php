<?php
/**
 * A02 each alternate path invokes the normal identity foundation and is denied.
 *
 * @package Coagmentator
 */

/** Real HTTP requests; no mocked current-user or authentication acceptance. */
final class AlternateAuthenticationTest extends GuardHttpCase {
	/** Headers, cookies, nonce, role/marker and a prior request cannot grant identity. */
	public function test_alternate_paths_and_cross_request_state(): void {
		$nonce_response = $this->request( '/c02-observe.php', 'GET', 'human-cookie' );
		$nonce          = json_decode( $nonce_response['body'], true, 512, JSON_THROW_ON_ERROR )['nonce'];
		self::assertNotSame( '', $nonce );
		$modes = array(
			'anonymous'             => array( '', array() ),
			'cookie'                => array( 'service-cookie', array() ),
			'human-cookie'          => array( 'human-cookie', array() ),
			'nonce'                 => array( '', array( 'X-WP-Nonce: ' . $nonce ) ),
			'cookie-nonce'          => array( 'human-cookie', array( 'X-WP-Nonce: ' . $nonce ) ),
			'injected-user'         => array( '', array( 'X-C03A-User: service' ) ),
			'jwt-like-plugin'       => array( '', array( 'X-C03A-User: human', 'Authorization: Bearer disposable.invalid.jwt' ) ),
			'oauth-bearer'          => array( '', array( 'Authorization: Bearer disposable-token' ) ),
			// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- Test-only Basic header exercises alternate-authentication denial.
			'basic-without-event'   => array( '', array( 'Authorization: Basic ' . base64_encode( 'c02_service:invalid-disposable' ) ) ),
			// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- Test-only Basic header exercises alternate-authentication denial.
			'normal-login-password' => array( '', array( 'Authorization: Basic ' . base64_encode( 'c02_human:' . $this->fixture['human_password'] ) ) ),
			'marker-cookie'         => array( 'marker-cookie', array() ),
		);
		foreach ( $modes as $name => $mode ) {
			// A successful foundation evaluation in another request cannot carry over.
			$valid = $this->request( '/wp-json/coagmentator/v1/site_info', 'POST', 'service-basic' );
			self::assertSame( 403, $valid['status'] );
			$probe = $this->request( '/c03a-observe.php' );
			self::assertSame( 1, json_decode( $probe['body'], true, 512, JSON_THROW_ON_ERROR )['outer'] );
			$before = $this->snapshot();
			$result = $this->request( '/wp-json/coagmentator/v1/site_info', 'POST', $mode[0], '', $mode[1] );
			self::assertSame( 403, $result['status'], $name );
			$this->closed_failure( $result['body'] );
			self::assertSame( $before, $this->snapshot(), 'Alternate request changed protected state.' );
			$probe    = $this->request( '/c03a-observe.php' );
			$observed = json_decode( $probe['body'], true, 512, JSON_THROW_ON_ERROR );
			self::assertNotEmpty( $observed['checks'], $name );
			self::assertNotContains( true, $observed['checks'], $name );
			self::assertSame( 0, $observed['events'], $name );
			self::assertSame( 0, $observed['outer'], $name );
		}
	}
}
