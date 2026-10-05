<?php
/**
 * Isolated WordPress core test database.
 *
 * @package Coagmentator
 */
define( 'ABSPATH', dirname( __DIR__, 2 ) . '/.runtime/wordpress/src/' );
define( 'DB_NAME', 'wp_tests' );
define( 'DB_USER', 'c01' );
define( 'DB_PASSWORD', trim( file_get_contents( '/run/secrets/database_password' ) ) );
define( 'DB_HOST', 'database' );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );
define( 'WP_TESTS_DOMAIN', 'wordpress.test' );
define( 'WP_TESTS_EMAIL', 'fixture@example.test' );
define( 'WP_TESTS_TITLE', 'C01 isolated fixture' );
define( 'WP_PHP_BINARY', PHP_BINARY );
define( 'WP_HTTP_BLOCK_EXTERNAL', true );
define( 'AUTOMATIC_UPDATER_DISABLED', true );
$table_prefix = 'c01_tests_';
