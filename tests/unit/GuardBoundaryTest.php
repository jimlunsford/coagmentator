<?php
/**
 * Pure guard byte and lifecycle tests. HTTP suites prove actual authentication.
 *
 * @package Coagmentator
 */

use Coagmentator\Guard\Dispatch_Scope;
use Coagmentator\Guard\Guard_Config;
use Coagmentator\Guard\Route_Boundary;
use PHPUnit\Framework\TestCase;

/** Registry corruption cannot become an empty denial list. */
final class GuardBoundaryTest extends TestCase {
	/** Every invalid representation stays explicitly unhealthy. */
	public function test_registry_validation(): void {
		$path = tempnam( sys_get_temp_dir(), 'c02-registry-' );
		try {
			foreach ( array( '', 'null', '{}', '{"version":2,"protected_user_ids":[],"credential_uuids":[]}', '{"version":1,"protected_user_ids":["1"],"credential_uuids":[]}', '{"version":1,"protected_user_ids":[1,1],"credential_uuids":[]}', '{"version":1,"version":1,"protected_user_ids":[1],"credential_uuids":[]}', str_repeat( 'x', 8193 ) ) as $bytes ) {
				file_put_contents( $path, $bytes );
				self::assertFalse( ( new Guard_Config( $path ) )->healthy() );
			}
			file_put_contents( $path, '{"version":1,"protected_user_ids":[1],"credential_uuids":[]}' );
			$config = new Guard_Config( $path );
			self::assertTrue( $config->healthy() );
			self::assertTrue( $config->protects( 1 ) );
			self::assertFalse( $config->protects( 2 ) );
			self::assertFalse( $config->lists( 'not-authorized' ) );
		} finally {
			unlink( $path );
		}
		self::assertFalse( ( new Guard_Config( $path ) )->healthy() );
	}

	/**
	 * Dispatch token/object state is balanced even when the callback throws.
	 *
	 * @throws RuntimeException Caught locally to exercise balanced cleanup.
	 */
	public function test_balanced_scope(): void {
		$scope   = new Dispatch_Scope();
		$request = new stdClass();
		self::assertFalse( $scope->enter( $request ) );
		self::assertTrue( $scope->begin() );
		self::assertFalse( $scope->begin() );
		self::assertTrue( $scope->enter( $request ) );
		self::assertTrue( $scope->matches( $request ) );
		self::assertFalse( $scope->matches( clone $request ) );
		self::assertFalse( $scope->enter( $request ) );
		self::assertFalse( $scope->enter( clone $request ) );
		$scope->leave();
		self::assertFalse( $scope->enter( $request ) );
		$scope->finish();
		self::assertTrue( $scope->begin() );
		try {
			self::assertTrue( $scope->enter( $request ) );
			throw new RuntimeException( 'Safe fixture exception.' );
		} catch ( RuntimeException $failure ) {
			self::assertSame( 'Safe fixture exception.', $failure->getMessage() );
		} finally {
			$scope->leave();
			$scope->finish();
		}
		self::assertTrue( $scope->begin() );
		self::assertTrue( $scope->enter( new stdClass() ) );
	}

	/** Independent literal table and raw transport aliases. */
	public function test_immutable_routes(): void {
		$operations = array( 'site_info', 'search_content', 'get_content', 'list_terms', 'search_media', 'get_media', 'get_metadata', 'list_revisions', 'get_revision' );
		self::assertSame( $operations, Route_Boundary::OPERATIONS );
		foreach ( $operations as $operation ) {
			$route  = '/coagmentator/v1/' . $operation;
			$server = array(
				'REQUEST_METHOD' => 'POST',
				'REQUEST_URI' => '/wp-json' . $route,
			);
			self::assertTrue( Route_Boundary::canonical( $route, 'POST', $server, '/wp-json' ) );
			self::assertFalse( Route_Boundary::canonical( $route . '/', 'POST', $server, '/wp-json' ) );
			self::assertFalse( Route_Boundary::canonical( $route, 'GET', $server, '/wp-json' ) );
			$server['REQUEST_URI'] = '/sub/wp-json' . $route;
			self::assertTrue( Route_Boundary::canonical( $route, 'POST', $server, '/sub/wp-json' ) );
		}
		self::assertFalse( Route_Boundary::known( '/coagmentator/v1/get_mutation' ) );
	}
}
