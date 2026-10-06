<?php
/**
 * Core event evidence intersected with the independent MU guard on every use.
 *
 * @package Coagmentator
 */

namespace Coagmentator\Auth;

use Coagmentator\Config\Identity_Values;
use Coagmentator\Guard\Guard;

/** Request-local scalar evidence only; never an authentication implementation. */
final class Authentication_Evidence {
	/**
	 * Hook installation is idempotent.
	 *
	 * @var bool Hook installation is idempotent.
	 */
	private static bool $booted = false;
	/**
	 * Core event user ID.
	 *
	 * @var int Core event user ID.
	 */
	private static int $event_user = 0;
	/**
	 * Core matched credential UUID.
	 *
	 * @var string Core matched credential UUID.
	 */
	private static string $event_uuid = '';
	/**
	 * Multiple events or any failure invalidate this request.
	 *
	 * @var bool Multiple events or any failure invalidate this request.
	 */
	private static bool $failed = false;
	/**
	 * Authenticated WordPress ID.
	 *
	 * @var int Authenticated WordPress ID.
	 */
	public readonly int $user_id;
	/**
	 * Matched UUID, never app_id.
	 *
	 * @var string Matched UUID, never app_id.
	 */
	public readonly string $credential_uuid;

	/**
	 * Only current() constructs a scalar snapshot after checking live evidence.
	 *
	 * @param int    $user User ID.
	 * @param string $uuid Credential UUID.
	 */
	private function __construct( int $user, string $uuid ) {
		$this->user_id         = $user;
		$this->credential_uuid = $uuid;
	}

	/** Register after the MU guard, without asking core for password arguments. */
	public static function boot(): void {
		if ( self::$booted ) {
			return;
		}
		self::$booted = true;
		add_action(
			'application_password_did_authenticate',
			/**
			 * Retain only the two authenticated scalar identity facts.
			 *
			 * @param \WP_User             $user Core-authenticated user.
			 * @param array<string, mixed> $item Core record, UUID inspected only.
			 */
			static function ( \WP_User $user, array $item ): void {
				if ( 0 !== self::$event_user || ! Identity_Values::uuid( $item['uuid'] ?? null ) ) {
					self::$failed = true;
					return;
				}
				self::$event_user = (int) $user->ID;
				self::$event_uuid = is_string( $item['uuid'] ) ? $item['uuid'] : '';
			},
			PHP_INT_MAX,
			2
		);
		add_action(
			'application_password_failed_authentication',
			static function (): void {
				self::$failed = true;
			},
			PHP_INT_MAX,
			0
		);
	}

	/**
	 * Revalidate current core identity, credential existence and guard authority.
	 * This is identity evidence only, not permission to dispatch a REST request.
	 * The MU server separately owns the external request/depth/object fence.
	 *
	 * @param string $route Fixed operation route chosen by trusted code.
	 * @return self|null Fresh scalar snapshot; never cached authorization.
	 */
	public static function current( string $route ): ?self {
		if ( self::$failed || self::$event_user < 1 || ! class_exists( Guard::class, false ) || ! Guard::instance()->admits( $route ) || get_current_user_id() !== self::$event_user || ! wp_is_application_passwords_available_for_user( wp_get_current_user() ) ) {
			return null;
		}
		// Re-read metadata at admission rather than reusing the authentication cache.
		wp_cache_delete( self::$event_user, 'user_meta' );
		// Core returns a record transiently; retain only an existence/UUID result.
		if ( ( \WP_Application_Passwords::get_user_application_password( self::$event_user, self::$event_uuid )['uuid'] ?? null ) !== self::$event_uuid ) {
			return null;
		}
		return new self( self::$event_user, self::$event_uuid );
	}

	/**
	 * Evidence cannot become a persisted authority object.
	 *
	 * @return never Always throws.
	 * @throws \LogicException Always, without identity details.
	 */
	public function __serialize(): array {
		throw new \LogicException( 'Authentication evidence is request-local.' );
	}

	/**
	 * Reject reconstruction from another request.
	 *
	 * @param array<mixed> $data Ignored serialized data.
	 * @throws \LogicException Always, without reflecting data.
	 */
	public function __unserialize( array $data ): void {
		throw new \LogicException( 'Authentication evidence is request-local.' );
	}
}
