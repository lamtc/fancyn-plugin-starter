<?php
/**
 * WordPress API markup tests.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\Tests\Admin;

use PHPUnit\Framework\TestCase;

/** Verifies settings template contracts consumed by JavaScript. */
final class WordPressApiMarkupTest extends TestCase {
	/** Requires attachment IDs, preview controls, color marker, and native editor. */
	public function test_settings_template_contains_wordpress_api_contracts(): void {
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Reads a local source fixture.
		$source = file_get_contents( dirname( __DIR__, 3 ) . '/templates/admin/settings.php' );
		$this->assertIsString( $source );
		$this->assertStringContainsString( 'data-media-field', $source );
		$this->assertStringContainsString( 'data-media-id', $source );
		$this->assertStringContainsString( 'data-media-preview', $source );
		$this->assertStringContainsString( 'data-color-picker', $source );
		$this->assertStringContainsString( 'wp_editor(', $source );
	}
}
