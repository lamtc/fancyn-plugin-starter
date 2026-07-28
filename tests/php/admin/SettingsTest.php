<?php
/**
 * Settings service tests.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\Tests\Admin;

use Fancyn\PluginStarter\Admin\Settings;
use Fancyn\PluginStarter\Support\View;
use Fancyn\PluginStarter\Tests\Support\WordPressTestCase;

/** Tests Settings API registration and schema sanitization. */
final class SettingsTest extends WordPressTestCase {
	/** Starts mocks. */
	protected function setUp(): void {
		parent::setUp();
		self::stubCallback( '__', static fn ( mixed $value ): mixed => $value ); }

	/** Registers one prefixed setting and section. */
	public function test_register_settings_uses_settings_api(): void {
		$settings = $this->make_settings();
		$settings->register_settings();
		self::assertCalledTimes( 'register_setting', 1 );
		self::assertCalledTimes( 'add_settings_section', 1 );
		$registration = self::calls( 'register_setting' )[0];
		$this->assertSame( 'fancyn_plugin_starter', $registration[0] );
		$this->assertSame( 'fancyn_plugin_starter_settings', $registration[1] );
		$this->assertIsCallable( $registration[2]['sanitize_callback'] );
	}

	/** Sanitizes declared fields and ignores unknown input. */
	public function test_sanitize_applies_schema_and_discards_unknown_fields(): void {
		self::stub( 'get_option', array( 'company_email' => 'old@example.test' ) );
		self::stubCallback( 'sanitize_text_field', static fn ( mixed $value ): mixed => $value );
		self::stubCallback( 'sanitize_email', static fn ( string $v ): string => filter_var( $v, FILTER_VALIDATE_EMAIL ) ? $v : '' );
		self::stubCallback( 'esc_url_raw', static fn ( mixed $value ): mixed => $value );
		self::stubCallback( 'absint', static fn ( mixed $v ): int => abs( (int) $v ) );
		self::stubCallback( 'sanitize_hex_color', static fn ( mixed $value ): mixed => $value );
		self::stubCallback( 'wp_kses_post', static fn ( mixed $value ): mixed => $value );
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
