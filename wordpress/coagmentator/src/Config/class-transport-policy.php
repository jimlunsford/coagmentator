<?php
/**
 * Closed deployment transport binding, not a general proxy parser.
 *
 * @package Coagmentator
 */

namespace Coagmentator\Config;

use Coagmentator\Guard\Route_Boundary;

/** Exact host-managed peers, origin and operation paths. */
final class Transport_Policy {

	/**
	 * Validated deployment values.
	 *
	 * @param string   $mode Direct TLS or trusted proxy.
	 * @param string[] $peers Canonical exact addresses.
	 * @phpstan-param list<string> $peers
	 * @param string   $origin Canonical HTTPS origin.
	 * @param string   $path Canonical bridge path.
	 */
	private function __construct( private string $mode, private array $peers, private string $origin, private string $path ) {}

	/**
	 * Validate the complete transport object after origin/path validation.
	 *
	 * @param mixed  $value Host configuration value.
	 * @param string $origin Validated canonical origin.
	 * @param string $path Validated canonical bridge path.
	 * @return self|null Valid policy or denial.
	 */
	public static function parse( mixed $value, string $origin, string $path ): ?self {
		if ( ! is_array( $value ) || array_keys( $value ) !== array( 'mode', 'trusted_proxies' ) || ! in_array( $value['mode'], array( 'direct_tls', 'trusted_proxy' ), true ) || ! is_array( $value['trusted_proxies'] ) || ! array_is_list( $value['trusted_proxies'] ) || count( $value['trusted_proxies'] ) > 16 ) {
			return null;
		}
		$peers = array();
		foreach ( $value['trusted_proxies'] as $peer ) {
			if ( ! self::address( $peer ) || in_array( $peer, $peers, true ) ) {
				return null;
			}
			$peers[] = $peer;
		}
		if ( ( 'direct_tls' === $value['mode'] && array() !== $peers ) || ( 'trusted_proxy' === $value['mode'] && array() === $peers ) ) {
			return null;
		}
		return new self( $value['mode'], $peers, $origin, $path );
	}

	/**
	 * Canonical single IP only. No CIDR, wildcard, zone or mapped-IPv4 aliases.
	 *
	 * @param mixed $value Host value or immediate peer.
	 * @phpstan-assert-if-true string $value
	 * @return bool Exact address representation.
	 */
	private static function address( mixed $value ): bool {
		if ( ! is_string( $value ) || strlen( $value ) > 45 || false === filter_var( $value, FILTER_VALIDATE_IP ) || in_array( $value, array( '0.0.0.0', '::' ), true ) ) {
			return false;
		}
		$packed = inet_pton( $value );
		return false !== $packed && inet_ntop( $packed ) === $value && ! ( 16 === strlen( $packed ) && substr( $packed, 0, 12 ) === str_repeat( "\0", 10 ) . "\xff\xff" );
	}

	/**
	 * Whether an early host bootstrap must normalize proxy HTTPS for core.
	 *
	 * @return bool Proxy mode.
	 */
	public function proxied(): bool {
		return 'trusted_proxy' === $this->mode;
	}

	/**
	 * Evaluate raw server facts. No returned identity or reusable grant.
	 *
	 * @param array<string, mixed> $server Web-server supplied request state.
	 * @param string               $route Fixed route from trusted code.
	 * @return bool Transport binding satisfied.
	 */
	public function allows( array $server, string $route ): bool {
		$prefix = substr( $this->path, 0, -strlen( '/coagmentator/v1' ) );
		return Route_Boundary::canonical( $route, 'POST', $server, $prefix ) && $this->secure_connection( $server );
	}

	/**
	 * Scheme, authority and peer only, also usable by the early host bootstrap.
	 * This never authorizes a path or an identity.
	 *
	 * @param array<string, mixed> $server Web-server supplied state.
	 * @return bool Trusted HTTPS connection to the canonical authority.
	 */
	public function secure_connection( array $server ): bool {
		$authority = substr( $this->origin, strlen( 'https://' ) );
		if ( ( $server['HTTP_HOST'] ?? null ) !== $authority ) {
			return false;
		}
		// Neither RFC Forwarded nor a separate port is an alternate authority.
		foreach ( array( 'HTTP_FORWARDED', 'HTTP_X_FORWARDED_PORT' ) as $header ) {
			if ( isset( $server[ $header ] ) && '' !== $server[ $header ] ) {
				return false;
			}
		}
		if ( $this->proxied() ) {
			$peer = $server['REMOTE_ADDR'] ?? null;
			return self::address( $peer ) && in_array( $peer, $this->peers, true ) && ( $server['HTTP_X_FORWARDED_PROTO'] ?? null ) === 'https' && ( $server['HTTP_X_FORWARDED_HOST'] ?? null ) === $authority;
		}
		foreach ( array( 'HTTP_X_FORWARDED_PROTO', 'HTTP_X_FORWARDED_HOST' ) as $header ) {
			if ( isset( $server[ $header ] ) && '' !== $server[ $header ] ) {
				return false;
			}
		}
		// SERVER_PORT alone is not proof of TLS. HTTPS must be set by the server.
		return in_array( $server['HTTPS'] ?? null, array( 'on', '1' ), true );
	}
}
