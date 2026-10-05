<?php
/**
 * Operator-owned denial registry. No request supplies a configuration path.
 *
 * @package Coagmentator
 */

namespace Coagmentator\Guard;

/** Strict, bounded, canonical JSON registry, independent of feature policy. */
final class Guard_Config {
	/** Protected IDs.
	 *
	 * @var list<int> Protected IDs.
	 */
	private array $ids = array();
	/** Approved credential UUIDs.
	 *
	 * @var list<string> Approved credential UUIDs.
	 */
	private array $uuids = array();
	/** Valid registry.
	 *
	 * @var bool Valid registry.
	 */
	private bool $healthy = false;

	/**
	 * Read a fixed operator path. Canonical bytes also reject duplicate members.
	 *
	 * @param string $path Operator path.
	 */
	public function __construct( string $path ) {
		if ( '' === $path || '/' !== $path[0] || ! is_file( $path ) || ! is_readable( $path ) ) {
			return;
		}
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local operator file, bounded before decode.
		$bytes = file_get_contents( $path, false, null, 0, 8193 );
		if ( false === $bytes || strlen( $bytes ) > 8192 ) {
			return;
		}
		$data = json_decode( trim( $bytes ), true, 4 );
		if ( ! is_array( $data ) || array_keys( $data ) !== array( 'version', 'protected_user_ids', 'credential_uuids' ) || 1 !== $data['version'] || ! is_array( $data['protected_user_ids'] ) || ! is_array( $data['credential_uuids'] ) ) {
			return;
		}
		// phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode -- Canonical strict bytes without WordPress normalization or dependency.
		if ( json_encode( $data, JSON_UNESCAPED_SLASHES ) !== trim( $bytes ) || ! array_is_list( $data['protected_user_ids'] ) || ! array_is_list( $data['credential_uuids'] ) || count( $data['protected_user_ids'] ) > 32 || count( $data['credential_uuids'] ) > 2 ) {
			return;
		}
		foreach ( $data['protected_user_ids'] as $id ) {
			if ( ! is_int( $id ) || $id < 1 || $id > 9007199254740991 || in_array( $id, $this->ids, true ) ) {
				return;
			}
			$this->ids[] = $id;
		}
		foreach ( $data['credential_uuids'] as $uuid ) {
			if ( ! is_string( $uuid ) || 1 !== preg_match( '/^[a-f0-9]{8}-[a-f0-9]{4}-4[a-f0-9]{3}-[89ab][a-f0-9]{3}-[a-f0-9]{12}$/D', $uuid ) || in_array( $uuid, $this->uuids, true ) ) {
				return;
			}
			$this->uuids[] = $uuid;
		}
		$this->healthy = true;
	}

	/** Whether the registry is trustworthy.
	 *
	 * @return bool Whether the registry is trustworthy.
	 */
	public function healthy(): bool {
		return $this->healthy;
	}

	/**
	 * Denial membership is not authorization.
	 *
	 * @param int $id User ID.
	 * @return bool Protected ID.
	 */
	public function protects( int $id ): bool {
		return $this->healthy && in_array( $id, $this->ids, true );
	}

	/**
	 * Minimal credential fence, not C03's rotation/binding policy.
	 *
	 * @param string $uuid Credential UUID.
	 * @return bool Listed UUID.
	 */
	public function lists( string $uuid ): bool {
		return $this->healthy && in_array( $uuid, $this->uuids, true );
	}
}
