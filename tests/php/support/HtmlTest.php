<?php
/**
 * HTML helper tests.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\Tests\Support;

use Fancyn\PluginStarter\Support\Html;
use Fancyn\PluginStarter\Tests\Support\WordPressTestCase;

/**
 * Tests safe HTML attribute serialization.
 */
final class HtmlTest extends WordPressTestCase {
	/** Starts WordPress function mocks. */
	protected function setUp(): void {
		parent::setUp();
		self::stubCallback(
			'esc_attr',
			static fn ( mixed $value ): string => htmlspecialchars( (string) $value, ENT_QUOTES, 'UTF-8' )
		);
	}

	/**
	 * Flattens classes, boolean attributes, and safe data/ARIA attributes.
	 */
	public function test_attributes_serializes_supported_values(): void {
		$html = Html::attributes(
			array(
				'id'           => 'company-name',
				'class'        => array( 'fps-block', '', false, 'fps-rounded' ),
				'required'     => true,
				'disabled'     => false,
				'data-counter' => '10',
				'aria-invalid' => 'true',
			)
		);

		$this->assertSame(
			' id="company-name" class="fps-block fps-rounded" required data-counter="10" aria-invalid="true"',
			$html
		);
	}

	/**
	 * Escapes attribute values for their HTML context.
	 */
	public function test_attributes_escapes_values(): void {
		$this->assertSame( ' title="&quot; onclick=&quot;alert(1)"', Html::attributes( array( 'title' => '" onclick="alert(1)' ) ) );
	}

	/**
	 * Rejects handlers, malformed names, and unknown attributes.
	 */
	public function test_attributes_discards_unsafe_names(): void {
		$html = Html::attributes(
			array(
				'onclick'       => 'alert(1)',
				'bad attribute' => 'value',
				'unknown'       => 'value',
			)
		);

		$this->assertSame( '', $html );
	}

	/**
	 * Allows the explicit Alpine attribute contract without allowing native handlers.
	 */
	public function test_attributes_allows_explicit_alpine_attributes(): void {
		$html = Html::attributes(
			array(
				'@click'         => 'toggle()',
				':aria-expanded' => 'isOpen',
				'onmouseover'    => 'bad',
			)
		);

		$this->assertStringContainsString( '@click="toggle()"', $html );
		$this->assertStringContainsString( ':aria-expanded="isOpen"', $html );
		$this->assertStringNotContainsString( 'onmouseover', $html );
	}

	/**
	 * Creates a deterministic class list from nested values.
	 */
	public function test_class_names_flattens_and_deduplicates_values(): void {
		$this->assertSame(
			'fps-block fps-rounded fps-active',
			Html::class_names(
				array(
					'fps-block',
					array( 'fps-rounded', 'fps-block' ),
					'fps-active' => true,
					'fps-hidden' => false,
				)
			)
		);
	}
}
