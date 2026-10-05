<?php
/**
 * B02 guard-only, missing components, emergency recovery and callback substitution.
 *
 * @package Coagmentator
 */

/** The shell runner supplies separate fresh-process component scenarios. */
final class GuardIndependenceTest extends GuardHttpCase {
	/** None of the nine names authorizes a missing or replaced handler. */
	public function test_bridge_denial(): void {
		foreach ( array( 'site_info', 'search_content', 'get_content', 'list_terms', 'search_media', 'get_media', 'get_metadata', 'list_revisions', 'get_revision' ) as $operation ) {
			foreach ( array( '', 'service-basic', 'human-cookie' ) as $auth ) {
				$this->denied( '/wp-json/coagmentator/v1/' . $operation, 'POST', $auth );
			}
		}
	}

	/** Emergency denies valid human Application Passwords without blocking recovery. */
	public function test_emergency_credentials(): void {
		if ( in_array( $this->fixture['scenario'], array( 'missing-registry', 'malformed-registry', 'missing-registry-active', 'malformed-registry-active', 'unreadable-registry', 'missing-support' ), true ) ) {
			$this->denied( '/wp-json/c02/v1/target', 'GET', 'human-basic' );
			$this->denied( '/c02-target.php', 'GET', '', '', array( 'X-C02-User: human' ) );
		} else {
			// Healthy controls must have no anonymous bridge success either.
			$this->denied( '/wp-json/coagmentator/v1/site_info', 'POST', '' );
		}
	}
}
