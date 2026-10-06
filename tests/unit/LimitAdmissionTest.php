<?php
/**
 * C03C-owned L01 and E02 controls with independent worker evidence.
 *
 * @package Coagmentator
 */

use Coagmentator\Infrastructure\Local_Store;
use Coagmentator\Infrastructure\Operational_Failure;
use Coagmentator\Infrastructure\Rate_Admission;
use Coagmentator\Infrastructure\Read_Audit;
use Coagmentator\Infrastructure\Read_Deadline;
use Coagmentator\Infrastructure\Read_Slot;
use Coagmentator\Infrastructure\Server_Clock;
use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__ ) . '/fixtures/c03c-storage.php';

/** Pure infrastructure controls, not a mock WordPress authorization claim. */
final class LimitAdmissionTest extends TestCase {
	/**
	 * Disposable local root.
	 *
	 * @var string Disposable local root.
	 */
	private string $root;
	/**
	 * Trusted test wall clock.
	 *
	 * @var int Trusted test wall clock.
	 */
	private int $now = 1800000000;
	/**
	 * Private admission store.
	 *
	 * @var Local_Store Private admission store.
	 */
	private Local_Store $store;
	/**
	 * Deterministic clock.
	 *
	 * @var Server_Clock Deterministic clock.
	 */
	private Server_Clock $clock;

	/** Create fresh local files, never a host or production directory. */
	protected function setUp(): void {
		$this->root = sys_get_temp_dir() . '/coagmentator-c03c-' . bin2hex( random_bytes( 8 ) );
		c03c_storage( $this->root );
		$this->store = new Local_Store( $this->root . '/admission', array( dirname( __DIR__, 2 ) ) );
		$this->clock = new Server_Clock( fn() => $this->now );
	}

	/** Remove disposable files even after a failed assertion. */
	protected function tearDown(): void {
		$files = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $this->root, FilesystemIterator::SKIP_DOTS ), RecursiveIteratorIterator::CHILD_FIRST );
		foreach ( $files as $file ) {
			if ( $file->isDir() && ! $file->isLink() ) {
				chmod( $file->getPathname(), 0700 );
				rmdir( $file->getPathname() );
			} else {
				unlink( $file->getPathname() );
			}
		}
		rmdir( $this->root );
	}

	/**
	 * Assert a closed failure without propagating its original input.
	 *
	 * @param Closure $operation Test action.
	 * @param string  $reason Expected safe reason.
	 */
	private function fails( Closure $operation, string $reason = 'operational_storage_failed' ): void {
		try {
			$operation();
			self::fail( 'Required fail-closed boundary admitted work.' );
		} catch ( Operational_Failure $failure ) {
			self::assertSame( $reason, $failure->getMessage() );
		}
	}

	/** Sixty read admissions, sixty-first denial, independent process and rollover. */
	public function test_authenticated_60_61_restart_and_rollover(): void {
		$config = c03c_config( $this->root );
		$rate   = new Rate_Admission( $this->store, $this->clock );
		for ( $index = 0; $index < 60; ++$index ) {
			$rate->authenticated( $config );
			self::assertTrue( true );
		}
		$this->fails( fn() => $rate->authenticated( $config ), 'rate_limit' );
		self::assertSame( 'rate_limit', $this->worker_once( 'rate' ) );
		$this->now += 60;
		$rate->authenticated( $config );
		self::assertTrue( true );
		--$this->now;
		$this->fails( fn() => $rate->authenticated( $config ) );
	}

	/** Actual canonical peer selects one persistent bucket, never forwarding claims. */
	public function test_preauth_120_121_peer_forgery_restart_and_rollover(): void {
		$rate = new Rate_Admission( $this->store, $this->clock );
		for ( $index = 0; $index < 120; ++$index ) {
			$rate->preauth(
				array(
					'REMOTE_ADDR'          => '192.0.2.1',
					'HTTP_X_FORWARDED_FOR' => '198.51.100.' . $index,
					'HTTP_FORWARDED'       => 'for=203.0.113.1',
				)
			);
			self::assertTrue( true );
		}
		$this->fails( fn() => $rate->preauth( array( 'REMOTE_ADDR' => '192.0.2.1' ) ), 'rate_limit' );
		self::assertSame( 'rate_limit', $this->worker_once( 'peer' ) );
		self::assertCount( 1, glob( $this->root . '/admission/peer-*' ) );
		$this->now += 60;
		$rate->preauth( array( 'REMOTE_ADDR' => '192.0.2.1' ) );
		foreach ( array( '', '192.0.2.01', '::ffff:192.0.2.1', '2001:DB8::1' ) as $peer ) {
			$this->fails( fn() => $rate->preauth( array( 'REMOTE_ADDR' => $peer ) ) );
		}
	}

	/** A full peer store refuses new peers without evicting active accounting. */
	public function test_preauth_capacity_and_expired_bucket_retirement(): void {
		$rate = new Rate_Admission( $this->store, $this->clock );
		for ( $index = 0; $index < Rate_Admission::PEER_BUCKETS; ++$index ) {
			$peer = '10.0.' . intdiv( $index, 256 ) . '.' . ( $index % 256 );
			$rate->preauth( array( 'REMOTE_ADDR' => $peer ) );
		}
		$before = glob( $this->root . '/admission/peer-*' );
		$this->fails( fn() => $rate->preauth( array( 'REMOTE_ADDR' => '192.0.2.1' ) ) );
		self::assertSame( $before, glob( $this->root . '/admission/peer-*' ) );
		$rate->preauth( array( 'REMOTE_ADDR' => '10.0.0.0' ) );
		$this->now += 60;
		$rate->preauth( array( 'REMOTE_ADDR' => '192.0.2.1' ) );
		self::assertCount( Rate_Admission::PEER_BUCKETS, glob( $this->root . '/admission/peer-*' ) );
	}

	/** Partial/invalid records never become zero; unwritable/oversized state denies. */
	public function test_corrupt_full_unwritable_counters_and_nonblocking_lock(): void {
		$config = c03c_config( $this->root );
		$rate   = new Rate_Admission( $this->store, $this->clock );
		$rate->authenticated( $config );
		$path = glob( $this->root . '/admission/read-*' )[0];
		foreach ( array( '', '{}', '{"window":30000000,"last":1800000000,"count":"1"}', str_repeat( 'x', 129 ) ) as $corruption ) {
			file_put_contents( $path, $corruption );
			$this->fails( fn() => $rate->authenticated( $config ) );
		}
		chmod( $path, 0400 );
		$this->fails( fn() => $rate->authenticated( $config ) );
		$rate->preauth( array( 'REMOTE_ADDR' => '192.0.2.1' ) );
		$peer = glob( $this->root . '/admission/peer-*' )[0];
		file_put_contents( $peer, '{broken' );
		$this->fails( fn() => $rate->preauth( array( 'REMOTE_ADDR' => '192.0.2.1' ) ) );
		$lock = $this->store->lock( 'control.lock' );
		self::assertIsResource( $lock );
		$start = hrtime( true );
		$this->fails( fn() => $rate->authenticated( $config ) );
		self::assertLessThan( 1000000000, hrtime( true ) - $start );
		fclose( $lock );
	}

	/** Missing/non-directory/unsafe permissions/symlinks and corrupt slots fail closed. */
	public function test_storage_readiness_denials(): void {
		foreach ( array( $this->root . '/missing', $this->root . '/admission/clock', 'php://memory' ) as $path ) {
			$this->fails( fn() => new Local_Store( $path, array() ) );
		}
		$this->fails( fn() => new Local_Store( $this->root . '/admission', array( $this->root ) ) );
		chmod( $this->root . '/admission', 0777 );
		$this->fails( fn() => new Local_Store( $this->root . '/admission', array() ) );
		chmod( $this->root . '/admission', 0700 );
		symlink( $this->root . '/admission', $this->root . '/alias' );
		$this->fails( fn() => new Local_Store( $this->root . '/alias', array() ) );
		unlink( $this->root . '/admission/slot-0' );
		symlink( $this->root . '/audit/events', $this->root . '/admission/slot-0' );
		$this->fails( fn() => new Read_Slot( $this->store ) );
	}

	/** Volatile/non-allowlisted mounts fail setup instead of claiming persistence. */
	public function test_incompatible_filesystem_fails_setup(): void {
		$directory = '/dev/shm/coagmentator-c03c-' . bin2hex( random_bytes( 8 ) );
		mkdir( $directory, 0700 );
		try {
			$this->fails( fn() => new Local_Store( $directory, array() ) );
		} finally {
			rmdir( $directory );
		}
	}

	/** The accepted 15 seconds remains fixed and every clock failure is sticky. */
	public function test_deadline_is_local_monotonic_and_fixed(): void {
		$tick     = 100;
		$clock    = new Server_Clock(
			null,
			static function () use ( &$tick ) {
				return $tick;
			}
		);
		$deadline = new Read_Deadline( $clock );
		self::assertSame( 15000000000, $deadline->remaining() );
		$tick += 14999999999;
		self::assertSame( 1, $deadline->remaining() );
		++$tick;
		$this->fails( fn() => $deadline->remaining(), 'deadline_exceeded' );
		$tick = 100;
		$this->fails( fn() => $deadline->remaining(), 'deadline_exceeded' );
		$other = new Read_Deadline( $clock );
		--$tick;
		$this->fails( fn() => $other->remaining(), 'deadline_exceeded' );
		foreach ( array( -1, NAN, INF, '100', null ) as $invalid ) {
			$this->fails( fn() => new Read_Deadline( new Server_Clock( null, static fn() => $invalid ) ) );
			$this->fails( fn() => ( new Server_Clock( static fn() => $invalid ) )->now() );
		}
	}

	/** Exact schema, no raw marker fields; retention never deletes active evidence. */
	public function test_audit_schema_disclosure_retention_and_corruption(): void {
		$audit = new Read_Audit( new Local_Store( $this->root . '/audit', array() ), $this->clock );
		$site  = c03c_config( $this->root )->site();
		$audit->append( $site, 'site_info', 'started' );
		$path   = $this->root . '/audit/events';
		$before = file_get_contents( $path );
		foreach ( array( 'FAKE_CREDENTIAL_SECRET', 'SELECT fake FROM marker', '/private/fake/path', 'STACK_FAKE', 'fake@example.test', 'BODY_FAKE' ) as $marker ) {
			$this->fails( fn() => $audit->append( $marker, 'site_info', 'started' ) );
			$this->fails( fn() => $audit->append( $site, $marker, 'started' ) );
			$this->fails( fn() => $audit->append( $site, 'site_info', $marker ) );
			self::assertStringNotContainsString( $marker, file_get_contents( $path ) );
		}
		self::assertSame( array( 'timestamp', 'operation', 'site_id', 'outcome' ), array_keys( json_decode( trim( $before ), true ) ) );
		$this->now += Read_Audit::RETENTION_SECONDS - 1;
		$audit->append( $site, 'site_info', 'returned' );
		self::assertStringStartsWith( $before, file_get_contents( $path ) );
		++$this->now;
		$audit->append( $site, 'site_info', 'started' );
		self::assertStringNotContainsString( $before, file_get_contents( $path ) );
		self::assertCount( 2, explode( "\n", trim( file_get_contents( $path ) ) ) );
		file_put_contents( $path, "{broken\n" );
		$this->fails( fn() => $audit->append( $site, 'site_info', 'started' ) );
	}

	/** Idle local maintenance expires evidence; unexpected truncation denies. */
	public function test_idle_retention_and_partial_write_detection(): void {
		$audit = new Read_Audit( new Local_Store( $this->root . '/audit', array() ), $this->clock );
		$site  = c03c_config( $this->root )->site();
		$audit->append( $site, 'site_info', 'started' );
		$this->now += Read_Audit::RETENTION_SECONDS;
		$audit->maintain();
		self::assertSame( '', file_get_contents( $this->root . '/audit/events' ) );
		$audit->append( $site, 'site_info', 'started' );
		file_put_contents( $this->root . '/audit/events', '' );
		$this->fails( fn() => $audit->append( $site, 'site_info', 'started' ) );
	}

	/** Valid retained records fill the quota; no live record is evicted for space. */
	public function test_audit_capacity_unwritable_and_corrupt_clock(): void {
		$audit = new Read_Audit( new Local_Store( $this->root . '/audit', array() ), $this->clock );
		$site  = c03c_config( $this->root )->site();
		$audit->append( $site, 'site_info', 'started' );
		$path  = $this->root . '/audit/events';
		$line  = file_get_contents( $path );
		$bytes = str_repeat( $line, intdiv( Read_Audit::CAPACITY, strlen( $line ) ) );
		file_put_contents( $path, $bytes );
		file_put_contents( $this->root . '/audit/state', Read_Audit::fingerprint( $bytes ) );
		$this->fails( fn() => $audit->append( $site, 'site_info', 'started' ) );
		self::assertSame( hash( 'sha256', $bytes ), hash_file( 'sha256', $path ) );
		chmod( $path, 0400 );
		$this->fails( fn() => $audit->append( $site, 'site_info', 'started' ) );
		file_put_contents( $this->root . '/audit/clock', "corrupt\n" );
		$this->fails( fn() => $audit->append( $site, 'site_info', 'started' ) );
	}

	/** Four simultaneous processes hold actual locks; fifth fails promptly. */
	public function test_parallel_4_5_release_process_death_and_no_leaks(): void {
		$workers = array();
		try {
			for ( $index = 0; $index < 4; ++$index ) {
				$workers[] = $this->start_worker( 'hold' );
				self::assertSame( 'held', $this->line( $workers[ $index ][1][1] ) );
			}
			$start = hrtime( true );
			self::assertSame( 'concurrency_limit', $this->worker_once( 'once' ) );
			self::assertLessThan( 2000000000, hrtime( true ) - $start );
			fwrite( $workers[0][1][0], "release\n" );
			$this->close_worker( $workers[0] );
			unset( $workers[0] );
			self::assertSame( 'held', $this->worker_once( 'once' ) );
			proc_terminate( $workers[1][0], 9 );
			$this->close_worker( $workers[1] );
			unset( $workers[1] );
			self::assertSame( 'held', $this->worker_once( 'once' ) );
		} finally {
			foreach ( $workers as $worker ) {
				proc_terminate( $worker[0], 9 );
				$this->close_worker( $worker );
			}
		}
		$slots = array();
		for ( $index = 0; $index < 4; ++$index ) {
			$slots[] = new Read_Slot( $this->store );
		}
		self::assertCount( 4, $slots );
		foreach ( $slots as $slot ) {
			$slot->release();
		}
	}

	/**
	 * Independent interpreter; no inherited PHP object or static rate state.
	 *
	 * @param string $mode Fixed test mode.
	 * @return array Worker and pipes.
	 */
	private function start_worker( string $mode ): array {
		$command = array( PHP_BINARY );
		if ( php_ini_loaded_file() ) {
			$command[] = '-c';
			$command[] = php_ini_loaded_file();
		}
		if ( ! extension_loaded( 'posix' ) ) {
			self::fail( 'Local lock environment needs POSIX identity checks.' );
		}
		$command = array_merge( $command, array( dirname( __DIR__ ) . '/fixtures/c03c-worker.php', $this->root, $mode ) );
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.system_calls_proc_open -- Bounded test-only independent PHP workers are required to prove real flock contention.
		$process = proc_open( $command, array( array( 'pipe', 'r' ), array( 'pipe', 'w' ), array( 'pipe', 'w' ) ), $pipes );
		self::assertIsResource( $process );
		return array( $process, $pipes );
	}

	/**
	 * Bounded read even if a broken worker never reaches the expected lock state.
	 *
	 * @param resource $pipe Worker output.
	 * @return string Safe worker result.
	 */
	private function line( $pipe ): string {
		$read   = array( $pipe );
		$write  = null;
		$except = null;
		self::assertSame( 1, stream_select( $read, $write, $except, 5 ), 'Independent worker timed out.' );
		return trim( (string) fgets( $pipe ) );
	}

	/**
	 * Reap a worker and close all pipes.
	 *
	 * @param array $worker Worker and pipes.
	 */
	private function close_worker( array $worker ): void {
		foreach ( $worker[1] as $pipe ) {
			fclose( $pipe );
		}
		proc_close( $worker[0] );
	}

	/**
	 * Start and reap one independent non-holding operation.
	 *
	 * @param string $mode Fixed mode.
	 * @return string Safe result.
	 */
	private function worker_once( string $mode ): string {
		$worker = $this->start_worker( $mode );
		try {
			return $this->line( $worker[1][1] );
		} finally {
			$this->close_worker( $worker );
		}
	}
}
