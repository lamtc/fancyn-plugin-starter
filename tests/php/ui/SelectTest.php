<?php
/**
 * Select component tests.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\Tests\UI;

use Fancyn\PluginStarter\UI\Select;
use Fancyn\PluginStarter\Tests\Support\WordPressTestCase;

/** Tests native and enhanced select rendering. */
final class SelectTest extends WordPressTestCase {
	/** Starts mocks. */
	protected function setUp(): void {
		parent::setUp();
		self::stubCallback( 'esc_html', static fn ( mixed $value ): string => htmlspecialchars( (string) $value ) );
		self::stubCallback( 'esc_html__', static fn ( mixed $value ): mixed => $value );
		self::stubCallback( 'esc_attr', static fn ( mixed $value ): string => htmlspecialchars( (string) $value ) );
		self::stubCallback( 'wp_kses', static fn ( mixed $value ): mixed => $value );
		self::stubCallback( 'sanitize_key', static fn ( string $value ): string => strtolower( preg_replace( '/[^a-zA-Z0-9_-]/', '-', $value ) ?? '' ) );
	}

	/** Renders placeholder, scalar options, optgroups, and selection. */
	public function test_make_renders_native_option_structures(): void {
		$html = Select::make(
			array(
				'name'        => 'region',
				'label'       => 'Region',
				'placeholder' => 'Choose region',
				'value'       => 'vn',
				'options'     => array(
					'vn'   => 'Vietnam',
					'Asia' => array(
						'sg' => 'Singapore',
						'jp' => 'Japan',
					),
				),
			)
		);
		$this->assertStringContainsString( '<option value="">Choose region</option>', $html );
		$this->assertStringContainsString( '<option value="vn" selected>Vietnam</option>', $html );
		$this->assertStringContainsString( '<optgroup label="Asia">', $html );
	}

	/** Supports multiple values and enhanced-select configuration. */
	public function test_make_renders_multiple_enhanced_contract(): void {
		$html = Select::make(
			array(
				'name'       => 'services',
				'multiple'   => true,
				'value'      => array( 'seo', 'ads' ),
				'options'    => array(
					'seo' => 'SEO',
					'ads' => 'Ads',
				),
				'enhanced'   => true,
				'searchable' => true,
				'clearable'  => true,
				'create'     => true,
			)
		);
		$this->assertStringContainsString( 'name="services[]"', $html );
		$this->assertStringContainsString( ' multiple', $html );
		$this->assertStringContainsString( 'data-enhanced-select="1"', $html );
		$this->assertSame( 2, substr_count( $html, ' selected' ) );
	}

	/** Leaves ordinary native selects untouched. */
	public function test_native_select_has_no_enhancement_marker(): void {
		$this->assertStringNotContainsString(
			'data-enhanced-select',
			Select::make(
				array(
					'name'    => 'small',
					'options' => array( 'a' => 'A' ),
				)
			)
		);
	}
}
