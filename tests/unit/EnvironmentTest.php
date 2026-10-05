<?php
/**
 * Runtime prerequisite tests.
 *
 * @package Coagmentator
 */

use Coagmentator\Environment;
use PHPUnit\Framework\TestCase;

/** Exercises the package admission boundary with independently supplied facts. */
final class EnvironmentTest extends TestCase {
	/** Required production extensions. @var list<string> */
	private const EXTENSIONS = array( 'json', 'hash', 'sodium', 'mbstring', 'mysqli', 'fileinfo' );

	/** The real lane runtime is supported. */
	public function test_current_runtime(): void {
		self::assertSame( array(), Environment::issues( PHP_VERSION, '7.1.2', PHP_INT_SIZE, get_loaded_extensions(), false ) );
	}

	/** Every supported language line and the exact WordPress floor are accepted. */
	public function test_supported_lines(): void {
		foreach ( array( '8.3.0', '8.4.0', '8.5.0' ) as $version ) {
			self::assertSame( array(), Environment::issues( $version, '7.1.2', 8, self::EXTENSIONS, false ) );
		}
	}

	/** Unsupported facts never silently pass. */
	public function test_unsupported_environments(): void {
		self::assertSame( array( 'unsupported_php' ), Environment::issues( '8.2.99', '7.1.2', 8, self::EXTENSIONS, false ) );
		self::assertSame( array( 'unsupported_php' ), Environment::issues( '8.6.0', '7.1.2', 8, self::EXTENSIONS, false ) );
		self::assertSame( array( 'unsupported_wordpress' ), Environment::issues( '8.3.0', '7.1.1', 8, self::EXTENSIONS, false ) );
	}

	/** Reject each missing extension and unsupported platform independently. */
	public function test_platform_prerequisites(): void {
		self::assertSame( array( 'unsupported_integer_size' ), Environment::issues( '8.3.0', '7.1.2', 4, self::EXTENSIONS, false ) );
		self::assertSame( array( 'unsupported_multisite' ), Environment::issues( '8.3.0', '7.1.2', 8, self::EXTENSIONS, true ) );
		foreach ( self::EXTENSIONS as $extension ) {
			self::assertSame( array( 'missing_' . $extension ), Environment::issues( '8.3.0', '7.1.2', 8, array_values( array_diff( self::EXTENSIONS, array( $extension ) ) ), false ) );
		}
	}
}
