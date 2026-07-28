<?php
/**
 * AJAX service tests.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\Tests\Http;

use Fancyn\PluginStarter\Http\Ajax;
use Fancyn\PluginStarter\Tests\Support\JsonResponseException;
use Fancyn\PluginStarter\Tests\Support\WordPressTestCase;

/** Tests authenticated AJAX validation. */
final class AjaxTest extends WordPressTestCase {
	/** Starts mocks and clears request state. */
	protected function setUp(): void {
		parent::setUp();
		$_POST = array(); }
	/** Stops mocks and clears request state. */
	protected function tearDown(): void {
		$_POST = array();
		parent::tearDown(); }

	/** Registers only the authenticated WordPress AJAX action. */
	public function test_register_does_not_expose_nopriv_action(): void {
		( new Ajax() )->register();
		self::assertCalledTimes( 'add_action', 1 );
		$this->assertSame( 'wp_ajax_fancyn_plugin_starter_validate_setting', self::calls( 'add_action' )[0][0] );
		$this->assertIsCallable( self::calls( 'add_action' )[0][1] );
	}

	/** Rejects users without the required capability after nonce verification. */
	public function test_handler_rejects_unauthorized_user(): void {
		self::stub( 'check_ajax_referer', true );
		self::stub( 'current_user_can', false );
		try {
			( new Ajax() )->handle();
			$this->fail( 'Expected JSON termination.' );
		} catch ( JsonResponseException $exception ) {
			$this->assertFalse( $exception->success );
			$this->assertSame( 403, $exception->status );
			$this->assertSame( 'fancyn_plugin_starter_forbidden', $exception->payload['code'] );
		}
		$this->assertSame( array( 'fancyn_plugin_starter_validate_setting', 'nonce', false ), self::calls( 'check_ajax_referer' )[0] );
		$this->assertSame( array( 'manage_options' ), self::calls( 'current_user_can' )[0] );
	}

	/** Sanitizes and returns authorized input. */
	public function test_handler_returns_structured_success(): void {
		$_POST = array( 'value' => ' value ' );
		self::stub( 'check_ajax_referer', true );
		self::stub( 'current_user_can', true );
		self::stubCallback( 'wp_unslash', static fn ( mixed $value ): mixed => $value );
		self::stubCallback( 'sanitize_text_field', static fn ( mixed $value ): string => trim( (string) $value ) );
		try {
			( new Ajax() )->handle();
			$this->fail( 'Expected JSON termination.' );
		} catch ( JsonResponseException $exception ) {
			$this->assertTrue( $exception->success );
			$this->assertSame( 200, $exception->status );
			$this->assertSame(
				array(
					'code'  => 'fancyn_plugin_starter_valid',
					'value' => 'value',
				),
				$exception->payload
			);
		}
	}
}
