<?php
/**
 * Button component tests.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\Tests\UI;

use Brain\Monkey;
use Brain\Monkey\Functions;
use Fancyn\PluginStarter\UI\Button;
use PHPUnit\Framework\TestCase;

/**
 * Tests button variants, sizes, and states.
 */
final class ButtonTest extends TestCase {
	/** Starts WordPress function mocks. */
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
		Functions\when( 'esc_html' )->alias( 'htmlspecialchars' );
		Functions\when( 'esc_attr' )->alias( 'htmlspecialchars' );
		Functions\when( 'esc_url' )->returnArg( 1 );
		Functions\when( 'wp_kses' )->returnArg( 1 );
	}

	/** Stops WordPress function mocks. */
	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * Maps supported variants and sizes through fixed class names.
	 *
	 * @dataProvider variants_and_sizes
	 * @param string $variant Variant name.
	 * @param string $size    Size name.
	 */
	public function test_make_renders_supported_variant_and_size( string $variant, string $size ): void {
		$html = Button::make(
			array(
				'variant' => $variant,
				'size'    => $size,
				'text'    => 'Action',
			)
		);

		$this->assertStringContainsString( 'data-variant="' . $variant . '"', $html );
		$this->assertStringContainsString( 'data-size="' . $size . '"', $html );
	}

	/**
	 * Supported variant and size pairs.
	 *
	 * @return array<string, array<int, string>>
	 */
	public static function variants_and_sizes(): array {
		return array(
			'primary-sm'   => array( 'primary', 'sm' ),
			'secondary-md' => array( 'secondary', 'md' ),
			'outline-lg'   => array( 'outline', 'lg' ),
			'ghost-icon'   => array( 'ghost', 'icon' ),
			'danger-md'    => array( 'danger', 'md' ),
			'link-md'      => array( 'link', 'md' ),
		);
	}

	/**
	 * Keeps loading buttons disabled, busy, and width-stable.
	 */
	public function test_loading_state_preserves_text_width(): void {
		$html = Button::make(
			array(
				'text'    => 'Save changes',
				'loading' => true,
			)
		);

		$this->assertStringContainsString( ' disabled', $html );
		$this->assertStringContainsString( 'aria-busy="true"', $html );
		$this->assertStringContainsString( 'fps-opacity-0', $html );
		$this->assertStringContainsString( 'Save changes', $html );
	}

	/**
	 * Renders links natively and filters their inline icon.
	 */
	public function test_link_button_uses_anchor_and_safe_icon_slot(): void {
		$html = Button::make(
			array(
				'href' => 'https://example.test',
				'text' => 'Read more',
				'icon' => '<svg aria-hidden="true"><path d="M0 0" /></svg>',
			)
		);

		$this->assertStringStartsWith( '<a', $html );
		$this->assertStringContainsString( '<svg', $html );
	}

	/**
	 * Requires an accessible name for icon-only buttons.
	 */
	public function test_icon_only_button_receives_accessible_label(): void {
		$html = Button::make(
			array(
				'size'       => 'icon',
				'icon'       => '<svg></svg>',
				'aria-label' => 'Open menu',
			)
		);

		$this->assertStringContainsString( 'aria-label="Open menu"', $html );
	}
}
