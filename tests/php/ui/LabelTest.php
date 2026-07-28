<?php
/**
 * Label component tests.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\Tests\UI;

use Fancyn\PluginStarter\UI\Label;
use Fancyn\PluginStarter\Tests\Support\WordPressTestCase;

/**
 * Tests accessible label rendering.
 */
final class LabelTest extends WordPressTestCase {
	/** Starts WordPress function mocks. */
	protected function setUp(): void {
		parent::setUp();
		self::stubCallback( 'esc_html', static fn ( mixed $value ): string => htmlspecialchars( (string) $value ) );
		self::stubCallback( 'esc_attr', static fn ( mixed $value ): string => htmlspecialchars( (string) $value ) );
		self::stubCallback( 'esc_html__', static fn ( mixed $value ): mixed => $value );
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
