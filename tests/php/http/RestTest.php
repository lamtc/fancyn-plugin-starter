<?php
/**
 * REST service tests.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\Tests\Http;

use Brain\Monkey;
use Brain\Monkey\Functions;
use Fancyn\PluginStarter\Http\Rest;
use PHPUnit\Framework\TestCase;

/** Tests REST route schema and authorization. */
final class RestTest extends TestCase {
	/** Starts mocks. */
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp(); }
	/** Stops mocks. */
	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown(); }

	/** Registers a non-public POST route with schema and permission callback. */
	public function test_register_routes_declares_secure_contract(): void {
		Functions\expect( 'register_rest_route' )->once()->with(
			'fancyn-plugin-starter/v1',
			'/validate-setting',
			\Mockery::on( static fn ( array $route ): bool => 'POST' === $route['methods'] && is_callable( $route['permission_callback'] ) && 'string' === $route['args']['value']['type'] && true === $route['args']['value']['required'] )
		);
		( new Rest() )->register_routes();
		$this->addToAssertionCount( 1 );
	}

	/** Uses manage_options for route authorization. */
	public function test_permission_callback_uses_capability(): void {
		Functions\expect( 'current_user_can' )->once()->with( 'manage_options' )->andReturn( true );
		$this->assertTrue( ( new Rest() )->can_manage() );
	}

	/** Sanitizes a valid request and returns a structured response. */
	public function test_validate_setting_returns_sanitized_payload(): void {
		$request = new class() {
			/**
			 * Returns a test request value.
			 *
			 * @param string $key Parameter key.
			 */
			public function get_param( string $key ): string {
				return ' value ';
			}
		};
		Functions\when( 'sanitize_text_field' )->alias( 'trim' );
		Functions\when( 'rest_ensure_response' )->returnArg( 1 );
		$this->assertSame(
			array(
				'code'  => 'fancyn_plugin_starter_valid',
				'value' => 'value',
			),
			( new Rest() )->validate_setting( $request )
		);
	}
}
