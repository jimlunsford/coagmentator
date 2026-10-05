<?php
/**
 * Credential issuance requires these guard/recovery controls to pass first.
 *
 * @package Coagmentator
 */

/** Runs with an empty UUID allowlist and no Application Passwords. */
final class PreflightTest extends GuardHttpCase {
	/** Independent guard denial and human recovery precede issuance. */
	public function test_guard_before_credentials(): void {
		self::assertArrayNotHasKey( 'service_secret', $this->fixture );
		self::assertArrayNotHasKey( 'human_secret', $this->fixture );
		foreach ( array( 'site_info', 'search_content', 'get_content', 'list_terms', 'search_media', 'get_media', 'get_metadata', 'list_revisions', 'get_revision' ) as $operation ) {
			$this->denied( '/wp-json/coagmentator/v1/' . $operation, 'POST', 'service-cookie' );
			$this->denied( '/wp-json/coagmentator/v1/' . $operation, 'POST', 'human-cookie' );
		}
		$this->denied( '/wp-json/c02/v1/target', 'GET', 'service-cookie' );
		$this->denied( '/wp-admin/', 'GET', 'service-cookie' );
		$this->denied( '/c02-target.php', 'GET', '', '', array( 'X-C02-User: service' ) );
		$this->human_recovery();
	}
}
