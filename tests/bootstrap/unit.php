<?php
/**
 * Modern runner bootstrap, independent of WordPress and its vendor tree.
 *
 * @package Coagmentator
 */

// Load the pure prerequisite evaluator without booting WordPress.
require_once dirname( __DIR__, 2 ) . '/wordpress/coagmentator/src/class-environment.php';

// Pure guard classes have no WordPress or vendor dependency.
require_once dirname( __DIR__, 2 ) . '/wordpress/mu-plugins/coagmentator-guard/src/class-guard-config.php';
require_once dirname( __DIR__, 2 ) . '/wordpress/mu-plugins/coagmentator-guard/src/class-route-boundary.php';
require_once dirname( __DIR__, 2 ) . '/wordpress/mu-plugins/coagmentator-guard/src/class-dispatch-scope.php';

require_once dirname( __DIR__, 2 ) . '/wordpress/coagmentator/src/foundation.php';
