<?php
/**
 * A04 closed transport parsing and immediate-peer classification.
 *
 * @package Coagmentator
 */

use Coagmentator\Config\Transport_Policy;
use PHPUnit\Framework\TestCase;

/** HTTP/TLS acceptance remains the real-edge suite, not these pure vectors. */
final class TransportPolicyTest extends TestCase {

	/**
	 * Explicit nonsecret deployment fixture.
	 *
	 * @param string $mode Profile.
	 * @param array  $peers Exact peers.
	 * @param string $path Bridge path.
	 * @return Transport_Policy Valid fixture.
	 */
	private function policy( string $mode = 'direct_tls', array $peers = array(), string $path = '/wp-json/coagmentator/v1' ): Transport_Policy {
		$policy = Transport_Policy::parse(
			array(
				'mode'            => $mode,
				'trusted_proxies' => $peers,
			),
			'https://wordpress.test',
			$path
		);
		self::assertNotNull( $policy );
		return $policy;
	}

	/**
	 * Raw transport fixture.
	 *
	 * @return array<string, string> No credential data.
	 */
	private function server(): array {
		return array(
			'HTTPS'          => 'on',
			'HTTP_HOST'      => 'wordpress.test',
			'REMOTE_ADDR'    => '192.0.2.10',
			'REQUEST_METHOD' => 'POST',
			'REQUEST_URI'    => '/wp-json/coagmentator/v1/site_info',
			'QUERY_STRING'   => '',
		);
	}

	/** Missing, extra and inconsistent fields cannot silently select trust. */
	public function test_closed_modes_and_exact_addresses(): void {
		$invalid = array( null, array(), array( 'mode' => 'direct_tls' ) );
		foreach ( array( 'auto', 'https', '', 1, true ) as $mode ) {
			$invalid[] = array(
				'mode'            => $mode,
				'trusted_proxies' => array(),
			);
		}
		foreach ( array( array(), array( '*' ), array( '0.0.0.0/0' ), array( '192.0.2.0/24' ), array( 'proxy.test' ), array( '192.0.2.010' ), array( '2001:DB8::1' ), array( '::ffff:192.0.2.1' ), array( 'fe80::1%eth0' ), array( '0.0.0.0' ), array( '::' ), array( 1 ), array( '192.0.2.10', '192.0.2.10' ), array_fill( 0, 17, '192.0.2.10' ), array( 'peer' => '192.0.2.10' ) ) as $peers ) {
			$invalid[] = array(
				'mode'            => 'trusted_proxy',
				'trusted_proxies' => $peers,
			);
		}
		$invalid[] = array(
			'mode'            => 'direct_tls',
			'trusted_proxies' => array( '192.0.2.10' ),
		);
		$invalid[] = array(
			'mode'            => 'direct_tls',
			'trusted_proxies' => array(),
			'caller_trust'    => true,
		);
		foreach ( $invalid as $value ) {
			self::assertNull( Transport_Policy::parse( $value, 'https://wordpress.test', '/wp-json/coagmentator/v1' ) );
		}
		self::assertTrue( $this->policy( 'trusted_proxy', array( '2001:db8::1' ) )->proxied() );
	}

	/** Port 443 or forged headers cannot supply direct TLS evidence. */
	public function test_direct_requires_real_https_and_canonical_authority(): void {
		$policy = $this->policy();
		$server = $this->server();
		self::assertTrue( $policy->allows( $server, '/coagmentator/v1/site_info' ) );
		foreach ( array( '', 'off', 'https', true, 1, null ) as $https ) {
			$bad                = $server;
			$bad['HTTPS']       = $https;
			$bad['SERVER_PORT'] = '443';
			self::assertFalse( $policy->allows( $bad, '/coagmentator/v1/site_info' ) );
		}
		foreach ( array( 'HTTP_FORWARDED', 'HTTP_X_FORWARDED_PROTO', 'HTTP_X_FORWARDED_HOST', 'HTTP_X_FORWARDED_PORT' ) as $header ) {
			$bad            = $server;
			$bad[ $header ] = 'https';
			self::assertFalse( $policy->allows( $bad, '/coagmentator/v1/site_info' ) );
		}
		foreach ( array( 'evil.test', 'wordpress.test:443', 'WORDPRESS.test', 'wordpress.test,evil.test', "wordpress.test\r\n" ) as $host ) {
			$bad              = $server;
			$bad['HTTP_HOST'] = $host;
			self::assertFalse( $policy->allows( $bad, '/coagmentator/v1/site_info' ) );
		}
	}

	/** Only a configured immediate peer may supply one exact authority pair. */
	public function test_proxy_is_conjunctive_and_rejects_ambiguous_headers(): void {
		$policy                           = $this->policy( 'trusted_proxy', array( '192.0.2.10' ) );
		$server                           = $this->server();
		$server['HTTPS']                  = 'off';
		$server['HTTP_X_FORWARDED_PROTO'] = 'https';
		$server['HTTP_X_FORWARDED_HOST']  = 'wordpress.test';
		self::assertTrue( $policy->allows( $server, '/coagmentator/v1/site_info' ) );
		$invalid = array(
			'REMOTE_ADDR'            => array( '192.0.2.11', 'proxy.test', '', '::ffff:192.0.2.10' ),
			'HTTP_X_FORWARDED_PROTO' => array( 'http', 'HTTPS', 'https,http', 'https, https', ' https', null ),
			'HTTP_X_FORWARDED_HOST'  => array( 'evil.test', 'wordpress.test,evil.test', 'wordpress.test, wordpress.test', 'wordpress.test:443', null ),
			'HTTP_HOST'              => array( 'evil.test' ),
			'HTTP_FORWARDED'         => array( 'proto=https;host=wordpress.test' ),
			'HTTP_X_FORWARDED_PORT'  => array( '443' ),
		);
		foreach ( $invalid as $key => $values ) {
			foreach ( $values as $value ) {
				$bad         = $server;
				$bad[ $key ] = $value;
				self::assertFalse( $policy->allows( $bad, '/coagmentator/v1/site_info' ), $key );
			}
		}
	}

	/** Subdirectory support retains exact operation matching and raw bytes. */
	public function test_subdirectory_has_no_prefix_or_encoding_aliases(): void {
		$policy                = $this->policy( 'direct_tls', array(), '/journal/wp-json/coagmentator/v1' );
		$server                = $this->server();
		$server['REQUEST_URI'] = '/journal/wp-json/coagmentator/v1/site_info';
		self::assertTrue( $policy->allows( $server, '/coagmentator/v1/site_info' ) );
		foreach ( array( '/wp-json/coagmentator/v1/site_info', '/journal-extra/wp-json/coagmentator/v1/site_info', '/journal/wp-json/coagmentator/v1/site_info/', '/journal//wp-json/coagmentator/v1/site_info', '/journal/%77p-json/coagmentator/v1/site_info', '/journal/wp-json/coagmentator/v1/get_mutation', '/journal/wp-json/coagmentator/v1/site_info?x=1' ) as $uri ) {
			$server['REQUEST_URI'] = $uri;
			self::assertFalse( $policy->allows( $server, '/coagmentator/v1/site_info' ) );
		}
	}
}
