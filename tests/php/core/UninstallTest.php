<?php
/**
 * Uninstall contract tests.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\Tests\Core;

use PHPUnit\Framework\TestCase;

/**
 * Tests the uninstall script's destructive-operation guard.
 */
final class UninstallTest extends TestCase {
	/**
	 * Ensures uninstall is guarded and deletes only the starter option.
	 */
	public function test_uninstall_script_has_a_guard_and_one_prefixed_delete(): void {
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Reads a local test fixture.
		$source = file_get_contents( dirname( __DIR__, 3 ) . '/uninstall.php' );

		$this->assertIsString( $source );
		$this->assertStringContainsString( 'WP_UNINSTALL_PLUGIN', $source );
		$this->assertSame( 1, substr_count( $source, 'delete_option(' ) );
		$this->assertStringContainsString( "delete_option( 'fancyn_plugin_starter_settings' )", $source );
	}
}
