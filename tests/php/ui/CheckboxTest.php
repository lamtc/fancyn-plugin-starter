<?php
/**
 * Checkbox component tests.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\Tests\UI;

use Brain\Monkey;
use Brain\Monkey\Functions;
use Fancyn\PluginStarter\UI\Checkbox;
use PHPUnit\Framework\TestCase;

/** Tests native checkbox states. */
final class CheckboxTest extends TestCase {
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
