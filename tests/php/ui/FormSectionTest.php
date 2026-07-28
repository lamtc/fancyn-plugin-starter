<?php
/**
 * Form section component tests.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\Tests\UI;

use Fancyn\PluginStarter\UI\FormSection;
use Fancyn\PluginStarter\Tests\Support\WordPressTestCase;

/**
 * Tests semantic section variants and slots.
 */
final class FormSectionTest extends WordPressTestCase {
	/** Starts WordPress function mocks. */
	protected function setUp(): void {
		parent::setUp();
		self::stubCallback( 'esc_html', static fn ( mixed $value ): string => htmlspecialchars( (string) $value ) );
		self::stubCallback( 'esc_attr', static fn ( mixed $value ): string => htmlspecialchars( (string) $value ) );
		self::stubCallback( 'wp_kses', static fn ( mixed $value ): mixed => $value );
	}

	/**
	 * Maps every supported visual variant through fixed classes.
	 *
	 * @dataProvider variants
	 * @param string $variant Section variant.
	 */
	public function test_make_renders_supported_variant( string $variant ): void {
		$html = FormSection::make(
			array(
				'variant' => $variant,
				'title'   => 'General',
				'content' => '<input>',
			)
		);

		$this->assertStringContainsString( 'data-variant="' . $variant . '"', $html );
		$this->assertStringContainsString( '<h2', $html );
	}

	/**
	 * Returns section variants.
	 *
	 * @return array<string, array<int, string>> Variant values.
	 */
	public static function variants(): array {
		return array(
			'card'    => array( 'card' ),
			'flat'    => array( 'flat' ),
			'divided' => array( 'divided' ),
			'compact' => array( 'compact' ),
		);
	}

	/**
	 * Renders escaped text around content and filtered actions.
	 */
	public function test_make_renders_title_description_content_and_actions(): void {
		$html = FormSection::make(
			array(
				'title'       => '<script>General</script>',
				'description' => '<img src=x>Configuration.',
				'content'     => '<input name="company">',
				'actions'     => '<button type="submit">Save</button>',
			)
		);

		$this->assertStringNotContainsString( '<script>', $html );
		$this->assertStringNotContainsString( '<img', $html );
		$this->assertStringContainsString( '<input name="company">', $html );
		$this->assertStringContainsString( '<button type="submit">Save</button>', $html );
	}
}
