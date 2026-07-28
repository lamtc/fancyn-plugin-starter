<?php
/**
 * Radio component tests.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\Tests\UI;

use Fancyn\PluginStarter\UI\Radio;
use Fancyn\PluginStarter\Tests\Support\WordPressTestCase;

/** Tests native radio and radio-group rendering. */
final class RadioTest extends WordPressTestCase {
	/** Starts mocks. */
	protected function setUp(): void {
		parent::setUp();
		self::stubCallback( 'esc_html', static fn ( mixed $value ): string => htmlspecialchars( (string) $value ) );
		self::stubCallback( 'esc_attr', static fn ( mixed $value ): string => htmlspecialchars( (string) $value ) ); }

	/** Renders a single native radio. */
	public function test_make_renders_single_radio(): void {
		$html = Radio::make(
			array(
				'name'    => 'mode',
				'value'   => 'simple',
				'label'   => 'Simple',
				'checked' => true,
			)
		);
		$this->assertStringContainsString( 'type="radio"', $html );
		$this->assertStringContainsString( ' checked', $html );
	}

	/** Renders a labelled group with one selected option. */
	public function test_group_renders_options_and_selected_value(): void {
		$html = Radio::make(
			array(
				'name'    => 'mode',
				'label'   => 'Mode',
				'value'   => 'advanced',
				'options' => array(
					'simple'   => 'Simple',
					'advanced' => 'Advanced',
				),
			)
		);
		$this->assertStringContainsString( '<fieldset', $html );
		$this->assertSame( 2, substr_count( $html, 'type="radio"' ) );
		$this->assertSame( 1, substr_count( $html, ' checked' ) );
	}
}
