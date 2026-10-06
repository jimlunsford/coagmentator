<?php
/**
 * Server transport evidence and the explicit early host bootstrap boundary.
 *
 * @package Coagmentator
 */

namespace Coagmentator\Auth;

use Coagmentator\Config\Feature_Config;

/** Transport never supplies user or credential evidence. */
final class Transport_Evidence {

	/**
	 * Read current raw server facts; caller cannot supply a peer or header.
	 *
	 * @param Feature_Config $config Closed host policy.
	 * @param string         $route Fixed route from trusted code.
	 * @return bool Current transport binding.
	 */
	public static function current( Feature_Config $config, string $route ): bool {
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- Reject exact raw transport bytes, never sanitize them into authority.
		return $config->transport()->allows( $_SERVER, $route );
	}

	/**
	 * Host calls this before wp-settings.php, never from a request-selected file.
	 * Only a fully bound proxy request receives the HTTPS signal required by C02.
	 * Direct TLS leaves the actual web-server HTTPS state untouched.
	 *
	 * @param Feature_Config|null $config Fresh host policy, null fails closed.
	 */
	public static function bootstrap( ?Feature_Config $config ): void {
		if ( null !== $config && ! $config->transport()->proxied() ) {
			return;
		}
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- Exact raw peer/header checks before normalizing only the core HTTPS signal.
		$allowed = null !== $config && $config->transport()->secure_connection( $_SERVER );
		// Prevent core's port-only HTTPS fallback on an untrusted proxy request.
		$_SERVER['HTTPS']       = $allowed ? 'on' : 'off';
		$_SERVER['SERVER_PORT'] = $allowed ? '443' : '80';
	}
}
