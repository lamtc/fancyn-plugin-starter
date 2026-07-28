<?php
/**
 * AJAX service tests.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\Tests\Http;

use Brain\Monkey;
use Brain\Monkey\Functions;
use Fancyn\PluginStarter\Http\Ajax;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/** Tests authenticated AJAX validation. */
final class AjaxTest extends TestCase {
	/** Starts mocks and clears request state. */
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
		$_POST = array(); }
	/** Stops mocks and clears request state. */
	protected function tearDown(): void {
		$_POST = array();
		Monkey\tearDown();
		parent::tearDown(); }

	/** Registers only the authenticated WordPress AJAX action. */
	public function test_register_does_not_expose_nopriv_action(): void {
		Functions\expect( 'add_action' )->once()->with( 'wp_ajax_fancyn_plugin_starter_validate_setting', \Mockery::type( 'callable' ) );
		( new Ajax() )->register();
		$this->addToAssertionCount( 1 );
	}

	/** Rejects users without the required capability after nonce verification. */
	public function test_handler_rejects_unauthorized_user(): void {
		Functions\expect( 'check_ajax_referer' )->once()->with( 'fancyn_plugin_starter_validate_setting', 'nonce', false )->andReturn( true );
		Functions\expect( 'current_user_can' )->once()->with( 'manage_options' )->andReturn( false );
		Functions\expect( 'wp_send_json_error' )->once()->with( \Mockery::on( static fn ( array $data ): bool => 'fancyn_plugin_starter_forbidden' === $data['code'] ), 403 )->andThrow( new RuntimeException( 'sent' ) );
		$this->expectException( RuntimeException::class );
		( new Ajax() )->handle();
	}

	/** Sanitizes and returns authorized input. */
	public function test_handler_returns_structured_success(): void {
		$_POST = array( 'value' => ' value ' );
		Functions\expect( 'check_ajax_referer' )->once()->andReturn( true );
		Functions\expect( 'current_user_can' )->once()->andReturn( true );
		Functions\when( 'wp_unslash' )->returnArg( 1 );
		Functions\when( 'sanitize_text_field' )->alias( 'trim' );
		Functions\expect( 'wp_send_json_success' )->once()->with(
			array(
				'code'  => 'fancyn_plugin_starter_valid',
				'value' => 'value',
			)
		)->andThrow( new RuntimeException( 'sent' ) );
		$this->expectException( RuntimeException::class );
		( new Ajax() )->handle();
	}
}
