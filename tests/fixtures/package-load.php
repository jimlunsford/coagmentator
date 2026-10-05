<?php
/**
 * Fresh-process package load probe with no Composer runtime dependency.
 *
 * @package Coagmentator
 */
define( 'ABSPATH', __DIR__ . '/' );
$GLOBALS['wp_version'] = $argv[1] ?? '7.1.2';
$result                = require dirname( __DIR__, 2 ) . '/wordpress/coagmentator/coagmentator.php';
if ( 'reject' === ( $argv[2] ?? '' ) ) {
	exit( false === $result ? 0 : 1 );
}
exit( true === $result ? 0 : 1 );
