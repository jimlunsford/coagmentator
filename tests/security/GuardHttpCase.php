<?php
/**
 * Real HTTPS guard assertions, using protected disposable runtime credentials.
 *
 * @package Coagmentator
 */

use PHPUnit\Framework\TestCase;

/**
 * Shared runner, without any public provisioning endpoint.
 */
abstract class GuardHttpCase extends TestCase {
	/**
	 * Runtime-only fixture.
	 *
	 * @var array<string, mixed> Runtime-only fixture.
	 */
	protected array $fixture;
	/**
	 * Newly issued human session, memory only.
	 *
	 * @var string Newly issued human session, memory only.
	 */
	protected string $fresh_cookie = '';

	/**
	 * Read the protected local fixture, never output its contents.
	 */
	protected function setUp(): void {
		$this->fixture = json_decode( file_get_contents( dirname( __DIR__, 2 ) . '/.runtime/c02-fixtures.json' ), true, 512, JSON_THROW_ON_ERROR );
	}

	/**
	 * Fixed test origin, verified TLS, no redirects or credential logging.
	 *
	 * @param string $path Test path.
	 * @param string $method HTTP method.
	 * @param string $auth Runtime credential selector.
	 * @param string $body Test payload.
	 * @param array  $headers Extra test headers.
	 * @return array{status:int,body:string}
	 */
	protected function request( string $path, string $method = 'GET', string $auth = '', string $body = '', array $headers = array() ): array {
		$client  = curl_init( 'https://wordpress.test' . $path );
		$options = array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CAINFO         => C01_CA,
			CURLOPT_TIMEOUT        => 10,
			CURLOPT_FOLLOWLOCATION => false,
			CURLOPT_CUSTOMREQUEST  => $method,
			CURLOPT_PATH_AS_IS     => true,
			CURLOPT_COOKIEFILE     => '',
		);
		if ( 'HEAD' === $method ) {
			$options[ CURLOPT_NOBODY ] = true;
		}
		if ( str_ends_with( $auth, '-basic' ) ) {
			$kind                        = substr( $auth, 0, -6 );
			$options[ CURLOPT_USERPWD ]  = 'c02_' . $kind . ':' . $this->fixture[ $kind . '_secret' ];
			$options[ CURLOPT_HTTPAUTH ] = CURLAUTH_BASIC;
		} elseif ( 'fresh-human-cookie' === $auth ) {
			$options[ CURLOPT_COOKIE ] = $this->fresh_cookie;
		} elseif ( str_ends_with( $auth, '-cookie' ) ) {
			$options[ CURLOPT_COOKIE ] = $this->fixture[ substr( $auth, 0, -7 ) . '_cookie' ];
		}
		if ( 'POST' === $method || '' !== $body ) {
			$options[ CURLOPT_POSTFIELDS ] = $body;
		}
		$options[ CURLOPT_HTTPHEADER ] = $headers;
		curl_setopt_array( $client, $options );
		$response = curl_exec( $client );
		if ( '/wp-login.php' === $path && 'POST' === $method ) {
			$cookies = array();
			foreach ( curl_getinfo( $client, CURLINFO_COOKIELIST ) as $cookie ) {
				$parts = explode( "\t", $cookie );
				if ( count( $parts ) >= 7 && str_starts_with( $parts[5], 'wordpress_' ) ) {
					$cookies[] = $parts[5] . '=' . $parts[6];
				}
			}
			$this->fresh_cookie = implode( '; ', $cookies );
		}
		self::assertSame( 0, curl_errno( $client ), 'Verified test transport failed.' );
		return array(
			'status' => curl_getinfo( $client, CURLINFO_RESPONSE_CODE ),
			'body'   => is_string( $response ) ? $response : '',
		);
	}

	/**
	 * Safe hashes and callback counters.
	 *
	 * @return array<string, mixed> Safe hashes and callback counters.
	 */
	protected function snapshot(): array {
		$response = $this->request( '/c02-observe.php' );
		self::assertSame( 200, $response['status'] );
		$data = json_decode( $response['body'], true, 512, JSON_THROW_ON_ERROR );
		unset( $data['nonce'], $data['guard'] );
		return $data;
	}

	/**
	 * HTTP denial plus independent no-callback and no-state-change proof.
	 *
	 * @param string $path Target.
	 * @param string $method Method.
	 * @param string $auth Credential selector.
	 * @param string $body Payload.
	 * @param array  $headers Headers.
	 */
	protected function denied( string $path, string $method = 'GET', string $auth = 'service-basic', string $body = '', array $headers = array() ): void {
		$before = $this->snapshot();
		$result = $this->request( $path, $method, $auth, $body, $headers );
		self::assertContains( $result['status'], array( 400, 401, 403, 404, 405 ), 'Hostile request must be denied.' );
		self::assertSame( $before, $this->snapshot(), 'Denied request executed a callback or changed protected state.' );
		if ( '/wp-json/coagmentator/v1/site_info' === $path && 'POST' === $method && '' === $body && array() === $headers ) {
			$this->closed_failure( $result['body'] );
		}
	}
	/**
	 * Minimal MU failure encoder must not leak the incoming core error.
	 *
	 * @param string $body JSON response.
	 */
	protected function closed_failure( string $body ): void {
		$data = json_decode( $body, true, 512, JSON_THROW_ON_ERROR );
		self::assertSame( array( 'ok', 'contract_version', 'correlation_id', 'site_id', 'error', 'receipt' ), array_keys( $data ) );
		self::assertFalse( $data['ok'] );
		self::assertNull( $data['site_id'] );
		self::assertNull( $data['receipt'] );
		self::assertSame( 'not_applied', $data['error']['write_state'] );
		self::assertSame( array( 'code', 'message', 'origin', 'retryable', 'retry_after_seconds', 'write_state', 'details' ), array_keys( $data['error'] ) );
		self::assertSame( 'AUTHORIZATION_DENIED', $data['error']['code'] );
	}

	/**
	 * Verify new and existing real human sessions, plus public traffic.
	 */
	protected function human_recovery(): void {
		$result = $this->request( '/wp-login.php' );
		self::assertSame( 200, $result['status'] );
		self::assertStringContainsString( 'loginform', $result['body'] );
		$result = $this->request(
			'/wp-login.php',
			'POST',
			'',
			http_build_query(
				array(
					'log'         => 'c02_human',
					'pwd'         => $this->fixture['human_password'],
					'redirect_to' => 'https://wordpress.test/wp-admin/',
					'testcookie'  => '1',
				)
			),
			array( 'Content-Type: application/x-www-form-urlencoded', 'Cookie: wordpress_test_cookie=WP%20Cookie%20check' )
		);
		self::assertSame( 302, $result['status'], 'Human password authentication must create a session.' );
		self::assertNotSame( '', $this->fresh_cookie, 'Password login must issue a real session cookie.' );
		$result = $this->request( '/wp-admin/', 'GET', 'fresh-human-cookie' );
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
