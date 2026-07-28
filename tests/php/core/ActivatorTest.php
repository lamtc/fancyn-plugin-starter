<?php
/**
 * Activator tests.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\Tests\Core;

use Fancyn\PluginStarter\Core\Activator;
use Fancyn\PluginStarter\Tests\Support\WordPressTestCase;
use Fancyn\PluginStarter\Tests\Support\WpDieException;

/**
 * Tests environment validation during activation.
 */
final class ActivatorTest extends WordPressTestCase {
	/** Starts WordPress function mocking. */
	protected function setUp(): void {
		parent::setUp();
		self::stubCallback( '__', static fn ( mixed $value ): mixed => $value );
		self::stubCallback( 'esc_html', static fn ( mixed $value ): mixed => $value );
		self::stubCallback( 'plugin_basename', static fn ( mixed $value ): mixed => $value );
	}

	/**
	 * Rejects unsupported PHP versions.
	 */
	public function test_rejects_php_below_8_1(): void {
		try {
			Activator::validate_environment( '8.0.30', '6.5' );
			$this->fail( 'Expected activation to terminate.' );
		} catch ( WpDieException $exception ) {
			$this->assertStringContainsString( 'PHP 8.1', (string) $exception->message_value );
		}
		self::assertCalledTimes( 'deactivate_plugins', 1 );
		self::assertCalledTimes( 'wp_die', 1 );
	}

	/**
	 * Rejects unsupported WordPress versions.
	 */
	public function test_rejects_wordpress_below_6_5(): void {
		try {
			Activator::validate_environment( '8.1.0', '6.4.9' );
			$this->fail( 'Expected activation to terminate.' );
		} catch ( WpDieException $exception ) {
			$this->assertStringContainsString( 'WordPress 6.5', (string) $exception->message_value );
		}
		self::assertCalledTimes( 'deactivate_plugins', 1 );
		self::assertCalledTimes( 'wp_die', 1 );
	}

	/**
	 * Flushes rewrite rules after compatible environments pass.
	 */
	public function test_flushes_rules_for_supported_environment(): void {
		Activator::validate_environment( '8.1.0', '6.5' );
		self::assertCalledTimes( 'flush_rewrite_rules', 1 );
	}

	/**
	 * Accepts the network-wide boolean passed by WordPress activation hooks.
	 */
	public function test_activation_hook_uses_runtime_versions_instead_of_network_flag(): void {
		$GLOBALS['wp_version'] = '6.5'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Isolated activation fixture.
		Activator::activate( false );
		self::assertCalledTimes( 'flush_rewrite_rules', 1 );
	}
}
