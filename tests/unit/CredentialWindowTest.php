<?php
/**
 * Fixed rotation boundaries, independent of any credential-management system.
 *
 * @package Coagmentator
 */

use Coagmentator\Config\Credential_Window;
use PHPUnit\Framework\TestCase;

/** Two credentials require a reviewed absolute interval of at most 24 hours. */
final class CredentialWindowTest extends TestCase {
	/** Boundary, unknown-field and primitive-type cases. */
	public function test_rotation_boundaries(): void {
		$first = '9d131d6c-a917-4dd0-9f06-a933a846583c';
		$next  = '5a7bc459-9999-4350-aaa0-426614174000';
		$pair  = array( $first, $next );
		foreach ( array(
			null,
			array(),
			array(
				'started_at' => '100',
				'expires_at' => 200,
			),
			array(
				'started_at' => 100,
				'expires_at' => 100,
			),
			array(
				'started_at' => 101,
				'expires_at' => 200,
			),
			array(
				'started_at' => 0,
				'expires_at' => 200,
			),
			array(
				'started_at' => 100,
				'expires_at' => 86501,
			),
			array(
				'started_at' => 100,
				'expires_at' => 200,
				'extend'     => true,
			),
		) as $rotation ) {
			self::assertNull( Credential_Window::parse( $pair, $rotation, 100 ) );
		}
		$window = Credential_Window::parse(
			$pair,
			array(
				'started_at' => 100,
				'expires_at' => 86500,
			),
			100
		);
		self::assertNotNull( $window );
		self::assertTrue( $window->allows( $first, 100 ) );
		self::assertTrue( $window->allows( $next, 86499 ) );
		self::assertFalse( $window->allows( $first, 86500 ) );
		self::assertFalse( $window->allows( $next, 99 ) );
		self::assertNull(
			Credential_Window::parse(
				$pair,
				array(
					'started_at' => 100,
					'expires_at' => 86500,
				),
				86500
			)
		);
		self::assertNull( Credential_Window::parse( array( $first, $first ), null, 100 ) );
		self::assertNull( Credential_Window::parse( array( $first, $next, $first ), null, 100 ) );
		self::assertNull( Credential_Window::parse( array( 'bad' ), null, 100 ) );
		self::assertNull( Credential_Window::parse( array( $first ), null, 0 ) );
		$single = Credential_Window::parse( array( $first ), null, 100 );
		self::assertNotNull( $single );
		self::assertTrue( $single->allows( $first, 86500 ) );
		self::assertFalse( $single->allows( $next, 100 ) );
	}
}
