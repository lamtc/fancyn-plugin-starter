<?php
/**
 * Input component tests.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\Tests\UI;

use Brain\Monkey;
use Brain\Monkey\Functions;
use Fancyn\PluginStarter\UI\Input;
use PHPUnit\Framework\TestCase;

/**
 * Tests native input rendering and field composition.
 */
final class InputTest extends TestCase {
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
