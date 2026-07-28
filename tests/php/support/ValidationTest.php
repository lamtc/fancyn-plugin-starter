<?php
/**
 * Validation helper tests.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\Tests\Support;

use Brain\Monkey;
use Brain\Monkey\Functions;
use Fancyn\PluginStarter\Support\Validation;
use PHPUnit\Framework\TestCase;

/** Tests field-specific WordPress sanitization. */
final class ValidationTest extends TestCase {
	/** Starts mocks. */
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
		Functions\when( 'sanitize_text_field' )->alias( static fn ( mixed $v ): string => trim( preg_replace( '/<[^>]*>/', '', (string) $v ) ?? '' ) );
		Functions\when( 'sanitize_email' )->alias( static fn ( mixed $v ): string => filter_var( $v, FILTER_VALIDATE_EMAIL ) ? (string) $v : '' );
		Functions\when( 'esc_url_raw' )->alias( static fn ( mixed $v ): string => filter_var( $v, FILTER_VALIDATE_URL ) ? (string) $v : '' );
		Functions\when( 'absint' )->alias( static fn ( mixed $v ): int => abs( (int) $v ) );
		Functions\when( 'sanitize_hex_color' )->alias( static fn ( mixed $v ): string => preg_match( '/^#[0-9a-fA-F]{6}$/', (string) $v ) ? (string) $v : '' );
		Functions\when( 'wp_kses_post' )->returnArg( 1 );
	}
	/** Stops mocks. */
	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown(); }

	/** Sanitizes values by declared field type. */
	public function test_by_type_uses_field_specific_sanitizers(): void {
		$this->assertSame( 'Company', Validation::by_type( 'text', ' <b>Company</b> ' ) );
		$this->assertSame( 'hello@example.test', Validation::by_type( 'email', 'hello@example.test', 'old@example.test' ) );
		$this->assertSame( 'https://example.test', Validation::by_type( 'url', 'https://example.test' ) );
		$this->assertSame( 12, Validation::by_type( 'integer', '-12' ) );
		$this->assertTrue( Validation::by_type( 'boolean', '1' ) );
		$this->assertSame( '#F4511E', Validation::by_type( 'color', '#F4511E' ) );
		$this->assertSame( 42, Validation::by_type( 'attachment_id', '42' ) );
	}

	/** Keeps the previous value when a strict type is invalid. */
	public function test_invalid_strict_value_returns_fallback(): void {
		$this->assertSame( 'old@example.test', Validation::by_type( 'email', 'not-email', 'old@example.test' ) );
		$this->assertSame( '#171717', Validation::by_type( 'color', 'red', '#171717' ) );
	}
}
