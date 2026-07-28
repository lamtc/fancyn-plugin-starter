<?php
/**
 * Radio component tests.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\Tests\UI;

use Brain\Monkey;
use Brain\Monkey\Functions;
use Fancyn\PluginStarter\UI\Radio;
use PHPUnit\Framework\TestCase;

/** Tests native radio and radio-group rendering. */
final class RadioTest extends TestCase {
	/** Starts mocks. */
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
		Functions\when( 'esc_html' )->alias( 'htmlspecialchars' );
		Functions\when( 'esc_attr' )->alias( 'htmlspecialchars' ); }
	/** Stops mocks. */
	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown(); }

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
