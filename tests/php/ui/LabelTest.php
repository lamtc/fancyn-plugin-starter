<?php
/**
 * Label component tests.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\Tests\UI;

use Brain\Monkey;
use Brain\Monkey\Functions;
use Fancyn\PluginStarter\UI\Label;
use PHPUnit\Framework\TestCase;

/**
 * Tests accessible label rendering.
 */
final class LabelTest extends TestCase {
	/** Starts WordPress function mocks. */
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
		Functions\when( 'esc_html' )->alias( 'htmlspecialchars' );
		Functions\when( 'esc_attr' )->alias( 'htmlspecialchars' );
		Functions\when( 'esc_html__' )->returnArg( 1 );
	}

	/** Stops WordPress function mocks. */
	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * Associates text with a control and exposes required state in text.
	 */
	public function test_make_renders_for_required_and_optional_text(): void {
		$html = Label::make(
			array(
				'for'      => 'company-name',
				'text'     => 'Company name',
				'required' => true,
				'optional' => 'Public profile',
			)
		);

		$this->assertStringContainsString( 'for="company-name"', $html );
		$this->assertStringContainsString( 'Company name', $html );
		$this->assertStringContainsString( 'aria-hidden="true"', $html );
		$this->assertStringContainsString( 'Public profile', $html );
	}

	/**
	 * Echoes exactly the same markup returned by make.
	 */
	public function test_render_echoes_make_output(): void {
		$args = array(
			'for'  => 'email',
			'text' => 'Email',
		);

		$this->expectOutputString( Label::make( $args ) );
		Label::render( $args );
	}
}
