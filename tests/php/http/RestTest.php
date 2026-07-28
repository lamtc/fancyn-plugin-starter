<?php
/**
 * REST service tests.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\Tests\Http;

use Fancyn\PluginStarter\Http\Rest;
use Fancyn\PluginStarter\Tests\Support\WordPressTestCase;

/** Tests REST route schema and authorization. */
final class RestTest extends WordPressTestCase {
	/** Registers a non-public POST route with schema and permission callback. */
	public function test_register_routes_declares_secure_contract(): void {
		( new Rest() )->register_routes();
		self::assertCalledTimes( 'register_rest_route', 1 );
		$route = self::calls( 'register_rest_route' )[0];
		$this->assertSame( 'fancyn-plugin-starter/v1', $route[0] );
		$this->assertSame( '/validate-setting', $route[1] );
		$this->assertSame( 'POST', $route[2]['methods'] );
		$this->assertIsCallable( $route[2]['permission_callback'] );
		$this->assertSame( 'string', $route[2]['args']['value']['type'] );
		$this->assertTrue( $route[2]['args']['value']['required'] );
	}

	/** Uses manage_options for route authorization. */
	public function test_permission_callback_uses_capability(): void {
		self::stub( 'current_user_can', true );
		$this->assertTrue( ( new Rest() )->can_manage() );
		$this->assertSame( array( 'manage_options' ), self::calls( 'current_user_can' )[0] );
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
		self::stubCallback( 'sanitize_text_field', static fn ( mixed $value ): string => trim( (string) $value ) );
		self::stubCallback( 'rest_ensure_response', static fn ( mixed $value ): mixed => $value );
		$this->assertSame(
			array(
				'code'  => 'fancyn_plugin_starter_valid',
				'value' => 'value',
			),
			( new Rest() )->validate_setting( $request )
		);
	}
}
