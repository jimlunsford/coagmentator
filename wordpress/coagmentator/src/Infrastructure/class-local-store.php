<?php
/**
 * Restricted local locked storage, never a WordPress cache or filesystem proxy.
 *
 * @package Coagmentator
 */

namespace Coagmentator\Infrastructure;

use Coagmentator\Config\Operator_Path;

/** Filesystem access is limited to a previously configured private directory. */
final class Local_Store {

	/**
	 * Validate each use; HTTP never creates directories or fixed lock files.
	 *
	 * @param string   $path Fixed host configuration reference.
	 * @param string[] $excluded Repository and web roots.
	 * @phpstan-param list<string> $excluded
	 * @throws Operational_Failure Unsupported or unsafe storage.
	 */
	public function __construct( private string $path, array $excluded ) {
		clearstatcache();
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_is_writable -- Validate mandatory local storage, not a WordPress filesystem adapter.
		if ( ! function_exists( 'posix_geteuid' ) || ! Operator_Path::valid( $path, $excluded ) || realpath( $path ) !== $path || ! is_dir( $path ) || ! is_readable( $path ) || ! is_writable( $path ) ) {
			throw new Operational_Failure();
		}
		$stat = self::quiet( static fn() => lstat( $path ) );
		if ( false === $stat || posix_geteuid() !== $stat['uid'] || 0700 !== ( $stat['mode'] & 0777 ) || ! self::local_mount( $path ) ) {
			throw new Operational_Failure();
		}
		$parent = dirname( $path );
		while ( true ) {
			$stat = self::quiet( static fn() => lstat( $parent ) );
			// A root-owned sticky temporary ancestor cannot replace our private child.
			if ( false === $stat || 0040000 !== ( $stat['mode'] & 0170000 ) || ! in_array( $stat['uid'], array( 0, posix_geteuid() ), true ) || ( 0 !== ( $stat['mode'] & 0022 ) && ! ( 0 === $stat['uid'] && 0 !== ( $stat['mode'] & 01000 ) ) ) ) {
				throw new Operational_Failure();
			}
			if ( '/' === $parent ) {
				break;
			}
			$parent = dirname( $parent );
		}
	}

	/**
	 * Linux single-host prerequisite. Unknown/network/distributed mounts deny.
	 *
	 * @param string $path Vetted absolute directory.
	 * @return bool Supported mount, including local container overlay.
	 */
	private static function local_mount( string $path ): bool {
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Fixed kernel metadata, bounded local read; no HTTP fetch or WordPress fallback.
		$bytes = self::quiet( static fn() => file_get_contents( '/proc/self/mountinfo', false, null, 0, 1048577 ) );
		if ( ! is_string( $bytes ) || strlen( $bytes ) > 1048576 ) {
			return false;
		}
		$longest = -1;
		$type    = '';
		foreach ( explode( "\n", trim( $bytes ) ) as $line ) {
			$parts  = explode( ' - ', $line );
			$fields = explode( ' ', $parts[0] );
			if ( count( $parts ) !== 2 || count( $fields ) < 6 ) {
				return false;
			}
			$mount = strtr(
				$fields[4],
				array(
					'\040' => ' ',
					'\011' => "\t",
					'\012' => "\n",
					'\134' => '\\',
				)
			);
			if ( ( '/' === $mount || $path === $mount || str_starts_with( $path, $mount . '/' ) ) && strlen( $mount ) >= $longest ) {
				$longest = strlen( $mount );
				$type    = explode( ' ', $parts[1] )[0];
			}
		}
		return in_array( $type, array( 'ext4', 'xfs', 'btrfs', 'overlay' ), true );
	}

	/**
	 * Prevent host paths from leaking via PHP warnings; retain no raw message.
	 *
	 * @template T
	 * @param \Closure $operation One checked local IO operation.
	 * @phpstan-param \Closure(): T $operation
	 * @return T Result, with caller checking false/short results too.
	 * @throws Operational_Failure IO warning or exception.
	 */
	public static function quiet( \Closure $operation ): mixed {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_set_error_handler -- Convert IO warnings to safe denials without logging raw paths.
		set_error_handler(
			static function (): never {
				throw new Operational_Failure();
			}
		);
		try {
			return $operation();
		} catch ( \Throwable $failure ) {
			throw new Operational_Failure();
		} finally {
			restore_error_handler();
		}
	}

	/**
	 * Only fixed names and hashes, never raw peer/identity values.
	 *
	 * @param string $name Internal filename.
	 * @return string Safe joined path.
	 * @throws Operational_Failure Invalid internal filename.
	 */
	private function file( string $name ): string {
		if ( 1 !== preg_match( '/^[a-z0-9][a-z0-9.-]{0,79}$/D', $name ) || str_contains( $name, '..' ) ) {
			throw new Operational_Failure();
		}
		return $this->path . '/' . $name;
	}

	/**
	 * Open a private regular file; creation is exclusive and only under control lock.
	 *
	 * @param string $name Internal name.
	 * @param bool   $create Exclusive creation, never reinterpret existing empty data.
	 * @return resource Verified local descriptor.
	 * @throws Operational_Failure Unsafe file or failed open.
	 */
	public function open( string $name, bool $create = false ) {
		$path = $this->file( $name );
		clearstatcache( true, $path );
		if ( ! $create ) {
			$before = self::quiet( static fn() => lstat( $path ) );
			if ( false === $before || 0100000 !== ( $before['mode'] & 0170000 ) || 0600 !== ( $before['mode'] & 0777 ) || 1 !== $before['nlink'] || posix_geteuid() !== $before['uid'] ) {
				throw new Operational_Failure();
			}
		}
		$mask = umask( 0077 );
		try {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen -- Private local descriptors and flock are mandatory; WP_Filesystem cannot supply this contract.
			$file = self::quiet( static fn() => fopen( $path, $create ? 'x+b' : 'r+b' ) );
		} finally {
			umask( $mask );
		}
		if ( false === $file ) {
			throw new Operational_Failure();
		}
		try {
			$stat = self::quiet( static fn() => fstat( $file ) );
			$link = self::quiet( static fn() => lstat( $path ) );
			if ( false === $stat || false === $link || 0100000 !== ( $link['mode'] & 0170000 ) || 0600 !== ( $link['mode'] & 0777 ) || 1 !== $link['nlink'] || posix_geteuid() !== $link['uid'] || $stat['dev'] !== $link['dev'] || $stat['ino'] !== $link['ino'] ) {
				throw new Operational_Failure();
			}
			return $file;
		} catch ( \Throwable $failure ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Release the mandatory local flock descriptor.
			fclose( $file );
			throw new Operational_Failure();
		}
	}

	/**
	 * Bounded directory scan; an unexpected growth denies before scanning forever.
	 *
	 * @param int $maximum Fixed component file cap.
	 * @return string[] Local names.
	 * @phpstan-return list<string>
	 * @throws Operational_Failure Directory or capacity failure.
	 */
	public function names( int $maximum ): array {
		$directory = self::quiet( fn() => opendir( $this->path ) );
		if ( false === $directory ) {
			throw new Operational_Failure();
		}
		$names = array();
		try {
			$name = readdir( $directory );
			while ( false !== $name ) {
				if ( '.' === $name || '..' === $name ) {
					$name = readdir( $directory );
					continue;
				}
				$this->file( $name );
				$names[] = $name;
				if ( count( $names ) > $maximum ) {
					throw new Operational_Failure();
				}
				$name = readdir( $directory );
			}
		} finally {
			closedir( $directory );
		}
		return $names;
	}

	/**
	 * Nonblocking exclusive lock. Never truncate, unlink, lease or age-reclaim locks.
	 *
	 * @param string $name Fixed lock file.
	 * @return resource|null Acquired descriptor or contention.
	 * @throws Operational_Failure Lock failure distinct from ordinary contention.
	 */
	public function lock( string $name ) {
		$file = $this->open( $name );
		try {
			if ( '' !== self::read( $file, 1 ) ) {
				throw new Operational_Failure();
			}
			$blocked = 0;
			$locked  = self::quiet(
				static function () use ( $file, &$blocked ): bool {
					return flock( $file, LOCK_EX | LOCK_NB, $blocked );
				}
			);
			if ( ! $locked ) {
				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Release the mandatory local flock descriptor.
				fclose( $file );
				if ( 1 !== $blocked ) {
					throw new Operational_Failure();
				}
				return null;
			}
			return $file;
		} catch ( \Throwable $failure ) {
			if ( is_resource( $file ) ) {
				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Release the mandatory local flock descriptor.
				fclose( $file );
			}
			throw new Operational_Failure();
		}
	}

	/**
	 * A short local transaction, with no queue and deterministic release.
	 *
	 * @template T
	 * @param \Closure $operation Bounded component operation.
	 * @phpstan-param \Closure(): T $operation
	 * @return T Operation result.
	 * @throws Operational_Failure Lock unavailable.
	 */
	public function transaction( \Closure $operation ): mixed {
		$file = $this->lock( 'control.lock' );
		if ( null === $file ) {
			throw new Operational_Failure();
		}
		try {
			return $operation();
		} finally {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Release the mandatory local flock descriptor.
			fclose( $file );
		}
	}

	/**
	 * Read at most the hard bound plus an overflow byte.
	 *
	 * @param resource $file Descriptor.
	 * @param int      $maximum Component bound.
	 * @return string Bytes.
	 * @throws Operational_Failure Read or size failure.
	 */
	public static function read( $file, int $maximum ): string {
		if ( ! rewind( $file ) ) {
			throw new Operational_Failure();
		}
		$bytes = self::quiet( static fn() => stream_get_contents( $file, $maximum + 1 ) );
		if ( false === $bytes || strlen( $bytes ) > $maximum ) {
			throw new Operational_Failure();
		}
		return $bytes;
	}

	/**
	 * Exact durable write. Partial writes leave corrupt state, never zero accounting.
	 *
	 * @param resource $file Descriptor under the component control lock.
	 * @param string   $bytes Bounded caller-validated bytes.
	 * @param bool     $append Normal audit appends; false only counters/retention.
	 * @throws Operational_Failure Short write, truncate, flush or sync failure.
	 */
	public static function write( $file, string $bytes, bool $append = false ): void {
		self::quiet(
			static function () use ( $file, $bytes, $append ): void {
				if ( 0 !== fseek( $file, 0, $append ? SEEK_END : SEEK_SET ) ) {
					throw new Operational_Failure();
				}
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite -- Mandatory checked local descriptor write under flock.
				if ( ( '' !== $bytes && fwrite( $file, $bytes ) !== strlen( $bytes ) ) || ( ! $append && ! ftruncate( $file, strlen( $bytes ) ) ) || ! fflush( $file ) || ! fsync( $file ) ) {
					throw new Operational_Failure();
				}
			}
		);
	}

	/**
	 * Remove only a fully validated expired counter, while holding control.lock.
	 *
	 * @param string $name Expired bucket name.
	 * @throws Operational_Failure Failed cleanup.
	 */
	public function retire( string $name ): void {
		$path = $this->file( $name );
		// phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink -- Only expired bounded operational accounting, never an active slot or evidence.
		if ( ! self::quiet( static fn() => unlink( $path ) ) ) {
			throw new Operational_Failure();
		}
	}
}
