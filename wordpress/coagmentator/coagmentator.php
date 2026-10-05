<?php
/**
 * Plugin Name: Coagmentator
 * Description: Package skeleton only. No bridge operations are available.
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

// C01 loads only the package. No hooks, routes, roles or identities are created.
return true;
