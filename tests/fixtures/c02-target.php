<?php
/**
 * Non-REST callback sentinel in the isolated environment only.
 *
 * @package Coagmentator
 */

require __DIR__ . '/wp-load.php';
update_option( 'c02_targets', (int) get_option( 'c02_targets', 0 ) + 1 );
header( 'Content-Type: application/json' );
echo '{"target":true}';
