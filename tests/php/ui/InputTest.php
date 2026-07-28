<?php
/**
 * Input component tests.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\Tests\UI;

use Fancyn\PluginStarter\UI\Input;
use Fancyn\PluginStarter\Tests\Support\WordPressTestCase;

/**
 * Tests native input rendering and field composition.
 */
final class InputTest extends WordPressTestCase {
	/** Starts WordPress function mocks. */
	protected function setUp(): void {
		parent::setUp();
		self::stubCallback( 'esc_html', static fn ( mixed $value ): string => htmlspecialchars( (string) $value ) );
		self::stubCallback( 'esc_html__', static fn ( mixed $value ): mixed => $value );
		self::stubCallback( 'esc_attr', static fn ( mixed $value ): string => htmlspecialchars( (string) $value ) );
		self::stubCallback( 'wp_kses', static fn ( mixed $value ): mixed => $value );
		self::stubCallback( 'sanitize_key', static fn ( string $value ): string => strtolower( preg_replace( '/[^a-zA-Z0-9_-]/', '-', $value ) ?? '' ) );
	}

	/**
	 * Renders supported types with native state attributes.
	 */
	public function test_make_renders_native_input_states(): void {
		$html = Input::make(
			array(
				'type'     => 'email',
				'name'     => 'company_email',
				'value'    => 'hello@example.test',
				'label'    => 'Email',
				'required' => true,
				'readonly' => true,
				'error'    => 'Invalid email',
			)
		);

		$this->assertStringContainsString( 'type="email"', $html );
		$this->assertStringContainsString( ' required', $html );
		$this->assertStringContainsString( ' readonly', $html );
		$this->assertStringContainsString( 'aria-invalid="true"', $html );
		$this->assertStringContainsString( 'fps-border-danger', $html );
	}

	/**
	 * Renders hidden inputs without a visual field wrapper.
	 */
	public function test_hidden_input_has_no_visual_wrapper(): void {
		$html = Input::make(
			array(
				'type'  => 'hidden',
				'name'  => 'attachment_id',
				'value' => '42',
			)
		);

		$this->assertStringStartsWith( '<input', $html );
		$this->assertStringNotContainsString( '<label', $html );
	}

	/**
	 * Normalizes unsupported input types to text.
	 */
	public function test_invalid_type_falls_back_to_text(): void {
		$this->assertStringContainsString(
			'type="text"',
			Input::make(
				array(
					'type' => 'file',
					'name' => 'unsafe',
				)
			)
		);
	}

	/**
	 * Preserves allowlisted custom attributes and escapes their values.
	 */
	public function test_make_supports_safe_additional_attributes(): void {
		$html = Input::make(
			array(
				'name'       => 'search',
				'attributes' => array(
					'data-source' => 'a"b',
					'onclick'     => 'bad',
				),
			)
		);

		$this->assertStringContainsString( 'data-source="a&quot;b"', $html );
		$this->assertStringNotContainsString( 'onclick', $html );
	}
}
