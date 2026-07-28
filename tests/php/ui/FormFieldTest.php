<?php
/**
 * Form field component tests.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\Tests\UI;

use Fancyn\PluginStarter\UI\FormField;
use Fancyn\PluginStarter\Tests\Support\WordPressTestCase;

/**
 * Tests accessible field composition.
 */
final class FormFieldTest extends WordPressTestCase {
	/** Starts WordPress function mocks. */
	protected function setUp(): void {
		parent::setUp();
		self::stubCallback( 'esc_html', static fn ( mixed $value ): string => htmlspecialchars( (string) $value ) );
		self::stubCallback( 'esc_attr', static fn ( mixed $value ): string => htmlspecialchars( (string) $value ) );
		self::stubCallback( 'esc_html__', static fn ( mixed $value ): mixed => $value );
		self::stubCallback( 'wp_kses', static fn ( mixed $value ): mixed => $value );
		self::stubCallback(
			'sanitize_key',
			static fn ( string $value ): string => strtolower( preg_replace( '/[^a-zA-Z0-9_-]/', '-', $value ) ?? '' )
		);
	}

	/**
	 * Links label, control, description, and error in display order.
	 */
	public function test_make_composes_accessible_relationships(): void {
		$received = array();
		$html     = FormField::make(
			array(
				'id'          => 'company-name',
				'label'       => 'Company name',
				'description' => 'Shown on reports.',
				'error'       => 'Company name is required.',
				'control'     => static function ( array $attributes ) use ( &$received ): string {
					$received = $attributes;
					return '<input id="company-name">';
				},
			)
		);

		$this->assertSame( 'company-name', $received['id'] );
		$this->assertSame( 'company-name-description company-name-error', $received['aria-describedby'] );
		$this->assertSame( 'true', $received['aria-invalid'] );
		$this->assertLessThan( strpos( $html, '<input' ), strpos( $html, '<label' ) );
		$this->assertLessThan( strpos( $html, 'Shown on reports.' ), strpos( $html, '<input' ) );
		$this->assertLessThan( strpos( $html, 'Company name is required.' ), strpos( $html, 'Shown on reports.' ) );
	}

	/**
	 * Generates a stable safe ID when one is not provided.
	 */
	public function test_make_generates_stable_id_from_name(): void {
		$control = static fn ( array $attributes ): string => '<span>' . $attributes['id'] . '</span>';
		$args    = array(
			'name'    => 'Company Name',
			'label'   => 'Company name',
			'control' => $control,
		);

		$this->assertStringContainsString( '<span>company-name</span>', FormField::make( $args ) );
	}

	/**
	 * Escapes description and error content.
	 */
	public function test_make_escapes_feedback_text(): void {
		$html = FormField::make(
			array(
				'name'        => 'safe',
				'label'       => 'Safe',
				'description' => '<script>alert(1)</script>',
				'error'       => '<img src=x onerror=alert(1)>',
				'control'     => static fn (): string => '<input>',
			)
		);

		$this->assertStringNotContainsString( '<script>', $html );
		$this->assertStringNotContainsString( '<img', $html );
	}
}
