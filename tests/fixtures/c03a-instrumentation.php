<?php
/**
 * Disposable observers call the normal foundation even when the guard denies.
 * No request selects configuration, scenarios or credential administration.
 *
 * @package Coagmentator
 */

if ( 'disposable' !== getenv( 'C01_TEST_ENVIRONMENT' ) ) {
	exit( 1 );
}
if ( ! defined( 'COAGMENTATOR_FEATURE_CONFIG' ) ) {
	define( 'COAGMENTATOR_FEATURE_CONFIG', '/run/coagmentator/feature.json' );
}
add_filter( 'pre_wp_mail', '__return_true' );
add_filter( 'wp_is_application_passwords_available', static fn( $available ) => 'app-disabled' === get_option( 'c03b_case' ) ? false : $available );
add_filter( 'wp_is_application_passwords_available_for_user', static fn( $available ) => 'app-user-disabled' === get_option( 'c03b_case' ) ? false : $available );

/**
 * Test only: evaluate fixed envelope claims against real normal-plugin code.
 *
 * @return bool Foundation's actual answer.
 */
function c03a_check(): bool {
	$config = Coagmentator\Config\Feature_Config::load();
	$case   = get_option( 'c03a_case' );
	$site   = 'wrong-site' === $case ? '5a7bc459-9999-4350-aaa0-426614174000' : 'f3858c09-8c56-48fd-99d1-ab75862bb955';
	$actor  = 'wrong-actor' === $case ? 'other-actor' : 'operator-1';
	$allow  = null !== $config && Coagmentator\Auth\Bridge_Identity::allows( $config, '/coagmentator/v1/site_info', $site, $actor );
	$GLOBALS['c03a_observed']['transport_checks'][] = null !== $config && Coagmentator\Auth\Transport_Evidence::current( $config, '/coagmentator/v1/site_info' );
	$GLOBALS['c03a_observed']['checks'][]           = $allow;
	return $allow;
}
add_action(
	'plugins_loaded',
	static function (): void {
		// This loads definitions but deliberately does not boot the observer when
		// the normal plugin is absent in the missing-evidence scenario.
		require_once WP_PLUGIN_DIR . '/coagmentator/src/foundation.php';
		if ( Coagmentator\Guard\Guard::instance()->prefix() . '/coagmentator/v1/site_info' !== ( $_SERVER['REQUEST_URI'] ?? '' ) ) {
			return;
		}
		$GLOBALS['c03a_observed'] = array(
			'transport_checks'         => array(),
			'checks'                   => array(),
			'events'                   => 0,
			'outer'                    => 0,
			'removed'                  => false,
			'serialization_denied'     => false,
			'stale_denied'             => false,
			'uuid_differs_from_app_id' => false,
		);
		add_action( 'set_current_user', 'c03a_check', PHP_INT_MAX );
		add_action( 'rest_api_init', 'c03a_check', PHP_INT_MAX );
		add_action(
			'application_password_did_authenticate',
			static function ( WP_User $user, array $item ): void {
				++$GLOBALS['c03a_observed']['events'];
				$GLOBALS['c03a_observed']['uuid_differs_from_app_id'] = $item['uuid'] !== $item['app_id'];
				$case = get_option( 'c03a_case' );
				if ( 'revoke-after-event' === $case ) {
					WP_Application_Passwords::delete_application_password( $user->ID, $item['uuid'] );
					$GLOBALS['c03a_observed']['removed'] = null === WP_Application_Passwords::get_user_application_password( $user->ID, $item['uuid'] );
				} elseif ( 'mismatch' === $case ) {
					wp_set_current_user( (int) get_option( 'c02_human' ) );
				}
			},
			PHP_INT_MAX,
			2
		);
		add_filter(
			'determine_current_user',
			static function ( $id ) {
				if ( isset( $_SERVER['HTTP_X_C03A_USER'] ) ) {
					return (int) get_option( 'c02_' . ( 'human' === $_SERVER['HTTP_X_C03A_USER'] ? 'human' : 'service' ) );
				}
				return $id;
			},
			90
		);
		add_action(
			'shutdown',
			static function (): void {
				update_option( 'c03a_observed', $GLOBALS['c03a_observed'] );
			}
		);
	}
);
