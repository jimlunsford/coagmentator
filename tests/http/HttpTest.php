<?php
/**
 * Actual TLS and WordPress edge tests.
 *
 * @package Coagmentator
 */

use PHPUnit\Framework\TestCase;

/** The test network has no published ports or external route. */
final class HttpTest extends TestCase {
	/**
	 * Request the fixed test edge with normal TLS verification.
	 *
	 * @param string $path Fixed test path.
	 * @return array{0:string|false,1:int,2:int}
	 */
	private function request( string $path ): array {
		$client = curl_init( 'https://wordpress.test' . $path );
		curl_setopt_array( $client, array( CURLOPT_RETURNTRANSFER => true, CURLOPT_CAINFO => C01_CA, CURLOPT_TIMEOUT => 10, CURLOPT_FOLLOWLOCATION => false, CURLOPT_HTTPHEADER => array( 'Authorization: C01 noncredential-sentinel' ) ) );
		$body   = curl_exec( $client );
		$status = curl_getinfo( $client, CURLINFO_RESPONSE_CODE );
		$error  = curl_errno( $client );
		return array( $body, $status, $error );
	}

	/** WordPress is served through Nginx, TLS and FPM with verified trust. */
	public function test_wordpress_and_header(): void {
		list( $body, $status, $error ) = $this->request( '/c01-probe.php' );
		self::assertSame( 0, $error );
		self::assertSame( 200, $status );
		self::assertSame( array( 'wordpress' => true, 'database' => true, 'authorization_preserved' => true, 'service_users_absent' => true, 'credentials_absent' => true ), json_decode( $body, true, 512, JSON_THROW_ON_ERROR ) );
		list( $body, $status, $error ) = $this->request( '/wp-login.php' );
		self::assertSame( 0, $error );
		self::assertSame( 200, $status );
		self::assertStringContainsString( 'loginform', $body );
	}

	/** The test edge cannot satisfy an untrusted chain. */
	public function test_untrusted_ca_is_rejected(): void {
		$client = curl_init( 'https://wordpress.test/wp-login.php' );
		curl_setopt_array( $client, array( CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 10 ) );
		self::assertFalse( curl_exec( $client ) );
		self::assertSame( CURLE_PEER_FAILED_VERIFICATION, curl_errno( $client ) );
	}

	/** A trusted CA does not waive hostname verification. */
	public function test_wrong_hostname_is_rejected(): void {
		$client = curl_init( 'https://edge/wp-login.php' );
		curl_setopt_array( $client, array( CURLOPT_RETURNTRANSFER => true, CURLOPT_CAINFO => C01_CA, CURLOPT_TIMEOUT => 10 ) );
		self::assertFalse( curl_exec( $client ) );
		self::assertSame( CURLE_PEER_FAILED_VERIFICATION, curl_errno( $client ) );
	}
}
