<?php
/**
 * Immutable future read surface, without registering a handler.
 *
 * @package Coagmentator
 */

namespace Coagmentator\Guard;

/** Compares raw transport and REST request identity without normalization. */
final class Route_Boundary {
	/** @var list<string> Fixed operations. */
	public const OPERATIONS = array( 'site_info', 'search_content', 'get_content', 'list_terms', 'search_media', 'get_media', 'get_metadata', 'list_revisions', 'get_revision' );

	/**
	 * Exact canonical route.
	 *
	 * @param string $route REST path.
	 * @return bool Known read path.
	 */
	public static function known( string $route ): bool {
		foreach ( self::OPERATIONS as $operation ) {
			if ( '/coagmentator/v1/' . $operation === $route ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Compare raw bytes, rejecting aliases instead of decoding them.
	 *
	 * @param string               $route REST route.
	 * @param string               $method REST method.
	 * @param array<string, mixed> $server Server variables.
	 * @param string               $prefix Operator-selected pretty REST prefix.
	 * @return bool Canonical transport.
	 */
	public static function canonical( string $route, string $method, array $server, string $prefix ): bool {
		if ( ! self::known( $route ) || 'POST' !== $method || ( $server['REQUEST_METHOD'] ?? null ) !== 'POST' || ( $server['REQUEST_URI'] ?? null ) !== $prefix . $route || '' !== ( $server['QUERY_STRING'] ?? '' ) ) {
			return false;
		}
		foreach ( array( 'HTTP_X_HTTP_METHOD_OVERRIDE', 'HTTP_X_METHOD_OVERRIDE', 'HTTP_X_HTTP_METHOD', 'HTTP_CONTENT_ENCODING', 'CONTENT_ENCODING' ) as $key ) {
			if ( isset( $server[ $key ] ) ) {
				return false;
			}
		}
		return true;
	}
}
