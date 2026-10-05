<?php
/**
 * G02 canonical transport denial through a real edge and core routing.
 *
 * @package Coagmentator
 */

/** Exact route bytes and POST are the only future surface. */
final class RouteNormalizationTest extends GuardHttpCase {
	/** Aliases cannot reach a callback, even if Nginx/core normalize them. */
	public function test_aliases(): void {
		$path = '/wp-json/coagmentator/v1/site_info';
		foreach ( array( 'GET', 'HEAD', 'OPTIONS', 'PUT', 'DELETE', 'PATCH' ) as $method ) {
			$this->denied( $path, $method );
		}
		foreach ( array(
			$path . '/',
			'/wp-json//coagmentator/v1/site_info',
			'/wp-json/coagmentator//v1/site_info',
			'/wp-json/coagmentator%2fv1/site_info',
			'/wp-json/coagmentator%252fv1/site_info',
			'/wp-json/coagmentator%5cv1/site_info',
			'/wp-json/coagmentator/../coagmentator/v1/site_info',
			'/wp-json/Coagmentator/v1/site_info',
			'/?rest_route=/coagmentator/v1/site_info',
			$path . '?_method=POST',
			$path . '?_jsonp=callback',
			$path . '?_envelope=1',
			$path . '?_embed=1',
			'/wp-json/coagmentator/v1/arbitrary',
		) as $alias ) {
			$this->denied( $alias, 'POST' );
		}
		foreach ( array( 'X-HTTP-Method-Override: POST', 'X-Method-Override: POST', 'X-HTTP-Method: POST' ) as $header ) {
			$this->denied( $path, 'POST', 'service-basic', '', array( $header ) );
		}
	}
}
