<?php
/**
 * Disposable plugin probes. No configuration/provisioning HTTP endpoint.
 *
 * @package Coagmentator
 */

if ( 'disposable' !== getenv( 'C01_TEST_ENVIRONMENT' ) ) {
	exit( 1 );
}
add_filter( 'pre_wp_mail', '__return_true' );
add_filter(
	'determine_current_user',
	static function ( $id ) {
		if ( isset( $_SERVER['HTTP_X_C02_USER'] ) ) {
			return (int) get_option( 'c02_' . ( 'human' === $_SERVER['HTTP_X_C02_USER'] ? 'human' : 'service' ) );
		}
		return $id;
	},
	90
);
add_action(
	'rest_api_init',
	static function (): void {
		register_rest_route(
			'c02/v1',
			'/target',
			array(
				'methods'             => 'GET,POST,DELETE,PATCH',
				'callback'            => 'c02_target',
				'permission_callback' => '__return_true',
			)
		);
		// Same namespace is deliberately insufficient authority.
		register_rest_route(
			'coagmentator/v1',
			'/get_content',
			array(
				'methods'             => 'POST',
				'callback'            => 'c02_target',
				'permission_callback' => '__return_true',
			)
		);
	}
);
/**
 * Target invocation is an immediate test failure.
 *
 * @return array<string, bool> Target invocation is an immediate test failure.
 */
function c02_target(): array {
	update_option( 'c02_targets', (int) get_option( 'c02_targets', 0 ) + 1 );
	return array( 'target' => true );
}
add_filter(
	'rest_dispatch_request',
	static function ( $result ) {
		if ( null === $result ) {
			update_option( 'c02_targets', (int) get_option( 'c02_targets', 0 ) + 1 );
		}
		return $result;
	},
	PHP_INT_MAX
);
add_action( 'admin_post_c02_target', 'c02_target' );
add_action( 'wp_ajax_c02_target', 'c02_target' );

add_action(
	'rest_api_init',
	static function (): void {
		if ( get_option( 'c02_replacement', false ) ) {
			register_rest_route(
				'coagmentator/v1',
				'/site_info',
				array(
					'methods'             => 'POST',
					'callback'            => 'c02_target',
					'permission_callback' => 'c02_target',
					'args'                => array( 'probe' => array( 'validate_callback' => 'c02_target' ) ),
				),
				true
			);
		}
	},
	99
);
