<?php
/**
 * Independent guard-side evidence and denial hooks, without bridge capabilities.
 *
 * @package Coagmentator
 */

namespace Coagmentator\Guard;

/**
 * Never obtains a password argument or retains a complete credential record.
 */
final class Guard {
	/**
	 * Request singleton.
	 *
	 * @var self|null Request singleton.
	 */
	private static ?self $instance = null;
	/**
	 * Protected registry.
	 *
	 * @var Guard_Config Protected registry.
	 */
	private Guard_Config $config;
	/**
	 * Sticky service classification.
	 *
	 * @var bool Sticky service classification.
	 */
	private bool $service = false;
	/**
	 * Sticky authentication failure.
	 *
	 * @var bool Sticky authentication failure.
	 */
	private bool $failed = false;
	/**
	 * Current-user observation recursion.
	 *
	 * @var bool Current-user observation recursion.
	 */
	private bool $observing = false;
	/**
	 * Authenticated user only.
	 *
	 * @var int Authenticated user only.
	 */
	private int $user = 0;
	/**
	 * Matched UUID only.
	 *
	 * @var string Matched UUID only.
	 */
	private string $uuid = '';

	/**
	 * Load operator configuration without REST-selected paths.
	 */
	private function __construct() {
		$path         = defined( 'COAGMENTATOR_GUARD_REGISTRY' ) ? COAGMENTATOR_GUARD_REGISTRY : '';
		$this->config = new Guard_Config( is_string( $path ) ? $path : '' );
	}

	/**
	 * Current request guard.
	 *
	 * @return self Current request guard.
	 */
	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Install guard hooks once.
	 */
	public static function boot(): void {
		$guard = self::instance();
		if ( ! $guard->config->healthy() ) {
			\Coagmentator_Guard_Loader::emergency();
		}
		add_action( 'wp_authenticate_application_password_errors', array( $guard, 'application_error' ), PHP_INT_MAX, 3 );
		add_action( 'application_password_did_authenticate', array( $guard, 'authenticated' ), PHP_INT_MAX, 2 );
		add_action( 'application_password_failed_authentication', array( $guard, 'failed' ), PHP_INT_MAX, 0 );
		add_filter( 'authenticate', array( $guard, 'authenticate' ), PHP_INT_MAX, 1 );
		add_filter( 'determine_current_user', array( $guard, 'determine' ), PHP_INT_MAX, 1 );
		add_action( 'set_current_user', array( $guard, 'observe' ), PHP_INT_MAX );
		add_filter( 'rest_authentication_errors', array( $guard, 'rest_auth' ), PHP_INT_MAX );
		add_filter( 'wp_rest_server_class', array( $guard, 'server_class' ), PHP_INT_MAX );
		add_filter( 'rest_pre_dispatch', array( $guard, 'pre_dispatch' ), PHP_INT_MAX, 3 );
		add_filter( 'rest_request_before_callbacks', array( $guard, 'before_callbacks' ), PHP_INT_MAX, 3 );
		add_filter( 'rest_post_dispatch', array( $guard, 'finalize' ), PHP_INT_MAX, 3 );
		add_filter( 'rest_dispatch_request', array( $guard, 'last_callback_fence' ), PHP_INT_MAX, 4 );
		foreach ( array( 'init', 'admin_init', 'login_init' ) as $hook ) {
			add_action( $hook, array( $guard, 'non_rest' ), -PHP_INT_MAX );
			add_action( $hook, array( $guard, 'non_rest' ), PHP_INT_MAX );
		}
	}

	/**
	 * Registry IDs remain protected after marker or role changes.
	 *
	 * @param int $id Identity.
	 * @return bool Denial membership.
	 */
	public function protected_id( int $id ): bool {
		return $this->config->protects( $id ) || \Coagmentator_Guard_Loader::marked( $id );
	}

	/**
	 * Sticky restrictions survive clearing/switching users.
	 *
	 * @return bool Sticky restrictions survive clearing/switching users.
	 */
	public function restricted(): bool {
		if ( $this->protected_id( (int) ( $GLOBALS['current_user']->ID ?? 0 ) ) ) {
			$this->service = true;
		}
		return $this->service || $this->failed;
	}

	/**
	 * Fixed host pretty REST prefix.
	 *
	 * @return string Fixed host pretty REST prefix.
	 */
	public function prefix(): string {
		return defined( 'COAGMENTATOR_GUARD_REST_PREFIX' ) && is_string( COAGMENTATOR_GUARD_REST_PREFIX ) ? COAGMENTATOR_GUARD_REST_PREFIX : '/wp-json';
	}

	/**
	 * Guard readiness requires a real fixed callback, never just a namespace.
	 * The ordinary plugin must load its own class; this method never loads it.
	 *
	 * @param string $route Exact REST route.
	 * @return bool Feature policy and fixed class available.
	 */
	public function handler_ready( string $route ): bool {
		if ( ! Route_Boundary::known( $route ) || ! defined( 'COAGMENTATOR_GUARD_FEATURE_POLICY' ) || ! is_string( COAGMENTATOR_GUARD_FEATURE_POLICY ) || ! is_readable( COAGMENTATOR_GUARD_FEATURE_POLICY ) ) {
			return false;
		}
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Bounded, operator-owned readiness file, not C03's feature policy.
		$policy        = file_get_contents( COAGMENTATOR_GUARD_FEATURE_POLICY, false, null, 0, 129 );
		$handler_class = 'Coagmentator\\Rest\\ReadController';
		if ( '{"version":1,"guard_api":1}' !== trim( false === $policy ? '' : $policy ) || ! class_exists( $handler_class, false ) || ! defined( 'WP_PLUGIN_DIR' ) || ! is_string( WP_PLUGIN_DIR ) ) {
			return false;
		}
		$class     = $this->reflect_handler( $handler_class );
		$operation = substr( $route, strlen( '/coagmentator/v1/' ) );
		if ( null === $class || ! $class->isFinal() || $class->getFileName() !== realpath( WP_PLUGIN_DIR . '/coagmentator/src/Rest/ReadController.php' ) || ! $class->hasMethod( $operation ) || ! $class->hasMethod( 'authorize_guard_request' ) ) {
			return false;
		}
		$server = $GLOBALS['wp_rest_server'] ?? null;
		if ( ! $server instanceof Guarded_REST_Server ) {
			return false;
		}
		foreach ( $server->get_routes()[ $route ] ?? array() as $handler ) {
			if ( is_array( $handler ) && isset( $handler['methods']['POST'] ) && ( $handler['callback'] ?? null ) === array( 'Coagmentator\\Rest\\ReadController', $operation ) && ( $handler['permission_callback'] ?? null ) === array( 'Coagmentator\\Rest\\ReadController', 'authorize_guard_request' ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Reflect only an already loaded handler class.
	 *
	 * @param string $name Fixed handler name supplied by this guard.
	 * @return \ReflectionClass<object>|null Existing class reflection.
	 */
	private function reflect_handler( string $name ): ?\ReflectionClass {
		return class_exists( $name, false ) ? new \ReflectionClass( $name ) : null;
	}

	/**
	 * Reject before core records success. No plaintext argument is registered.
	 *
	 * @param \WP_Error            $error Mutable core error.
	 * @param \WP_User             $user Candidate user.
	 * @param array<string, mixed> $item Matched record, UUID inspected only.
	 */
	public function application_error( \WP_Error $error, \WP_User $user, array $item ): void {
		if ( ! $this->config->healthy() || $this->protected_id( (int) $user->ID ) ) {
			$this->service = $this->protected_id( (int) $user->ID );
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- Raw bytes must be rejected, never normalized into an accepted route.
			$uri   = $_SERVER['REQUEST_URI'] ?? '';
			$route = is_string( $uri ) && str_starts_with( $uri, $this->prefix() ) ? substr( $uri, strlen( $this->prefix() ) ) : '';
			$uuid  = $item['uuid'] ?? null;
			if ( ! $this->config->protects( (int) $user->ID ) || ! is_string( $uuid ) || ! $this->config->lists( $uuid ) || ! defined( 'REST_REQUEST' ) || ! REST_REQUEST || ! is_ssl() || ! Route_Boundary::canonical( $route, 'POST', $_SERVER, $this->prefix() ) || ! $this->handler_ready( $route ) ) {
				$this->failed = true;
				$error->add( 'coagmentator_guard_denied', 'Authentication is unavailable for this request.' );
			}
		}
	}

	/**
	 * Copy only user ID and UUID, never the hash or entire record.
	 *
	 * @param \WP_User             $user Authenticated user.
	 * @param array<string, mixed> $item Matched record.
	 */
	public function authenticated( \WP_User $user, array $item ): void {
		if ( $this->protected_id( (int) $user->ID ) ) {
			$this->service = true;
			$this->user    = (int) $user->ID;
			$this->uuid    = is_string( $item['uuid'] ?? null ) ? $item['uuid'] : '';
		}
	}

	/**
	 * Discard raw WordPress error details.
	 */
	public function failed(): void {
		$this->failed = true;
	}

	/**
	 * Reject passwords/alternate login for service IDs even after promotion.
	 *
	 * @param mixed $user Core result.
	 * @return mixed Core result or safe denial.
	 */
	public function authenticate( $user ) {
		if ( $user instanceof \WP_User && $this->protected_id( (int) $user->ID ) ) {
			$this->service = true;
			if ( $this->user !== (int) $user->ID || '' === $this->uuid ) {
				$this->failed = true;
				return \Coagmentator_Guard_Loader::error();
			}
		}
		return $this->failed ? \Coagmentator_Guard_Loader::error() : $user;
	}

	/**
	 * A service cookie or alternate identity is never Application Password evidence.
	 *
	 * @param int|false $id Candidate ID.
	 * @return int|false Core ID or denied identity.
	 */
	public function determine( $id ) {
		if ( $id && $this->protected_id( (int) $id ) ) {
			$this->service = true;
			if ( $this->user !== (int) $id || '' === $this->uuid ) {
				$this->failed = true;
				return 0;
			}
		}
		return $this->failed ? 0 : $id;
	}

	/**
	 * Observe and invalidate changed identities without dropping the service latch.
	 */
	public function observe(): void {
		if ( $this->observing ) {
			return;
		}
		$id = (int) ( $GLOBALS['current_user']->ID ?? 0 );
		if ( $this->protected_id( $id ) ) {
			$this->service = true;
		}
		if ( $this->service && ( $id !== $this->user || 0 === $this->user ) ) {
			$this->failed    = true;
			$this->observing = true;
			try {
				wp_set_current_user( 0 );
			} finally {
				$this->observing = false;
			}
		}
	}

	/**
	 * Validate live core evidence at admission, without C03 binding/capabilities.
	 *
	 * @param string $route Exact request route.
	 * @return bool Guard-only admission.
	 */
	public function admits( string $route ): bool {
		return ! $this->failed && $this->service && 0 < $this->user && get_current_user_id() === $this->user && $this->config->protects( $this->user ) && $this->config->lists( $this->uuid ) && null !== \WP_Application_Passwords::get_user_application_password( $this->user, $this->uuid ) && $this->handler_ready( $route );
	}

	/**
	 * Preserve unrelated prior errors and reject service authentication failures.
	 *
	 * @param mixed $result Prior error.
	 * @return mixed Unchanged result or safe error.
	 */
	public function rest_auth( $result ) {
		$this->restricted();
		return $this->failed ? \Coagmentator_Guard_Loader::error() : $result;
	}

	/**
	 * Refuse competing server authority for the service path.
	 *
	 * @param string $server_class Existing selection.
	 * @return string MU server.
	 */
	public function server_class( string $server_class ): string {
		if ( 'WP_REST_Server' !== $server_class && Guarded_REST_Server::class !== $server_class ) {
			$this->failed = true;
		}
		return Guarded_REST_Server::class;
	}

	/**
	 * Covers calls through a separately constructed native server too.
	 *
	 * @param mixed            $result Prior result.
	 * @param \WP_REST_Server  $server Server.
	 * @param \WP_REST_Request $request Request.
	 * @return mixed Preserved result or denial.
	 */
	public function pre_dispatch( $result, $server, $request ) {
		if ( $this->restricted() || str_starts_with( $request->get_route(), '/coagmentator/' ) ) {
			if ( ! $server instanceof Guarded_REST_Server || ! $server->admitted( $request ) || ! $this->admits( $request->get_route() ) ) {
				return \Coagmentator_Guard_Loader::error();
			}
		}
		return $result;
	}

	/**
	 * Route registration cannot replace the fixed callback/permission identities.
	 *
	 * @param mixed                $result Prior result.
	 * @param array<string, mixed> $handler Registered handler.
	 * @param \WP_REST_Request     $request Request.
	 * @return mixed Original result or safe denial.
	 */
	public function before_callbacks( $result, array $handler, $request ) {
		if ( $this->restricted() || str_starts_with( $request->get_route(), '/coagmentator/' ) ) {
			$operation = substr( $request->get_route(), strlen( '/coagmentator/v1/' ) );
			if ( ! $this->admits( $request->get_route() ) || ( $handler['callback'] ?? null ) !== array( 'Coagmentator\\Rest\\ReadController', $operation ) || ( $handler['permission_callback'] ?? null ) !== array( 'Coagmentator\\Rest\\ReadController', 'authorize_guard_request' ) ) {
				return \Coagmentator_Guard_Loader::error();
			}
		}
		return $result;
	}

	/**
	 * Recheck after permission callbacks, including identity changes they trigger.
	 *
	 * @param mixed                $result Prior result.
	 * @param \WP_REST_Request     $request Request.
	 * @param string               $route Matched route.
	 * @param array<string, mixed> $handler Matched handler.
	 * @return mixed Prior result or denial before target execution.
	 */
	public function last_callback_fence( $result, $request, string $route, array $handler ) {
		return $this->before_callbacks( $result, $handler, $request );
	}

	/**
	 * C02 has no successful bridge response; discard raw core/plugin errors.
	 *
	 * @param mixed            $response Prior response.
	 * @param \WP_REST_Server  $server Server.
	 * @param \WP_REST_Request $request Request.
	 * @return mixed Original human response or closed C02 denial.
	 */
	public function finalize( $response, $server, $request ) {
		return $this->restricted() || str_starts_with( $request->get_route(), '/coagmentator/' ) ? Guarded_REST_Server::denial() : $response;
	}

	/**
	 * Deny non-REST service use before target callbacks.
	 */
	public function non_rest(): void {
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- Raw bytes select denial timing only, never authorization.
		$uri = $_SERVER['REQUEST_URI'] ?? '';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Presence only defers to REST denial; no action is authorized.
		$potential_rest = is_string( $uri ) && ( str_starts_with( $uri, $this->prefix() . '/' ) || isset( $_GET['rest_route'] ) );
		$xmlrpc         = defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST;
		if ( \Coagmentator_Guard_Loader::remote() && ! $potential_rest && ! $xmlrpc ) {
			$this->failed = true;
		}
		if ( $this->restricted() && ( ! defined( 'REST_REQUEST' ) || ! REST_REQUEST ) ) {
			status_header( 403 );
			wp_die( 'Authentication is unavailable for this request.', '', array( 'response' => 403 ) );
		}
	}

	/**
	 * External lifecycle owner clears only when its complete cycle has ended.
	 */
	public function finish(): void {
		$this->user = 0;
		$this->uuid = '';
		// Keep denial classification for the entire PHP request, including a second serve cycle.
		// PHP destroys the singleton before the next independent HTTP request.
	}
}
