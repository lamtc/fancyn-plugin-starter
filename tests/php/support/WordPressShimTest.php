<?php
/**
 * WordPress test shim contract tests.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\Tests\Support;

/**
 * Verifies the lightweight WordPress function registry.
 */
final class WordPressShimTest extends WordPressTestCase {
	/**
	 * Stubs return values and records arguments.
	 */
	public function test_stubs_values_and_records_calls(): void {
		self::stub( 'current_user_can', true );

		$this->assertTrue( current_user_can( 'manage_options' ) );
		$this->assertSame( array( array( 'manage_options' ) ), self::calls( 'current_user_can' ) );
		self::assertCalledTimes( 'current_user_can', 1 );
	}

	/**
	 * Callbacks receive the original arguments.
	 */
	public function test_callbacks_receive_arguments(): void {
		self::stubCallback( 'sanitize_text_field', static fn( string $value ): string => trim( $value ) );

		$this->assertSame( 'value', sanitize_text_field( ' value ' ) );
	}

	/**
	 * State resets automatically between tests.
	 */
	public function test_registry_starts_empty(): void {
		$this->assertSame( array(), self::calls( 'current_user_can' ) );
	}

	/**
	 * JSON helpers terminate with typed payload exceptions.
	 */
	public function test_json_response_throws_typed_exception(): void {
		try {
			wp_send_json_error( array( 'code' => 'forbidden' ), 403 );
			$this->fail( 'Expected JSON response termination.' );
		} catch ( JsonResponseException $exception ) {
			$this->assertFalse( $exception->success );
			$this->assertSame( 403, $exception->status );
			$this->assertSame( array( 'code' => 'forbidden' ), $exception->payload );
		}
	}

	/**
	 * Wp_die terminates with a typed exception.
	 */
	public function test_wp_die_throws_typed_exception(): void {
		$this->expectException( WpDieException::class );
		wp_die( 'Stopped', 'Error', array( 'response' => 500 ) );
	}
}
