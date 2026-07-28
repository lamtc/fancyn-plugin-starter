<?php
/**
 * Validation message component tests.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\Tests\UI;

use Brain\Monkey;
use Brain\Monkey\Functions;
use Fancyn\PluginStarter\UI\ValidationMessage;
use PHPUnit\Framework\TestCase;

/**
 * Tests semantic validation feedback.
 */
final class ValidationMessageTest extends TestCase {
	/** Starts WordPress function mocks. */
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
		Functions\when( 'esc_html' )->alias( 'htmlspecialchars' );
		Functions\when( 'esc_attr' )->alias( 'htmlspecialchars' );
		Functions\when( 'wp_kses' )->returnArg( 1 );
	}

	/** Stops WordPress function mocks. */
	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * Maps every supported variant to text and a semantic class.
	 *
	 * @dataProvider variants
	 * @param string $variant Expected variant.
	 * @param string $color   Expected semantic color.
	 */
	public function test_make_renders_supported_variants( string $variant, string $color ): void {
		$html = ValidationMessage::make(
			array(
				'variant' => $variant,
				'message' => 'Feedback',
			)
		);

		$this->assertStringContainsString( 'Feedback', $html );
		$this->assertStringContainsString( 'fps-text-' . $color, $html );
	}

	/**
	 * Variant provider.
	 *
	 * @return array<string, array<int, string>>
	 */
	public static function variants(): array {
		return array(
			'error'   => array( 'error', 'danger' ),
			'warning' => array( 'warning', 'warning' ),
			'success' => array( 'success', 'success' ),
			'info'    => array( 'info', 'info' ),
		);
	}

	/**
	 * Uses an alert role only for submit errors and keeps an icon accessible.
	 */
	public function test_submit_error_has_alert_role_and_filtered_icon(): void {
		$html = ValidationMessage::make(
			array(
				'variant'      => 'error',
				'message'      => 'Save failed',
				'icon'         => '<svg aria-hidden="true"><path d="M0 0" /></svg>',
				'submit_error' => true,
			)
		);

		$this->assertStringContainsString( 'role="alert"', $html );
		$this->assertStringContainsString( '<svg', $html );
	}

	/**
	 * Normalizes unsupported variants to error.
	 */
	public function test_invalid_variant_falls_back_to_error(): void {
		$html = ValidationMessage::make(
			array(
				'variant' => 'made-up',
				'message' => 'Invalid',
			)
		);

		$this->assertStringContainsString( 'fps-text-danger', $html );
	}
}
