<?php
/**
 * Real HTTPS guard assertions, using protected disposable runtime credentials.
 *
 * @package Coagmentator
 */

use PHPUnit\Framework\TestCase;

/** Shared runner, without any public provisioning endpoint. */
abstract class GuardHttpCase extends TestCase {
	/** @var array<string, mixed> Runtime-only fixture. */
	protected array $fixture;
	/** @var string Newly issued human session, memory only. */
	protected string $fresh_cookie = '';

	/** Read the protected local fixture, never output its contents. */
	protected function setUp(): void {
		$this->fixture = json_decode( file_get_contents( dirname( __DIR__, 2 ) . '/.runtime/c02-fixtures.json' ), true, 512, JSON_THROW_ON_ERROR );
	}

	/**
	 * Fixed test origin, verified TLS, no redirects or credential logging.
	 *
	 * @param string       $path Test path.
	 * @param string       $method HTTP method.
	 * @param string       $auth Runtime credential selector.
	 * @param string       $body Test payload.
	 * @param list<string> $headers Extra test headers.
	 * @return array{status:int,body:string}
	 */
	protected function request( string $path, string $method = 'GET', string $auth = '', string $body = '', array $headers = array() ): array {
		$client  = curl_init( 'https://wordpress.test' . $path );
		$options = array( CURLOPT_RETURNTRANSFER => true, CURLOPT_CAINFO => C01_CA, CURLOPT_TIMEOUT => 10, CURLOPT_FOLLOWLOCATION => false, CURLOPT_CUSTOMREQUEST => $method, CURLOPT_PATH_AS_IS => true, CURLOPT_COOKIEFILE => '' );
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
		return array( 'status' => curl_getinfo( $client, CURLINFO_RESPONSE_CODE ), 'body' => is_string( $response ) ? $response : '' );
	}

	/** @return array<string, mixed> Safe hashes and callback counters. */
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
	 * @param string       $path Target.
	 * @param string       $method Method.
	 * @param string       $auth Credential selector.
	 * @param string       $body Payload.
	 * @param list<string> $headers Headers.
	 */
	protected function denied( string $path, string $method = 'GET', string $auth = 'service-basic', string $body = '', array $headers = array() ): void {
		$before = $this->snapshot();
		$result = $this->request( $path, $method, $auth, $body, $headers );
		self::assertContains( $result['status'], array( 400, 401, 403, 404, 405 ), 'Hostile request must be denied.' );
		self::assertSame( $before, $this->snapshot(), 'Denied request executed a callback or changed protected state.' );
	}
}
