<?php
/**
 * Plugin Name: Coagmentator Independent Guard
 * Description: Host-managed service identity denial boundary. Install before credentials.
 * Version: 0.1.0
 * Requires PHP: 8.3
 *
 * @package Coagmentator
 */

// phpcs:ignoreFile WordPress.Files.FileName.InvalidClassFileName -- Required self-contained MU entry point retains its fallback class.

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Minimal fallback remains in the auto-loaded file if any support file is absent.
 * It never grants bridge authority. Installed PHP remains a trusted execution domain.
 */
final class Coagmentator_Guard_Loader {
	/** Sticky denial for this PHP request.
	 *
	 * @var bool Sticky denial for this PHP request.
	 */
	private static bool $denied = false;
	/** Observation recursion guard.
	 *
	 * @var bool Observation recursion guard.
	 */
	private static bool $observing = false;

	/** Safe denial, without incoming error data.
	 *
	 * @return WP_Error Safe denial, without incoming error data.
	 */
	public static function error(): WP_Error {
		return new WP_Error( 'coagmentator_guard_denied', 'Authentication is unavailable for this request.', array( 'status' => 403 ) );
	}

	/**
	 * Minimal independent failure encoder. No source response data is copied.
	 *
	 * @return array<string, mixed> Closed C02 denial.
	 */
	public static function failure_data(): array {
		return array(
			'ok' => false,
			'contract_version' => '1.0',
			'correlation_id' => wp_generate_uuid4(),
			'site_id' => null,
			'error' => array(
				'code' => 'AUTHORIZATION_DENIED',
				'message' => 'This request is not permitted.',
				'origin' => 'bridge',
				'retryable' => false,
				'retry_after_seconds' => null,
				'write_state' => 'not_applied',
				'details' => array(
					'fields' => array(),
					'reason' => null,
					'approval' => null,
				),
			),
			'receipt' => null,
		);
	}

	/**
	 * Normalize emergency REST responses even if authentication stopped dispatch.
	 *
	 * @param mixed $response Prior response.
	 * @param WP_REST_Server $server Server.
	 * @param WP_REST_Request $request Request.
	 * @return mixed Original or closed denial.
	 */
	public static function finalize( $response, $server, $request ) {
		if ( self::$denied || self::remote() || str_starts_with( $request->get_route(), '/coagmentator/' ) ) {
			return new WP_REST_Response( self::failure_data(), 403, array( 'Cache-Control' => 'no-store' ) );
		}
		return $response;
	}

	/**
	 * User metadata is denial only, independent of roles.
	 *
	 * @param int $id User ID.
	 * @return bool Recognizable service identity.
	 */
	public static function marked( int $id ): bool {
		return $id > 0 && '' !== get_user_meta( $id, 'coagmentator_service', true );
	}

	/** A supplied remote credential must not fall through anonymously.
	 *
	 * @return bool A supplied remote credential must not fall through anonymously.
	 */
	public static function remote(): bool {
		foreach ( array( 'HTTP_AUTHORIZATION', 'REDIRECT_HTTP_AUTHORIZATION', 'PHP_AUTH_USER', 'PHP_AUTH_PW' ) as $key ) {
			if ( isset( $_SERVER[ $key ] ) && '' !== $_SERVER[ $key ] ) {
				return true;
			}
		}
		return defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST;
	}

	/**
	 * A real valid WordPress cookie, not a request path or claimed identity.
	 *
	 * @param int $id Current identity.
	 * @return bool Valid human cookie identity.
	 */
	public static function human_cookie( int $id ): bool {
		if ( $id < 1 || self::marked( $id ) || ! function_exists( 'wp_validate_auth_cookie' ) ) {
			return false;
		}
		foreach ( array( 'auth', 'secure_auth', 'logged_in' ) as $scheme ) {
			if ( wp_validate_auth_cookie( '', $scheme ) === $id ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Disable core credential success before last-use accounting.
	 *
	 * @param WP_Error $error Mutable core error.
	 */
	public static function application_error( WP_Error $error ): void {
		self::$denied = true;
		$error->add( 'coagmentator_guard_denied', 'Authentication is unavailable for this request.' );
	}

	/**
	 * Late authenticate fence. Ordinary human password login remains core-owned.
	 *
	 * @param WP_User|WP_Error|null $user Core result.
	 * @return WP_User|WP_Error|null Safe result.
	 */
	public static function authenticate( $user ) {
		if ( self::remote() || ( $user instanceof WP_User && self::marked( (int) $user->ID ) ) ) {
			self::$denied = true;
			return self::error();
		}
		// Ordinary authentication is permitted only at WordPress's actual login script.
		if ( $user instanceof WP_User && 'wp-login.php' !== ( $GLOBALS['pagenow'] ?? '' ) ) {
			self::$denied = true;
			return self::error();
		}
		return $user;
	}

	/**
	 * Preserve valid human cookies; reject injected remote identities.
	 *
	 * @param int|false $id Candidate identity.
	 * @return int|false Denied identity is zero and remains sticky.
	 */
	public static function determine( $id ) {
		if ( self::remote() || ( $id && ! self::human_cookie( (int) $id ) ) ) {
			self::$denied = true;
			return 0;
		}
		return $id;
	}

	/** Observe direct wp_set_current_user injection without retaining privilege. */
	public static function observe(): void {
		if ( self::$observing ) {
			return;
		}
		$id = (int) ( $GLOBALS['current_user']->ID ?? 0 );
		if ( $id && ! self::human_cookie( $id ) ) {
			self::$denied    = true;
			self::$observing = true;
			try {
				wp_set_current_user( 0 );
			} finally {
				self::$observing = false;
			}
		}
	}

	/**
	 * Every bridge path stays denied for every authentication mode.
	 *
	 * @param mixed           $result Prior result.
	 * @param WP_REST_Server  $server Server instance.
	 * @param WP_REST_Request $request Request instance.
	 * @return mixed Preserved result or safe denial.
	 */
	public static function dispatch( $result, $server, $request ) {
		if ( self::$denied || self::remote() || str_starts_with( $request->get_route(), '/coagmentator/' ) ) {
			return self::error();
		}
		return $result;
	}

	/**
	 * Never turn a denied credential-bearing request into public REST success.
	 *
	 * @param mixed $result Existing authentication error.
	 * @return mixed Original result or denial.
	 */
	public static function rest_auth( $result ) {
		return self::$denied || self::remote() ? self::error() : $result;
	}

	/** Fail before non-REST target callbacks. */
	public static function non_rest(): void {
		if ( self::$denied || self::remote() || self::marked( get_current_user_id() ) ) {
			$prefix = defined( 'COAGMENTATOR_GUARD_REST_PREFIX' ) && is_string( COAGMENTATOR_GUARD_REST_PREFIX ) ? COAGMENTATOR_GUARD_REST_PREFIX : '/wp-json';
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- Raw path selects denial encoding only.
			if ( str_starts_with( $_SERVER['REQUEST_URI'] ?? '', $prefix . '/coagmentator/' ) ) {
				status_header( 403 );
				header( 'Content-Type: application/json; charset=UTF-8' );
				header( 'Cache-Control: no-store' );
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Closed JSON, no reflected input.
				echo wp_json_encode( self::failure_data() );
				exit;
			}
			wp_die( 'Authentication is unavailable for this request.', '', array( 'response' => 403 ) );
		}
	}

	/** Install emergency controls without the support directory or vendor tree. */
	public static function emergency(): void {
		add_action( 'wp_authenticate_application_password_errors', array( self::class, 'application_error' ), PHP_INT_MAX, 1 );
		add_filter( 'authenticate', array( self::class, 'authenticate' ), PHP_INT_MAX, 1 );
		add_filter( 'determine_current_user', array( self::class, 'determine' ), PHP_INT_MAX, 1 );
		add_action( 'set_current_user', array( self::class, 'observe' ), PHP_INT_MAX );
		add_filter( 'rest_authentication_errors', array( self::class, 'rest_auth' ), PHP_INT_MAX );
		add_filter( 'rest_pre_dispatch', array( self::class, 'dispatch' ), PHP_INT_MAX, 3 );
		add_filter( 'rest_post_dispatch', array( self::class, 'finalize' ), PHP_INT_MAX, 3 );
		foreach ( array( 'init', 'admin_init', 'login_init' ) as $hook ) {
			add_action( $hook, array( self::class, 'non_rest' ), -PHP_INT_MAX );
		}
	}
}

$coagmentator_guard_files = array( 'class-guard-config.php', 'class-route-boundary.php', 'class-dispatch-scope.php', 'class-guard.php', 'class-guarded-rest-server.php' );
$coagmentator_guard_ready = true;
foreach ( $coagmentator_guard_files as $coagmentator_guard_file ) {
	if ( ! is_readable( __DIR__ . '/coagmentator-guard/src/' . $coagmentator_guard_file ) ) {
		$coagmentator_guard_ready = false;
	}
}
if ( $coagmentator_guard_ready ) {
	try {
		foreach ( $coagmentator_guard_files as $coagmentator_guard_file ) {
			require_once __DIR__ . '/coagmentator-guard/src/' . $coagmentator_guard_file;
		}
		Coagmentator\Guard\Guard::boot();
	} catch ( Throwable $coagmentator_guard_failure ) {
		// Deliberately omit exception contents, paths and request values.
		Coagmentator_Guard_Loader::emergency();
	}
} else {
	Coagmentator_Guard_Loader::emergency();
}
unset( $coagmentator_guard_files, $coagmentator_guard_file, $coagmentator_guard_ready, $coagmentator_guard_failure );
