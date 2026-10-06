<?php
/**
 * Conjunctive identity/binding check, without capabilities or read operations.
 *
 * @package Coagmentator
 */

namespace Coagmentator\Auth;

use Coagmentator\Config\Feature_Config;
use Coagmentator\Guard\Guard;

/** No method accepts a credential UUID or WordPress user claimed by a caller. */
final class Bridge_Identity {
	/**
	 * Evaluate live event evidence before using claimed envelope bindings.
	 * Later capabilities and operational admission remain mandatory.
	 *
	 * @param Feature_Config $config Closed host policy.
	 * @param string         $route Fixed operation route from trusted code.
	 * @param string         $site Envelope site to compare, never to select policy.
	 * @param string         $actor Envelope actor to compare, never an authority.
	 * @return bool Identity foundation only; no reusable grant is returned.
	 */
	public static function allows( Feature_Config $config, string $route, string $site, string $actor ): bool {
		$evidence = Authentication_Evidence::current( $route );
		return null !== $evidence && Transport_Evidence::current( $config, $route ) && $config->enables( $route, Guard::instance()->prefix() ) && $config->matches( $evidence->user_id, $evidence->credential_uuid, $site, $actor, time() );
	}
}
