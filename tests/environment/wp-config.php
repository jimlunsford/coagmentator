<?php
/**
 * Disposable HTTP environment configuration.
 *
 * @package Coagmentator
 */

define( 'COAGMENTATOR_GUARD_REGISTRY', '/run/coagmentator/registry.json' );
define( 'COAGMENTATOR_GUARD_FEATURE_POLICY', '/run/coagmentator/policy.json' );
define( 'DB_NAME', 'wordpress' );
define( 'DB_USER', 'c01' );
define( 'DB_PASSWORD', trim( file_get_contents( '/run/secrets/database_password' ) ) );
define( 'DB_HOST', 'database' );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );
define( 'WP_HOME', 'https://wordpress.test' );
define( 'WP_SITEURL', 'https://wordpress.test' );
define( 'DISABLE_WP_CRON', true );
define( 'AUTOMATIC_UPDATER_DISABLED', true );
define( 'DISALLOW_FILE_MODS', true );
define( 'WP_HTTP_BLOCK_EXTERNAL', true );
define( 'WP_DEBUG', false );
// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Required isolated WordPress configuration.
$table_prefix = 'c01_';
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}
require_once ABSPATH . 'wp-settings.php';
