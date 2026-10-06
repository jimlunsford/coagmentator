<?php
/**
 * Actual authenticated synthetic reads plus early invalid-auth accounting.
 *
 * @package Coagmentator
 */

/** C03C HTTP checks retain the accepted guard and identity/transport boundary. */
final class OperationalAdmissionTest extends GuardHttpCase {
	private const CONTROL = '/var/coagmentator-c03c/control';
	private const PATH    = '/wp-json/coagmentator/v1/site_info';

	/** Rate, storage failures and error cleanup must be observed before the target. */
	public function test_operational_scenario(): void {
		$case   = $this->fixture['c03c_scenario'];
		$before = $this->snapshot();
		if ( 'parallel' === $case ) {
			$this->parallel();
		} elseif ( 'preauth' === $case ) {
			// Wait at most one minute only if too close to the window edge.
			// The runner primes a fresh minute before this one boundary scenario.
			$window = intdiv( time(), 60 );
			for ( $index = 1; $index <= 121; ++$index ) {
				$result = $this->request( self::PATH, 'POST', '', '', array( 'Content-Type: application/json', 'Authorization: Basic ZmFrZTpmYWtl', 'X-Forwarded-For: 198.51.100.' . ( $index % 255 ) ) );
				self::assertSame( $index <= 120 ? 403 : 429, $result['status'], 'Pre-auth boundary.' );
			}
			self::assertSame( $window, intdiv( time(), 60 ), 'Boundary evidence must remain in the same active minute.' );
			self::assertCount( 0, glob( self::CONTROL . '/target-*' ) );
			self::assertCount( 1, glob( '/var/coagmentator-c03c/admission/peer-*' ) );
		} elseif ( 'rate' === $case ) {
			$window = intdiv( time(), 60 );
			for ( $index = 1; $index <= 61; ++$index ) {
				$result = $this->request( self::PATH, 'POST', 'service-basic', '', array( 'Content-Type: application/json' ) );
				self::assertSame( 403, $result['status'] );
				self::assertCount( min( 60, $index ), glob( self::CONTROL . '/target-*' ) );
			}
			self::assertSame( $window, intdiv( time(), 60 ), 'Read boundary evidence must remain in the same active minute.' );
			$denials = glob( self::CONTROL . '/denied-*' );
			self::assertCount( 1, $denials );
			self::assertSame( 'rate_limit', file_get_contents( $denials[0] ) );
		} else {
			$admitted = in_array( $case, array( 'healthy', 'throw' ), true );
			for ( $index = 1; $index <= 6; ++$index ) {
				$result = $this->request( self::PATH, 'POST', 'service-basic', '{"marker":"BODY_FAKE"}', array( 'Content-Type: application/json' ) );
				self::assertSame( str_starts_with( $case, 'preauth-' ) ? 429 : 403, $result['status'] );
				self::assertCount( $admitted ? $index : 0, glob( self::CONTROL . '/target-*' ), 'A denied target must never execute; errors must release slots.' );
			}
		}
		self::assertSame( $before, $this->snapshot(), 'No editorial or prohibited target change.' );
		if ( in_array( $case, array( 'healthy', 'throw', 'rate', 'parallel' ), true ) ) {
			$slots = array();
			try {
				for ( $index = 0; $index < 4; ++$index ) {
					$file    = fopen( '/var/coagmentator-c03c/admission/slot-' . $index, 'r+b' );
					$slots[] = $file;
					self::assertTrue( flock( $file, LOCK_EX | LOCK_NB ), 'No slot may remain held after success or error response.' );
				}
			} finally {
				foreach ( $slots as $file ) {
					fclose( $file );
				}
			}
			$audit = file_get_contents( '/var/coagmentator-c03c/audit/events' );
			foreach ( array( 'FAKE_CREDENTIAL_SECRET', 'SELECT fake', '/private/fake/path', 'STACK_FAKE', 'fake@example.test', 'BODY_FAKE', $this->fixture['service_secret'] ) as $marker ) {
				self::assertStringNotContainsString( $marker, $audit, 'Restricted audit must not disclose payload, secrets or raw failures.' );
			}
			foreach ( explode( "\n", trim( $audit ) ) as $line ) {
				self::assertLessThanOrEqual( 256, strlen( $line ) + 1 );
				self::assertSame( array( 'timestamp', 'operation', 'site_id', 'outcome' ), array_keys( json_decode( $line, true, 4, JSON_THROW_ON_ERROR ) ) );
			}
		}
	}

	/** Four authenticated HTTP callbacks simultaneously hold slots behind real FPM. */
	private function parallel(): void {
		$multi   = curl_multi_init();
		$clients = array();
		try {
			// Launch individually and wait for each held marker. Requests remain
			// simultaneous, while short accounting locks are not artificially raced.
			for ( $index = 0; $index < 4; ++$index ) {
				$client = curl_init( 'https://wordpress.test' . self::PATH );
				curl_setopt_array(
					$client,
					array(
						CURLOPT_RETURNTRANSFER => true,
						CURLOPT_CAINFO         => C01_CA,
						CURLOPT_TIMEOUT        => 20,
						CURLOPT_FOLLOWLOCATION => false,
						CURLOPT_POST           => true,
						CURLOPT_POSTFIELDS     => '',
						CURLOPT_HTTPHEADER     => array( 'Content-Type: application/json' ),
						CURLOPT_USERPWD        => 'c02_service:' . $this->fixture['service_secret'],
						CURLOPT_HTTPAUTH       => CURLAUTH_BASIC,
					)
				);
				$clients[] = $client;
				curl_multi_add_handle( $multi, $client );
				$end  = hrtime( true ) + 3000000000;
				$held = count( glob( self::CONTROL . '/held-*' ) );
				while ( $held < $index + 1 && hrtime( true ) < $end ) {
					curl_multi_exec( $multi, $running );
					curl_multi_select( $multi, 0.01 );
					$held = count( glob( self::CONTROL . '/held-*' ) );
				}
				self::assertCount( $index + 1, glob( self::CONTROL . '/held-*' ), 'Real FPM workers must hold all four slots concurrently.' );
			}
			$start  = hrtime( true );
			$result = $this->request( self::PATH, 'POST', 'service-basic', '', array( 'Content-Type: application/json' ) );
			self::assertSame( 403, $result['status'] );
			self::assertLessThan( 2000000000, hrtime( true ) - $start );
			self::assertCount( 4, glob( self::CONTROL . '/target-*' ) );
			$denials = glob( self::CONTROL . '/denied-*' );
			self::assertCount( 1, $denials );
			self::assertSame( 'concurrency_limit', file_get_contents( $denials[0] ) );
		} finally {
			file_put_contents( self::CONTROL . '/release', '' );
			// Stagger completions, allowing fail-closed audit lock contention as a
			// safe error, but every request must release its own slot in finally.
			$end = hrtime( true ) + 5000000000;
			do {
				curl_multi_exec( $multi, $running );
				curl_multi_select( $multi, 0.01 );
			} while ( $running > 0 && hrtime( true ) < $end );
			foreach ( $clients as $client ) {
				curl_multi_remove_handle( $multi, $client );
			}
			curl_multi_close( $multi );
		}
		self::assertCount( 0, glob( self::CONTROL . '/held-*' ) );
		$result = $this->request( self::PATH, 'POST', 'service-basic', '', array( 'Content-Type: application/json' ) );
		self::assertSame( 403, $result['status'] );
		self::assertCount( 5, glob( self::CONTROL . '/target-*' ), 'Later independent HTTP request must acquire a released slot.' );
	}
}
