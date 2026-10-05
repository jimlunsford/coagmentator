<?php
/**
 * MU-owned external dispatch scope and safe failure finalization.
 *
 * @package Coagmentator
 */

namespace Coagmentator\Guard;

/** Service requests cannot recursively enter core dispatch. */
final class Guarded_REST_Server extends \WP_REST_Server {
	/** @var Dispatch_Scope Balanced lifecycle. */
	private Dispatch_Scope $scope;
	/** @var bool External serve cycle is active. */
	private bool $serving = false;

	/** Construct without a normal-plugin dependency. */
	public function __construct() {
		parent::__construct();
		$this->scope = new Dispatch_Scope();
	}

	/**
	 * Safe closed read failure, with no incoming WordPress error details.
	 *
	 * @return \WP_REST_Response Denial.
	 */
	public static function denial(): \WP_REST_Response {
		return new \WP_REST_Response(
			array(
				'ok'               => false,
				'contract_version' => '1.0',
				'correlation_id'   => wp_generate_uuid4(),
				'site_id'          => null,
				'error'            => array(
					'code'                => 'AUTHORIZATION_DENIED',
					'message'             => 'This request is not permitted.',
					'origin'              => 'bridge',
					'retryable'           => false,
					'retry_after_seconds' => null,
					'write_state'         => 'not_applied',
					'details'             => array( 'fields' => array(), 'reason' => null, 'approval' => null ),
				),
				'receipt'          => null,
			),
			403,
			array( 'Cache-Control' => 'no-store' )
		);
	}

	/**
	 * Inspect before core buffers the body or runs argument validation.
	 *
	 * @param string|null $path Core route.
	 * @return false|null Core serving result.
	 */
	public function serve_request( $path = null ) {
		$guard = Guard::instance();
		if ( $this->serving || ! $this->scope->begin() ) {
			return false;
		}
		$this->serving = true;
		try {
			if ( isset( $GLOBALS['current_user'] ) && $GLOBALS['current_user'] instanceof \WP_User && ! $GLOBALS['current_user']->exists() ) {
				// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Match core's deferred REST authentication behavior.
				$GLOBALS['current_user'] = null;
			}
			get_current_user_id();
			$route = is_string( $path ) ? $path : '/';
			if ( $guard->restricted() || str_starts_with( $route, '/coagmentator/' ) ) {
				if ( ! $guard->admits( $route ) || ! Route_Boundary::canonical( $route, 'POST', $_SERVER, $guard->prefix() ) ) {
					return $this->emit_denial();
				}
				// A bounded read replaces core's otherwise unbounded php://input read.
				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Fixed input stream, hard byte bound before buffering.
				$body = file_get_contents( 'php://input', false, null, 0, 2097153 );
				if ( false === $body || strlen( $body ) > 2097152 ) {
					return $this->emit_denial();
				}
				// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Core's documented REST raw-body cache; bounded at ingress.
				$GLOBALS['HTTP_RAW_POST_DATA'] = $body;
			}
			return parent::serve_request( $path );
		} catch ( \Throwable $failure ) {
			return $this->emit_denial();
		} finally {
			$this->scope->finish();
			$this->serving = false;
			$guard->finish();
		}
	}

	/** @return false Safely emitted failure without JSONP/envelope handling. */
	private function emit_denial(): bool {
		$this->set_status( 403 );
		$this->send_header( 'Content-Type', 'application/json; charset=UTF-8' );
		$this->send_header( 'Cache-Control', 'no-store' );
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Closed JSON response, no reflected input.
		echo wp_json_encode( self::denial()->get_data() );
		return false;
	}

	/**
	 * Enforce before core's filters, matching, argument validators and callbacks.
	 *
	 * @param \WP_REST_Request $request Actual request.
	 * @return \WP_REST_Response Core response or denial.
	 */
	public function dispatch( $request ) {
		$guard      = Guard::instance();
		$restricted = $guard->restricted() || str_starts_with( $request->get_route(), '/coagmentator/' );
		if ( $restricted && ( ! $guard->admits( $request->get_route() ) || ! Route_Boundary::canonical( $request->get_route(), $request->get_method(), $_SERVER, $guard->prefix() ) || ! $this->scope->enter( $request ) ) ) {
			return self::denial();
		}
		$stack_depth = count( $this->dispatching_requests );
		try {
			return parent::dispatch( $request );
		} finally {
			$this->dispatching_requests = array_slice( $this->dispatching_requests, 0, $stack_depth );
			if ( $restricted ) {
				$this->scope->leave();
			}
		}
	}

	/**
	 * Only the actual top-level request object can pass the fallback hooks.
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return bool Current admission.
	 */
	public function admitted( \WP_REST_Request $request ): bool {
		return $this->scope->matches( $request );
	}
}
