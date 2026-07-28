<?php
/**
 * Checkbox component tests.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\Tests\UI;

use Fancyn\PluginStarter\UI\Checkbox;
use Fancyn\PluginStarter\Tests\Support\WordPressTestCase;

/** Tests native checkbox states. */
final class CheckboxTest extends WordPressTestCase {
	/** Starts mocks. */
	protected function setUp(): void {
		parent::setUp();
		self::stubCallback( 'esc_html', static fn ( mixed $value ): string => htmlspecialchars( (string) $value ) );
		self::stubCallback( 'esc_attr', static fn ( mixed $value ): string => htmlspecialchars( (string) $value ) ); }

	/** Renders label, description, checked, disabled, and indeterminate states. */
	public function test_make_renders_native_states(): void {
		$html = Checkbox::make(
			array(
				'name'          => 'enabled',
				'value'         => '1',
				'label'         => 'Enabled',
				'description'   => 'Allow reports.',
				'checked'       => true,
				'disabled'      => true,
				'indeterminate' => true,
			)
		);
		$this->assertStringContainsString( 'type="checkbox"', $html );
		$this->assertStringContainsString( ' checked', $html );
		$this->assertStringContainsString( ' disabled', $html );
		$this->assertStringContainsString( 'data-indeterminate="1"', $html );
		$this->assertStringContainsString( 'Allow reports.', $html );
	}

	/** Echoes make output. */
	public function test_render_echoes_make_output(): void {
		$args = array(
			'name'  => 'enabled',
			'label' => 'Enabled',
		);
		$this->expectOutputString( Checkbox::make( $args ) );
		Checkbox::render( $args );
	}
}
