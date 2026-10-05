<?php
/**
 * Real WordPress integration smoke tests.
 *
 * @package Coagmentator
 */


/** Confirms core factories, isolated PHPUnit and dormant package loading. */
final class BootstrapTest extends WP_UnitTestCase {
	/** Real WordPress, Polyfills and the package load in the same process. */
	public function test_bootstrap(): void {
		self::assertSame( '7.1.2', $GLOBALS['wp_version'] );
		self::assertTrue( $GLOBALS['coagmentator_c01_loaded'] );
		self::assertTrue( class_exists( 'Yoast\PHPUnitPolyfills\Autoload' ) );
		self::assertStringStartsWith( '9.6.', PHPUnit\Runner\Version::id() );
	}

	/** A real database-backed post fixture can be created and retrieved. */
	public function test_factory(): void {
		$post_id = self::factory()->post->create(
			array(
				'post_title'  => 'C01 fixture',
				'post_status' => 'draft',
			)
		);
		self::assertIsInt( $post_id );
		self::assertSame( 'C01 fixture', get_post( $post_id )->post_title );
		self::assertSame( 'draft', get_post_status( $post_id ) );
	}

	/** C01 cannot register a bridge or issue a service identity. */
	public function test_no_bridge_or_credentials(): void {
		foreach ( array_keys( rest_get_server()->get_routes() ) as $route ) {
			self::assertStringNotContainsString( 'coagmentator', $route );
		}
		// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Bounded empty disposable fixture inventory.
		self::assertSame( array(), get_users( array( 'meta_key' => 'coagmentator_service' ) ) );
		// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Bounded empty disposable fixture inventory.
		self::assertSame( array(), get_users( array( 'meta_key' => '_application_passwords' ) ) );
	}
}
