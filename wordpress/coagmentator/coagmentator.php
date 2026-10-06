<?php
/**
 * Plugin Name: Coagmentator
 * Description: Authentication and configuration foundation. No bridge operations are available.
 * Version: 0.1.0-dev.c01
 * Requires at least: 7.1.2
 * Requires PHP: 8.3
 * License: AGPL-3.0-or-later
 *
 * @package Coagmentator
 */

if ( ! defined( 'ABSPATH' ) ) {
	return false;
}

// Check the language floor before loading any modern package source.
if ( version_compare( PHP_VERSION, '8.3', '<' ) ) {
	return false;
}

require_once __DIR__ . '/src/class-environment.php';

if ( array() !== \Coagmentator\Environment::issues(
	PHP_VERSION,
	isset( $GLOBALS['wp_version'] ) ? (string) $GLOBALS['wp_version'] : '',
	PHP_INT_SIZE,
	get_loaded_extensions(),
	defined( 'MULTISITE' ) && MULTISITE
) ) {
	return false;
}

require_once __DIR__ . '/src/foundation.php';

// A normal plugin can observe core evidence only when the independent MU exists.
if ( class_exists( \Coagmentator\Guard\Guard::class, false ) && function_exists( 'add_action' ) ) {
	\Coagmentator\Auth\Authentication_Evidence::boot();
}

// C03A adds no routes, roles, credentials, capabilities or operational storage.
return true;
