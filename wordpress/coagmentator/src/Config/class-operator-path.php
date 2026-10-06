<?php
/**
 * Fixed host path syntax and containment, without opening future storage.
 *
 * @package Coagmentator
 */

namespace Coagmentator\Config;

/** Request data never selects a configuration or storage path. */
final class Operator_Path {
	/**
	 * Reject ambiguous/relative paths and resolved web/package containment.
	 * Existing ancestors are resolved to catch symlink escapes for future paths.
	 *
	 * @param mixed        $path Operator value.
	 * @param list<string> $excluded Web root and code roots.
	 * @return bool Safe absolute reference, not a storage-readiness claim.
	 */
	public static function valid( mixed $path, array $excluded ): bool {
		if ( ! is_string( $path ) || strlen( $path ) > 512 || 1 !== preg_match( '#^/(?:[A-Za-z0-9_-][A-Za-z0-9_.-]*/)*[A-Za-z0-9_-][A-Za-z0-9_.-]*$#D', $path ) ) {
			return false;
		}
		$ancestor = $path;
		$suffix   = '';
		while ( ! file_exists( $ancestor ) && ! is_link( $ancestor ) && '/' !== $ancestor ) {
			$suffix   = '/' . basename( $ancestor ) . $suffix;
			$ancestor = dirname( $ancestor );
		}
		$resolved = realpath( $ancestor );
		if ( false === $resolved ) {
			return false;
		}
		$resolved = rtrim( $resolved, '/' ) . $suffix;
		foreach ( $excluded as $root ) {
			$real_root = realpath( $root );
			$root      = rtrim( false === $real_root ? $root : $real_root, '/' );
			if ( '' === $root || $path === $root || str_starts_with( $path, $root . '/' ) || $resolved === $root || str_starts_with( $resolved, $root . '/' ) ) {
				return false;
			}
		}
		return true;
	}
}
