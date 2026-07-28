<?php
/**
 * Textarea component tests.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\Tests\UI;

use Brain\Monkey;
use Brain\Monkey\Functions;
use Fancyn\PluginStarter\UI\Textarea;
use PHPUnit\Framework\TestCase;

/**
 * Tests textarea configuration and progressive-enhancement data.
 */
final class TextareaTest extends TestCase {
	/** Starts WordPress function mocks. */
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
		Functions\when( 'esc_html' )->alias( 'htmlspecialchars' );
		Functions\when( 'esc_html__' )->returnArg( 1 );
		Functions\when( 'esc_attr' )->alias( 'htmlspecialchars' );
		Functions\when( 'wp_kses' )->returnArg( 1 );
		Functions\when( 'sanitize_key' )->alias( static fn ( string $value ): string => strtolower( preg_replace( '/[^a-zA-Z0-9_-]/', '-', $value ) ?? '' ) );
	}

	/** Stops WordPress function mocks. */
	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * Renders rows, maxlength, counter, auto-resize, and escaped content.
	 */
	public function test_make_renders_textarea_features(): void {
		$html = Textarea::make(
			array(
				'name'              => 'summary',
				'label'             => 'Summary',
				'value'             => '<script>alert(1)</script>',
				'rows'              => 6,
				'maxlength'         => 120,
				'character_counter' => true,
				'auto_resize'       => true,
			)
		);

		$this->assertStringContainsString( 'rows="6"', $html );
		$this->assertStringContainsString( 'maxlength="120"', $html );
		$this->assertStringContainsString( 'data-character-counter="1"', $html );
		$this->assertStringContainsString( 'data-auto-resize="1"', $html );
		$this->assertStringNotContainsString( '<script>', $html );
		$this->assertStringContainsString( '25 / 120', $html );
	}

	/**
	 * Applies disabled, readonly, and error states.
	 */
	public function test_make_renders_native_and_error_states(): void {
		$html = Textarea::make(
			array(
				'name'     => 'notes',
				'disabled' => true,
				'readonly' => true,
				'error'    => 'Invalid notes',
			)
		);

		$this->assertStringContainsString( ' disabled', $html );
		$this->assertStringContainsString( ' readonly', $html );
		$this->assertStringContainsString( 'fps-border-danger', $html );
	}
}
