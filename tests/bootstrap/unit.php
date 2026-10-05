<?php
/**
 * Modern runner bootstrap, independent of WordPress and its vendor tree.
 *
 * @package Coagmentator
 */

// Load the pure prerequisite evaluator without booting WordPress.
require_once dirname( __DIR__, 2 ) . '/wordpress/coagmentator/src/class-environment.php';
