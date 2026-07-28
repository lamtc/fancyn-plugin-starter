<?php
/**
 * Validation message component tests.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\Tests\UI;

use Fancyn\PluginStarter\UI\ValidationMessage;
use Fancyn\PluginStarter\Tests\Support\WordPressTestCase;

/**
 * Tests semantic validation feedback.
 */
final class ValidationMessageTest extends WordPressTestCase {
	/** Starts WordPress function mocks. */
	protected function setUp(): void {
		parent::setUp();
		self::stubCallback( 'esc_html', static fn ( mixed $value ): string => htmlspecialchars( (string) $value ) );
		self::stubCallback( 'esc_attr', static fn ( mixed $value ): string => htmlspecialchars( (string) $value ) );
		self::stubCallback( 'wp_kses', static fn ( mixed $value ): mixed => $value );
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
