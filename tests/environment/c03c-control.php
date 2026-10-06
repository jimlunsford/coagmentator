<?php
/**
 * Trusted disposable storage scenarios; no public provisioning or control route.
 *
 * @package Coagmentator
 */

if ( 'cli' !== PHP_SAPI || 'disposable' !== getenv( 'C01_TEST_ENVIRONMENT' ) ) {
	exit( 1 );
}
$root = dirname( __DIR__, 2 );
require $root . '/.runtime/wordpress/src/wp-load.php';
require $root . '/tests/fixtures/c03c-storage.php';
$data      = json_decode( file_get_contents( $root . '/.runtime/c02-fixtures.json' ), true, 512, JSON_THROW_ON_ERROR );
$c03c_case = $argv[1] ?? '';
$storage   = '/var/coagmentator-c03c';
// This mount is newly allocated outside the repository by the test runner.
$files = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $storage, FilesystemIterator::SKIP_DOTS ), RecursiveIteratorIterator::CHILD_FIRST );
foreach ( $files as $file ) {
	if ( $file->isDir() && ! $file->isLink() ) {
		rmdir( $file->getPathname() );
	} else {
		unlink( $file->getPathname() );
	}
}
chown( $storage, 0 );
c03c_storage( $storage );
mkdir( $storage . '/control', 0700 );
$policy                                   = c03a_policy( $data['service'], $data['service_uuid'] );
$policy['storage']['admission_directory'] = $storage . '/admission';
$policy['storage']['audit_directory']     = $storage . '/audit';
file_put_contents( $root . '/.runtime/guard-config/feature.json', json_encode( $policy, JSON_UNESCAPED_SLASHES ) );
update_option( 'c03a_case', 'approved' );
update_option( 'c03b_case', 'direct' );
update_option( 'c03c_case', $c03c_case );
if ( 'wrong-site' === $c03c_case ) {
	update_option( 'c03a_case', 'wrong-site' );
}
$counter = $storage . '/admission/read-' . hash( 'sha256', $policy['site_id'] );
if ( 'read-full' === $c03c_case || 'read-corrupt' === $c03c_case ) {
	file_put_contents( $counter, 'read-full' === $c03c_case ? str_repeat( 'x', 129 ) : '{broken' );
	chmod( $counter, 0600 );
} elseif ( 'read-unwritable' === $c03c_case ) {
	file_put_contents( $counter, "{}\n" );
	chmod( $counter, 0400 );
} elseif ( 'audit-unwritable' === $c03c_case ) {
	chmod( $storage . '/audit/events', 0400 );
} elseif ( 'audit-corrupt' === $c03c_case ) {
	file_put_contents( $storage . '/audit/events', "{broken\n" );
} elseif ( 'audit-clock-corrupt' === $c03c_case ) {
	file_put_contents( $storage . '/audit/clock', "{broken\n" );
} elseif ( 'audit-full' === $c03c_case ) {
	$line = json_encode(
		array(
			'timestamp' => time(),
			'operation' => 'site_info',
			'site_id'   => $policy['site_id'],
			'outcome'   => 'started',
		)
	) . "\n";
	file_put_contents( $storage . '/audit/events', str_repeat( $line, intdiv( Coagmentator\Infrastructure\Read_Audit::CAPACITY, strlen( $line ) ) ) );
} elseif ( 'audit-missing' === $c03c_case ) {
	unlink( $storage . '/audit/events' );
} elseif ( 'slot-corrupt' === $c03c_case ) {
	file_put_contents( $storage . '/admission/slot-0', 'broken' );
} elseif ( 'preauth-corrupt' === $c03c_case ) {
	file_put_contents( $storage . '/admission/clock', 'broken' );
} elseif ( 'preauth-unwritable' === $c03c_case ) {
	chmod( $storage . '/admission/clock', 0400 );
}
if ( 'audit-full' === $c03c_case ) {
	file_put_contents( $storage . '/audit/state', Coagmentator\Infrastructure\Read_Audit::fingerprint( file_get_contents( $storage . '/audit/events' ) ) );
}
// Root setup gives only the FPM worker ownership. Parent and files remain private.
$files = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $storage, FilesystemIterator::SKIP_DOTS ), RecursiveIteratorIterator::SELF_FIRST );
foreach ( $files as $file ) {
	chown( $file->getPathname(), 33 );
}
chown( $storage, 33 );
chmod( $storage, 0700 );
$data['c03c_scenario'] = $c03c_case;
file_put_contents( $root . '/.runtime/c02-fixtures.json', json_encode( $data ) );
echo "C03C disposable storage scenario ready; no credentials issued.\n";
