<?php
/**
 * Package runtime prerequisites, without enabling bridge behavior.
 *
 * @package Coagmentator
 */

namespace Coagmentator;

/** Evaluates facts supplied by the package loader. */
final class Environment {
	/**
	 * Return stable prerequisite failure identifiers.
	 *
	 * @param string       $php_version PHP runtime version.
	 * @param string       $wp_version WordPress version.
	 * @param int          $integer_bytes Native integer size.
	 * @param list<string> $extensions Loaded extension names.
	 * @param bool         $multisite Whether multisite is enabled.
	 * @return list<string>
	 */
	public static function issues( string $php_version, string $wp_version, int $integer_bytes, array $extensions, bool $multisite ): array {
		$issues = array();
		if ( version_compare( $php_version, '8.3', '<' ) || version_compare( $php_version, '8.6', '>=' ) ) {
			$issues[] = 'unsupported_php';
		}
		if ( version_compare( $wp_version, '7.1.2', '<' ) ) {
			$issues[] = 'unsupported_wordpress';
		}
		if ( 8 !== $integer_bytes ) {
			$issues[] = 'unsupported_integer_size';
		}
		foreach ( array( 'json', 'hash', 'sodium', 'mbstring', 'mysqli', 'fileinfo' ) as $extension ) {
			if ( ! in_array( $extension, $extensions, true ) ) {
				$issues[] = 'missing_' . $extension;
			}
		}
		if ( $multisite ) {
			$issues[] = 'unsupported_multisite';
		}
		return $issues;
	}
}
