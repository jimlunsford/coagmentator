<?php
/**
 * A03 pure closed configuration and identity binding, without mocking WordPress.
 *
 * @package Coagmentator
 */

use Coagmentator\Config\Feature_Config;
use Coagmentator\Config\Operator_Path;
use Coagmentator\Guard\Guard_Config;
use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__ ) . '/fixtures/c03a-policy.php';

/** Parsing cannot grant authority from a role, marker, policy switch or path. */
final class BindingConfigurationTest extends TestCase {
	/** Primary nonsecret UUID fixture. */
	private const UUID = '9d131d6c-a917-4dd0-9f06-a933a846583c';
	/** Overlap nonsecret UUID fixture. */
	private const NEXT = '5a7bc459-9999-4350-aaa0-426614174000';
	/**
	 * Temporary minimal registry path.
	 *
	 * @var string Temporary minimal registry path.
	 */
	private string $registry_path;
	/**
	 * Valid independent registry.
	 *
	 * @var Guard_Config Valid independent registry.
	 */
	private Guard_Config $registry;

	/** Independent registry restricts two fixture IDs but grants no authority. */
	protected function setUp(): void {
		$this->registry_path = tempnam( sys_get_temp_dir(), 'c03a-registry-' );
		file_put_contents(
			$this->registry_path,
			json_encode(
				array(
					'version'            => 1,
					'protected_user_ids' => array( 17, 18 ),
					'credential_uuids'   => array( self::UUID, self::NEXT ),
				)
			)
		);
		$this->registry = new Guard_Config( $this->registry_path );
	}

	/** Always remove the temporary nonsecret registry. */
	protected function tearDown(): void {
		unlink( $this->registry_path );
	}

	/**
	 * Fixed roots represent host configuration; these are not HTTP inputs.
	 *
	 * @param array $data Synthetic policy.
	 * @return Feature_Config|null Validated policy.
	 */
	private function parse( array $data ): ?Feature_Config {
		return Feature_Config::parse( json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION ), $this->registry, 100000, array( '/var/www', dirname( __DIR__, 2 ) ) );
	}

	/** Correct binding succeeds only for the exact configured user/site/actor. */
	public function test_exact_identity_and_disabled_binding(): void {
		$data   = c03a_policy( 17, self::UUID );
		$config = $this->parse( $data );
		self::assertNotNull( $config );
		self::assertTrue( $config->matches( 17, self::UUID, $data['site_id'], 'operator-1', 100000 ) );
		self::assertFalse( $config->matches( 18, self::UUID, $data['site_id'], 'operator-1', 100000 ) );
		self::assertFalse( $config->matches( 17, self::NEXT, $data['site_id'], 'operator-1', 100000 ) );
		self::assertFalse( $config->matches( 17, self::UUID, self::NEXT, 'operator-1', 100000 ) );
		self::assertFalse( $config->matches( 17, self::UUID, $data['site_id'], 'operator-2', 100000 ) );
		self::assertTrue( $config->enables( '/coagmentator/v1/site_info', '/wp-json' ) );
		self::assertFalse( $config->enables( '/coagmentator/v1/get_content', '/wp-json' ) );
		self::assertFalse( $config->enables( '/coagmentator/v1/site_info', '/other/wp-json' ) );
		$data['enabled'] = false;
		$config          = $this->parse( $data );
		self::assertNotNull( $config );
		self::assertFalse( $config->matches( 17, self::UUID, $data['site_id'], 'operator-1', 100000 ) );
	}

	/** Every invalid field is rejected, including attempted secret storage. */
	public function test_closed_schema_and_identity_types(): void {
		$invalid = array(
			'version'            => array( 2, '1', null ),
			'guard_api'          => array( 2, '1' ),
			'enabled'            => array( 1, 'true', null ),
			'site_id'            => array( '', strtoupper( self::UUID ), str_replace( '-4', '-1', self::UUID ), 1 ),
			'actor_id'           => array( '', 'actor@example.test', str_repeat( 'x', 65 ), 1 ),
			'service_user_id'    => array( 0, -1, 19, '17', 17.0, true, 9007199254740992 ),
			'protected_user_ids' => array( array(), array( 17, 17 ), array( 18 ), array( 17, '18' ), array( 17, 19 ), array( 'id' => 17 ) ),
			'credential_uuids'   => array( array(), array( self::UUID, self::UUID ), array( self::UUID, self::NEXT, self::NEXT ), array( 'invalid' ), array( 17 ), array( 'uuid' => self::UUID ) ),
			'rotation'           => array(
				array(
					'started_at' => 99999,
					'expires_at' => 100001,
				),
			),
			'home_origin'        => array( 'http://wordpress.test', 'https://user@wordpress.test', 'https://wordpress.test/', 'https://wordpress.test?x=1' ),
			'home_path'          => array( '', '/sub', '/../', '/%2f/', '/sub//', 17 ),
			'bridge_origin'      => array( 'https://other.test', 'https://wordpress.test:99999' ),
			'bridge_path'        => array( '/wp-json', '/wp-json/coagmentator/v1/', '/wp-json/../coagmentator/v1' ),
			'private_reads'      => array( 'false', 0 ),
			'read_operations'    => array( array( 'site_info', 'site_info' ), array( 'create_draft' ), array( 'get_mutation' ), array( true ), 'site_info' ),
			'policy_version'     => array( 0, '1', 1.5 ),
			'approval_profile'   => array( 'administrator', 'STRICT', '', 1 ),
			'writes_enabled'     => array( true, 0, 'false' ),
		);
		foreach ( $invalid as $field => $values ) {
			foreach ( $values as $value ) {
				$data           = c03a_policy( 17, self::UUID );
				$data[ $field ] = $value;
				self::assertNull( $this->parse( $data ), 'Invalid field: ' . $field );
			}
		}
		foreach ( array( 'role', 'coagmentator_service', 'password', 'app_id', 'unknown' ) as $field ) {
			$data           = c03a_policy( 17, self::UUID );
			$data[ $field ] = 'not-authority';
			self::assertNull( $this->parse( $data ) );
		}
		foreach ( array_keys( c03a_policy( 17, self::UUID ) ) as $field ) {
			$data = c03a_policy( 17, self::UUID );
			unset( $data[ $field ] );
			self::assertNull( $this->parse( $data ), 'Missing field: ' . $field );
		}
	}

	/** Neither approved policy profile can enable a Gate 2 write. */
	public function test_profiles_and_rotation_configuration(): void {
		foreach ( array( 'strict', 'trusted_single_operator' ) as $profile ) {
			$data                     = c03a_policy( 17, self::UUID );
			$data['approval_profile'] = $profile;
			self::assertNotNull( $this->parse( $data ) );
			$data['writes_enabled'] = true;
			self::assertNull( $this->parse( $data ) );
		}
		$data                     = c03a_policy( 17, self::UUID );
		$data['credential_uuids'] = array( self::UUID, self::NEXT );
		$data['rotation']         = array(
			'started_at' => 99999,
			'expires_at' => 186399,
		);
		$config                   = $this->parse( $data );
		self::assertNotNull( $config );
		self::assertTrue( $config->matches( 17, self::NEXT, $data['site_id'], 'operator-1', 186398 ) );
		self::assertFalse( $config->matches( 17, self::UUID, $data['site_id'], 'operator-1', 186399 ) );
		self::assertFalse( $config->matches( 17, self::NEXT, $data['site_id'], 'operator-1', 99998 ) );
	}

	/** Duplicate JSON members, byte overflow and broken registry fail closed. */
	public function test_bytes_duplicates_and_registry(): void {
		$bytes = json_encode( c03a_policy( 17, self::UUID ), JSON_UNESCAPED_SLASHES );
		foreach ( array( '', '{}', 'null', '[]', str_repeat( ' ', 16385 ), str_replace( '"version":1', '"version":1,"version":1', $bytes ), 'invalid' ) as $bad ) {
			self::assertNull( Feature_Config::parse( $bad, $this->registry, 100000, array() ) );
		}
		file_put_contents( $this->registry_path, '{"version":1,"protected_user_ids":[17],"credential_uuids":[]}' );
		self::assertNull( Feature_Config::parse( $bytes, new Guard_Config( $this->registry_path ), 100000, array() ) );
		file_put_contents( $this->registry_path, 'invalid' );
		self::assertNull( Feature_Config::parse( $bytes, new Guard_Config( $this->registry_path ), 100000, array() ) );
		self::assertNull( Feature_Config::load(), 'No request-selected path fallback exists.' );
	}

	/** Paths are references only; no future storage is created while parsing. */
	public function test_operator_paths_and_storage_fields(): void {
		foreach ( array( 'relative/file', '../file', '/safe/../file', '/safe//file', 'php://input', '/safe/%2e/file', '/safe/file?x=1', '/var/www/private/file', '/safe/file' . "\0" ) as $path ) {
			$data                                   = c03a_policy( 17, self::UUID );
			$data['storage']['admission_directory'] = $path;
			self::assertNull( $this->parse( $data ) );
		}
		$data                            = c03a_policy( 17, self::UUID );
		$data['storage']['request_path'] = '/safe/other';
		self::assertNull( $this->parse( $data ) );
		$root = sys_get_temp_dir() . '/c03a-path-' . bin2hex( random_bytes( 8 ) );
		mkdir( $root );
		mkdir( $root . '/web' );
		symlink( $root . '/web', $root . '/alias' );
		try {
			self::assertFalse( Operator_Path::valid( $root . '/alias/future/file', array( $root . '/web' ) ) );
			self::assertTrue( Operator_Path::valid( $root . '/private/future', array( $root . '/web' ) ) );
			self::assertDirectoryDoesNotExist( $root . '/private' );
		} finally {
			unlink( $root . '/alias' );
			rmdir( $root . '/web' );
			rmdir( $root );
		}
	}
}
