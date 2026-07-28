<?php
/**
 * Component showcase coverage tests.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\Tests\Admin;

use PHPUnit\Framework\TestCase;

/**
 * Guards the required v1.0 demonstration surface.
 */
final class ComponentsDemoTest extends TestCase {
	/**
	 * Includes every required PHP component and visual variant.
	 */
	public function test_showcase_covers_required_components_and_variants(): void {
		$source = $this->source();
		foreach ( array( 'Button', 'Input', 'Textarea', 'Select', 'Checkbox', 'Radio', 'SwitchField', 'Label', 'FormField', 'FormSection', 'ValidationMessage' ) as $component ) {
			$this->assertStringContainsString( $component . '::make', $source, 'Missing component: ' . $component );
		}

		foreach ( array( 'primary', 'secondary', 'outline', 'ghost', 'danger', 'link' ) as $variant ) {
			$this->assertMatchesRegularExpression( "/'variant'\\s*=>\\s*'" . preg_quote( $variant, '/' ) . "'/", $source );
		}

		foreach ( array( 'sm', 'md', 'lg', 'icon' ) as $size ) {
			$this->assertMatchesRegularExpression( "/'size'\\s*=>\\s*'" . preg_quote( $size, '/' ) . "'/", $source );
		}
	}

	/**
	 * Includes progressive enhancement and WordPress API examples.
	 */
	public function test_showcase_covers_interactions_and_wordpress_apis(): void {
		$source = $this->source();
		foreach ( array( 'data-enhanced-select', 'data-color-picker', 'data-media-field', 'wp_editor(', 'x-data="modal"', 'x-data="tabs', 'x-data="dropdown"' ) as $contract ) {
			$this->assertStringContainsString( $contract, $source, 'Missing showcase contract: ' . $contract );
		}
	}

	/**
	 * Requires the scoped responsive page shell.
	 */
	public function test_showcase_uses_scoped_responsive_shell(): void {
		$source = $this->source();
		$this->assertStringContainsString( 'id="fancyn-plugin-starter-app"', $source );
		$this->assertStringContainsString( 'fps-plugin-root', $source );
		$this->assertStringContainsString( 'md:fps-', $source );
		$this->assertStringContainsString( 'lg:fps-', $source );
	}

	/**
	 * Reads the local showcase template.
	 */
	private function source(): string {
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Reads a local source fixture.
		$source = file_get_contents( dirname( __DIR__, 3 ) . '/templates/admin/components-demo.php' );
		$this->assertIsString( $source );
		return $source;
	}
}
