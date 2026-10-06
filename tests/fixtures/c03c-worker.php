<?php
/**
 * Bounded independent CLI worker for persistent rates and actual flock contention.
 *
 * @package Coagmentator
 */

use Coagmentator\Infrastructure\Local_Store;
use Coagmentator\Infrastructure\Operational_Failure;
use Coagmentator\Infrastructure\Rate_Admission;
use Coagmentator\Infrastructure\Read_Slot;
use Coagmentator\Infrastructure\Server_Clock;

if ( 'cli' !== PHP_SAPI ) {
	exit( 1 );
}
require dirname( __DIR__ ) . '/bootstrap/unit.php';
require __DIR__ . '/c03c-storage.php';
$root      = $argv[1] ?? '';
$c03c_mode = $argv[2] ?? '';
try {
	$store = new Local_Store( $root . '/admission', array( dirname( __DIR__, 2 ) ) );
	if ( 'rate' === $c03c_mode || 'peer' === $c03c_mode ) {
		$rate = new Rate_Admission( $store, new Server_Clock( static fn() => 1800000000 ) );
		if ( 'rate' === $c03c_mode ) {
			$rate->authenticated( c03c_config( $root ) );
		} else {
			$rate->preauth( array( 'REMOTE_ADDR' => '192.0.2.1' ) );
		}
		echo "admitted\n";
	} else {
		$slot = new Read_Slot( $store );
		echo "held\n";
		flush();
		if ( 'hold' === $c03c_mode ) {
			$read   = array( STDIN );
			$write  = null;
			$except = null;
			if ( 1 !== stream_select( $read, $write, $except, 15 ) || "release\n" !== fgets( STDIN ) ) {
				exit( 2 );
			}
		}
		$slot->release();
	}
} catch ( Operational_Failure $failure ) {
	echo $failure->getMessage() . "\n";
}
