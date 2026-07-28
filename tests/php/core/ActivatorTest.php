<?php
/**
 * Activator tests.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\Tests\Core;

use Brain\Monkey;
use Brain\Monkey\Functions;
use Fancyn\PluginStarter\Core\Activator;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Tests environment validation during activation.
 */
final class ActivatorTest extends TestCase {
	/** Starts WordPress function mocking. */
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
		Functions\when( '__' )->returnArg( 1 );
		Functions\when( 'esc_html' )->returnArg( 1 );
		Functions\when( 'plugin_basename' )->returnArg( 1 );
	}

	/** Stops WordPress function mocking. */
	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * Rejects unsupported PHP versions.
	 */
	public function test_rejects_php_below_8_1(): void {
		Functions\expect( 'deactivate_plugins' )->once();
		Functions\expect( 'wp_die' )
			->once()
			->andThrow( new RuntimeException( 'unsupported' ) );

		$this->expectException( RuntimeException::class );
		Activator::validate_environment( '8.0.30', '6.5' );
	}

	/**
	 * Rejects unsupported WordPress versions.
	 */
	public function test_rejects_wordpress_below_6_5(): void {
		Functions\expect( 'deactivate_plugins' )->once();
		Functions\expect( 'wp_die' )
			->once()
			->andThrow( new RuntimeException( 'unsupported' ) );

		$this->expectException( RuntimeException::class );
		Activator::validate_environment( '8.1.0', '6.4.9' );
	}

	/**
	 * Flushes rewrite rules after compatible environments pass.
	 */
	public function test_flushes_rules_for_supported_environment(): void {
		Functions\expect( 'flush_rewrite_rules' )->once();

		Activator::validate_environment( '8.1.0', '6.5' );
		$this->assertTrue( true );
	}

	/**
	 * Accepts the network-wide boolean passed by WordPress activation hooks.
	 */
	public function test_activation_hook_uses_runtime_versions_instead_of_network_flag(): void {
		$GLOBALS['wp_version'] = '6.5'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Isolated activation fixture.
		Functions\expect( 'flush_rewrite_rules' )->once();

		Activator::activate( false );
		$this->assertTrue( true );
	}
}
