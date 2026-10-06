<?php
/**
 * A04 and stripped-Authorization A02 evidence through real HTTP/TLS edges.
 *
 * @package Coagmentator
 */

/** Fresh host-selected scenarios share no request-selected trust settings. */
final class TransportTest extends GuardHttpCase {

	/**
	 * Protected credentials remain in memory, never assertions or evidence.
	 *
	 * @param string $scenario Trusted fixture scenario.
	 * @return array{status:int,error:int,redirects:int,body:string} Safe result.
	 */
	private function transport_request( string $scenario ): array {
		$path = '/wp-json/coagmentator/v1/site_info';
		if ( 'subdirectory' === $scenario ) {
			$path = '/journal' . $path;
		}
		$origin = 'https://wordpress.test';
		if ( in_array( $scenario, array( 'direct-http', 'direct-forged-proto', 'proxy-http' ), true ) ) {
			$origin = 'http://wordpress.test';
		} elseif ( 'proxy-untrusted' === $scenario ) {
			$origin = 'http://origin:8080';
		} elseif ( 'wrong-certificate-host' === $scenario ) {
			$origin = 'https://edge';
		}
		$headers = array( 'Content-Type: application/json' );
		if ( 'proxy-untrusted' === $scenario ) {
			$headers = array_merge( $headers, array( 'Host: wordpress.test', 'X-Forwarded-Proto: https', 'X-Forwarded-Host: wordpress.test', 'X-Forwarded-For: ' . gethostbyname( 'edge' ) ) );
		} elseif ( 'direct-forged-proto' === $scenario ) {
			$headers[] = 'X-Forwarded-Proto: https';
		} elseif ( 'direct-forged-host' === $scenario ) {
			$headers[] = 'X-Forwarded-Host: wordpress.test';
		} elseif ( 'proxy-spoof' === $scenario ) {
			$headers = array_merge( $headers, array( 'X-Forwarded-Proto: http', 'X-Forwarded-Proto: https', 'X-Forwarded-Host: evil.test', 'X-Forwarded-Host: second.test', 'Forwarded: proto=http;host=evil.test', 'X-Forwarded-Port: 81', 'X-Forwarded-For: 192.0.2.99' ) );
		} elseif ( 'wrong-request-host' === $scenario ) {
			$headers[] = 'Host: evil.test';
		}
		$options = array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT        => 10,
			CURLOPT_FOLLOWLOCATION => false,
			CURLOPT_SSL_VERIFYPEER => true,
			CURLOPT_SSL_VERIFYHOST => 2,
			CURLOPT_CUSTOMREQUEST  => 'POST',
			CURLOPT_POSTFIELDS     => '',
			CURLOPT_PATH_AS_IS     => true,
			CURLOPT_PROXY          => '',
		);
		if ( 'invalid-certificate' !== $scenario ) {
			$options[ CURLOPT_CAINFO ] = C01_CA;
		}
		if ( 'proxy-alternate-authorization' !== $scenario ) {
			$options[ CURLOPT_USERPWD ]  = 'c02_service:' . $this->fixture['service_secret'];
			$options[ CURLOPT_HTTPAUTH ] = CURLAUTH_BASIC;
		}
		if ( 'proxy-strip-cookie' === $scenario ) {
			$probe = $this->request( '/c02-observe.php', 'GET', 'human-cookie' );
			$nonce = json_decode( $probe['body'], true, 512, JSON_THROW_ON_ERROR )['nonce'];
			self::assertNotSame( '', $nonce );
			$headers[]                 = 'X-WP-Nonce: ' . $nonce;
			$options[ CURLOPT_COOKIE ] = $this->fixture['human_cookie'];
		} elseif ( 'proxy-strip-injected' === $scenario ) {
			$headers[] = 'X-C03A-User: human';
		} elseif ( 'proxy-alternate-authorization' === $scenario ) {
			// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- One runtime-only alternate-header denial vector; never printed or persisted.
			$alternate = 'Basic ' . base64_encode( 'c02_service:' . $this->fixture['service_secret'] );
			foreach ( array( 'X-Authorization', 'X-Original-Authorization', 'Proxy-Authorization' ) as $header ) {
				$headers[] = $header . ': ' . $alternate;
			}
			unset( $alternate );
		}
		$options[ CURLOPT_HTTPHEADER ] = $headers;
		$client                        = curl_init( $origin . $path );
		curl_setopt_array( $client, $options );
		$body = curl_exec( $client );
		return array(
			'status'    => curl_getinfo( $client, CURLINFO_RESPONSE_CODE ),
			'error'     => curl_errno( $client ),
			'redirects' => curl_getinfo( $client, CURLINFO_REDIRECT_COUNT ),
			'body'      => is_string( $body ) ? $body : '',
		);
	}

	/** Denial is backed by independent callbacks, identity checks and state. */
	public function test_transport_scenario(): void {
		$scenario = $this->fixture['c03b_scenario'];
		$accepted = in_array( $scenario, array( 'direct', 'proxy', 'proxy-spoof', 'subdirectory' ), true );
		if ( 'redirect' === $scenario ) {
			// Prove the other destination is reachable with verified TLS, without
			// credentials. Its counter must not move when the bridge redirects.
			$control = curl_init( 'https://redirect.test/c03b-sink.php' );
			curl_setopt_array(
				$control,
				array(
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_CAINFO         => C01_CA,
					CURLOPT_TIMEOUT        => 10,
					CURLOPT_FOLLOWLOCATION => false,
					CURLOPT_PROXY          => '',
				)
			);
			curl_exec( $control );
			self::assertSame( 0, curl_errno( $control ) );
			self::assertSame( 403, curl_getinfo( $control, CURLINFO_RESPONSE_CODE ) );
		}
		$before = $this->snapshot();
		$result = $this->transport_request( $scenario );
		if ( in_array( $scenario, array( 'invalid-certificate', 'wrong-certificate-host' ), true ) ) {
			self::assertSame( CURLE_SSL_CACERT, $result['error'], 'Actual client must reject certificate verification.' );
			self::assertSame( 0, $result['status'] );
		} else {
			self::assertSame( 0, $result['error'], 'Transport fixture failed.' );
			if ( 'subdirectory-alias' === $scenario ) {
				self::assertContains( $result['status'], array( 403, 404 ), 'The root alias must remain denied.' );
			} else {
				self::assertSame( 'redirect' === $scenario ? 307 : 403, $result['status'], 'No synthetic bridge success is allowed.' );
			}
		}
		self::assertSame( 0, $result['redirects'], 'Credential-bearing redirects must never be followed.' );
		self::assertSame( $before, $this->snapshot(), 'Transport request changed protected state or executed a prohibited target.' );
		$response = $this->request( '/c03b-observe.php' );
		self::assertSame( 200, $response['status'] );
		$observed = json_decode( $response['body'], true, 512, JSON_THROW_ON_ERROR );
		self::assertSame( 'redirect' === $scenario ? 1 : 0, $observed['sink'], 'No redirected request or Authorization replay may reach the destination.' );
		$identity = $observed['identity'];
		self::assertSame( $accepted, in_array( true, $identity['checks'] ?? array(), true ), 'Conjunctive identity result: ' . $scenario );
		self::assertSame( $accepted ? 1 : 0, $identity['outer'] ?? 0, 'Synthetic callback count: ' . $scenario );
		if ( $accepted ) {
			self::assertSame( 1, $identity['events'], 'Authorization must survive and authenticate in core.' );
			self::assertContains( true, $identity['transport_checks'] );
			self::assertTrue( $identity['stale_denied'] );
			$this->closed_failure( $result['body'] );
		}
		if ( str_starts_with( $scenario, 'proxy-strip' ) || in_array( $scenario, array( 'proxy-alternate-authorization', 'app-disabled', 'app-user-disabled' ), true ) ) {
			self::assertSame( 0, $identity['events'], 'Stripped or unavailable authentication cannot fall back.' );
			self::assertNotEmpty( $identity['checks'], 'Identity foundation must actually be evaluated.' );
		}
		if ( 'subdirectory' === $scenario || 'subdirectory-alias' === $scenario ) {
			self::assertSame( 'https://wordpress.test/journal', $observed['home'] );
			self::assertSame( 'https://wordpress.test/journal', $observed['siteurl'] );
			self::assertSame( '/journal/wp-json', $observed['prefix'] );
		}
	}
}
