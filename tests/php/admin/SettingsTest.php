<?php
/**
 * Settings service tests.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\Tests\Admin;

use Brain\Monkey;
use Brain\Monkey\Functions;
use Fancyn\PluginStarter\Admin\Settings;
use Fancyn\PluginStarter\Support\View;
use PHPUnit\Framework\TestCase;

/** Tests Settings API registration and schema sanitization. */
final class SettingsTest extends TestCase {
	/** Starts mocks. */
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
		Functions\when( '__' )->returnArg( 1 ); }
	/** Stops mocks. */
	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown(); }

	/** Registers one prefixed setting and section. */
	public function test_register_settings_uses_settings_api(): void {
		$settings = $this->make_settings();
		Functions\expect( 'register_setting' )->once()->with( 'fancyn_plugin_starter', 'fancyn_plugin_starter_settings', \Mockery::on( static fn ( array $args ): bool => is_callable( $args['sanitize_callback'] ) ) );
		Functions\expect( 'add_settings_section' )->once();
		$settings->register_settings();
		$this->addToAssertionCount( 1 );
	}

	/** Sanitizes declared fields and ignores unknown input. */
	public function test_sanitize_applies_schema_and_discards_unknown_fields(): void {
		Functions\when( 'get_option' )->justReturn( array( 'company_email' => 'old@example.test' ) );
		Functions\when( 'sanitize_text_field' )->returnArg( 1 );
		Functions\when( 'sanitize_email' )->alias( static fn ( string $v ): string => filter_var( $v, FILTER_VALIDATE_EMAIL ) ? $v : '' );
		Functions\when( 'esc_url_raw' )->returnArg( 1 );
		Functions\when( 'absint' )->alias( static fn ( mixed $v ): int => abs( (int) $v ) );
		Functions\when( 'sanitize_hex_color' )->returnArg( 1 );
		Functions\when( 'wp_kses_post' )->returnArg( 1 );
		$result = $this->make_settings()->sanitize(
			array(
				'company_name'  => 'Acme',
				'company_email' => 'bad',
				'enabled'       => '1',
				'unknown'       => 'drop',
			)
		);
		$this->assertSame( 'Acme', $result['company_name'] );
		$this->assertSame( 'old@example.test', $result['company_email'] );
		$this->assertTrue( $result['enabled'] );
		$this->assertArrayNotHasKey( 'unknown', $result );
	}

	/** Creates settings with the real template root. */
	private function make_settings(): Settings {
		return new Settings( new View( dirname( __DIR__, 3 ) . '/templates' ) );
	}
}
