<?php
/**
 * Rich feature policy, independent from the MU guard's minimal denial registry.
 *
 * @package Coagmentator
 */

namespace Coagmentator\Config;

use Coagmentator\Guard\Guard_Config;
use Coagmentator\Guard\Route_Boundary;

/** Immutable closed policy. Parsing it does not enable any operation. */
final class Feature_Config {
	/**
	 * Validated bounded values; never a credential record.
	 *
	 * @var array<string, mixed> Validated bounded values; never a credential record.
	 */
	private array $values;
	/**
	 * Approved UUID window.
	 *
	 * @var Credential_Window Approved UUID window.
	 */
	private Credential_Window $credentials;
	/**
	 * Validated transport profile.
	 *
	 * @var Transport_Policy Transport profile.
	 */
	private Transport_Policy $transport;
	/**
	 * Trusted excluded code roots.
	 *
	 * @var list<string> Trusted excluded code roots.
	 */
	private array $excluded = array();

	/**
	 * Only the validating factory constructs policies.
	 *
	 * @param array<string, mixed> $values Validated fields.
	 * @param Credential_Window    $credentials Validated credential window.
	 * @param Transport_Policy     $transport Validated transport binding.
	 */
	private function __construct( array $values, Credential_Window $credentials, Transport_Policy $transport ) {
		$this->values      = $values;
		$this->credentials = $credentials;
		$this->transport   = $transport;
	}

	/**
	 * Load only the operator-defined constant, never an HTTP argument.
	 *
	 * @return self|null Valid policy or closed failure.
	 */
	public static function load(): ?self {
		if ( ! defined( 'COAGMENTATOR_FEATURE_CONFIG' ) || ! defined( 'COAGMENTATOR_GUARD_REGISTRY' ) || ! defined( 'ABSPATH' ) || ! is_string( COAGMENTATOR_FEATURE_CONFIG ) || ! is_string( COAGMENTATOR_GUARD_REGISTRY ) || ! is_string( ABSPATH ) ) {
			return null;
		}
		$excluded = array( ABSPATH, dirname( __DIR__, 4 ) );
		$path     = COAGMENTATOR_FEATURE_CONFIG;
		if ( ! Operator_Path::valid( $path, $excluded ) || ! is_file( $path ) || ! is_readable( $path ) ) {
			return null;
		}
		// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Bounded local host file; a read race must not disclose its path in a PHP warning.
		$bytes = @file_get_contents( $path, false, null, 0, 16385 );
		return is_string( $bytes ) ? self::parse( $bytes, new Guard_Config( COAGMENTATOR_GUARD_REGISTRY ), time(), $excluded ) : null;
	}

	/**
	 * Parse canonical compact JSON, also rejecting duplicate JSON members.
	 *
	 * @param string       $bytes Local configuration bytes, maximum 16 KiB.
	 * @param Guard_Config $registry Separately loaded protected registry.
	 * @param int          $now Trusted server clock.
	 * @param string[]     $excluded Web root and repository/package roots.
	 * @phpstan-param list<string> $excluded
	 * @return self|null Valid policy or closed failure.
	 */
	public static function parse( string $bytes, Guard_Config $registry, int $now, array $excluded ): ?self {
		if ( strlen( $bytes ) > 16384 || ! $registry->healthy() ) {
			return null;
		}
		$data = json_decode( trim( $bytes ), true, 8 );
		$keys = array( 'version', 'guard_api', 'enabled', 'site_id', 'actor_id', 'service_user_id', 'protected_user_ids', 'credential_uuids', 'rotation', 'home_origin', 'home_path', 'bridge_origin', 'bridge_path', 'transport', 'private_reads', 'read_operations', 'policy_version', 'approval_profile', 'writes_enabled', 'storage' );
		if ( ! is_array( $data ) || array_keys( $data ) !== $keys ) {
			return null;
		}
		// phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode -- Canonical host format detects duplicate members without normalizing identities.
		if ( json_encode( $data, JSON_UNESCAPED_SLASHES ) !== trim( $bytes ) || 1 !== $data['version'] || 1 !== $data['guard_api'] || ! is_bool( $data['enabled'] ) || ! is_bool( $data['private_reads'] ) || false !== $data['writes_enabled'] || ! Identity_Values::uuid( $data['site_id'] ) || ! Identity_Values::actor( $data['actor_id'] ) || ! Identity_Values::id( $data['service_user_id'] ) || ! Identity_Values::id( $data['policy_version'] ) || ! in_array( $data['approval_profile'], array( 'strict', 'trusted_single_operator' ), true ) ) {
			return null;
		}
		$ids = $data['protected_user_ids'];
		if ( ! is_array( $ids ) || ! array_is_list( $ids ) || count( $ids ) < 1 || count( $ids ) > 32 || ! in_array( $data['service_user_id'], $ids, true ) ) {
			return null;
		}
		$seen = array();
		foreach ( $ids as $id ) {
			if ( ! is_int( $id ) || ! Identity_Values::id( $id ) || in_array( $id, $seen, true ) || ! $registry->protects( $id ) ) {
				return null;
			}
			$seen[] = $id;
		}
		$window = Credential_Window::parse( $data['credential_uuids'], $data['rotation'], $now );
		if ( null === $window || ! is_array( $data['credential_uuids'] ) ) {
			return null;
		}
		foreach ( $data['credential_uuids'] as $uuid ) {
			if ( ! is_string( $uuid ) || ! $registry->lists( $uuid ) ) {
				return null;
			}
		}
		if ( ! self::origin( $data['home_origin'] ) || $data['bridge_origin'] !== $data['home_origin'] || ! is_string( $data['home_path'] ) || strlen( $data['home_path'] ) > 256 || 1 !== preg_match( '#^/(?:[A-Za-z0-9_-]+/)*$#D', $data['home_path'] ) || ! is_string( $data['bridge_path'] ) || rtrim( $data['home_path'], '/' ) . '/wp-json/coagmentator/v1' !== $data['bridge_path'] ) {
			return null;
		}
		$transport = Transport_Policy::parse( $data['transport'], $data['home_origin'], $data['bridge_path'] );
		if ( null === $transport ) {
			return null;
		}
		$operations = $data['read_operations'];
		if ( ! is_array( $operations ) || ! array_is_list( $operations ) || count( $operations ) > 9 ) {
			return null;
		}
		$seen = array();
		foreach ( $operations as $operation ) {
			if ( ! is_string( $operation ) || ! in_array( $operation, Route_Boundary::OPERATIONS, true ) || in_array( $operation, $seen, true ) ) {
				return null;
			}
			$seen[] = $operation;
		}
		$storage = $data['storage'];
		if ( ! is_array( $storage ) || array_keys( $storage ) !== array( 'cursor_key_file', 'audit_directory', 'admission_directory' ) ) {
			return null;
		}
		foreach ( $storage as $path ) {
			if ( ! Operator_Path::valid( $path, $excluded ) ) {
				return null;
			}
		}
		$config           = new self( $data, $window, $transport );
		$config->excluded = $excluded;
		return $config;
	}

	/**
	 * Canonical HTTPS DNS origin, without userinfo, paths, query or default port.
	 * Transport enforcement uses the separately validated profile.
	 *
	 * @param mixed $origin Operator value.
	 * @phpstan-assert-if-true string $origin
	 * @return bool Valid origin.
	 */
	private static function origin( mixed $origin ): bool {
		if ( ! is_string( $origin ) ) {
			return false;
		}
		// phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url -- Pure host configuration parser also runs without WordPress.
		$port = parse_url( $origin, PHP_URL_PORT );
		return strlen( $origin ) <= 253 && 1 === preg_match( '#^https://[a-z0-9](?:[a-z0-9-]*[a-z0-9])?(?:\.[a-z0-9](?:[a-z0-9-]*[a-z0-9])?)+(?::(?:[1-9][0-9]{0,4}))?$#D', $origin ) && ! str_ends_with( $origin, ':443' ) && ( null === $port || is_int( $port ) );
	}

	/**
	 * Immutable host transport binding.
	 *
	 * @return Transport_Policy Validated profile.
	 */
	public function transport(): Transport_Policy {
		return $this->transport;
	}

	/**
	 * Trusted configured site for operational accounting and restricted audit.
	 *
	 * @return string Canonical configured UUID.
	 */
	public function site(): string {
		return (string) $this->values['site_id'];
	}

	/**
	 * Fixed storage reference, never selected from HTTP data.
	 *
	 * @param string $name Internal storage field.
	 * @return string Validated reference or empty denial value.
	 */
	public function storage( string $name ): string {
		$storage = $this->values['storage'];
		return is_array( $storage ) && in_array( $name, array( 'audit_directory', 'admission_directory' ), true ) && is_string( $storage[ $name ] ?? null ) ? $storage[ $name ] : '';
	}

	/**
	 * Preserve the code/web exclusions used by the validating factory.
	 *
	 * @return string[] Trusted excluded roots.
	 * @phpstan-return list<string>
	 */
	public function excluded_roots(): array {
		return $this->excluded;
	}

	/**
	 * Identity only. Later capability/transport/admission checks still compose.
	 *
	 * @param int    $user Observed core user.
	 * @param string $uuid Observed credential UUID.
	 * @param string $site Claimed envelope site, checked only after authentication.
	 * @param string $actor Claimed envelope actor, never an authority grant.
	 * @param int    $now Trusted clock.
	 * @return bool Exact enabled identity binding.
	 */
	public function matches( int $user, string $uuid, string $site, string $actor, int $now ): bool {
		return true === $this->values['enabled'] && $user === $this->values['service_user_id'] && $site === $this->values['site_id'] && $actor === $this->values['actor_id'] && $this->credentials->allows( $uuid, $now );
	}

	/**
	 * Exact future read-operation and guard-prefix consistency.
	 *
	 * @param string $route Guard-admitted route.
	 * @param string $prefix Operator-pinned guard prefix.
	 * @return bool Enabled read name only, never a dispatch instruction.
	 */
	public function enables( string $route, string $prefix ): bool {
		return $this->values['bridge_path'] === $prefix . '/coagmentator/v1' && str_starts_with( $route, '/coagmentator/v1/' ) && is_array( $this->values['read_operations'] ) && in_array( substr( $route, strlen( '/coagmentator/v1/' ) ), $this->values['read_operations'], true );
	}
}
