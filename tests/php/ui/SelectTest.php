<?php
/**
 * Select component tests.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\Tests\UI;

use Brain\Monkey;
use Brain\Monkey\Functions;
use Fancyn\PluginStarter\UI\Select;
use PHPUnit\Framework\TestCase;

/** Tests native and enhanced select rendering. */
final class SelectTest extends TestCase {
	/** Starts mocks. */
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
		Functions\when( 'esc_html' )->alias( 'htmlspecialchars' );
		Functions\when( 'esc_html__' )->returnArg( 1 );
		Functions\when( 'esc_attr' )->alias( 'htmlspecialchars' );
		Functions\when( 'wp_kses' )->returnArg( 1 );
		Functions\when( 'sanitize_key' )->alias( static fn ( string $value ): string => strtolower( preg_replace( '/[^a-zA-Z0-9_-]/', '-', $value ) ?? '' ) );
	}
	/** Stops mocks. */
	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown(); }

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
